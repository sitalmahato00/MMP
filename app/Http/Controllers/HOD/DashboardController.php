<?php

namespace App\Http\Controllers\HOD;

use App\Http\Controllers\Controller;
use App\Models\{Student, Teacher, Department, AcademicSession, Notice, Attendance, Mark, Program};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Find department where this user is HOD
        $department = Department::where('hod_id', $user->id)->first();
        
        if (!$department) {
            return view('hod.no-department', [
                'userName' => $user->name,
                'userEmail' => $user->email,
            ]);
        }

        $deptId = $department->id;
        $session = AcademicSession::current();

        $cacheKey = "hod_dashboard_{$deptId}_v2";
        $data = Cache::remember($cacheKey, 300, function () use ($deptId, $session) {
            $studentCount = Student::active()->where('department_id', $deptId)->count();
            $teacherCount = Teacher::active()->where('department_id', $deptId)->count();
            $programCount = Program::where('department_id', $deptId)->count();
            
            // Attendance rate for department (last 7 days)
            $sevenDaysAgo = Carbon::now()->subDays(7);
            $attendanceData = Attendance::query()
                ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendances.attendance_session_id')
                ->join('students', 'students.id', '=', 'attendances.student_id')
                ->where('students.department_id', $deptId)
                ->where('attendance_sessions.date', '>=', $sevenDaysAgo->toDateString())
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN attendances.status = 'present' THEN 1 ELSE 0 END) as present")
                ->first();

            $attendanceRate = $attendanceData && $attendanceData->total > 0 
                ? round(($attendanceData->present / $attendanceData->total) * 100, 1) 
                : 0;

            // Pass rate (if marks exist)
            $marksData = Mark::query()
                ->join('students', 'students.id', '=', 'marks.student_id')
                ->where('students.department_id', $deptId)
                ->where('marks.status', 'published')
                ->selectRaw('COUNT(*) as total')
                ->first();

            $totalMarks = $marksData->total ?? 0;

            return [
                'student_count' => $studentCount,
                'teacher_count' => $teacherCount,
                'program_count' => $programCount,
                'attendance_rate' => $attendanceRate,
                'total_marks' => $totalMarks,
            ];
        });
        
        $recentNotices = Cache::remember("hod_dashboard_notices:{$deptId}_v2", 300, function () use ($deptId) {
            return Notice::published()
                ->where(function($q) use ($deptId) {
                    $q->where('department_id', $deptId)
                      ->orWhereNull('department_id');
                })
                ->with(['author'])
                ->latest()
                ->take(5)
                ->get();
        });

        $greeting = $this->greeting();
        $lastUpdated = now();

        return view('hod.dashboard', compact(
            'data',
            'department',
            'session',
            'recentNotices',
            'greeting',
            'lastUpdated'
        ));
    }

    private function greeting(): string
    {
        $hour = Carbon::now()->hour;
        return match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening'
        };
    }
}
