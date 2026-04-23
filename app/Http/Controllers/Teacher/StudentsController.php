<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\Mark;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentsController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        if (!$teacher) {
            abort(403, 'Teacher profile not found');
        }

        // Get teacher's subjects with program and semester info
        $teacherSubjects = $teacher->subjects()
            ->wherePivot('academic_session_id', $session?->id)
            ->with(['program:id,name,department_id', 'program.department:id,name'])
            ->get();

        // Get unique programs and semesters from teacher's subjects
        $programs = $teacherSubjects->pluck('program')->unique('id');
        $semesters = $teacherSubjects->pluck('semester')->unique()->sort()->values();

        // Build query for students from teacher's assigned subjects
        $query = Student::query()
            ->whereIn('program_id', $teacherSubjects->pluck('program_id'))
            ->whereIn('current_semester', $teacherSubjects->pluck('semester'))
            ->with([
                'user:id,name,email,avatar,phone,gender,dob',
                'program:id,name,department_id',
                'program.department:id,name',
                'academicSession:id,name'
            ])
            ->withCount(['attendances', 'marks']);

        // Apply filters
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('student_no', 'like', "%{$search}%")
                  ->orWhere('roll_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->filled('semester')) {
            $query->where('current_semester', $request->semester);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('section')) {
            $query->where('section', $request->section);
        }

        // Get students with pagination
        $students = $query->latest('id')->paginate(20)->withQueryString();

        // Get statistics
        $totalStudents = (clone $query)->count();
        $activeStudents = (clone $query)->where('status', 'active')->count();
        
        // Get sections for filter
        $sections = Student::whereIn('program_id', $teacherSubjects->pluck('program_id'))
            ->whereIn('current_semester', $teacherSubjects->pluck('semester'))
            ->whereNotNull('section')
            ->distinct()
            ->pluck('section')
            ->sort()
            ->values();

        return view('teacher.students.index', compact(
            'students',
            'teacherSubjects',
            'programs',
            'semesters',
            'sections',
            'session',
            'totalStudents',
            'activeStudents'
        ));
    }

    public function show(Student $student, Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        // Verify teacher has access to this student through assigned subjects
        $hasAccess = $teacher->subjects()
            ->wherePivot('academic_session_id', $session?->id)
            ->where('program_id', $student->program_id)
            ->where('semester', $student->current_semester)
            ->exists();

        if (!$hasAccess) {
            abort(403, 'You do not have access to view this student');
        }

        $student->load([
            'user:id,name,email,avatar,phone,gender,dob,address',
            'program:id,name,department_id',
            'program.department:id,name',
            'academicSession:id,name',
            'parents.user:id,name,email,phone'
        ]);

        // Get attendance statistics
        $attendanceStats = $this->getAttendanceStats($student, $teacher);
        
        // Get marks summary for teacher's subjects only
        $marksSummary = $this->getMarksSummary($student, $teacher);
        
        // Get monthly attendance for chart (last 6 months)
        $monthlyAttendance = $this->getMonthlyAttendance($student, $teacher);
        
        // Get assignments for teacher's subjects
        $assignments = $this->getAssignments($student, $teacher);

        return view('teacher.students.show', compact(
            'student',
            'attendanceStats',
            'marksSummary',
            'monthlyAttendance',
            'assignments',
            'session'
        ));
    }

    private function getAttendanceStats($student, $teacher)
    {
        $attendanceRecords = DB::table('attendances')
            ->join('attendance_sessions', 'attendances.attendance_session_id', '=', 'attendance_sessions.id')
            ->where('attendances.student_id', $student->id)
            ->where('attendance_sessions.teacher_id', $teacher->id)
            ->select('attendances.*', 'attendance_sessions.date')
            ->get();

        $totalClasses = $attendanceRecords->count();
        $presentCount = $attendanceRecords->where('status', 'present')->count();
        $absentCount = $attendanceRecords->where('status', 'absent')->count();
        $attendanceRate = $totalClasses > 0 ? round(($presentCount / $totalClasses) * 100, 1) : 0;

        return [
            'total_classes' => $totalClasses,
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'attendance_rate' => $attendanceRate
        ];
    }

    private function getMarksSummary($student, $teacher)
    {
        return Mark::where('student_id', $student->id)
            ->where('teacher_id', $teacher->id)
            ->with(['subject:id,name,code', 'exam:id,name,type'])
            ->latest('created_at')
            ->take(10)
            ->get();
    }

    private function getMonthlyAttendance($student, $teacher)
    {
        return DB::table('attendances')
            ->join('attendance_sessions', 'attendances.attendance_session_id', '=', 'attendance_sessions.id')
            ->select(
                DB::raw('DATE_FORMAT(attendance_sessions.date, "%Y-%m") as month'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN attendances.status = "present" THEN 1 ELSE 0 END) as present')
            )
            ->where('attendances.student_id', $student->id)
            ->where('attendance_sessions.teacher_id', $teacher->id)
            ->where('attendance_sessions.date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function($row) {
                $date = \Carbon\Carbon::parse($row->month . '-01');
                return [
                    'label' => bsDate($date, 'F Y'),
                    'present' => (int) $row->present,
                    'absent' => (int) ($row->total - $row->present),
                    'total' => (int) $row->total,
                ];
            });
    }

    private function getAssignments($student, $teacher)
    {
        return DB::table('assignments')
            ->join('subjects', 'assignments.subject_id', '=', 'subjects.id')
            ->leftJoin('assignment_submissions', function ($join) use ($student) {
                $join->on('assignments.id', '=', 'assignment_submissions.assignment_id')
                    ->where('assignment_submissions.student_id', '=', $student->id);
            })
            ->where('assignments.program_id', $student->program_id)
            ->where('assignments.semester', $student->current_semester)
            ->where('assignments.teacher_id', $teacher->id)
            ->select(
                'assignments.*',
                'subjects.name as subject_name',
                'assignment_submissions.id as submission_id',
                'assignment_submissions.status as submission_status',
                'assignment_submissions.created_at as submission_date',
                'assignment_submissions.marks_obtained as obtained_marks'
            )
            ->orderByDesc('assignments.due_date')
            ->get();
    }
}
