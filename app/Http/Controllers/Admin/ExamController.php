<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Department;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\Program;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->resolveFilters($request);
        $examQuery = $this->baseExamQuery($filters);
        $markQuery = $this->baseMarkQuery($filters);

        $allExams = (clone $examQuery)->get();
        $allMarks = (clone $markQuery)->get();

        $exams = (clone $examQuery)
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();
        $exams->setCollection($this->decorateExamRows($exams->getCollection(), $allMarks));

        return view('admin.exams.index', [
            'filters' => $filters,
            'exams' => $exams,
            'kpis' => $this->buildKpis($allExams, $allMarks),
            'charts' => $this->buildCharts($allExams, $allMarks),
            'sessions' => AcademicSession::query()->orderByDesc('start_date')->get(['id', 'name', 'name_bs']),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name', 'code']),
            'programs' => Program::query()->with('department:id,name,code')->orderBy('name')->get(['id', 'department_id', 'name', 'code']),
            'typeOptions' => $this->typeOptions(),
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
        $data['marks_open'] = $request->boolean('marks_open');
        $data['is_published'] = $data['status'] === 'results_published';
        $data['published_at'] = $data['is_published'] ? now() : null;

        $programIds = collect($request->input('program_ids', []))->filter()->map(fn ($value) => (int) $value)->values();
        $semester = (int) $data['semester'];
        unset($data['program_ids'], $data['semester']);

        $exam = Exam::create($data);
        $this->syncProgramAssignments($exam, $programIds, $semester);

        return redirect()->route('admin.exams.show', $exam)->with('success', 'Exam created successfully.');
    }

    public function show(Exam $exam)
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

        $subjectRows = $this->buildSubjectRows($exam);
        $studentRows = $this->buildStudentResults($exam, $subjectRows);

        return view('admin.exams.show', [
            'exam' => $exam,
            'subjectRows' => $subjectRows,
            'markRows' => $subjectRows,
            'verificationRows' => $subjectRows,
            'studentRows' => $studentRows,
            'summary' => $this->buildExamSummary($exam, $subjectRows, $studentRows),
            'charts' => $this->buildExamCharts($exam, $subjectRows, $studentRows),
            'topPerformers' => $studentRows->sortByDesc('percentage')->take(5)->values(),
            'published' => $exam->is_published || $exam->status === 'results_published',
        ]);
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
        $exam->load(['programs:id,name,code,department_id']);

        return view('admin.exams.edit', $this->formPayload($exam));
    }

    public function update(Request $request, Exam $exam)
    {
        $data = $this->validateExam($request, $exam->id);
        $data['marks_open'] = $request->boolean('marks_open');
        $data['is_published'] = $exam->isPublishedState || $data['status'] === 'results_published';
        $data['published_at'] = $data['is_published'] && ! $exam->published_at ? now() : $exam->published_at;

        $programIds = collect($request->input('program_ids', []))->filter()->map(fn ($value) => (int) $value)->values();
        $semester = (int) $data['semester'];
        unset($data['program_ids'], $data['semester']);

        $exam->update($data);
        $this->syncProgramAssignments($exam, $programIds, $semester);

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

        return redirect()->route('admin.exams.show', $exam)->with('success', "Exam '{$exam->name}' has been published.");
    }

    public function export(Request $request, string $format)
    {
        abort_unless(in_array($format, ['pdf', 'csv', 'excel'], true), 404);

        $filters = $this->resolveFilters($request);
        $examQuery = $this->baseExamQuery($filters);
        $allExams = (clone $examQuery)->get();
        $allMarks = (clone $this->baseMarkQuery($filters))->get();
        $rows = $this->decorateExamRows($allExams, $allMarks);

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('admin.exams.export', [
                'rows' => $rows,
                'generatedAt' => now(),
                'filters' => $filters,
            ])->setPaper('a4', 'landscape');

            return $pdf->download($this->exportFilename('pdf'));
        }

        $columns = [
            'Exam Name', 'Type', 'Department', 'Programs', 'Semester', 'Start Date', 'End Date', 'Status', 'Pass Rate', 'Avg Score',
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
                    $row['pass_rate'] . '%',
                    $row['average_score'] . '%',
                ], $separator);
            }

            fclose($handle);
        }, $this->exportFilename($format), ['Content-Type' => $mime]);
    }

    private function formPayload(?Exam $exam = null): array
    {
        return [
            'exam' => $exam,
            'sessions' => AcademicSession::query()->orderByDesc('start_date')->get(['id', 'name', 'name_bs']),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name', 'code']),
            'programs' => Program::query()->with('department:id,name,code')->orderBy('name')->get(['id', 'department_id', 'name', 'code']),
            'currentSession' => AcademicSession::current(),
            'typeOptions' => $this->typeOptions(),
            'statusOptions' => $this->statusOptions(),
            'semesterOptions' => range(1, 8),
            'selectedProgramIds' => $exam ? $exam->programs->pluck('id')->all() : [],
        ];
    }

    private function validateExam(Request $request, ?int $examId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:regular,back,internal,practical'],
            'academic_session_id' => ['required', 'exists:academic_sessions,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'semester' => ['required', 'integer', 'min:1', 'max:8'],
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

    private function syncProgramAssignments(Exam $exam, Collection $programIds, int $semester): void
    {
        $sync = $programIds->mapWithKeys(fn (int $programId) => [$programId => ['semester' => $semester]])->all();
        $exam->programs()->sync($sync);
    }

    private function decorateExamRows(Collection $exams, Collection $marks): Collection
    {
        return $exams->map(function (Exam $exam) use ($marks) {
            $examMarks = $marks->where('exam_id', $exam->id);
            $subjectRows = $this->buildSubjectRows($exam);
            $studentRows = $this->buildStudentResults($exam, $subjectRows);
            $state = $this->examState($exam);

            return [
                'id' => $exam->id,
                'exam' => $exam,
                'name' => $exam->name,
                'type' => $exam->type,
                'type_label' => $this->typeOptions()[$exam->type] ?? Str::headline($exam->type),
                'department_label' => $exam->department?->code ? $exam->department->code . ' - ' . $exam->department->name : ($exam->department?->name ?? 'Common'),
                'programs_label' => $exam->programs->map(function (Program $program) {
                    $semester = $program->pivot?->semester;
                    return trim(($program->code ? $program->code . ' - ' : '') . $program->name) . ($semester ? ' · Sem ' . $semester : '');
                })->implode(' · ') ?: 'Unassigned',
                'semester_label' => $exam->programs->pluck('pivot.semester')->filter()->unique()->sort()->map(fn ($semester) => 'Sem ' . $semester)->implode(' · ') ?: '—',
                'total_subjects' => $subjectRows->count() ?: $exam->marks->pluck('subject_id')->unique()->count(),
                'total_students' => $studentRows->count(),
                'start_date_label' => bsDate($exam->start_date, 'Y, F d') ?: '—',
                'end_date_label' => bsDate($exam->end_date, 'Y, F d') ?: '—',
                'status_key' => $state['key'],
                'status_label' => $state['label'],
                'status_tone' => $state['tone'],
                'marks_count' => $examMarks->count(),
                'submitted_marks_count' => $exam->submitted_marks_count ?? $examMarks->whereIn('status', ['submitted', 'approved', 'published'])->count(),
                'approved_marks_count' => $exam->approved_marks_count ?? $examMarks->whereIn('status', ['approved', 'published'])->count(),
                'published_marks_count' => $exam->published_marks_count ?? $examMarks->where('status', 'published')->count(),
                'marks_completion' => $this->marksCompletion($subjectRows, $studentRows, $examMarks),
                'pass_rate' => $this->passRate($studentRows),
                'average_score' => $this->averageScore($studentRows),
                'sparkline' => $this->sparklinePath($studentRows->pluck('percentage')->map(fn ($value) => (float) $value)->all()),
            ];
        })->values();
    }

    private function buildKpis(Collection $exams, Collection $marks): array
    {
        $examStates = $exams->map(fn (Exam $exam) => $this->examState($exam)['key']);
        $runningCount = $examStates->filter(fn ($state) => in_array($state, ['upcoming', 'ongoing', 'marks_pending', 'verifying'], true))->count();
        $pendingMarks = $examStates->filter(fn ($state) => $state === 'marks_pending')->count();
        $resultsPendingReview = $examStates->filter(fn ($state) => $state === 'verifying')->count();
        $publishedMarks = $marks->filter(fn (Mark $mark) => $mark->status === 'published');
        $latestExam = $exams->sortByDesc(fn (Exam $exam) => $exam->start_date?->timestamp ?? $exam->created_at?->timestamp ?? 0)->first();
        $latestPassRate = $latestExam ? $this->examPassRate($latestExam->marks) : 0;
        $averageScore = $publishedMarks->isNotEmpty() ? round($publishedMarks->map(fn (Mark $mark) => $this->markPercentage($mark))->filter()->avg(), 1) : 0;

        $departmentRows = $this->departmentPerformanceRows($exams, $marks);
        $topDepartment = $departmentRows->first();

        $currentWindow = $exams->filter(fn (Exam $exam) => optional($exam->start_date)->gte(now()->copy()->subDays(29)->startOfDay()));
        $previousWindow = $exams->filter(fn (Exam $exam) => optional($exam->start_date)->between(now()->copy()->subDays(59)->startOfDay(), now()->copy()->subDays(30)->endOfDay()));

        return [
            [
                'label' => 'Total Exams Running',
                'value' => number_format($runningCount),
                'trend' => $this->trendLabel($currentWindow->count(), $previousWindow->count(), false),
                'direction' => $this->trendDirection($currentWindow->count(), $previousWindow->count(), false),
                'sparkline' => $this->sparklinePath($this->windowSeries($exams, fn (Exam $exam) => in_array($this->examState($exam)['key'], ['upcoming', 'ongoing', 'marks_pending', 'verifying'], true) ? 1 : 0)),
                'tone' => 'blue',
                'note' => 'Active cycle',
            ],
            [
                'label' => 'Exams Pending Marks',
                'value' => number_format($pendingMarks),
                'trend' => $this->trendLabel($currentWindow->filter(fn (Exam $exam) => $this->examState($exam)['key'] === 'marks_pending')->count(), $previousWindow->filter(fn (Exam $exam) => $this->examState($exam)['key'] === 'marks_pending')->count(), false),
                'direction' => $this->trendDirection($currentWindow->filter(fn (Exam $exam) => $this->examState($exam)['key'] === 'marks_pending')->count(), $previousWindow->filter(fn (Exam $exam) => $this->examState($exam)['key'] === 'marks_pending')->count(), false),
                'sparkline' => $this->sparklinePath($this->windowSeries($exams, fn (Exam $exam) => $this->examState($exam)['key'] === 'marks_pending' ? 1 : 0)),
                'tone' => 'amber',
                'note' => 'Teacher input',
            ],
            [
                'label' => 'Results Pending Review',
                'value' => number_format($resultsPendingReview),
                'trend' => $this->trendLabel($currentWindow->filter(fn (Exam $exam) => $this->examState($exam)['key'] === 'verifying')->count(), $previousWindow->filter(fn (Exam $exam) => $this->examState($exam)['key'] === 'verifying')->count(), false),
                'direction' => $this->trendDirection($currentWindow->filter(fn (Exam $exam) => $this->examState($exam)['key'] === 'verifying')->count(), $previousWindow->filter(fn (Exam $exam) => $this->examState($exam)['key'] === 'verifying')->count(), false),
                'sparkline' => $this->sparklinePath($this->windowSeries($exams, fn (Exam $exam) => $this->examState($exam)['key'] === 'verifying' ? 1 : 0)),
                'tone' => 'violet',
                'note' => 'HOD review',
            ],
            [
                'label' => 'Pass Rate',
                'value' => number_format($latestPassRate, 1) . '%',
                'trend' => $this->trendLabel($latestPassRate, $this->previousExamAverage($exams, $latestExam), true),
                'direction' => $this->trendDirection($latestPassRate, $this->previousExamAverage($exams, $latestExam), true),
                'sparkline' => $this->sparklinePath($this->windowSeries($exams, fn (Exam $exam) => $this->examPassRate($exam->marks))),
                'tone' => 'emerald',
                'note' => 'Latest exam',
            ],
            [
                'label' => 'Average Score',
                'value' => number_format($averageScore, 1) . '%',
                'trend' => $this->trendLabel($averageScore, $this->previousAverageScore($marks), true),
                'direction' => $this->trendDirection($averageScore, $this->previousAverageScore($marks), true),
                'sparkline' => $this->sparklinePath($this->windowSeries($exams, fn (Exam $exam) => $this->averageScore($this->buildStudentResults($exam, $this->buildSubjectRows($exam))))),
                'tone' => 'sky',
                'note' => 'Published marks',
            ],
            [
                'label' => 'Highest Dept',
                'value' => $topDepartment['department'] ?? 'N/A',
                'trend' => $this->trendLabel($topDepartment['pass_rate'] ?? 0, 0, true),
                'direction' => 'up',
                'sparkline' => $this->sparklinePath($departmentRows->pluck('pass_rate')->all()),
                'tone' => 'slate',
                'note' => $topDepartment['code'] ?? 'Department',
            ],
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
            'backExamTrend' => $this->backExamTrendRows($exams),
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

    private function buildSubjectRows(Exam $exam): Collection
    {
        $exam->loadMissing(['programs.department', 'programs.subjects.teachers.user', 'marks.subject', 'marks.teacher.user']);

        $rows = collect();

        foreach ($exam->programs as $program) {
            $semester = (int) ($program->pivot?->semester ?? 0);
            $students = $this->examStudentsForProgram($exam, $program->id, $semester);
            $subjects = $program->subjects->where('semester', $semester);

            foreach ($subjects as $subject) {
                $subjectMarks = $exam->marks->where('subject_id', $subject->id);
                $enteredCount = $subjectMarks->count();
                $submittedCount = $subjectMarks->whereIn('status', ['submitted', 'approved', 'published'])->count();
                $approvedCount = $subjectMarks->whereIn('status', ['approved', 'published'])->count();
                $publishedCount = $subjectMarks->where('status', 'published')->count();
                $teacher = $subject->teachers->first();
                $state = $publishedCount > 0
                    ? ['key' => 'completed', 'label' => 'Completed', 'tone' => 'green']
                    : ($submittedCount > 0 ? ['key' => 'verifying', 'label' => 'Verifying', 'tone' => 'purple'] : ['key' => 'pending', 'label' => 'Pending', 'tone' => 'yellow']);

                $rows->push([
                    'program_id' => $program->id,
                    'program_name' => $program->name,
                    'program_code' => $program->code,
                    'semester' => $semester,
                    'subject_id' => $subject->id,
                    'subject_name' => $subject->name,
                    'subject_code' => $subject->code,
                    'subject_type' => $this->subjectTypeLabel($subject->type),
                    'student_count' => $students->count(),
                    'full_marks' => (float) $subject->totalFullMarks,
                    'pass_marks' => (float) $subject->totalPassMarks,
                    'marks_count' => $enteredCount,
                    'submitted_count' => $submittedCount,
                    'approved_count' => $approvedCount,
                    'published_count' => $publishedCount,
                    'entered_pct' => $students->count() > 0 ? round(($enteredCount / $students->count()) * 100, 1) : 0,
                    'missing_count' => max(0, $students->count() - $enteredCount),
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
                    'last_updated' => $subjectMarks->max('updated_at') ? bsDate($subjectMarks->max('updated_at'), 'Y, F d h:i A') : '—',
                    'status_key' => $state['key'],
                    'status_label' => $state['label'],
                    'status_tone' => $state['tone'],
                    'remarks' => $subjectMarks->pluck('remarks')->filter()->unique()->take(2)->implode(' · '),
                ]);
            }
        }

        return $rows->sortBy([
            ['program_name', 'asc'],
            ['semester', 'asc'],
            ['subject_name', 'asc'],
        ])->values();
    }

    private function buildStudentResults(Exam $exam, Collection $subjectRows): Collection
    {
        $students = $this->examStudents($exam);
        $rows = collect();

        foreach ($students as $student) {
            $rows->push($this->buildStudentPerformance($exam, $student, $subjectRows));
        }

        return $rows->sortByDesc('percentage')->values();
    }

    private function buildStudentPerformance(Exam $exam, Student $student, Collection $subjectRows): array
    {
        $relevantRows = $subjectRows
            ->where('program_id', $student->program_id)
            ->where('semester', $student->current_semester)
            ->values();

        $studentMarks = $exam->marks->where('student_id', $student->id);
        $marksBySubject = $studentMarks->groupBy('subject_id');

        $subjectResults = collect();
        $obtainedTotal = 0.0;
        $fullTotal = 0.0;
        $passCount = 0;

        foreach ($relevantRows as $row) {
            $mark = $marksBySubject->get($row['subject_id'])?->first();
            $obtained = $mark && ! $mark->is_absent && ! $mark->is_withheld ? (float) $mark->total_marks : 0.0;
            $full = (float) $row['full_marks'];
            $percentage = $full > 0 ? round(($obtained / $full) * 100, 1) : 0.0;
            $gradeBand = $this->gradeBand($percentage);

            $subjectResults->push([
                'subject' => $row['subject'],
                'subject_name' => $row['subject_name'],
                'subject_code' => $row['subject_code'],
                'full_marks' => $full,
                'pass_marks' => $row['pass_marks'],
                'obtained' => $obtained,
                'percentage' => $percentage,
                'grade' => $gradeBand,
                'remarks' => $mark?->remarks ?? '',
                'result_status' => $mark?->result_remark ?? 'Pending',
            ]);

            $obtainedTotal += $obtained;
            $fullTotal += $full;
            if ($mark && $mark->is_passed) {
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
            'department' => $student->department?->name,
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
            'last_updated' => $studentMarks->max('updated_at') ? bsDate($studentMarks->max('updated_at'), 'd F Y, h:i A') : '—',
        ];
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
            ->with(['user:id,name,avatar', 'department:id,name,code', 'program:id,name,code'])
            ->where('academic_session_id', $exam->academic_session_id)
            ->whereIn('program_id', $programIds)
            ->whereIn('current_semester', $semesters)
            ->orderBy('roll_number')
            ->orderBy('student_no')
            ->get();
    }

    private function examStudents(Exam $exam): Collection
    {
        return $this->buildExamStudents($exam);
    }

    private function examStudentsForProgram(Exam $exam, int $programId, int $semester): Collection
    {
        return Student::query()
            ->where('academic_session_id', $exam->academic_session_id)
            ->where('program_id', $programId)
            ->where('current_semester', $semester)
            ->get();
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
            return $studentMarks->filter(fn (Mark $mark) => $mark->is_passed)->count() === $studentMarks->count() ? 1 : 0;
        });

        return round(($markRows->sum() / max(1, $markRows->count())) * 100, 1);
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

        return $previous ? $this->examPassRate($previous->marks) : 0.0;
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
        $departments = Department::query()->orderBy('name')->get(['id', 'name', 'code']);

        return $departments->map(function (Department $department) use ($exams, $marks) {
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
            $failCount = $subjectMarks->filter(fn (Mark $mark) => ! $mark->is_passed && ! $mark->is_absent && ! $mark->is_withheld)->count();
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

    private function backExamTrendRows(Collection $exams): Collection
    {
        return $exams->filter(fn (Exam $exam) => Str::contains(Str::lower($exam->type), 'back'))->groupBy(fn (Exam $exam) => bsDate($exam->start_date, 'Y') ?: 'Unknown')->map(function (Collection $yearExams, $year) {
            return [
                'label' => (string) $year,
                'count' => $yearExams->count(),
                'pass_rate' => $yearExams->avg(fn (Exam $exam) => $this->examPassRate($exam->marks)),
            ];
        })->sortKeys()->values();
    }

    private function yearTrendRows(Collection $marks): Collection
    {
        return $marks->groupBy(fn (Mark $mark) => bsDate($mark->exam?->start_date, 'Y') ?: 'Unknown')->map(function (Collection $yearMarks, $year) {
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

    private function markPercentage(Mark $mark): ?float
    {
        if ($mark->is_absent || $mark->is_withheld || ! $mark->subject) {
            return null;
        }

        $fullMarks = (float) ($mark->subject->full_marks_internal_theory ?? 0)
            + (float) ($mark->subject->full_marks_external_theory ?? 0)
            + (float) ($mark->subject->full_marks_internal_practical ?? 0)
            + (float) ($mark->subject->full_marks_external_practical ?? 0);

        if ($fullMarks <= 0) {
            return null;
        }

        return ((float) $mark->total_marks / $fullMarks) * 100;
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
