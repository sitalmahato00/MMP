<?php

namespace App\Http\Controllers\HOD;

use App\Models\Exam;
use App\Models\Mark;
use App\Models\Student;
use App\Models\Subject;
use App\Traits\ExportableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * HOD exam and marks management (department-scoped).
 * 
 * HODs can view exam data and results for their department only.
 */
class ExamController extends HodController
{
    use ExportableTrait;
    // ── Index ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Get exams for department
        $query = Exam::where('department_id', $deptId)
            ->with(['academicSession:id,name', 'programs'])
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->where('name', 'like', "%{$term}%");
            })
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->when($request->category, fn ($q) => $q->where('category', $request->category));

        $exams = (clone $query)
            ->latest('created_at')
            ->latest('start_date')
            ->paginate(20)
            ->withQueryString();

        // Stats
        $totalExams = (clone $query)->count();
        $upcomingExams = (clone $query)->where('status', 'upcoming')->count();
        $ongoingExams = (clone $query)->where('status', 'ongoing')->count();
        $completedExams = (clone $query)->where('status', 'completed')->count();

        return view('hod.exams.index', compact(
            'exams', 'department',
            'totalExams', 'upcomingExams', 'ongoingExams', 'completedExams'
        ));
    }

    // ── Create Assessment Exam ─────────────────────────────────────────────
    public function create(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Get active academic session
        $activeSessions = DB::table('academic_sessions')
            ->where('is_active', true)
            ->orderBy('start_date', 'desc')
            ->get();

        // Get department programs
        $programs = DB::table('programs')
            ->where('department_id', $deptId)
            ->select('id', 'name', 'duration_years')
            ->orderBy('name')
            ->get();

        return view('hod.exams.create', compact('department', 'activeSessions', 'programs'));
    }

    // ── Store Assessment Exam ──────────────────────────────────────────────
    public function store(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        $validated = $request->validate([
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'name' => 'required|string|max:255',
            'assessment_number' => 'required|integer|min:1|max:12',
            'assessment_full_marks' => 'required|numeric|min:0',
            'assessment_pass_marks' => 'required|numeric|min:0',
            'start_date_bs' => 'required|string',
            'end_date_bs' => 'nullable|string',
            'programs' => 'required|array|min:1',
            'programs.*' => 'exists:programs,id',
            'semesters' => 'required|array|min:1',
            'semesters.*' => 'string',
        ]);

        // Convert BS dates to AD
        $startDate = adDate($validated['start_date_bs']);
        $endDate = !empty($validated['end_date_bs']) ? adDate($validated['end_date_bs']) : $startDate;

        // Create assessment exam
        $exam = Exam::create([
            'academic_session_id' => $validated['academic_session_id'],
            'department_id' => $deptId,
            'name' => $validated['name'],
            'type' => 'theory',
            'category' => 'monthly_assessment',
            'assessment_number' => $validated['assessment_number'],
            'assessment_full_marks' => $validated['assessment_full_marks'],
            'assessment_pass_marks' => $validated['assessment_pass_marks'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'upcoming',
            'marks_open' => true,
            'is_published' => false,
        ]);

        // Attach programs with semesters
        foreach ($validated['programs'] as $index => $programId) {
            $semesterValue = $validated['semesters'][$index] ?? '1';
            
            // If "all" is selected, attach all semesters (1-8)
            if ($semesterValue === 'all') {
                for ($sem = 1; $sem <= 8; $sem++) {
                    $exam->programs()->attach($programId, ['semester' => $sem]);
                }
            } else {
                // Attach specific semester
                $exam->programs()->attach($programId, ['semester' => (int)$semesterValue]);
            }
        }

        return redirect()->route('hod.exams.index')
            ->with('success', 'Assessment exam created successfully.');
    }

    // ── Edit Assessment Exam ───────────────────────────────────────────────
    public function edit(Request $request, Exam $exam)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Ensure exam belongs to department and is assessment type
        if ($exam->department_id !== $deptId || $exam->category !== 'monthly_assessment') {
            abort(403, 'Unauthorized action.');
        }

        // Get active academic session
        $activeSessions = DB::table('academic_sessions')
            ->where('is_active', true)
            ->orderBy('start_date', 'desc')
            ->get();

        // Get department programs
        $programs = DB::table('programs')
            ->where('department_id', $deptId)
            ->select('id', 'name', 'duration_years')
            ->orderBy('name')
            ->get();

        // Get existing program-semester combinations
        $existingPrograms = $exam->programs()->get()->map(function ($program) {
            return [
                'program_id' => $program->id,
                'semester' => $program->pivot->semester,
            ];
        })->toArray();

        return view('hod.exams.edit', compact('exam', 'department', 'activeSessions', 'programs', 'existingPrograms'));
    }

    // ── Update Assessment Exam ─────────────────────────────────────────────
    public function update(Request $request, Exam $exam)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Ensure exam belongs to department and is assessment type
        if ($exam->department_id !== $deptId || $exam->category !== 'monthly_assessment') {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'name' => 'required|string|max:255',
            'assessment_number' => 'required|integer|min:1|max:12',
            'assessment_full_marks' => 'required|numeric|min:0',
            'assessment_pass_marks' => 'required|numeric|min:0',
            'start_date_bs' => 'required|string',
            'end_date_bs' => 'nullable|string',
            'programs' => 'required|array|min:1',
            'programs.*' => 'exists:programs,id',
            'semesters' => 'required|array|min:1',
            'semesters.*' => 'string',
        ]);

        // Convert BS dates to AD
        $startDate = adDate($validated['start_date_bs']);
        $endDate = !empty($validated['end_date_bs']) ? adDate($validated['end_date_bs']) : $startDate;

        // Update exam
        $exam->update([
            'academic_session_id' => $validated['academic_session_id'],
            'name' => $validated['name'],
            'assessment_number' => $validated['assessment_number'],
            'assessment_full_marks' => $validated['assessment_full_marks'],
            'assessment_pass_marks' => $validated['assessment_pass_marks'],
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        // Detach all existing programs
        $exam->programs()->detach();

        // Attach updated programs with semesters
        foreach ($validated['programs'] as $index => $programId) {
            $semesterValue = $validated['semesters'][$index] ?? '1';
            
            // If "all" is selected, attach all semesters (1-8)
            if ($semesterValue === 'all') {
                for ($sem = 1; $sem <= 8; $sem++) {
                    $exam->programs()->attach($programId, ['semester' => $sem]);
                }
            } else {
                // Attach specific semester
                $exam->programs()->attach($programId, ['semester' => (int)$semesterValue]);
            }
        }

        return redirect()->route('hod.exams.index')
            ->with('success', 'Assessment exam updated successfully.');
    }

    // ── Delete Assessment Exam ─────────────────────────────────────────────
    public function destroy(Request $request, Exam $exam)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Ensure exam belongs to department
        if ($exam->department_id !== $deptId) {
            abort(403, 'Unauthorized action.');
        }

        // For assessment exams, check if marks exist (but allow deletion anyway if forced)
        if ($exam->category === 'monthly_assessment') {
            $hasMarks = Mark::where('exam_id', $exam->id)->exists();
            
            if ($hasMarks && !$request->has('force')) {
                return redirect()->route('hod.exams.index')
                    ->with('warning', 'This exam has marks. Are you sure you want to delete it? This will also delete all associated marks.')
                    ->with('delete_exam_id', $exam->id);
            }
        }

        // Delete all marks first
        Mark::where('exam_id', $exam->id)->delete();
        
        // Delete exam
        $exam->delete();

        return redirect()->route('hod.exams.index')
            ->with('success', 'Exam and all associated marks deleted successfully.');
    }

    // ── Force Delete Exam ──────────────────────────────────────────────────
    public function forceDestroy(Request $request, Exam $exam)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Ensure exam belongs to department
        if ($exam->department_id !== $deptId) {
            abort(403, 'Unauthorized action.');
        }

        // Delete all marks first
        Mark::where('exam_id', $exam->id)->delete();
        
        // Delete exam
        $exam->delete();

        return redirect()->route('hod.exams.index')
            ->with('success', 'Exam and all associated marks deleted successfully.');
    }

    // ── Marks ──────────────────────────────────────────────────────────────
    public function marks(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        $examId = $request->exam_id;
        
        if (!$examId) {
            return redirect()->route('hod.exams.index')
                ->with('error', 'Please select an exam to view marks.');
        }

        $exam = Exam::where('department_id', $deptId)
            ->with(['academicSession:id,name', 'programs'])
            ->findOrFail($examId);

        // Get marks for this exam with filters — grouped display, no pagination
        $marksQuery = Mark::where('exam_id', $examId)
            ->with([
                'student.user:id,name,email',
                'student.program:id,name',
                'subject:id,name,code',
            ])
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->whereHas('student.user', fn ($uq) => $uq->where('name', 'like', "%{$term}%"));
            })
            ->when($request->subject_id, fn ($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->semester,   fn ($q) => $q->where('semester', $request->semester))
            ->when($request->program_id, function ($q) use ($request) {
                $q->whereHas('student', fn ($sq) => $sq->where('program_id', $request->program_id));
            })
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderBy('semester')
            ->orderBy('subject_id')
            ->orderBy('student_id');

        $allMarks = $marksQuery->get();

        // Group: semester → subject_id → marks collection
        $groupedMarks = $allMarks
            ->groupBy('semester')
            ->map(fn ($semMarks) => $semMarks->groupBy('subject_id'));

        // Subjects for filter
        $subjects = Subject::whereHas('program', fn ($q) => $q->where('department_id', $deptId))
            ->select('id', 'name', 'code', 'semester')
            ->orderBy('semester')
            ->orderBy('name')
            ->get();

        // Programs for filter
        $programs = DB::table('programs')
            ->where('department_id', $deptId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // Available semesters in this exam's marks
        $semesters = Mark::where('exam_id', $examId)
            ->distinct()
            ->orderBy('semester')
            ->pluck('semester');

        // Stats
        $totalMarks   = Mark::where('exam_id', $examId)->count();
        $pendingMarks  = Mark::where('exam_id', $examId)->where('status', 'draft')->count();
        $submittedMarks = Mark::where('exam_id', $examId)->where('status', 'submitted')->count();
        $approvedMarks  = Mark::where('exam_id', $examId)->where('status', 'approved')->count();

        return view('hod.exams.marks', compact(
            'exam', 'groupedMarks', 'department', 'subjects', 'programs', 'semesters',
            'totalMarks', 'pendingMarks', 'submittedMarks', 'approvedMarks'
        ));
    }

    // ── Fill Marks ─────────────────────────────────────────────────────────
    public function fillMarks(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        $examId = $request->exam_id;
        
        if (!$examId) {
            return redirect()->route('hod.exams.index')
                ->with('error', 'Please select an exam to fill marks.');
        }

        $exam = Exam::where('department_id', $deptId)
            ->with(['academicSession:id,name', 'programs'])
            ->findOrFail($examId);

        // Get programs and subjects for this exam
        $programs = $exam->programs;
        
        $programId = $request->program_id ?? $programs->first()?->id;
        $semester = $request->semester ?? $programs->first()?->pivot->semester ?? 1;
        $subjectId = $request->subject_id;

        if (!$programId || !$subjectId) {
            // Show selection form
            $subjects = Subject::where('program_id', $programId)
                ->where('semester', $semester)
                ->select('id', 'name', 'code', 'type')
                ->orderBy('name')
                ->get();

            return view('hod.exams.fill-marks-select', compact(
                'exam', 'department', 'programs', 'subjects', 'programId', 'semester'
            ));
        }

        // Get students and their marks
        $subject = Subject::findOrFail($subjectId);
        
        $students = Student::where('department_id', $deptId)
            ->where('program_id', $programId)
            ->where('current_semester', $semester)
            ->where('status', 'active')
            ->with(['user:id,name,email'])
            ->orderBy('roll_number')
            ->get();

        // Get existing marks
        $existingMarks = Mark::where('exam_id', $examId)
            ->where('subject_id', $subjectId)
            ->where('program_id', $programId)
            ->get()
            ->keyBy('student_id');

        return view('hod.exams.fill-marks', compact(
            'exam', 'department', 'subject', 'students', 'existingMarks', 'programId', 'semester'
        ));
    }

    // ── Save Marks ─────────────────────────────────────────────────────────
    public function saveMarks(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'subject_id' => 'required|exists:subjects,id',
            'program_id' => 'required|exists:programs,id',
            'semester' => 'required|integer|min:1|max:8',
            'marks' => 'required|array',
            'marks.*.student_id' => 'required|exists:students,id',
            'marks.*.is_absent' => 'nullable|boolean',
            'marks.*.assessment_obtained_marks' => 'nullable|numeric|min:0',
            'marks.*.internal_theory_marks' => 'nullable|numeric|min:0',
            'marks.*.external_theory_marks' => 'nullable|numeric|min:0',
            'marks.*.internal_practical_marks' => 'nullable|numeric|min:0',
            'marks.*.external_practical_marks' => 'nullable|numeric|min:0',
            'marks.*.remarks' => 'nullable|string|max:500',
            'overwrite' => 'nullable|boolean', // Allow overwriting existing marks
        ]);

        $exam = Exam::where('department_id', $deptId)->findOrFail($validated['exam_id']);
        $subject = Subject::findOrFail($validated['subject_id']);

        foreach ($validated['marks'] as $markData) {
            $isAbsent = $markData['is_absent'] ?? false;

            $data = [
                'exam_id' => $exam->id,
                'student_id' => $markData['student_id'],
                'subject_id' => $subject->id,
                'program_id' => $validated['program_id'],
                'semester' => $validated['semester'],
                'is_absent' => $isAbsent,
                'status' => 'submitted',
                'remarks' => $markData['remarks'] ?? null,
            ];

            if ($exam->category === 'monthly_assessment') {
                // Assessment exam - use exam's assessment marks
                $data['assessment_full_marks'] = $exam->assessment_full_marks ?? 100;
                $data['assessment_pass_marks'] = $exam->assessment_pass_marks ?? 40;
                $data['assessment_obtained_marks'] = $isAbsent ? null : ($markData['assessment_obtained_marks'] ?? null);
            } else {
                // CTEVT exam - just store the marks obtained
                // Validation will use exam_subject_marking_schemes or subject defaults (single source of truth)
                $data['internal_theory_marks'] = $isAbsent ? null : ($markData['internal_theory_marks'] ?? null);
                $data['external_theory_marks'] = $isAbsent ? null : ($markData['external_theory_marks'] ?? null);
                $data['internal_practical_marks'] = $isAbsent ? null : ($markData['internal_practical_marks'] ?? null);
                $data['external_practical_marks'] = $isAbsent ? null : ($markData['external_practical_marks'] ?? null);
            }

            // Check if marks already exist and are published
            $existingMark = Mark::where([
                'exam_id' => $exam->id,
                'student_id' => $markData['student_id'],
                'subject_id' => $subject->id,
            ])->first();

            // Allow overwriting even if published (if overwrite is true)
            if ($existingMark && $existingMark->status === 'published' && !($validated['overwrite'] ?? false)) {
                continue; // Skip this mark if it's published and overwrite is not allowed
            }

            Mark::updateOrCreate(
                [
                    'exam_id' => $exam->id,
                    'student_id' => $markData['student_id'],
                    'subject_id' => $subject->id,
                ],
                $data
            );
        }

        return redirect()->route('hod.exams.marks', ['exam_id' => $exam->id])
            ->with('success', 'Marks saved successfully.');
    }

    // ── Verify Marks ───────────────────────────────────────────────────────
    public function verifyMarks(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'mark_ids' => 'required|array|min:1',
            'mark_ids.*' => 'exists:marks,id',
        ]);

        $exam = Exam::where('department_id', $deptId)->findOrFail($validated['exam_id']);

        // Update marks status from 'submitted' to 'approved'
        $updated = Mark::where('exam_id', $exam->id)
            ->whereIn('id', $validated['mark_ids'])
            ->where('status', 'submitted')
            ->update(['status' => 'approved']);

        return redirect()->back()
            ->with('success', "Verified {$updated} mark(s) successfully.");
    }

    // ── Results ────────────────────────────────────────────────────────────
    public function results(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Student-wise results summary
        $students = Student::where('department_id', $deptId)
            ->with(['user:id,name,email', 'program:id,name'])
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->whereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"));
            })
            ->when($request->program_id, fn ($q) => $q->where('program_id', $request->program_id))
            ->when($request->semester, fn ($q) => $q->where('semester', $request->semester))
            ->paginate(20)
            ->withQueryString();

        // Add exam statistics to each student
        $students->getCollection()->transform(function ($student) {
            $examStats = Mark::where('student_id', $student->id)
                ->selectRaw('COUNT(*) as total_exams')
                ->selectRaw('AVG(marks_obtained) as avg_marks')
                ->selectRaw('SUM(CASE WHEN marks_obtained >= pass_marks THEN 1 ELSE 0 END) as passed_exams')
                ->first();

            $student->total_exams = (int) ($examStats->total_exams ?? 0);
            $student->avg_marks = round($examStats->avg_marks ?? 0, 1);
            $student->passed_exams = (int) ($examStats->passed_exams ?? 0);
            $student->pass_rate = $student->total_exams > 0 
                ? round(($student->passed_exams / $student->total_exams) * 100, 1) 
                : 0;

            return $student;
        });

        // Programs for filter
        $programs = DB::table('programs')
            ->where('department_id', $deptId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('hod.exams.results', compact('students', 'department', 'programs'));
    }

    // ── Analytics ──────────────────────────────────────────────────────────
    public function analytics(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Department exam analytics
        $totalStudents = Student::where('department_id', $deptId)->count();
        
        $examStats = Exam::where('department_id', $deptId)
            ->selectRaw('COUNT(*) as total_exams')
            ->selectRaw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_exams')
            ->selectRaw('SUM(CASE WHEN is_published = 1 THEN 1 ELSE 0 END) as published_exams')
            ->first();

        $markStats = DB::table('marks')
            ->join('exams', 'marks.exam_id', '=', 'exams.id')
            ->where('exams.department_id', $deptId)
            ->selectRaw('COUNT(*) as total_marks')
            ->selectRaw('AVG(marks_obtained) as avg_marks')
            ->selectRaw('SUM(CASE WHEN marks_obtained >= pass_marks THEN 1 ELSE 0 END) as passed_marks')
            ->first();

        $totalExams = (int) ($examStats->total_exams ?? 0);
        $completedExams = (int) ($examStats->completed_exams ?? 0);
        $publishedExams = (int) ($examStats->published_exams ?? 0);
        $totalMarks = (int) ($markStats->total_marks ?? 0);
        $avgMarks = round($markStats->avg_marks ?? 0, 1);
        $passedMarks = (int) ($markStats->passed_marks ?? 0);
        $overallPassRate = $totalMarks > 0 ? round(($passedMarks / $totalMarks) * 100, 1) : 0;

        // Subject-wise performance
        $subjectPerformance = DB::table('marks')
            ->join('subjects', 'marks.subject_id', '=', 'subjects.id')
            ->join('exams', 'marks.exam_id', '=', 'exams.id')
            ->where('exams.department_id', $deptId)
            ->groupBy('subjects.id', 'subjects.name')
            ->selectRaw('subjects.name as subject_name')
            ->selectRaw('COUNT(*) as total_attempts')
            ->selectRaw('AVG(marks_obtained) as avg_marks')
            ->selectRaw('SUM(CASE WHEN marks_obtained >= pass_marks THEN 1 ELSE 0 END) as passed')
            ->orderByDesc('avg_marks')
            ->limit(10)
            ->get();

        $subjectPerformance->transform(function ($item) {
            $item->pass_rate = $item->total_attempts > 0 
                ? round(($item->passed / $item->total_attempts) * 100, 1) 
                : 0;
            return $item;
        });

        return view('hod.exams.analytics', compact(
            'department', 'totalStudents', 'totalExams', 'completedExams', 'publishedExams',
            'totalMarks', 'avgMarks', 'overallPassRate', 'subjectPerformance'
        ));
    }

    // ── Edit Marking Scheme ────────────────────────────────────────────────
    public function editMarkingScheme(Request $request, Exam $exam)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Ensure exam belongs to department
        if ($exam->department_id !== $deptId) {
            abort(403, 'Unauthorized action.');
        }

        // Get subjects for this exam
        $subjects = Subject::whereHas('program', function ($q) use ($exam) {
            $q->whereIn('id', $exam->programs->pluck('id'));
        })->with(['markingScheme' => function ($q) use ($exam) {
            $q->where('exam_id', $exam->id);
        }])->orderBy('name')->get();

        return view('hod.exams.edit-marking-scheme', compact('exam', 'department', 'subjects'));
    }

    // ── Update Marking Scheme ──────────────────────────────────────────────
    public function updateMarkingScheme(Request $request, Exam $exam)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Ensure exam belongs to department
        if ($exam->department_id !== $deptId) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'subjects' => 'required|array|min:1',
            'subjects.*.subject_id' => 'required|exists:subjects,id',
            'subjects.*.full_marks_internal_theory' => 'required|numeric|min:0',
            'subjects.*.pass_marks_internal_theory' => 'required|numeric|min:0',
            'subjects.*.full_marks_external_theory' => 'required|numeric|min:0',
            'subjects.*.pass_marks_external_theory' => 'required|numeric|min:0',
            'subjects.*.full_marks_internal_practical' => 'nullable|numeric|min:0',
            'subjects.*.pass_marks_internal_practical' => 'nullable|numeric|min:0',
            'subjects.*.full_marks_external_practical' => 'nullable|numeric|min:0',
            'subjects.*.pass_marks_external_practical' => 'nullable|numeric|min:0',
        ]);

        foreach ($validated['subjects'] as $subjectData) {
            DB::table('exam_subject_marking_schemes')->updateOrInsert(
                [
                    'exam_id' => $exam->id,
                    'subject_id' => $subjectData['subject_id'],
                ],
                [
                    'full_marks_internal_theory' => $subjectData['full_marks_internal_theory'],
                    'pass_marks_internal_theory' => $subjectData['pass_marks_internal_theory'],
                    'full_marks_external_theory' => $subjectData['full_marks_external_theory'],
                    'pass_marks_external_theory' => $subjectData['pass_marks_external_theory'],
                    'full_marks_internal_practical' => $subjectData['full_marks_internal_practical'] ?? 0,
                    'pass_marks_internal_practical' => $subjectData['pass_marks_internal_practical'] ?? 0,
                    'full_marks_external_practical' => $subjectData['full_marks_external_practical'] ?? 0,
                    'pass_marks_external_practical' => $subjectData['pass_marks_external_practical'] ?? 0,
                    'updated_at' => now(),
                ]
            );
        }

        return redirect()->route('hod.exams.index')
            ->with('success', 'Marking scheme updated successfully.');
    }

    // ── Export Marks ───────────────────────────────────────────────────────
    public function exportMarks(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        $examId = $request->exam_id;
        $format = $request->get('format', 'csv');
        
        if (!$examId) {
            return redirect()->route('hod.exams.index')
                ->with('error', 'Please select an exam to export marks.');
        }

        $exam = Exam::where('department_id', $deptId)
            ->with(['academicSession:id,name', 'programs', 'department'])
            ->findOrFail($examId);

        // Get marks for this exam with filters
        $marks = Mark::where('exam_id', $examId)
            ->with([
                'student.user:id,name,email',
                'student.program:id,name',
                'subject:id,name,code'
            ])
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->whereHas('student.user', fn ($uq) => $uq->where('name', 'like', "%{$term}%"));
            })
            ->when($request->subject_id, fn ($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->program_id, function ($q) use ($request) {
                $q->whereHas('student', fn ($sq) => $sq->where('program_id', $request->program_id));
            })
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderBy('id')
            ->get();

        return $this->exportMarksData($exam, $marks, $department, $format);
    }
}