<?php

namespace App\Modules\HOD\Controllers;


/**
 * HOD reports and analytics (department-scoped).
 * 
 * HODs can generate reports for their department only.
 */
use App\Modules\Academic\Models\Program;
use App\Modules\Academic\Models\Subject;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Models\AttendanceSession;
use App\Modules\Department\Models\Department;
use App\Modules\Exam\Models\Exam;
use App\Modules\Exam\Models\Mark;
use App\Modules\Student\Models\Student;
use App\Modules\Teacher\Models\Teacher;
use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ReportController extends HodController
{
    // ── Index ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Department overview stats
        $totalStudents = Student::where('department_id', $deptId)->count();
        $totalTeachers = Teacher::where('department_id', $deptId)->count();
        $totalPrograms = DB::table('programs')->where('department_id', $deptId)->count();

        // Recent activity stats
        $recentAttendanceSessions = AttendanceSession::whereHas('subject', function ($q) use ($deptId) {
                $q->whereHas('program', fn ($pq) => $pq->where('department_id', $deptId));
            })
            ->whereBetween('date', [now()->subDays(30), now()])
            ->count();

        $recentExams = DB::table('exams')
            ->where('department_id', $deptId)
            ->whereBetween('start_date', [now()->subDays(30), now()])
            ->count();

        // Performance metrics
        $overallAttendanceRate = $this->calculateAttendanceRate($deptId);
        $overallPassRate = $this->calculatePassRate($deptId);

        return view('hod.reports.index', compact(
            'department', 'totalStudents', 'totalTeachers', 'totalPrograms',
            'recentAttendanceSessions', 'recentExams', 'overallAttendanceRate', 'overallPassRate'
        ));
    }

    // ── Attendance Reports ────────────────────────────────────────────────
    public function attendance(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        $dateFrom = $request->date_from ?? now()->subDays(30)->format('Y-m-d');
        $dateTo = $request->date_to ?? now()->format('Y-m-d');

        // Student attendance summary
        $studentAttendance = Student::where('department_id', $deptId)
            ->with(['user:id,name', 'program:id,name'])
            ->withCount([
                'attendances as total_sessions' => function ($q) use ($dateFrom, $dateTo) {
                    $q->whereHas('attendanceSession', fn ($sq) => $sq->whereBetween('date', [$dateFrom, $dateTo]));
                },
                'attendances as present_sessions' => function ($q) use ($dateFrom, $dateTo) {
                    $q->where('status', 'present')
                      ->whereHas('attendanceSession', fn ($sq) => $sq->whereBetween('date', [$dateFrom, $dateTo]));
                }
            ])
            ->when($request->program_id, fn ($q) => $q->where('program_id', $request->program_id))
            ->when($request->semester, fn ($q) => $q->where('semester', $request->semester))
            ->paginate(20)
            ->withQueryString();

        // Add attendance rate
        $studentAttendance->getCollection()->transform(function ($student) {
            $student->attendance_rate = $student->total_sessions > 0 
                ? round(($student->present_sessions / $student->total_sessions) * 100, 1) 
                : 0;
            return $student;
        });

        // Subject-wise attendance
        $subjectAttendance = DB::table('attendance_sessions')
            ->join('subjects', 'attendance_sessions.subject_id', '=', 'subjects.id')
            ->join('programs', 'subjects.program_id', '=', 'programs.id')
            ->leftJoin('attendances', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
            ->where('programs.department_id', $deptId)
            ->whereBetween('attendance_sessions.date', [$dateFrom, $dateTo])
            ->groupBy('subjects.id', 'subjects.name')
            ->selectRaw('subjects.name as subject_name')
            ->selectRaw('COUNT(DISTINCT attendance_sessions.id) as total_sessions')
            ->selectRaw('COUNT(attendances.id) as total_records')
            ->selectRaw('SUM(CASE WHEN attendances.status = "present" THEN 1 ELSE 0 END) as present_count')
            ->orderByDesc('total_sessions')
            ->limit(10)
            ->get();

        $subjectAttendance->transform(function ($item) {
            $item->attendance_rate = $item->total_records > 0 
                ? round(($item->present_count / $item->total_records) * 100, 1) 
                : 0;
            return $item;
        });

        // Programs for filter
        $programs = DB::table('programs')
            ->where('department_id', $deptId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('hod.reports.attendance', compact(
            'department', 'studentAttendance', 'subjectAttendance', 'programs', 'dateFrom', 'dateTo'
        ));
    }

    // ── Performance Reports ───────────────────────────────────────────────
    public function performance(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Student performance summary
        $studentPerformance = Student::where('department_id', $deptId)
            ->with(['user:id,name', 'program:id,name'])
            ->when($request->program_id, fn ($q) => $q->where('program_id', $request->program_id))
            ->when($request->semester, fn ($q) => $q->where('semester', $request->semester))
            ->paginate(20)
            ->withQueryString();

        // Add performance metrics
        $studentPerformance->getCollection()->transform(function ($student) {
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

        // Subject-wise performance
        $subjectPerformance = DB::table('marks')
            ->join('subjects', 'marks.subject_id', '=', 'subjects.id')
            ->join('programs', 'subjects.program_id', '=', 'programs.id')
            ->join('exams', 'marks.exam_id', '=', 'exams.id')
            ->where('programs.department_id', $deptId)
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

        // Programs for filter
        $programs = DB::table('programs')
            ->where('department_id', $deptId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('hod.reports.performance', compact(
            'department', 'studentPerformance', 'subjectPerformance', 'programs'
        ));
    }

    // ── Department Report ──────────────────────────────────────────────────
    public function department(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Comprehensive department statistics
        $stats = [
            'students' => [
                'total' => Student::where('department_id', $deptId)->count(),
                'active' => Student::where('department_id', $deptId)->where('is_active', true)->count(),
                'by_semester' => Student::where('department_id', $deptId)
                    ->groupBy('semester')
                    ->selectRaw('semester, COUNT(*) as count')
                    ->orderBy('semester')
                    ->get(),
            ],
            'teachers' => [
                'total' => Teacher::where('department_id', $deptId)->count(),
                'active' => Teacher::where('department_id', $deptId)->where('is_active', true)->count(),
                'by_designation' => Teacher::where('department_id', $deptId)
                    ->groupBy('designation')
                    ->selectRaw('designation, COUNT(*) as count')
                    ->get(),
            ],
            'programs' => DB::table('programs')->where('department_id', $deptId)->count(),
            'attendance_rate' => $this->calculateAttendanceRate($deptId),
            'pass_rate' => $this->calculatePassRate($deptId),
        ];

        // Recent activity
        $recentActivity = [
            'attendance_sessions' => AttendanceSession::whereHas('subject', function ($q) use ($deptId) {
                    $q->whereHas('program', fn ($pq) => $pq->where('department_id', $deptId));
                })
                ->whereBetween('date', [now()->subDays(7), now()])
                ->count(),
            'exams_conducted' => DB::table('exams')
                ->where('department_id', $deptId)
                ->whereBetween('start_date', [now()->subDays(30), now()])
                ->count(),
            'notices_published' => DB::table('notices')
                ->where('department_id', $deptId)
                ->where('is_published', true)
                ->whereBetween('created_at', [now()->subDays(30), now()])
                ->count(),
        ];

        return view('hod.reports.department', compact('department', 'stats', 'recentActivity'));
    }

    // ── Export ─────────────────────────────────────────────────────────────
    public function export(Request $request, $type)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        switch ($type) {
            case 'students':
                return $this->exportStudents($deptId);
            case 'teachers':
                return $this->exportTeachers($deptId);
            case 'attendance':
                return $this->exportAttendance($deptId, $request);
            case 'performance':
                return $this->exportPerformance($deptId, $request);
            default:
                abort(404, 'Export type not found.');
        }
    }

    // ── Helper Methods ─────────────────────────────────────────────────────
    private function calculateAttendanceRate($deptId)
    {
        $presentCount = DB::table('attendances')
            ->join('attendance_sessions', 'attendances.attendance_session_id', '=', 'attendance_sessions.id')
            ->join('subjects', 'attendance_sessions.subject_id', '=', 'subjects.id')
            ->join('programs', 'subjects.program_id', '=', 'programs.id')
            ->where('programs.department_id', $deptId)
            ->where('attendances.status', 'present')
            ->count();

        $totalCount = DB::table('attendances')
            ->join('attendance_sessions', 'attendances.attendance_session_id', '=', 'attendance_sessions.id')
            ->join('subjects', 'attendance_sessions.subject_id', '=', 'subjects.id')
            ->join('programs', 'subjects.program_id', '=', 'programs.id')
            ->where('programs.department_id', $deptId)
            ->count();

        return $totalCount > 0 ? round(($presentCount / $totalCount) * 100, 1) : 0;
    }

    private function calculatePassRate($deptId)
    {
        $passedCount = DB::table('marks')
            ->join('exams', 'marks.exam_id', '=', 'exams.id')
            ->where('exams.department_id', $deptId)
            ->whereRaw('marks_obtained >= pass_marks')
            ->count();

        $totalCount = DB::table('marks')
            ->join('exams', 'marks.exam_id', '=', 'exams.id')
            ->where('exams.department_id', $deptId)
            ->count();

        return $totalCount > 0 ? round(($passedCount / $totalCount) * 100, 1) : 0;
    }

    private function exportStudents($deptId)
    {
        $students = Student::where('department_id', $deptId)
            ->with(['user:id,name,email,phone', 'program:id,name'])
            ->get();

        $csv = "Name,Email,Phone,Program,Semester,Roll Number,Status\n";
        foreach ($students as $student) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s\n",
                $student->user->name ?? '',
                $student->user->email ?? '',
                $student->user->phone ?? '',
                $student->program->name ?? '',
                $student->semester ?? '',
                $student->roll_number ?? '',
                $student->is_active ? 'Active' : 'Inactive'
            );
        }

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="students_export.csv"',
        ]);
    }

    private function exportTeachers($deptId)
    {
        $teachers = Teacher::where('department_id', $deptId)
            ->with(['user:id,name,email,phone'])
            ->get();

        $csv = "Name,Email,Phone,Employee ID,Designation,Qualification,Status\n";
        foreach ($teachers as $teacher) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s\n",
                $teacher->user->name ?? '',
                $teacher->user->email ?? '',
                $teacher->user->phone ?? '',
                $teacher->employee_id ?? '',
                $teacher->designation ?? '',
                $teacher->qualification ?? '',
                $teacher->is_active ? 'Active' : 'Inactive'
            );
        }

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="teachers_export.csv"',
        ]);
    }

    private function exportAttendance($deptId, $request)
    {
        $dateFrom = $request->date_from ?? now()->subDays(30)->format('Y-m-d');
        $dateTo = $request->date_to ?? now()->format('Y-m-d');

        $attendanceData = DB::table('attendances')
            ->join('attendance_sessions', 'attendances.attendance_session_id', '=', 'attendance_sessions.id')
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->join('subjects', 'attendance_sessions.subject_id', '=', 'subjects.id')
            ->join('programs', 'subjects.program_id', '=', 'programs.id')
            ->where('programs.department_id', $deptId)
            ->whereBetween('attendance_sessions.date', [$dateFrom, $dateTo])
            ->select([
                'users.name as student_name',
                'subjects.name as subject_name',
                'attendance_sessions.date',
                'attendances.status'
            ])
            ->orderBy('attendance_sessions.date')
            ->get();

        $csv = "Student Name,Subject,Date,Status\n";
        foreach ($attendanceData as $record) {
            $csv .= sprintf(
                "%s,%s,%s,%s\n",
                $record->student_name,
                $record->subject_name,
                $record->date,
                ucfirst($record->status)
            );
        }

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendance_export.csv"',
        ]);
    }

    private function exportPerformance($deptId, $request)
    {
        $performanceData = DB::table('marks')
            ->join('students', 'marks.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->join('subjects', 'marks.subject_id', '=', 'subjects.id')
            ->join('exams', 'marks.exam_id', '=', 'exams.id')
            ->where('exams.department_id', $deptId)
            ->select([
                'users.name as student_name',
                'subjects.name as subject_name',
                'exams.name as exam_name',
                'marks.marks_obtained',
                'marks.total_marks',
                'marks.pass_marks'
            ])
            ->orderBy('exams.start_date')
            ->get();

        $csv = "Student Name,Subject,Exam,Marks Obtained,Total Marks,Pass Marks,Result\n";
        foreach ($performanceData as $record) {
            $result = $record->marks_obtained >= $record->pass_marks ? 'Pass' : 'Fail';
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s\n",
                $record->student_name,
                $record->subject_name,
                $record->exam_name,
                $record->marks_obtained,
                $record->total_marks,
                $record->pass_marks,
                $result
            );
        }

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="performance_export.csv"',
        ]);
    }
}
