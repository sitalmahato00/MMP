<?php

namespace App\Http\Controllers\HOD;

use App\Models\AttendanceSession;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * HOD attendance management (department-scoped).
 * 
 * HODs can view attendance data for their department only.
 */
class AttendanceController extends HodController
{
    // ── Index ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Get attendance sessions for department
        $query = AttendanceSession::query()
            ->whereHas('subject', function ($q) use ($deptId) {
                $q->whereHas('program', function ($pq) use ($deptId) {
                    $pq->where('department_id', $deptId);
                });
            })
            ->with([
                'subject:id,name,code',
                'teacher.user:id,name',
                'attendances'
            ])
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->whereHas('subject', fn ($sq) => $sq->where('name', 'like', "%{$term}%"))
                  ->orWhereHas('teacher.user', fn ($tq) => $tq->where('name', 'like', "%{$term}%"));
            })
            ->when($request->subject_id, fn ($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->date_from, fn ($q) => $q->where('date', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->where('date', '<=', $request->date_to));

        $sessions = (clone $query)
            ->latest('date')
            ->latest('start_time')
            ->paginate(20)
            ->withQueryString();

        // Stats
        $totalSessions = (clone $query)->count();
        $todaySessions = (clone $query)->whereDate('date', today())->count();
        $thisWeekSessions = (clone $query)->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->count();

        // Overall attendance rate for department
        $attendanceRate = DB::table('attendances')
            ->join('attendance_sessions', 'attendances.attendance_session_id', '=', 'attendance_sessions.id')
            ->join('subjects', 'attendance_sessions.subject_id', '=', 'subjects.id')
            ->join('programs', 'subjects.program_id', '=', 'programs.id')
            ->where('programs.department_id', $deptId)
            ->where('attendances.status', 'present')
            ->count();

        $totalAttendanceRecords = DB::table('attendances')
            ->join('attendance_sessions', 'attendances.attendance_session_id', '=', 'attendance_sessions.id')
            ->join('subjects', 'attendance_sessions.subject_id', '=', 'subjects.id')
            ->join('programs', 'subjects.program_id', '=', 'programs.id')
            ->where('programs.department_id', $deptId)
            ->count();

        $overallAttendanceRate = $totalAttendanceRecords > 0 ? round(($attendanceRate / $totalAttendanceRecords) * 100, 1) : 0;

        // Subjects for filter
        $subjects = Subject::whereHas('program', fn ($q) => $q->where('department_id', $deptId))
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();

        return view('hod.attendance.index', compact(
            'sessions', 'department', 'subjects',
            'totalSessions', 'todaySessions', 'thisWeekSessions', 'overallAttendanceRate'
        ));
    }

    // ── Sessions ───────────────────────────────────────────────────────────
    public function sessions(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Get detailed session view with student attendance
        $sessionId = $request->session_id;
        
        if (!$sessionId) {
            return redirect()->route('hod.attendance.index')
                ->with('error', 'Please select a session to view details.');
        }

        $session = AttendanceSession::with([
            'subject:id,name,code',
            'teacher.user:id,name',
            'attendances.student.user:id,name,email'
        ])
        ->whereHas('subject', function ($q) use ($deptId) {
            $q->whereHas('program', function ($pq) use ($deptId) {
                $pq->where('department_id', $deptId);
            });
        })
        ->findOrFail($sessionId);

        $presentCount = $session->attendances->where('status', 'present')->count();
        $absentCount = $session->attendances->where('status', 'absent')->count();
        $totalStudents = $session->attendances->count();
        $attendanceRate = $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100, 1) : 0;

        return view('hod.attendance.sessions', compact(
            'session', 'department', 'presentCount', 'absentCount', 'totalStudents', 'attendanceRate'
        ));
    }

    // ── Reports ────────────────────────────────────────────────────────────
    public function reports(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Student-wise attendance summary
        $students = Student::where('department_id', $deptId)
            ->with(['user:id,name,email', 'program:id,name'])
            ->withCount([
                'attendances as total_sessions',
                'attendances as present_sessions' => fn ($q) => $q->where('status', 'present'),
                'attendances as absent_sessions' => fn ($q) => $q->where('status', 'absent')
            ])
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->whereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"));
            })
            ->when($request->program_id, fn ($q) => $q->where('program_id', $request->program_id))
            ->when($request->semester, fn ($q) => $q->where('semester', $request->semester))
            ->paginate(20)
            ->withQueryString();

        // Add attendance rate to each student
        $students->getCollection()->transform(function ($student) {
            $student->attendance_rate = $student->total_sessions > 0 
                ? round(($student->present_sessions / $student->total_sessions) * 100, 1) 
                : 0;
            return $student;
        });

        // Programs for filter
        $programs = DB::table('programs')
            ->where('department_id', $deptId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('hod.attendance.reports', compact('students', 'department', 'programs'));
    }
}