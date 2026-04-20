<?php

namespace App\Http\Controllers\HOD;

use App\Models\Exam;
use App\Models\Mark;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * HOD exam and marks management (department-scoped).
 * 
 * HODs can view exam data and results for their department only.
 */
class ExamController extends HodController
{
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

        // Get marks for this exam
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
            ->paginate(20)
            ->withQueryString();

        // Subjects for filter
        $subjects = Subject::whereHas('program', fn ($q) => $q->where('department_id', $deptId))
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();

        // Programs for filter
        $programs = DB::table('programs')
            ->where('department_id', $deptId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('hod.exams.marks', compact(
            'exam', 'marks', 'department', 'subjects', 'programs'
        ));
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
}