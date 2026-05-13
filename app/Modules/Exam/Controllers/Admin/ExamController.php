<?php

namespace App\Modules\Exam\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\AcademicSession;
use App\Modules\Academic\Models\Program;
use App\Modules\Academic\Models\Subject;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\CMS\Models\Download;
use App\Modules\CMS\Models\Page;
use App\Modules\Department\Models\Department;
use App\Modules\Exam\Models\Exam;
use App\Modules\Exam\Models\ExamSubjectMarkingScheme;
use App\Modules\Exam\Models\Mark;
use App\Modules\Student\Models\Student;
use App\Modules\Teacher\Models\Teacher;
use App\Modules\User\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ExamController extends Controller
{
    private array $runningSemestersCache = [];
    /** @var array<int,float|null> Cached markPercentage results keyed by mark ID */
    private array $markPercentageCache = [];
    /** @var array<int,bool> Cached markIsPassed results keyed by mark ID */
    private array $markIsPassedCache = [];
    /** @var array<int,string> Cached exam category keyed by exam ID */
    private array $examCategoryCache = [];
    /** @var array<string,array<string,float>> Cached per exam+subject marking schemes keyed as "examId:subjectId" */
    private array $examSubjectMarkingSchemeCache = [];
    /** Cached departmentPerformanceRows to avoid computing it twice per request */
    private ?Collection $deptPerfCache = null;
    /** @var array<int,Collection> Cached exam student collections keyed by exam ID */
    private array $examStudentsCache = [];

    public function index(Request $request)
    {
        $filters = $this->resolveFilters($request);
        $examQuery = $this->baseExamQuery($filters);

        $allExams = (clone $examQuery)->get();

        $exams = (clone $examQuery)
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();
        $exams->setCollection($this->decorateExamRows($exams->getCollection()));

        return view('admin.exams.index', [
            'filters' => $filters,
            'exams' => $exams,
            'kpis' => $this->buildKpis($allExams),
            'sessions' => AcademicSession::query()->orderByDesc('start_date')->get(['id', 'name', 'name_bs']),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name', 'code']),
            'programs' => Program::query()->with('department:id,name,code')->orderBy('name')->get(['id', 'department_id', 'name', 'code']),
            'typeOptions' => $this->typeOptions(),
            'categoryOptions' => $this->categoryOptions(),
            'statusOptions' => $this->statusOptions(),
            'semesterOptions' => range(1, 8),
            'currentSession' => AcademicSession::current(),
        ]);
    }

    public function create()
    {
        return view('admin.exams.create', $this->formPayload());
    }

    public function store(Request $request)
    {
        $data = $this->validateExam($request);

        if (($data['category'] ?? 'ctevt_final') === 'monthly_assessment' && empty($data['assessment_number'])) {
            throw ValidationException::withMessages([
                'assessment_number' => 'Assessment number is required for monthly assessment exams.',
            ]);
        }

        if (($data['category'] ?? 'ctevt_final') !== 'monthly_assessment') {
            $data['assessment_number'] = null;
        }

        $data['marks_open'] = $request->boolean('marks_open');
        $data['is_published'] = $data['status'] === 'results_published';
        $data['published_at'] = $data['is_published'] ? now() : null;

        $programIds = collect($request->input('program_ids', []))->filter()->map(fn ($value) => (int) $value)->values();
        $semesterSelection = (string) $data['semester'];
        $sessionId = (int) $data['academic_session_id'];
        unset($data['program_ids'], $data['semester']);

        $exam = DB::transaction(function () use ($data, $programIds, $semesterSelection, $sessionId) {
            $exam = Exam::create($data);
            $this->syncProgramAssignments($exam, $programIds, $semesterSelection, $sessionId);

            return $exam;
        });
        app(\App\Services\PortalNotificationService::class)->dispatchExamPublished($exam);

        return redirect()->route('admin.exams.show', $exam)->with('success', 'Exam created successfully.');
    }

    public function show(Exam $exam)
    {
        $activeTab = (string) request('tab', 'overview');
        // Tabs on this page switch client-side (Alpine), so uploaded marks must be
        // prepared on initial response even when the URL has no ?tab=marks.
        $needsUploadedMarks = true;

        $exam->load([
            'academicSession:id,name,name_bs',
            'department:id,name,code',
            'markingSchemes',
            'programs.department:id,name,code',
            'programs.subjects.teachers.user:id,name,avatar',
            'marks:id,exam_id,student_id,subject_id,teacher_id,semester,status,is_absent,is_withheld,is_delayed,delay_reason,internal_theory_marks,external_theory_marks,internal_practical_marks,external_practical_marks,assessment_attendance_percent,assessment_full_marks,assessment_pass_marks,assessment_obtained_marks,remarks,updated_at',
            'marks.subject:id,name,code,type,semester,program_id,full_marks_internal_theory,full_marks_external_theory,full_marks_internal_practical,full_marks_external_practical,pass_marks_internal_theory,pass_marks_external_theory,pass_marks_internal_practical,pass_marks_external_practical',
        ]);

        if ($needsUploadedMarks) {
            $exam->load([
                'marks.student.user:id,name,avatar',
                'marks.student.program:id,name,code',
                'marks.student.department:id,name,code',
                'marks.teacher.user:id,name,avatar',
            ]);
        }

        $this->primeExamMarkingSchemeCache($exam);

        $marksBySubject = $exam->marks->groupBy('subject_id');
        $marksByStudent = $exam->marks->groupBy('student_id');

        $subjectRows = $this->buildSubjectRows($exam, $marksBySubject);
        $subjectRowsPaginator = $this->paginateCollection($subjectRows, 12, 'subject_page');
        $studentRows = $this->buildStudentResults($exam, $subjectRows, $marksByStudent);
        $uploadedMarkGroups = $needsUploadedMarks ? $this->buildUploadedMarkGroups($exam) : collect();
        $allMarksCount = $exam->marks->count();
        $filledMarksCount = $exam->marks->filter(fn (Mark $mark) => $this->markIsFilled($mark))->count();
        $unfilledMarksCount = max(0, $allMarksCount - $filledMarksCount);
        $delayedMarksCount = $exam->marks->where('is_delayed', true)->count();

        return view('admin.exams.show', [
            'exam' => $exam,
            'subjectRows' => $subjectRowsPaginator,
            'markRows' => $subjectRowsPaginator,
            'verificationRows' => $subjectRowsPaginator,
            'studentRows' => $studentRows,
            'uploadedMarkGroups' => $uploadedMarkGroups,
            'allMarksCount' => $allMarksCount,
            'filledMarksCount' => $filledMarksCount,
            'unfilledMarksCount' => $unfilledMarksCount,
            'delayedMarksCount' => $delayedMarksCount,
            'summary' => $this->buildExamSummary($exam, $subjectRows, $studentRows),
            'topPerformers' => $studentRows->sortByDesc('percentage')->take(5)->values(),
            'published' => $exam->is_published || $exam->status === 'results_published',
        ]);
    }

    public function editMark(Exam $exam, Mark $mark)
    {
        abort_unless((int) $mark->exam_id === (int) $exam->id, 404);

        $mark->loadMissing([
            'exam.academicSession:id,name,name_bs',
            'exam.department:id,name,code',
            'student.user:id,name,avatar',
            'student.program:id,name,code',
            'student.department:id,name,code',
            'subject:id,name,code,type,semester,program_id,full_marks_internal_theory,full_marks_external_theory,full_marks_internal_practical,full_marks_external_practical,pass_marks_internal_theory,pass_marks_external_theory,pass_marks_internal_practical,pass_marks_external_practical',
            'teacher.user:id,name,avatar',
        ]);

        return view('admin.exams.mark-edit', [
            'exam' => $exam,
            'mark' => $mark,
        ]);
    }

    public function updateMark(Request $request, Exam $exam, Mark $mark)
    {
        abort_unless((int) $mark->exam_id === (int) $exam->id, 404);

        $data = $request->validate([
            'internal_theory_marks' => ['nullable', 'numeric', 'min:0'],
            'external_theory_marks' => ['nullable', 'numeric', 'min:0'],
            'internal_practical_marks' => ['nullable', 'numeric', 'min:0'],
            'external_practical_marks' => ['nullable', 'numeric', 'min:0'],
            'assessment_attendance_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'assessment_full_marks' => ['nullable', 'numeric', 'min:0'],
            'assessment_pass_marks' => ['nullable', 'numeric', 'min:0'],
            'assessment_obtained_marks' => ['nullable', 'numeric', 'min:0'],
            // Attendance tracking
            'exam_attendance_date' => ['nullable', 'date'],
            'was_present_on_exam_date' => ['nullable', 'boolean'],
            'attendance_remarks' => ['nullable', 'string', 'max:500'],
            'is_delayed' => ['nullable', 'boolean'],
            'delay_reason' => ['nullable', 'string', 'max:500'],
            'result_state' => ['required', Rule::in(['normal', 'absent', 'withheld'])],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        // Validate assessment marks
        if (isset($data['assessment_full_marks'], $data['assessment_pass_marks'])
            && $data['assessment_full_marks'] !== null
            && $data['assessment_pass_marks'] !== null
            && (float) $data['assessment_pass_marks'] > (float) $data['assessment_full_marks']) {
            throw ValidationException::withMessages([
                'assessment_pass_marks' => 'Assessment pass marks cannot be greater than assessment full marks.',
            ]);
        }

        $mark->fill([
            'internal_theory_marks' => $data['internal_theory_marks'] ?? $mark->internal_theory_marks,
            'external_theory_marks' => $data['external_theory_marks'] ?? $mark->external_theory_marks,
            'internal_practical_marks' => $data['internal_practical_marks'] ?? $mark->internal_practical_marks,
            'external_practical_marks' => $data['external_practical_marks'] ?? $mark->external_practical_marks,
            'assessment_attendance_percent' => $data['assessment_attendance_percent'] ?? $mark->assessment_attendance_percent,
            'assessment_full_marks' => $data['assessment_full_marks'] ?? $mark->assessment_full_marks,
            'assessment_pass_marks' => $data['assessment_pass_marks'] ?? $mark->assessment_pass_marks,
            'assessment_obtained_marks' => $data['assessment_obtained_marks'] ?? $mark->assessment_obtained_marks,
            // Attendance tracking
            'exam_attendance_date' => $data['exam_attendance_date'] ?? $mark->exam_attendance_date,
            'was_present_on_exam_date' => $data['was_present_on_exam_date'] ?? $mark->was_present_on_exam_date,
            'attendance_remarks' => $data['attendance_remarks'] ?? $mark->attendance_remarks,
            'is_absent' => $data['result_state'] === 'absent',
            'is_withheld' => $data['result_state'] === 'withheld',
            'is_delayed' => $request->boolean('is_delayed'),
            'delay_reason' => $data['delay_reason'] ?? null,
            'remarks' => $data['remarks'] ?? null,
        ]);
        $mark->save();

        return redirect()
            ->route('admin.exams.show', ['exam' => $exam, 'tab' => 'marks'])
            ->with('success', 'Mark updated successfully.');
    }

    public function updateSubjectMarkingScheme(Request $request, Exam $exam, Subject $subject)
    {
        $hasMarksForSubject = $exam->marks()->where('subject_id', $subject->id)->exists();
        $isProgramAssigned = $exam->programs()->where('programs.id', $subject->program_id)->exists();

        abort_unless($hasMarksForSubject || $isProgramAssigned, 404);

        $data = $request->validate([
            'full_marks_internal_theory' => ['required', 'numeric', 'min:0'],
            'pass_marks_internal_theory' => ['required', 'numeric', 'min:0'],
            'full_marks_external_theory' => ['required', 'numeric', 'min:0'],
            'pass_marks_external_theory' => ['required', 'numeric', 'min:0'],
            'full_marks_internal_practical' => ['required', 'numeric', 'min:0'],
            'pass_marks_internal_practical' => ['required', 'numeric', 'min:0'],
            'full_marks_external_practical' => ['required', 'numeric', 'min:0'],
            'pass_marks_external_practical' => ['required', 'numeric', 'min:0'],
        ]);

        $pairs = [
            ['full_marks_internal_theory', 'pass_marks_internal_theory', 'Internal theory'],
            ['full_marks_external_theory', 'pass_marks_external_theory', 'External theory'],
            ['full_marks_internal_practical', 'pass_marks_internal_practical', 'Internal practical'],
            ['full_marks_external_practical', 'pass_marks_external_practical', 'External practical'],
        ];

        foreach ($pairs as [$fullKey, $passKey, $label]) {
            if ((float) $data[$passKey] > (float) $data[$fullKey]) {
                throw ValidationException::withMessages([
                    $passKey => "{$label} pass marks cannot be greater than full marks.",
                ]);
            }
        }

        ExamSubjectMarkingScheme::query()->updateOrCreate([
            'exam_id' => $exam->id,
            'subject_id' => $subject->id,
        ], [
            'full_marks_internal_theory' => $data['full_marks_internal_theory'],
            'pass_marks_internal_theory' => $data['pass_marks_internal_theory'],
            'full_marks_external_theory' => $data['full_marks_external_theory'],
            'pass_marks_external_theory' => $data['pass_marks_external_theory'],
            'full_marks_internal_practical' => $data['full_marks_internal_practical'],
            'pass_marks_internal_practical' => $data['pass_marks_internal_practical'],
            'full_marks_external_practical' => $data['full_marks_external_practical'],
            'pass_marks_external_practical' => $data['pass_marks_external_practical'],
        ]);

        return redirect()
            ->route('admin.exams.show', ['exam' => $exam, 'tab' => 'marks'])
            ->with('success', 'Subject marking scheme updated successfully.');
    }

    public function analytics(Request $request)
    {
        $filters = $this->resolveFilters($request);
        $examQuery = $this->baseExamQuery($filters);
        $markQuery = $this->baseMarkQuery($filters);

        $exams = (clone $examQuery)->get();
        $marks = (clone $markQuery)->get();

        return view('admin.exams.analytics', [
            'filters' => $filters,
            'sessions' => AcademicSession::query()->orderByDesc('start_date')->get(['id', 'name', 'name_bs']),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name', 'code']),
            'programs' => Program::query()->with('department:id,name,code')->orderBy('name')->get(['id', 'department_id', 'name', 'code']),
            'typeOptions' => $this->typeOptions(),
            'statusOptions' => $this->statusOptions(),
            'kpis' => $this->buildAnalyticsKpis($exams, $marks),
            'analytics' => $this->buildAnalyticsPayload($exams, $marks),
        ]);
    }

    public function resultSheet(Exam $exam, Student $student)
    {
        $exam->load([
            'academicSession:id,name,name_bs',
            'department:id,name,code',
            'programs.department:id,name,code',
            'programs.subjects.teachers.user:id,name,avatar',
            'marks.student.user:id,name,avatar',
            'marks.student.program:id,name,code',
            'marks.student.department:id,name,code',
            'marks.subject:id,name,code,type,semester,program_id,full_marks_internal_theory,full_marks_external_theory,full_marks_internal_practical,full_marks_external_practical,pass_marks_internal_theory,pass_marks_external_theory,pass_marks_internal_practical,pass_marks_external_practical',
            'marks.teacher.user:id,name,avatar',
        ]);

        $student->load(['user:id,name,avatar,email,phone,address', 'department:id,name,code', 'program:id,name,code', 'academicSession:id,name,name_bs']);

        $subjectRows = $this->buildSubjectRows($exam);
        $sheet = $this->buildStudentSheet($exam, $student, $subjectRows);

        return view('admin.exams.result-sheet', [
            'exam' => $exam,
            'student' => $student,
            'subjectResults' => $sheet['rows'],
            'summary' => $sheet['summary'],
            'verificationCode' => $sheet['verificationCode'],
        ]);
    }

    public function edit(Exam $exam)
    {
        $exam->load(['academicSession', 'programs:id,name,code,department_id,total_semesters']);

        return view('admin.exams.edit', $this->formPayload($exam));
    }

    public function update(Request $request, Exam $exam)
    {
        $data = $this->validateExam($request, $exam->id);

        if (($data['category'] ?? 'ctevt_final') === 'monthly_assessment' && empty($data['assessment_number'])) {
            throw ValidationException::withMessages([
                'assessment_number' => 'Assessment number is required for monthly assessment exams.',
            ]);
        }

        if (($data['category'] ?? 'ctevt_final') !== 'monthly_assessment') {
            $data['assessment_number'] = null;
        }

        $data['marks_open'] = $request->boolean('marks_open');
        $data['is_published'] = $exam->isPublishedState || $data['status'] === 'results_published';
        $data['published_at'] = $data['is_published'] && ! $exam->published_at ? now() : $exam->published_at;

        $programIds = collect($request->input('program_ids', []))->filter()->map(fn ($value) => (int) $value)->values();
        $semesterSelection = (string) $data['semester'];
        $sessionId = (int) $data['academic_session_id'];
        unset($data['program_ids'], $data['semester']);

        DB::transaction(function () use ($exam, $data, $programIds, $semesterSelection, $sessionId) {
            $exam->update($data);
            $this->syncProgramAssignments($exam, $programIds, $semesterSelection, $sessionId);
        });
        $exam->refresh()->loadMissing(['department:id,name,code', 'programs:id,name,code,department_id']);
        app(\App\Services\PortalNotificationService::class)->dispatchExamPublished($exam);

        return redirect()->route('admin.exams.show', $exam)->with('success', 'Exam updated successfully.');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();

        return redirect()->route('admin.exams.index')->with('success', 'Exam deleted successfully.');
    }

    public function publish(Exam $exam)
    {
        $exam->update([
            'status' => 'results_published',
            'marks_open' => false,
            'is_published' => true,
            'published_at' => now(),
        ]);
        $exam->refresh()->loadMissing(['department:id,name,code', 'programs:id,name,code,department_id']);
        app(\App\Services\PortalNotificationService::class)->dispatchExamPublished($exam);

        return redirect()->route('admin.exams.show', $exam)->with('success', "Exam '{$exam->name}' has been published.");
    }

    public function export(Request $request, string $format)
    {
        abort_unless(in_array($format, ['pdf', 'csv', 'excel'], true), 404);

        $filters = $this->resolveFilters($request);
        $examQuery = $this->baseExamQuery($filters);
        $allExams = (clone $examQuery)->get();
        $rows = $this->decorateExamRows($allExams);

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('admin.exams.export', [
                'rows' => $rows,
                'generatedAt' => now(),
                'filters' => $filters,
            ])->setPaper('a4', 'landscape');

            return $pdf->download($this->exportFilename('pdf'));
        }

        $columns = [
            'Exam Name', 'Type', 'Department', 'Programs', 'Semester', 'Start Date', 'End Date', 'Status', 'Marks', 'Submitted', 'Published',
        ];

        $separator = $format === 'excel' ? "\t" : ',';
        $mime = $format === 'excel' ? 'application/vnd.ms-excel' : 'text/csv';

        return response()->streamDownload(function () use ($columns, $rows, $separator) {
            $handle = fopen('php://output', 'wb');
            $this->writeDelimitedLine($handle, $columns, $separator);

            foreach ($rows as $row) {
                $this->writeDelimitedLine($handle, [
                    $row['name'],
                    $row['type_label'],
                    $row['department_label'],
                    $row['programs_label'],
                    $row['semester_label'],
                    $row['start_date_label'],
                    $row['end_date_label'],
                    $row['status_label'],
                    (int) ($row['marks_count'] ?? 0),
                    (int) ($row['submitted_marks_count'] ?? 0),
                    (int) ($row['published_marks_count'] ?? 0),
                ], $separator);
            }

            fclose($handle);
        }, $this->exportFilename($format), ['Content-Type' => $mime]);
    }

    public function exportSubjectMarks(Request $request, Exam $exam, string $format)
    {
        abort_unless(in_array($format, ['pdf', 'csv', 'excel'], true), 404);

        $subjectId = $request->integer('subject_id') ?: null;

        $exam->load([
            'academicSession:id,name,name_bs',
            'marks.student.user:id,name',
            'marks.student.program:id,name,code',
            'marks.subject:id,name,code,semester,type',
            'marks.teacher.user:id,name',
        ]);

        $rows = $this->subjectMarkExportRows($exam, $subjectId);
        $subject = $subjectId ? $exam->marks->firstWhere('subject_id', $subjectId)?->subject : null;

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('admin.exams.subject-marks-export', [
                'exam' => $exam,
                'rows' => $rows,
                'subject' => $subject,
                'generatedAt' => now(),
            ])->setPaper('a4', 'landscape');

            return $pdf->download($this->subjectMarkExportFilename($exam, 'pdf', $subject));
        }

        $columns = [
            'Subject Code', 'Subject', 'Semester', 'Student', 'Student No', 'Roll', 'Program',
            'Internal Theory', 'External Theory', 'Internal Practical', 'External Practical',
            'Total Marks', 'Percentage', 'Result', 'Status', 'Teacher', 'Updated (BS)', 'Remarks',
        ];

        $separator = $format === 'excel' ? "\t" : ',';
        $mime = $format === 'excel' ? 'application/vnd.ms-excel' : 'text/csv';

        return response()->streamDownload(function () use ($columns, $rows, $separator) {
            $handle = fopen('php://output', 'wb');
            $this->writeDelimitedLine($handle, $columns, $separator);

            foreach ($rows as $row) {
                $this->writeDelimitedLine($handle, [
                    $row['subject_code'],
                    $row['subject_name'],
                    $row['semester'],
                    $row['student_name'],
                    $row['student_no'],
                    $row['roll_number'],
                    $row['program_name'],
                    $row['internal_theory'],
                    $row['external_theory'],
                    $row['internal_practical'],
                    $row['external_practical'],
                    $row['total_marks'],
                    $row['percentage_label'],
                    $row['result_remark'],
                    $row['status'],
                    $row['teacher_name'],
                    $row['updated_at_label'],
                    $row['remarks'],
                ], $separator);
            }

            fclose($handle);
        }, $this->subjectMarkExportFilename($exam, $format, $subject), ['Content-Type' => $mime]);
    }

    private function subjectMarkExportRows(Exam $exam, ?int $subjectId = null): Collection
    {
        return $exam->marks
            ->when($subjectId, fn (Collection $marks) => $marks->where('subject_id', $subjectId))
            ->filter(fn (Mark $mark) => $mark->student && $mark->subject)
            ->sortBy([
                fn (Mark $mark) => (int) ($mark->semester ?: $mark->subject?->semester ?: 0),
                fn (Mark $mark) => strtolower((string) ($mark->subject?->code ?: $mark->subject?->name ?: '')),
                fn (Mark $mark) => strtolower((string) ($mark->student?->user?->name ?: '')),
            ])
            ->values()
            ->map(function (Mark $mark) {
                $percentage = $this->markPercentage($mark);

                return [
                    'subject_code' => $mark->subject?->code ?? '—',
                    'subject_name' => $mark->subject?->name ?? 'Subject',
                    'semester' => (int) ($mark->semester ?: $mark->subject?->semester ?: 0),
                    'student_name' => $mark->student?->user?->name ?? 'Student',
                    'student_no' => $mark->student?->student_no ?? '—',
                    'roll_number' => $mark->student?->roll_number ?? '—',
                    'program_name' => $mark->student?->program?->name ?? '—',
                    'internal_theory' => $mark->internal_theory_marks,
                    'external_theory' => $mark->external_theory_marks,
                    'internal_practical' => $mark->internal_practical_marks,
                    'external_practical' => $mark->external_practical_marks,
                    'total_marks' => number_format((float) $mark->total_marks, 2),
                    'percentage_label' => $percentage !== null ? number_format($percentage, 1) . '%' : '—',
                    'result_remark' => $this->markResultRemark($mark),
                    'status' => $mark->status,
                    'teacher_name' => $mark->teacher?->user?->name ?? '—',
                    'updated_at_label' => $mark->updated_at ? (bsDateTime($mark->updated_at, 'Y, F d', 'h:i A') ?: '—') : '—',
                    'remarks' => $mark->remarks ?: '',
                ];
            });
    }

    private function formPayload(?Exam $exam = null): array
    {
        $currentSession = AcademicSession::current();
        $scopeSession = $exam?->academicSession ?? $currentSession;

        return [
            'exam' => $exam,
            'sessions' => AcademicSession::query()->orderByDesc('start_date')->get(['id', 'name', 'name_bs']),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name', 'code']),
            'programs' => Program::query()->with('department:id,name,code')->orderBy('name')->get(['id', 'department_id', 'name', 'code', 'total_semesters']),
            'currentSession' => $currentSession,
            'typeOptions' => $this->typeOptions(),
            'categoryOptions' => $this->categoryOptions(),
            'statusOptions' => $this->statusOptions(),
            'semesterOptions' => $this->semesterOptions($scopeSession),
            'selectedSemester' => $exam ? $this->resolveSemesterSelection($exam) : '1',
            'runningSemesterLabel' => $this->runningSemesterLabel($scopeSession),
            'selectedProgramIds' => $exam ? $exam->programs->pluck('id')->all() : [],
        ];
    }

    private function validateExam(Request $request, ?int $examId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:regular,back,internal,practical'],
            'category' => ['required', Rule::in(['ctevt_final', 'monthly_assessment'])],
            'assessment_number' => ['nullable', 'integer', 'min:1', 'max:12'],
            'assessment_full_marks' => ['nullable', 'numeric', 'min:0'],
            'assessment_pass_marks' => ['nullable', 'numeric', 'min:0'],
            'academic_session_id' => ['required', 'exists:academic_sessions,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'semester' => ['required', 'string', Rule::in(array_merge(['all', 'running'], array_map('strval', range(1, 8))))],
            'program_ids' => ['nullable', 'array'],
            'program_ids.*' => ['integer', 'exists:programs,id'],
            'start_date' => ['nullable', 'string', 'max:10'],
            'end_date' => ['nullable', 'string', 'max:10'],
            'status' => ['required', 'in:upcoming,ongoing,completed,results_published'],
            'marks_open' => ['nullable', 'boolean'],
        ]);
    }

    private function resolveFilters(Request $request): array
    {
        $sessionId = $request->integer('year') ?: AcademicSession::current()?->id;

        return [
            'sessionId' => $sessionId,
            'departmentId' => $request->integer('department_id') ?: null,
            'programId' => $request->integer('program_id') ?: null,
            'semester' => $request->integer('semester') ?: null,
            'category' => $request->string('category')->trim()->toString() ?: null,
            'type' => $request->string('type')->trim()->toString() ?: null,
            'status' => $request->string('status')->trim()->toString() ?: null,
            'search' => trim($request->string('search')->toString()) ?: null,
        ];
    }

    private function baseExamQuery(array $filters): Builder
    {
        return Exam::query()
            ->with([
                'academicSession:id,name,name_bs',
                'department:id,name,code',
                'programs.department:id,name,code',
            ])
            ->withCount([
                'programs',
                'marks',
                'marks as submitted_marks_count' => fn ($query) => $query->whereIn('status', ['submitted', 'approved', 'published']),
                'marks as approved_marks_count' => fn ($query) => $query->whereIn('status', ['approved', 'published']),
                'marks as published_marks_count' => fn ($query) => $query->where('status', 'published'),
            ])
            ->when($filters['sessionId'], fn ($query) => $query->where('academic_session_id', $filters['sessionId']))
            ->when($filters['departmentId'], fn ($query) => $query->where('department_id', $filters['departmentId']))
            ->when($filters['programId'], fn ($query) => $query->whereHas('programs', fn ($programQuery) => $programQuery->where('programs.id', $filters['programId'])))
            ->when($filters['semester'], fn ($query) => $query->whereHas('programs', fn ($programQuery) => $programQuery->where('exam_program.semester', $filters['semester'])))
            ->when($filters['category'], fn ($query) => $query->where('category', $filters['category']))
            ->when($filters['type'], fn ($query) => $query->where('type', $filters['type']))
            ->when($filters['status'], fn ($query) => $this->applyStatusFilter($query, $filters['status']))
            ->when($filters['search'], fn ($query) => $this->applyExamSearch($query, $filters['search']));
    }

    private function baseMarkQuery(array $filters): Builder
    {
        return Mark::query()
            ->with([
                'exam.academicSession:id,name,name_bs',
                'exam.department:id,name,code',
                'subject:id,name,code,type,semester,program_id,full_marks_internal_theory,full_marks_external_theory,full_marks_internal_practical,full_marks_external_practical,pass_marks_internal_theory,pass_marks_external_theory,pass_marks_internal_practical,pass_marks_external_practical',
                'student.user:id,name,avatar',
                'student.program:id,name,code',
                'student.department:id,name,code',
                'teacher.user:id,name,avatar',
            ])
            ->when($filters['sessionId'], fn ($query) => $query->whereHas('exam', fn ($examQuery) => $examQuery->where('academic_session_id', $filters['sessionId'])))
            ->when($filters['departmentId'], fn ($query) => $query->whereHas('exam', fn ($examQuery) => $examQuery->where('department_id', $filters['departmentId'])))
            ->when($filters['programId'], fn ($query) => $query->where('program_id', $filters['programId']))
            ->when($filters['semester'], fn ($query) => $query->where('semester', $filters['semester']))
            ->when($filters['category'], fn ($query) => $query->whereHas('exam', fn ($examQuery) => $examQuery->where('category', $filters['category'])))
            ->when($filters['type'], fn ($query) => $query->whereHas('exam', fn ($examQuery) => $examQuery->where('type', $filters['type'])))
            ->when($filters['search'], function ($query) use ($filters) {
                $search = $filters['search'];
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->whereHas('exam', fn ($examQuery) => $examQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('subject', fn ($subjectQuery) => $subjectQuery->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                        ->orWhereHas('student.user', fn ($studentQuery) => $studentQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('teacher.user', fn ($teacherQuery) => $teacherQuery->where('name', 'like', "%{$search}%"));
                });
            });
    }

    private function applyStatusFilter(Builder $query, string $status): Builder
    {
        return match ($status) {
            'upcoming' => $query->where('status', 'upcoming'),
            'ongoing' => $query->where('status', 'ongoing'),
            'marks_pending' => $query->where('status', 'completed')->where('marks_open', true)->where('is_published', false),
            'verifying' => $query->where('status', 'completed')->where('is_published', false),
            'published' => $query->where(function ($statusQuery) {
                $statusQuery->where('status', 'results_published')->orWhere('is_published', true);
            }),
            default => $query,
        };
    }

    private function applyExamSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($subQuery) use ($search) {
            $subQuery->where('name', 'like', "%{$search}%")
                ->orWhereHas('academicSession', fn ($sessionQuery) => $sessionQuery->where('name', 'like', "%{$search}%"))
                ->orWhereHas('department', fn ($departmentQuery) => $departmentQuery->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                ->orWhereHas('programs', fn ($programQuery) => $programQuery->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                ->orWhereHas('marks.student.user', fn ($studentQuery) => $studentQuery->where('name', 'like', "%{$search}%"));
        });
    }

    private function syncProgramAssignments(Exam $exam, Collection $programIds, string $semesterSelection, ?int $sessionId = null): void
    {
        DB::table('exam_program')->where('exam_id', $exam->id)->delete();

        if ($programIds->isEmpty()) {
            return;
        }

        $scopeSession = $sessionId ? AcademicSession::find($sessionId) : AcademicSession::current();
        $runningSemesters = $this->runningSemesterNumbers($scopeSession);
        $programs = Program::query()
            ->whereIn('id', $programIds)
            ->get(['id', 'total_semesters']);

        $rows = collect();

        foreach ($programs as $program) {
            $semesterNumbers = $this->semesterNumbersForProgram($program, $semesterSelection, $runningSemesters);

            foreach ($semesterNumbers as $semesterNumber) {
                $rows->push([
                    'exam_id' => $exam->id,
                    'program_id' => $program->id,
                    'semester' => (int) $semesterNumber,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if ($rows->isEmpty()) {
            throw ValidationException::withMessages([
                'semester' => 'The selected semester scope does not match any program assignments.',
            ]);
        }

        DB::table('exam_program')->insert($rows->all());
    }

    private function semesterOptions(?AcademicSession $session = null): array
    {
        $options = [
            'all' => 'All semesters',
            'running' => $this->runningSemesterLabel($session),
        ];

        foreach (range(1, 8) as $semester) {
            $options[(string) $semester] = 'Semester ' . $semester;
        }

        return $options;
    }

    private function runningSemesterNumbers(?AcademicSession $session = null): Collection
    {
        $session ??= AcademicSession::current();

        if (! $session) {
            return collect();
        }

        $cacheKey = (int) $session->id;
        if (isset($this->runningSemestersCache[$cacheKey])) {
            return $this->runningSemestersCache[$cacheKey];
        }

        return $this->runningSemestersCache[$cacheKey] = $session->semesters()
            ->where('is_active', true)
            ->whereIn('status', ['running', 'delayed', 'upcoming'])
            ->orderBy('semester_number')
            ->pluck('semester_number')
            ->map(fn ($semester) => (int) $semester)
            ->values();
    }

    private function runningSemesterLabel(?AcademicSession $session = null): string
    {
        $runningSemesters = $this->runningSemesterNumbers($session);

        if ($runningSemesters->isEmpty()) {
            return 'All running semesters';
        }

        return 'All running semesters (' . $runningSemesters->implode(', ') . ')';
    }

    private function resolveSemesterSelection(Exam $exam): string
    {
        $exam->loadMissing(['programs' => fn ($query) => $query->select('programs.id', 'programs.name', 'programs.code', 'programs.department_id', 'programs.total_semesters')]);

        if ($exam->programs->isEmpty()) {
            return '1';
        }

        $runningSemesters = $this->runningSemesterNumbers($exam->academicSession ?? AcademicSession::current());
        $programGroups = $exam->programs->groupBy('id');

        $matchesAll = $programGroups->every(function (Collection $programAssignments) {
            $program = $programAssignments->first();
            $expected = $this->semesterNumbersForProgram($program, 'all', collect(), false);
            $actual = $programAssignments->pluck('pivot.semester')->filter()->map(fn ($semester) => (int) $semester)->unique()->sort()->values();

            return $expected->isNotEmpty() && $expected->all() === $actual->all();
        });

        if ($matchesAll) {
            return 'all';
        }

        $matchesRunning = $programGroups->every(function (Collection $programAssignments) use ($runningSemesters) {
            $program = $programAssignments->first();
            $expected = $this->semesterNumbersForProgram($program, 'running', $runningSemesters, false);
            $actual = $programAssignments->pluck('pivot.semester')->filter()->map(fn ($semester) => (int) $semester)->unique()->sort()->values();

            return $expected->isNotEmpty() && $expected->all() === $actual->all();
        });

        if ($matchesRunning) {
            return 'running';
        }

        $uniqueSemesters = $exam->programs->pluck('pivot.semester')->filter()->map(fn ($semester) => (string) $semester)->unique()->values();

        return $uniqueSemesters->count() === 1 ? $uniqueSemesters->first() : (string) ($uniqueSemesters->first() ?? 1);
    }

    private function buildUploadedMarkGroups(Exam $exam): Collection
    {
        $marks = $exam->marks->filter(fn (Mark $mark) => $mark->subject)->values();

        return $marks
            ->groupBy(fn (Mark $mark) => $mark->student?->department?->id ?? 'unassigned')
            ->map(function (Collection $departmentMarks) use ($exam) {
                $department = $departmentMarks->first()?->student?->department;

                $semesterGroups = $departmentMarks
                    ->groupBy(fn (Mark $mark) => (int) ($mark->semester ?: $mark->subject?->semester ?: 0))
                    ->sortKeys()
                    ->map(function (Collection $semesterMarks, $semesterNumber) use ($exam) {
                        $subjectGroups = $semesterMarks
                            ->groupBy(fn (Mark $mark) => $mark->subject_id)
                            ->map(function (Collection $subjectMarks) use ($exam) {
                                $subject = $subjectMarks->first()?->subject;
                                $validPercentages = $subjectMarks
                                    ->filter(fn (Mark $mark) => ! $mark->is_absent && ! $mark->is_withheld)
                                    ->map(fn (Mark $mark) => $this->markPercentage($mark))
                                    ->filter()
                                    ->values();

                                return [
                                    'subject_id' => $subject?->id,
                                    'subject_code' => $subject?->code,
                                    'subject_name' => $subject?->name ?? 'Subject',
                                    'criteria' => $subject ? $this->subjectMarkingSchemeForExam($exam, $subject) : null,
                                    'subject_type' => $subject ? $this->subjectTypeLabel($subject->type) : 'Theory',
                                    'subject_semester' => $subject?->semester,
                                    'marks_count' => $subjectMarks->count(),
                                    'average_score' => $validPercentages->isNotEmpty() ? round((float) $validPercentages->avg(), 1) : 0,
                                    'passed_count' => $subjectMarks->filter(fn (Mark $mark) => $this->markIsPassed($mark))->count(),
                                    'marks' => $subjectMarks
                                        ->sortBy(fn (Mark $mark) => $mark->student?->user?->name ?? '')
                                        ->values()
                                        ->map(function (Mark $mark) {
                                            $markPercentage = $this->markPercentage($mark);

                                            return [
                                                'student_name' => $mark->student?->user?->name ?? 'Student',
                                                'roll_number' => $mark->student?->roll_number ?? '—',
                                                'student_no' => $mark->student?->student_no ?? '—',
                                                'program_name' => $mark->student?->program?->name ?? '—',
                                                'internal_theory' => $mark->internal_theory_marks,
                                                'external_theory' => $mark->external_theory_marks,
                                                'internal_practical' => $mark->internal_practical_marks,
                                                'external_practical' => $mark->external_practical_marks,
                                                'assessment_attendance_percent' => $mark->assessment_attendance_percent,
                                                'assessment_full_marks' => $mark->assessment_full_marks,
                                                'assessment_pass_marks' => $mark->assessment_pass_marks,
                                                'assessment_obtained_marks' => $mark->assessment_obtained_marks,
                                                'total_marks' => $mark->total_marks,
                                                'percentage' => $markPercentage,
                                                'result_remark' => $this->markResultRemark($mark),
                                                'status' => $mark->status,
                                                'mark_id' => $mark->id,
                                                'teacher_name' => $mark->teacher?->user?->name ?? '—',
                                                'updated_at_label' => $mark->updated_at ? bsDateTime($mark->updated_at, 'Y, F d', 'h:i A') : '—',
                                                'remarks' => $mark->remarks,
                                                'is_absent' => $mark->is_absent,
                                                'is_withheld' => $mark->is_withheld,
                                                'is_delayed' => $mark->is_delayed,
                                                'delay_reason' => $mark->delay_reason,
                                            ];
                                        }),
                                ];
                            })
                            ->sortBy(fn (array $subjectGroup) => strtolower((string) ($subjectGroup['subject_code'] ?? '')) . '|' . strtolower((string) $subjectGroup['subject_name']))
                            ->values();

                        return [
                            'semester' => (int) $semesterNumber,
                            'marks_count' => $semesterMarks->count(),
                            'subjects_count' => $subjectGroups->count(),
                            'subjects' => $subjectGroups,
                        ];
                    })
                    ->values();

                $marksCount = $departmentMarks->count();
                $subjectsCount = $departmentMarks->pluck('subject_id')->unique()->count();

                return [
                    'department_name' => $department?->name ?? 'Unassigned',
                    'department_code' => $department?->code,
                    'marks_count' => $marksCount,
                    'subjects_count' => $subjectsCount,
                    'semesters' => $semesterGroups,
                ];
            })
            ->sortBy(fn (array $departmentGroup) => strtolower((string) $departmentGroup['department_name']))
            ->values();
    }

    private function semesterNumbersForProgram(Program $program, string $semesterSelection, Collection $runningSemesters, bool $throwOnEmpty = true): Collection
    {
        $semesterSelection = (string) $semesterSelection;

        if ($semesterSelection === 'all') {
            $totalSemesters = (int) ($program->total_semesters ?? 0);

            if ($totalSemesters < 1) {
                if ($throwOnEmpty) {
                    throw ValidationException::withMessages([
                        'semester' => "Program {$program->name} does not define any semesters.",
                    ]);
                }

                return collect();
            }

            return collect(range(1, $totalSemesters))->map(fn ($semester) => (int) $semester)->values();
        }

        if ($semesterSelection === 'running') {
            $totalSemesters = (int) ($program->total_semesters ?? 0);

            $semesterNumbers = $runningSemesters
                ->filter(fn ($semester) => $semester >= 1 && ($totalSemesters < 1 || $semester <= $totalSemesters))
                ->values();

            if ($semesterNumbers->isEmpty() && $throwOnEmpty) {
                throw ValidationException::withMessages([
                    'semester' => "No running semesters match the selected programs.",
                ]);
            }

            return $semesterNumbers;
        }

        $semester = (int) $semesterSelection;

        if ($semester < 1) {
            if ($throwOnEmpty) {
                throw ValidationException::withMessages([
                    'semester' => 'Select a valid semester scope.',
                ]);
            }

            return collect();
        }

        return collect([$semester]);
    }

    private function decorateExamRows(Collection $exams): Collection
    {
        return $exams->map(function (Exam $exam) {
            $state = $this->examState($exam);

            return [
                'id' => $exam->id,
                'exam' => $exam,
                'name' => $exam->name,
                'type' => $exam->type,
                'type_label' => $this->typeOptions()[$exam->type] ?? Str::headline($exam->type),
                'department_label' => $this->departmentScopeLabel($exam->department),
                'programs_label' => $this->programsScopeLabel($exam),
                'semester_label' => $this->semesterScopeLabel($exam),
                'start_date_label' => bsDate($exam->start_date, 'Y, F d') ?: '—',
                'end_date_label' => bsDate($exam->end_date, 'Y, F d') ?: '—',
                'status_key' => $state['key'],
                'status_label' => $state['label'],
                'status_tone' => $state['tone'],
                'marks_count' => $exam->marks_count ?? 0,
                'submitted_marks_count' => $exam->submitted_marks_count ?? 0,
                'approved_marks_count' => $exam->approved_marks_count ?? 0,
                'published_marks_count' => $exam->published_marks_count ?? 0,
                'marks_completion' => $this->marksCompletionFromCounts($exam->submitted_marks_count ?? 0, $exam->marks_count ?? 0),
            ];
        })->values();
    }

    private function buildKpis(Collection $exams): array
    {
        $total      = $exams->count();
        $published  = $exams->filter(fn (Exam $e) => $e->isPublishedState)->count();
        $ongoing    = $exams->filter(fn (Exam $e) => $e->status === 'ongoing')->count();
        $marksPending = $exams->filter(fn (Exam $e) => $this->examState($e)['key'] === 'marks_pending')->count();
        $verifying  = $exams->filter(fn (Exam $e) => $this->examState($e)['key'] === 'verifying')->count();
        $submitted  = (int) $exams->sum('submitted_marks_count');

        return [
            ['label' => 'Total Exams',    'value' => number_format($total),        'note' => 'This session',  'tone' => 'slate'],
            ['label' => 'Published',      'value' => number_format($published),    'note' => 'Results out',   'tone' => 'emerald'],
            ['label' => 'Ongoing',        'value' => number_format($ongoing),      'note' => 'In progress',   'tone' => 'blue'],
            ['label' => 'Marks Pending',  'value' => number_format($marksPending), 'note' => 'Teacher input', 'tone' => 'amber'],
            ['label' => 'Under Review',   'value' => number_format($verifying),    'note' => 'HOD review',    'tone' => 'violet'],
            ['label' => 'Marks Submitted','value' => number_format($submitted),    'note' => 'Total entries', 'tone' => 'rose'],
        ];
    }

    private function buildCharts(Collection $exams, Collection $marks): array
    {
        $departmentRows = $this->departmentPerformanceRows($exams, $marks);
        $statusRows = collect($exams)->map(fn (Exam $exam) => $this->examState($exam)['label'])->countBy();
        $gradeRows = $this->gradeDistributionRows($marks);
        $trendRows = $this->yearTrendRows($marks);

        return [
            'statusBreakdown' => [
                'labels' => $statusRows->keys()->all(),
                'values' => $statusRows->values()->all(),
            ],
            'departmentPerformance' => [
                'labels' => $departmentRows->pluck('label')->all(),
                'values' => $departmentRows->pluck('pass_rate')->all(),
                'rows' => $departmentRows,
            ],
            'gradeDistribution' => [
                'labels' => $gradeRows->pluck('label')->all(),
                'values' => $gradeRows->pluck('count')->all(),
                'rows' => $gradeRows,
            ],
            'yearTrend' => [
                'labels' => $trendRows->pluck('label')->all(),
                'values' => $trendRows->pluck('pass_rate')->all(),
                'rows' => $trendRows,
            ],
        ];
    }

    private function buildAnalyticsKpis(Collection $exams, Collection $marks): array
    {
        $departmentRows = $this->departmentPerformanceRows($exams, $marks);
        $backCount = $exams->filter(fn (Exam $exam) => Str::contains(Str::lower($exam->type), 'back'))->count();
        $topProgram = $this->topProgramRows($marks)->first();
        $subjectDifficulty = $this->subjectDifficultyRows($marks)->first();

        return [
            [
                'label' => 'Department Avg',
                'value' => $departmentRows->isNotEmpty() ? number_format($departmentRows->avg('pass_rate'), 1) . '%' : '0.0%',
                'trend' => 'Across programs',
                'direction' => 'up',
                'tone' => 'blue',
            ],
            [
                'label' => 'Back Exams',
                'value' => number_format($backCount),
                'trend' => 'Latest cycle',
                'direction' => 'flat',
                'tone' => 'amber',
            ],
            [
                'label' => 'Top Program',
                'value' => $topProgram['program'] ?? 'N/A',
                'trend' => number_format($topProgram['pass_rate'] ?? 0, 1) . '%',
                'direction' => 'up',
                'tone' => 'emerald',
            ],
            [
                'label' => 'Hardest Subject',
                'value' => $subjectDifficulty['subject'] ?? 'N/A',
                'trend' => number_format($subjectDifficulty['fail_rate'] ?? 0, 1) . '% fail rate',
                'direction' => 'down',
                'tone' => 'violet',
            ],
        ];
    }

    private function buildAnalyticsPayload(Collection $exams, Collection $marks): array
    {
        return [
            'departmentPerformance' => $this->departmentPerformanceRows($exams, $marks),
            'backExamTrend' => $this->backExamTrendRows($exams, $marks),
            'topPrograms' => $this->topProgramRows($marks),
            'subjectDifficulty' => $this->subjectDifficultyRows($marks),
            'teacherContribution' => $this->teacherContributionRows($marks),
            'yearTrend' => $this->yearTrendRows($marks),
            'gradeDistribution' => $this->gradeDistributionRows($marks),
        ];
    }

    private function buildExamSummary(Exam $exam, Collection $subjectRows, Collection $studentRows): array
    {
        $marks = $exam->marks;
        $publishedMarks = $marks->where('status', 'published');
        $summaryRows = [
            ['label' => 'Total Students', 'value' => $studentRows->count(), 'tone' => 'blue'],
            ['label' => 'Total Subjects', 'value' => $subjectRows->count(), 'tone' => 'emerald'],
            ['label' => 'Marks Completion', 'value' => $this->marksCompletion($subjectRows, $studentRows, $marks) . '%', 'tone' => 'violet'],
            ['label' => 'Pass Rate', 'value' => $this->passRate($studentRows) . '%', 'tone' => 'rose'],
        ];

        return [
            'cards' => $summaryRows,
            'topSubject' => $subjectRows->sortByDesc('entered_pct')->first()['subject_name'] ?? 'N/A',
            'topPerformer' => $studentRows->sortByDesc('percentage')->first()['name'] ?? 'N/A',
            'departmentAverage' => $publishedMarks->isNotEmpty() ? number_format($publishedMarks->map(fn (Mark $mark) => $this->markPercentage($mark))->filter()->avg(), 1) : '0.0',
            'semesterAverage' => $studentRows->isNotEmpty() ? number_format($studentRows->avg('percentage'), 1) : '0.0',
            'passCount' => $studentRows->where('result_status', 'Pass')->count(),
            'failCount' => $studentRows->where('result_status', 'Fail')->count(),
        ];
    }

    private function buildExamCharts(Exam $exam, Collection $subjectRows, Collection $studentRows): array
    {
        return [
            'subjectPerformance' => [
                'labels' => $subjectRows->pluck('subject_name')->all(),
                'values' => $subjectRows->map(fn ($row) => (float) ($row['entered_pct'] ?? 0))->all(),
            ],
            'gradeDistribution' => [
                'labels' => ['A', 'B', 'C', 'Fail'],
                'values' => [
                    $studentRows->where('grade_band', 'A')->count(),
                    $studentRows->where('grade_band', 'B')->count(),
                    $studentRows->where('grade_band', 'C')->count(),
                    $studentRows->where('grade_band', 'Fail')->count(),
                ],
            ],
            'yearTrend' => [
                'labels' => [$exam->academicSession?->name ?? 'Current session'],
                'values' => [$this->passRate($studentRows)],
            ],
        ];
    }

    private function buildSubjectRows(Exam $exam, ?Collection $marksBySubject = null): Collection
    {
        $exam->loadMissing(['programs.department', 'programs.subjects.teachers.user', 'marks.subject']);

        $marksBySubject ??= $exam->marks->groupBy('subject_id');

        $studentsByProgramSemester = $this->examStudents($exam)
            ->groupBy(fn (Student $student) => $student->program_id . '|' . $student->current_semester);

        $rows = collect();
        $programs = $exam->programs->filter(function (Program $program) use ($exam) {
            return ! $exam->department_id || (int) $program->department_id === (int) $exam->department_id;
        });

        foreach ($programs as $program) {
            $semester = (int) ($program->pivot?->semester ?? 0);
            $studentCount = $studentsByProgramSemester
                ->get($program->id . '|' . $semester, collect())
                ->count();
            $subjects = $program->subjects->where('semester', $semester);

            foreach ($subjects as $subject) {
                $subjectMarks = $marksBySubject->get($subject->id, collect());
                $enteredCount = $subjectMarks->count();
                $submittedCount = $subjectMarks->whereIn('status', ['submitted', 'approved', 'published'])->count();
                $approvedCount = $subjectMarks->whereIn('status', ['approved', 'published'])->count();
                $publishedCount = $subjectMarks->where('status', 'published')->count();
                $teacher = $subject->teachers->first();
                $state = $publishedCount > 0
                    ? ['key' => 'completed', 'label' => 'Completed', 'tone' => 'green']
                    : ($submittedCount > 0 ? ['key' => 'verifying', 'label' => 'Verifying', 'tone' => 'purple'] : ['key' => 'pending', 'label' => 'Pending', 'tone' => 'yellow']);
                $uploadState = $publishedCount > 0
                    ? ['key' => 'published', 'label' => 'Published', 'tone' => 'green']
                    : ($enteredCount === 0
                        ? ['key' => 'pending', 'label' => 'Pending', 'tone' => 'yellow']
                        : (($studentCount > 0 && $enteredCount >= $studentCount)
                            ? ['key' => 'mark_open', 'label' => 'Mark Open', 'tone' => 'green']
                            : ['key' => 'mark_open', 'label' => 'Mark Open', 'tone' => 'amber']));

                $rows->push([
                    'program_id' => $program->id,
                    'program_name' => $program->name,
                    'program_code' => $program->code,
                    'department_id' => $program->department_id,
                    'department_name' => $program->department?->name,
                    'department_code' => $program->department?->code,
                    'semester' => $semester,
                    'subject_id' => $subject->id,
                    'subject_name' => $subject->name,
                    'subject_code' => $subject->code,
                    'subject_type' => $this->subjectTypeLabel($subject->type),
                    'student_count' => $studentCount,
                    'full_marks' => (float) $subject->totalFullMarks,
                    'pass_marks' => (float) $subject->totalPassMarks,
                    'marks_count' => $enteredCount,
                    'submitted_count' => $submittedCount,
                    'approved_count' => $approvedCount,
                    'published_count' => $publishedCount,
                    'entered_pct' => $studentCount > 0 ? round(($enteredCount / $studentCount) * 100, 1) : 0,
                    'missing_count' => max(0, $studentCount - $enteredCount),
                    'subject' => $subject,
                    'program' => $program,
                    'teacher' => $teacher,
                    'teacher_name' => $teacher?->user?->name ?? 'Unassigned',
                    'hall' => 'TBD',
                    'invigilator' => $teacher?->user?->name ?? 'Unassigned',
                    'exam_date' => $exam->start_date,
                    'exam_date_label' => bsDate($exam->start_date, 'Y, F d') ?: '—',
                    'start_time' => 'TBD',
                    'end_time' => 'TBD',
                    'last_updated' => $subjectMarks->max('updated_at') ? bsDateTime($subjectMarks->max('updated_at'), 'Y, F d', 'h:i A') : '—',
                    'status_key' => $state['key'],
                    'status_label' => $state['label'],
                    'status_tone' => $state['tone'],
                    'upload_state_key' => $uploadState['key'],
                    'upload_state_label' => $uploadState['label'],
                    'upload_state_tone' => $uploadState['tone'],
                    'remarks' => $subjectMarks->pluck('remarks')->filter()->unique()->take(2)->implode(' · '),
                ]);
            }
        }

        return $rows->sortBy([
            ['department_name', 'asc'],
            ['program_name', 'asc'],
            ['semester', 'asc'],
            ['subject_name', 'asc'],
        ])->values();
    }

    private function buildStudentResults(Exam $exam, Collection $subjectRows, ?Collection $marksByStudent = null): Collection
    {
        $students = $this->examStudents($exam);
        $marksByStudent ??= $exam->marks->groupBy('student_id');
        $rows = collect();

        foreach ($students as $student) {
            $rows->push($this->buildStudentPerformance($exam, $student, $subjectRows, $marksByStudent));
        }

        return $rows->sortByDesc('percentage')->values();
    }

    private function buildStudentPerformance(Exam $exam, Student $student, Collection $subjectRows, ?Collection $marksByStudent = null): array
    {
        $relevantRows = $subjectRows
            ->where('program_id', $student->program_id)
            ->where('semester', $student->current_semester)
            ->values();
        $studentMarks = $marksByStudent?->get($student->id, collect())
            ?? $exam->marks->where('student_id', $student->id);
        $marksBySubject = $studentMarks->groupBy('subject_id');

        $subjectResults = collect();
        $obtainedTotal = 0.0;
        $fullTotal = 0.0;
        $passCount = 0;

        foreach ($relevantRows as $row) {
            $mark = $marksBySubject->get($row['subject_id'])?->first();
            $obtained = $mark && ! $mark->is_absent && ! $mark->is_withheld ? (float) $mark->total_marks : 0.0;
            $isMonthlyAssessment = $mark && $this->examCategoryForMark($mark) === 'monthly_assessment';
            $full = $isMonthlyAssessment
                ? (float) ($mark->assessment_full_marks ?? $row['full_marks'])
                : (float) $row['full_marks'];
            $passMarks = $isMonthlyAssessment
                ? (float) ($mark->assessment_pass_marks ?? $row['pass_marks'])
                : (float) $row['pass_marks'];
            $percentage = $full > 0 ? round(($obtained / $full) * 100, 1) : 0.0;
            $gradeBand = $this->gradeBand($percentage);

            $subjectResults->push([
                'subject' => $row['subject'],
                'subject_name' => $row['subject_name'],
                'subject_code' => $row['subject_code'],
                'full_marks' => $full,
                'pass_marks' => $passMarks,
                'obtained' => $obtained,
                'percentage' => $percentage,
                'grade' => $gradeBand,
                'remarks' => $mark?->remarks ?? '',
                'result_status' => $mark ? $this->markResultRemark($mark) : 'Pending',
            ]);

            $obtainedTotal += $obtained;
            $fullTotal += $full;
            if ($mark && $this->markIsPassed($mark)) {
                $passCount++;
            }
        }

        $percentage = $fullTotal > 0 ? round(($obtainedTotal / $fullTotal) * 100, 1) : 0.0;
        $gradeBand = $this->gradeBand($percentage);
        $gpa = $this->gradePoint($percentage);
        $resultStatus = $subjectResults->count() > 0 && $studentMarks->count() === $subjectResults->count()
            ? ($passCount === $subjectResults->count() ? 'Pass' : 'Fail')
            : 'Pending';

        return [
            'student' => $student,
            'student_id' => $student->id,
            'name' => $student->user?->name ?? 'Student',
            'avatar' => $student->user?->avatar,
            'program' => $student->program?->name,
            'semester' => $student->current_semester,
            'roll_number' => $student->roll_number,
            'symbol_no' => $student->student_no,
            'subject_count' => $subjectResults->count(),
            'obtained' => round($obtainedTotal, 2),
            'full_marks' => round($fullTotal, 2),
            'percentage' => $percentage,
            'grade_band' => $gradeBand,
            'gpa' => $gpa,
            'result_status' => $resultStatus,
            'subjectResults' => $subjectResults,
            'absent_count' => $studentMarks->where('is_absent', true)->count(),
            'withheld_count' => $studentMarks->where('is_withheld', true)->count(),
            'last_updated' => $studentMarks->max('updated_at') ? bsDateTime($studentMarks->max('updated_at'), 'd F Y', 'h:i A') : '—',
        ];
    }

    private function departmentScopeLabel(?Department $department): string
    {
        return $department?->code ? $department->code . ' - ' . $department->name : ($department?->name ?? 'All departments');
    }

    private function programsScopeLabel(Exam $exam): string
    {
        $programs = $exam->programs
            ->sortBy(fn (Program $program) => strtolower((string) ($program->code ?: $program->name)))
            ->values();

        if ($programs->isEmpty()) {
            return 'Unassigned';
        }

        if ($programs->count() === 1) {
            $program = $programs->first();
            $semester = $program->pivot?->semester;

            return trim(($program->code ? $program->code . ' - ' : '') . $program->name) . ($semester ? ' · Sem ' . $semester : '');
        }

        if ($programs->count() <= 3) {
            return $programs->map(function (Program $program) {
                $semester = $program->pivot?->semester;

                return trim(($program->code ? $program->code . ' - ' : '') . $program->name) . ($semester ? ' · Sem ' . $semester : '');
            })->implode(' · ');
        }

        $programCount = $programs->count();

        return ($exam->department_id ? '' : 'All departments · ') . $programCount . ' programs';
    }

    private function semesterScopeLabel(Exam $exam): string
    {
        $selection = $this->resolveSemesterSelection($exam);

        if ($selection === 'all') {
            return 'All semesters';
        }

        if ($selection === 'running') {
            return $this->runningSemesterLabel($exam->academicSession ?? AcademicSession::current());
        }

        $semesterLabels = $exam->programs
            ->pluck('pivot.semester')
            ->filter()
            ->map(fn ($semester) => (int) $semester)
            ->unique()
            ->sort()
            ->map(fn ($semester) => 'Sem ' . $semester)
            ->implode(' · ');

        return $semesterLabels ?: '—';
    }

    private function normalizeMarkImportHeaders(array $headers): array
    {
        return array_map(function ($header) {
            $header = Str::of((string) $header)
                ->replace("\xEF\xBB\xBF", '')
                ->trim()
                ->lower()
                ->replaceMatches('/[^a-z0-9]+/', '_')
                ->trim('_')
                ->toString();

            return match ($header) {
                'student_id' => 'student_id',
                'student_no', 'student_number', 'student_no_', 'symbol_no', 'symbol_number', 'roll_number', 'roll_no', 'roll' => 'student_key',
                'subject_id' => 'subject_id',
                'subject_code', 'subject_number', 'subject', 'code' => 'subject_key',
                'teacher_id' => 'teacher_id',
                'internal_theory', 'int_theory', 'internal_theory_marks' => 'internal_theory_marks',
                'external_theory', 'ext_theory', 'external_theory_marks' => 'external_theory_marks',
                'internal_practical', 'int_practical', 'internal_practical_marks' => 'internal_practical_marks',
                'external_practical', 'ext_practical', 'external_practical_marks' => 'external_practical_marks',
                'is_absent', 'absent' => 'is_absent',
                'is_withheld', 'withheld' => 'is_withheld',
                'status' => 'status',
                'remarks', 'remark', 'note' => 'remarks',
                default => $header,
            };
        }, $headers);
    }

    private function normalizeMarkImportRow(array $row): array
    {
        $normalized = [];

        foreach ($row as $key => $value) {
            $normalized[$key] = is_string($value) ? trim($value) : $value;
        }

        $payload = [
            'student_id' => isset($normalized['student_id']) && is_numeric($normalized['student_id']) ? (int) $normalized['student_id'] : null,
            'student_key' => filled($normalized['student_key'] ?? null) ? Str::lower(trim((string) $normalized['student_key'])) : null,
            'subject_id' => isset($normalized['subject_id']) && is_numeric($normalized['subject_id']) ? (int) $normalized['subject_id'] : null,
            'subject_key' => filled($normalized['subject_key'] ?? null) ? Str::lower(trim((string) $normalized['subject_key'])) : null,
            'teacher_id' => isset($normalized['teacher_id']) && is_numeric($normalized['teacher_id']) ? (int) $normalized['teacher_id'] : null,
            'internal_theory_marks' => $this->parseMarkImportNumber($normalized['internal_theory_marks'] ?? null),
            'external_theory_marks' => $this->parseMarkImportNumber($normalized['external_theory_marks'] ?? null),
            'internal_practical_marks' => $this->parseMarkImportNumber($normalized['internal_practical_marks'] ?? null),
            'external_practical_marks' => $this->parseMarkImportNumber($normalized['external_practical_marks'] ?? null),
            'is_absent' => $this->parseMarkImportBoolean($normalized['is_absent'] ?? null),
            'is_withheld' => $this->parseMarkImportBoolean($normalized['is_withheld'] ?? null),
            'status' => filled($normalized['status'] ?? null) ? Str::lower(trim((string) $normalized['status'])) : null,
            'remarks' => filled($normalized['remarks'] ?? null) ? trim((string) $normalized['remarks']) : null,
        ];

        return array_filter($payload, fn ($value) => $value !== null && $value !== '');
    }

    private function resolveImportedStudent(array $normalized, Collection $studentsById, Collection $studentsByStudentNo, Collection $studentsByRoll): ?Student
    {
        if (! empty($normalized['student_id'])) {
            $student = $studentsById->get((int) $normalized['student_id']);
            if ($student) {
                return $student;
            }
        }

        if (! empty($normalized['student_key'])) {
            $student = $studentsByStudentNo->get($normalized['student_key']) ?? $studentsByRoll->get($normalized['student_key']);
            if ($student) {
                return $student;
            }
        }

        return null;
    }

    private function resolveImportedSubject(array $normalized, Collection $subjectsById, Collection $subjectsByCode): ?Subject
    {
        if (! empty($normalized['subject_id'])) {
            $subject = $subjectsById->get((int) $normalized['subject_id']);
            if ($subject) {
                return $subject;
            }
        }

        if (! empty($normalized['subject_key'])) {
            $subject = $subjectsByCode->get($normalized['subject_key']);
            if ($subject) {
                return $subject;
            }
        }

        return null;
    }

    private function parseMarkImportNumber(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function parseMarkImportBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $normalized = Str::lower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'yes', 'y', 'on'], true);
    }

    private function paginateCollection(Collection $items, int $perPage, string $pageName): LengthAwarePaginator
    {
        $currentPage = max((int) request()->integer($pageName, 1), 1);
        $pageItems = $items->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $pageItems,
            $items->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
            ]
        );
    }

    private function buildStudentSheet(Exam $exam, Student $student, Collection $subjectRows): array
    {
        $studentPerformance = $this->buildStudentPerformance($exam, $student, $subjectRows);

        return [
            'rows' => $studentPerformance['subjectResults'],
            'summary' => [
                'obtained' => $studentPerformance['obtained'],
                'full_marks' => $studentPerformance['full_marks'],
                'percentage' => $studentPerformance['percentage'],
                'gpa' => $studentPerformance['gpa'],
                'result_status' => $studentPerformance['result_status'],
                'grade_band' => $studentPerformance['grade_band'],
            ],
            'verificationCode' => strtoupper(substr(hash('sha256', $exam->id . '-' . $student->id . '-' . $studentPerformance['percentage']), 0, 12)),
        ];
    }

    private function buildExamStudents(Exam $exam): Collection
    {
        $programIds = $exam->programs->pluck('id')->all();
        $semesters = $exam->programs->pluck('pivot.semester')->filter()->unique()->all();

        if ($programIds === [] || $semesters === []) {
            return collect();
        }

        return Student::query()
            ->with(['user:id,name,avatar', 'program:id,name,code'])
            ->where('academic_session_id', $exam->academic_session_id)
            ->whereIn('program_id', $programIds)
            ->whereIn('current_semester', $semesters)
            ->orderBy('roll_number')
            ->orderBy('student_no')
            ->get();
    }

    private function examStudents(Exam $exam): Collection
    {
        $examId = (int) $exam->id;

        if (! isset($this->examStudentsCache[$examId])) {
            $this->examStudentsCache[$examId] = $this->buildExamStudents($exam);
        }

        return $this->examStudentsCache[$examId];
    }

    private function marksCompletion(Collection $subjectRows, Collection $studentRows, Collection $marks): float
    {
        $expected = max(1, $subjectRows->sum('student_count'));
        return round(($marks->count() / $expected) * 100, 1);
    }

    private function passRate(Collection $studentRows): float
    {
        if ($studentRows->isEmpty()) {
            return 0.0;
        }

        $passCount = $studentRows->filter(fn (array $row) => $row['result_status'] === 'Pass')->count();

        return round(($passCount / $studentRows->count()) * 100, 1);
    }

    private function examPassRate(Collection $marks): float
    {
        $publishedMarks = $marks->where('status', 'published');
        if ($publishedMarks->isEmpty()) {
            $publishedMarks = $marks;
        }

        if ($publishedMarks->isEmpty()) {
            return 0.0;
        }

        $markRows = $publishedMarks->groupBy('student_id')->map(function (Collection $studentMarks) {
            return $studentMarks->filter(fn (Mark $mark) => $this->markIsPassed($mark))->count() === $studentMarks->count() ? 1 : 0;
        });

        return round(($markRows->sum() / max(1, $markRows->count())) * 100, 1);
    }

    /**
     * Check if a mark is passing using raw attribute values to avoid decimal:2 cast BCMath overhead.
     * Result is cached by mark ID for the lifetime of the request.
     */
    private function markIsPassed(Mark $mark): bool
    {
        $id = $mark->id;
        if (array_key_exists($id, $this->markIsPassedCache)) {
            return $this->markIsPassedCache[$id];
        }

        if ($mark->is_absent || $mark->is_withheld || $mark->is_delayed || ! $mark->subject) {
            return $this->markIsPassedCache[$id] = false;
        }

        if ($this->examCategoryForMark($mark) === 'monthly_assessment') {
            $full = (float) ($mark->assessment_full_marks ?? 0);
            $pass = (float) ($mark->assessment_pass_marks ?? 0);
            $obtained = $mark->assessment_obtained_marks;

            if ($full <= 0 || $obtained === null) {
                return $this->markIsPassedCache[$id] = false;
            }

            return $this->markIsPassedCache[$id] = (float) $obtained >= $pass;
        }

        // For CTEVT exams, ALWAYS use the single source of truth: exam scheme or subject defaults
        $scheme = $this->markingSchemeForMark($mark);
        $attrs = $mark->getAttributes();

        $passInternalTheory = (float) $scheme['pass_internal_theory'];
        $passExternalTheory = (float) $scheme['pass_external_theory'];
        $passInternalPractical = (float) $scheme['pass_internal_practical'];
        $passExternalPractical = (float) $scheme['pass_external_practical'];
        $fullInternalPractical = (float) $scheme['full_internal_practical'];
        $fullExternalPractical = (float) $scheme['full_external_practical'];
        
        // Theory validation - BOTH internal and external must pass
        $theoryPass = ((float) ($attrs['internal_theory_marks'] ?? 0)) >= $passInternalTheory
            && ((float) ($attrs['external_theory_marks'] ?? 0)) >= $passExternalTheory;

        if (! $theoryPass) {
            return $this->markIsPassedCache[$id] = false;
        }

        // Practical validation - BOTH internal and external must pass (if practical exists)
        $practicalPass = true;
        $practicalThresholdApplies = $fullInternalPractical > 0
            || $fullExternalPractical > 0
            || $passInternalPractical > 0
            || $passExternalPractical > 0;

        if ($practicalThresholdApplies) {
            $practicalPass = ((float) ($attrs['internal_practical_marks'] ?? 0)) >= $passInternalPractical
                && ((float) ($attrs['external_practical_marks'] ?? 0)) >= $passExternalPractical;
        }

        return $this->markIsPassedCache[$id] = $theoryPass && $practicalPass;
    }

    private function averageScore(Collection $studentRows): float
    {
        return $studentRows->isNotEmpty() ? round((float) $studentRows->avg('percentage'), 1) : 0.0;
    }

    private function previousExamAverage(Collection $exams, ?Exam $latestExam): float
    {
        if (! $latestExam) {
            return 0.0;
        }

        $previous = $exams->where('id', '!=', $latestExam->id)->sortByDesc(fn (Exam $exam) => $exam->start_date?->timestamp ?? 0)->first();

        return 0.0;
    }

    private function markAverageScore(Collection $marks): float
    {
        $scores = $marks->map(fn (Mark $mark) => $this->markPercentage($mark))->filter();

        return $scores->isNotEmpty() ? round((float) $scores->avg(), 1) : 0.0;
    }

    private function marksCompletionFromCounts(int $submittedCount, int $totalCount): float
    {
        if ($totalCount <= 0) {
            return 0.0;
        }

        return round(($submittedCount / $totalCount) * 100, 1);
    }

    private function previousAverageScore(Collection $marks): float
    {
        $publishedMarks = $marks->where('status', 'published');
        return $publishedMarks->isNotEmpty() ? round((float) $publishedMarks->map(fn (Mark $mark) => $this->markPercentage($mark))->filter()->avg(), 1) : 0.0;
    }

    private function examState(Exam $exam): array
    {
        if ($exam->isPublishedState) {
            return ['key' => 'published', 'label' => 'Published', 'tone' => 'green'];
        }

        if ($exam->status === 'ongoing') {
            return ['key' => 'ongoing', 'label' => 'Ongoing', 'tone' => 'orange'];
        }

        if ($exam->status === 'completed' && $exam->marks_open) {
            return ['key' => 'marks_pending', 'label' => 'Marks Pending', 'tone' => 'yellow'];
        }

        if ($exam->status === 'completed') {
            return ['key' => 'verifying', 'label' => 'Verifying', 'tone' => 'purple'];
        }

        return ['key' => 'upcoming', 'label' => 'Upcoming', 'tone' => 'blue'];
    }

    private function departmentPerformanceRows(Collection $exams, Collection $marks): Collection
    {
        if ($this->deptPerfCache !== null) {
            return $this->deptPerfCache;
        }

        $departments = Department::query()->orderBy('name')->get(['id', 'name', 'code']);

        return $this->deptPerfCache = $departments->map(function (Department $department) use ($exams, $marks) {
            $departmentExams = $exams->where('department_id', $department->id);
            $departmentMarks = $marks->filter(fn (Mark $mark) => (int) $mark->exam?->department_id === (int) $department->id);
            $scores = $departmentMarks->map(fn (Mark $mark) => $this->markPercentage($mark))->filter();

            return [
                'department_id' => $department->id,
                'department' => $department->name,
                'code' => $department->code,
                'exams' => $departmentExams->count(),
                'pass_rate' => $scores->isNotEmpty() ? round($scores->avg(), 1) : 0,
                'score' => $scores->isNotEmpty() ? round($scores->avg(), 1) : 0,
            ];
        })->sortByDesc('pass_rate')->values();
    }

    private function topProgramRows(Collection $marks): Collection
    {
        return $marks->groupBy('program_id')->map(function (Collection $programMarks) {
            $program = $programMarks->first()?->program;
            $scores = $programMarks->map(fn (Mark $mark) => $this->markPercentage($mark))->filter();

            return [
                'program_id' => $program?->id,
                'program' => $program?->name ?? 'Program',
                'code' => $program?->code,
                'pass_rate' => $scores->isNotEmpty() ? round($scores->avg(), 1) : 0,
                'count' => $scores->count(),
            ];
        })->sortByDesc('pass_rate')->values();
    }

    private function subjectDifficultyRows(Collection $marks): Collection
    {
        return $marks->groupBy('subject_id')->map(function (Collection $subjectMarks) {
            $subject = $subjectMarks->first()?->subject;
            $scores = $subjectMarks->map(fn (Mark $mark) => $this->markPercentage($mark))->filter();
            $failCount = $subjectMarks->filter(fn (Mark $mark) => ! $this->markIsPassed($mark) && ! $mark->is_absent && ! $mark->is_withheld)->count();
            $total = $subjectMarks->count();

            return [
                'subject_id' => $subject?->id,
                'subject' => $subject?->name ?? 'Subject',
                'code' => $subject?->code,
                'fail_rate' => $total > 0 ? round(($failCount / $total) * 100, 1) : 0,
                'average' => $scores->isNotEmpty() ? round($scores->avg(), 1) : 0,
                'count' => $total,
            ];
        })->sortByDesc('fail_rate')->values();
    }

    private function teacherContributionRows(Collection $marks): Collection
    {
        return $marks->groupBy('teacher_id')->map(function (Collection $teacherMarks) {
            $teacher = $teacherMarks->first()?->teacher;
            $scores = $teacherMarks->map(fn (Mark $mark) => $this->markPercentage($mark))->filter();

            return [
                'teacher_id' => $teacher?->id,
                'teacher' => $teacher?->user?->name ?? 'Teacher',
                'avatar' => $teacher?->user?->avatar,
                'score' => $scores->isNotEmpty() ? round($scores->avg(), 1) : 0,
                'count' => $scores->count(),
            ];
        })->sortByDesc('score')->values();
    }

    private function backExamTrendRows(Collection $exams, Collection $marks): Collection
    {
        return $exams->filter(fn (Exam $exam) => Str::contains(Str::lower($exam->type), 'back'))
            ->groupBy(fn (Exam $exam) => $exam->academicSession?->name_bs ?? 'Unknown')
            ->map(function (Collection $yearExams, $year) use ($marks) {
                return [
                    'label' => (string) $year,
                    'count' => $yearExams->count(),
                    'pass_rate' => $yearExams->avg(fn (Exam $exam) => $this->examPassRate($marks->where('exam_id', $exam->id))),
                ];
            })->sortKeys()->values();
    }

    private function yearTrendRows(Collection $marks): Collection
    {
        // Group by the academic session BS name (already eagerly loaded — zero bsDate conversions needed)
        return $marks->groupBy(fn (Mark $mark) => $mark->exam?->academicSession?->name_bs ?? 'Unknown')
            ->map(function (Collection $yearMarks, $year) {
                $scores = $yearMarks->map(fn (Mark $mark) => $this->markPercentage($mark))->filter();

                return [
                    'label' => (string) $year,
                    'pass_rate' => $scores->isNotEmpty() ? round($scores->avg(), 1) : 0,
                    'count' => $scores->count(),
                ];
            })->sortKeys()->values();
    }

    private function gradeDistributionRows(Collection $marks): Collection
    {
        $bands = ['A' => 0, 'B' => 0, 'C' => 0, 'Fail' => 0];

        foreach ($marks as $mark) {
            if (! $mark->subject) {
                continue;
            }

            $percentage = $this->markPercentage($mark);
            $band = $this->gradeBand($percentage);
            $bands[$band] = ($bands[$band] ?? 0) + 1;
        }

        return collect($bands)->map(fn ($count, $label) => ['label' => $label, 'count' => $count])->values();
    }

    /**
     * Compute mark percentage using raw attribute values (bypasses decimal:2 cast to avoid BCMath overhead).
     * Result is cached by mark ID for the lifetime of the request.
     */
    private function markPercentage(Mark $mark): ?float
    {
        $id = $mark->id;
        if (array_key_exists($id, $this->markPercentageCache)) {
            return $this->markPercentageCache[$id];
        }

        if ($mark->is_absent || $mark->is_withheld || ! $mark->subject) {
            return $this->markPercentageCache[$id] = null;
        }

        if ($this->examCategoryForMark($mark) === 'monthly_assessment') {
            $full = (float) ($mark->assessment_full_marks ?? 0);
            $obtained = $mark->assessment_obtained_marks;

            if ($full <= 0 || $obtained === null) {
                return $this->markPercentageCache[$id] = null;
            }

            return $this->markPercentageCache[$id] = ((float) $obtained / $full) * 100;
        }

        $scheme = $this->markingSchemeForMark($mark);
        $fullMarks = (float) $scheme['full_internal_theory']
            + (float) $scheme['full_external_theory']
            + (float) $scheme['full_internal_practical']
            + (float) $scheme['full_external_practical'];

        if ($fullMarks <= 0) {
            return $this->markPercentageCache[$id] = null;
        }

        // Use getAttributes() to read raw DB values, bypassing the decimal:2 cast
        // which would otherwise create brick/math Decimal objects on every access.
        $attrs      = $mark->getAttributes();
        $totalMarks = (float) ($attrs['internal_theory_marks'] ?? 0)
            + (float) ($attrs['external_theory_marks'] ?? 0)
            + (float) ($attrs['internal_practical_marks'] ?? 0)
            + (float) ($attrs['external_practical_marks'] ?? 0);

        return $this->markPercentageCache[$id] = ($totalMarks / $fullMarks) * 100;
    }

    private function markResultRemark(Mark $mark): string
    {
        if ($mark->is_absent) {
            return 'Absent';
        }

        if ($mark->is_withheld) {
            return 'Withheld';
        }

        if ($mark->is_delayed) {
            return 'Delayed';
        }

        return $this->markIsPassed($mark) ? 'Pass' : 'Fail';
    }

    private function markIsFilled(Mark $mark): bool
    {
        if ($mark->is_absent || $mark->is_withheld || $mark->is_delayed) {
            return true;
        }

        if ($this->examCategoryForMark($mark) === 'monthly_assessment') {
            return $mark->assessment_obtained_marks !== null;
        }

        return $mark->internal_theory_marks !== null
            || $mark->external_theory_marks !== null
            || $mark->internal_practical_marks !== null
            || $mark->external_practical_marks !== null;
    }

    private function examCategoryForMark(Mark $mark): string
    {
        $examId = (int) $mark->exam_id;

        if (! isset($this->examCategoryCache[$examId])) {
            $this->examCategoryCache[$examId] = (string) (Exam::query()->whereKey($examId)->value('category') ?: 'ctevt_final');
        }

        return $this->examCategoryCache[$examId];
    }

    private function primeExamMarkingSchemeCache(Exam $exam): void
    {
        $schemes = $exam->relationLoaded('markingSchemes')
            ? $exam->markingSchemes
            : ExamSubjectMarkingScheme::query()->where('exam_id', $exam->id)->get();

        foreach ($schemes as $scheme) {
            $this->examSubjectMarkingSchemeCache[$this->schemeCacheKey((int) $scheme->exam_id, (int) $scheme->subject_id)] = [
                'full_internal_theory' => (float) $scheme->full_marks_internal_theory,
                'pass_internal_theory' => (float) $scheme->pass_marks_internal_theory,
                'full_external_theory' => (float) $scheme->full_marks_external_theory,
                'pass_external_theory' => (float) $scheme->pass_marks_external_theory,
                'full_internal_practical' => (float) $scheme->full_marks_internal_practical,
                'pass_internal_practical' => (float) $scheme->pass_marks_internal_practical,
                'full_external_practical' => (float) $scheme->full_marks_external_practical,
                'pass_external_practical' => (float) $scheme->pass_marks_external_practical,
            ];
        }
    }

    private function markingSchemeForMark(Mark $mark): array
    {
        if (! $mark->subject) {
            return [
                'full_internal_theory' => 0.0,
                'pass_internal_theory' => 0.0,
                'full_external_theory' => 0.0,
                'pass_external_theory' => 0.0,
                'full_internal_practical' => 0.0,
                'pass_internal_practical' => 0.0,
                'full_external_practical' => 0.0,
                'pass_external_practical' => 0.0,
            ];
        }

        return $this->subjectMarkingSchemeForExamId((int) $mark->exam_id, $mark->subject);
    }

    private function subjectMarkingSchemeForExam(Exam $exam, Subject $subject): array
    {
        return $this->subjectMarkingSchemeForExamId((int) $exam->id, $subject);
    }

    private function subjectMarkingSchemeForExamId(int $examId, Subject $subject): array
    {
        $cacheKey = $this->schemeCacheKey($examId, (int) $subject->id);

        if (! array_key_exists($cacheKey, $this->examSubjectMarkingSchemeCache)) {
            $scheme = ExamSubjectMarkingScheme::query()
                ->where('exam_id', $examId)
                ->where('subject_id', $subject->id)
                ->first();

            $this->examSubjectMarkingSchemeCache[$cacheKey] = $scheme
                ? [
                    'full_internal_theory' => (float) $scheme->full_marks_internal_theory,
                    'pass_internal_theory' => (float) $scheme->pass_marks_internal_theory,
                    'full_external_theory' => (float) $scheme->full_marks_external_theory,
                    'pass_external_theory' => (float) $scheme->pass_marks_external_theory,
                    'full_internal_practical' => (float) $scheme->full_marks_internal_practical,
                    'pass_internal_practical' => (float) $scheme->pass_marks_internal_practical,
                    'full_external_practical' => (float) $scheme->full_marks_external_practical,
                    'pass_external_practical' => (float) $scheme->pass_marks_external_practical,
                ]
                : $this->subjectDefaultMarkingScheme($subject);
        }

        return $this->examSubjectMarkingSchemeCache[$cacheKey];
    }

    private function subjectDefaultMarkingScheme(Subject $subject): array
    {
        return [
            'full_internal_theory' => (float) ($subject->full_marks_internal_theory ?? 0),
            'pass_internal_theory' => (float) ($subject->pass_marks_internal_theory ?? 0),
            'full_external_theory' => (float) ($subject->full_marks_external_theory ?? 0),
            'pass_external_theory' => (float) ($subject->pass_marks_external_theory ?? 0),
            'full_internal_practical' => (float) ($subject->full_marks_internal_practical ?? 0),
            'pass_internal_practical' => (float) ($subject->pass_marks_internal_practical ?? 0),
            'full_external_practical' => (float) ($subject->full_marks_external_practical ?? 0),
            'pass_external_practical' => (float) ($subject->pass_marks_external_practical ?? 0),
        ];
    }

    private function schemeCacheKey(int $examId, int $subjectId): string
    {
        return $examId . ':' . $subjectId;
    }

    private function practicalThresholdApplies(array $scheme): bool
    {
        return (float) ($scheme['full_internal_practical'] ?? 0) > 0
            || (float) ($scheme['full_external_practical'] ?? 0) > 0
            || (float) ($scheme['pass_internal_practical'] ?? 0) > 0
            || (float) ($scheme['pass_external_practical'] ?? 0) > 0;
    }

    private function gradeBand(?float $percentage): string
    {
        if ($percentage === null) {
            return 'Fail';
        }

        if ($percentage >= 80) {
            return 'A';
        }

        if ($percentage >= 70) {
            return 'B';
        }

        if ($percentage >= 60) {
            return 'C';
        }

        return 'Fail';
    }

    private function gradePoint(?float $percentage): float
    {
        if ($percentage === null) {
            return 0.0;
        }

        return match (true) {
            $percentage >= 80 => 4.0,
            $percentage >= 70 => 3.0,
            $percentage >= 60 => 2.0,
            $percentage >= 50 => 1.0,
            default => 0.0,
        };
    }

    private function subjectTypeLabel(?string $type): string
    {
        return match ($type) {
            'theory' => 'Theory',
            'practical' => 'Practical',
            'both' => 'Theory + Practical',
            default => $type ? Str::headline($type) : 'Theory',
        };
    }

    private function typeOptions(): array
    {
        return [
            'regular' => 'Regular Semester Exam',
            'back' => 'Back / Partial Exam',
            'internal' => 'Internal / Monthly Test',
            'practical' => 'Practical Exam',
        ];
    }

    private function categoryOptions(): array
    {
        return [
            'ctevt_final' => 'CTEVT Final',
            'monthly_assessment' => 'Monthly Test / Assessment',
        ];
    }

    private function statusOptions(): array
    {
        return [
            'upcoming' => 'Upcoming',
            'ongoing' => 'Ongoing',
            'completed' => 'Completed',
            'results_published' => 'Published',
        ];
    }

    private function trendLabel(float|int $current, float|int $previous, bool $asPercentage = true): string
    {
        $delta = $current - $previous;
        $sign = $delta >= 0 ? '+' : '';

        return $sign . number_format($delta, 1) . ($asPercentage ? '%' : '');
    }

    private function trendDirection(float|int $current, float|int $previous, bool $higherIsBetter = true): string
    {
        if ($current === $previous) {
            return 'flat';
        }

        $improved = $current > $previous;
        if (! $higherIsBetter) {
            $improved = ! $improved;
        }

        return $improved ? 'up' : 'down';
    }

    private function sparklinePath(array $values, int $width = 96, int $height = 28): string
    {
        if ($values === []) {
            $values = [0, 0, 0, 0, 0];
        }

        $max = max(100, max($values));
        $min = 0;
        $count = count($values);
        $stepX = $count > 1 ? $width / ($count - 1) : $width;
        $points = [];

        foreach ($values as $index => $value) {
            $x = round($stepX * $index, 2);
            $normalized = $max > $min ? (($value - $min) / ($max - $min)) : 0;
            $y = round($height - ($normalized * $height), 2);
            $points[] = [$x, $y];
        }

        $path = 'M ' . $points[0][0] . ' ' . $points[0][1];
        foreach (array_slice($points, 1) as [$x, $y]) {
            $path .= ' L ' . $x . ' ' . $y;
        }

        return $path;
    }

    private function windowSeries(Collection $exams, callable $resolver): array
    {
        $series = [];
        foreach ($exams->sortBy('start_date')->take(-6) as $exam) {
            $series[] = (float) $resolver($exam);
        }

        return $series;
    }

    private function exportFilename(string $format): string
    {
        $stamp = str_replace([' ', ':'], ['_', '-'], bsDate(now(), 'd_F_Y_H_i'));

        return "exam-results-{$stamp}.{$format}";
    }

    private function subjectMarkExportFilename(Exam $exam, string $format, ?Subject $subject = null): string
    {
        $stamp = str_replace([' ', ':'], ['_', '-'], bsDate(now(), 'd_F_Y_H_i'));
        $examKey = Str::slug($exam->name ?: 'exam', '-');
        $subjectKey = $subject ? '-' . Str::slug($subject->code ?: $subject->name ?: 'subject', '-') : '';

        return "subject-marks-{$examKey}{$subjectKey}-{$stamp}.{$format}";
    }

    private function writeDelimitedLine($handle, array $values, string $separator): void
    {
        $escaped = array_map(function ($value) use ($separator) {
            $value = (string) $value;
            if ($separator === ',') {
                return '"' . str_replace('"', '""', $value) . '"';
            }

            return str_replace(["\t", "\n", "\r"], ' ', $value);
        }, $values);

        fwrite($handle, implode($separator, $escaped) . "\n");
    }
}
