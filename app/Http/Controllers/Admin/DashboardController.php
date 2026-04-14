<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Student, Teacher, Department, AcademicSession, Exam, Notice, AuditLog, Alumni};
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $session = AcademicSession::current();

        // Cache KPI data for 5 minutes
        $stats = Cache::remember('admin_dashboard_stats', 300, function () use ($session) {
            
            // Calculate real attendance rate
            $totalAttendances = \App\Models\Attendance::count();
            $presentAttendances = \App\Models\Attendance::where('status', 'present')->count();
            $attendanceRate = $totalAttendances > 0 ? round(($presentAttendances / $totalAttendances) * 100, 1) : 0;
            
            // Calculate real pass rate based on CTEVT theory logic as baseline
            $totalMarks = \App\Models\Mark::count();
            $passedMarks = \App\Models\Mark::where('internal_theory_marks', '>=', 8)
                                           ->where('external_theory_marks', '>=', 32)
                                           ->count();
            $passRate = $totalMarks > 0 ? round(($passedMarks / $totalMarks) * 100, 1) : 0;
            
            return [
                'total_students' => Student::active()->count(),
                'total_teachers' => Teacher::active()->count(),
                'total_departments' => Department::active()->count(),
                'total_alumni' => Alumni::count(),
                'active_session' => $session?->name ?? 'None',
                'attendance_rate' => $attendanceRate,
                'pass_rate' => $passRate,
                'departments_data' => Department::withCount('students')->get()
            ];
        });

        $recentNotices = Notice::published()->latest()->take(4)->get();
        // Fallback for events if notice type 'event' doesn't exist yet
        $upcomingEvents = Notice::published()->where('type', 'event')->latest()->take(3)->get();
        if ($upcomingEvents->isEmpty()) {
            $upcomingEvents = Notice::published()->latest()->take(3)->get();
        }
        
        $recentLogs = AuditLog::with('user')->latest()->take(8)->get();

        return view('admin.dashboard', compact('stats', 'recentNotices', 'recentLogs', 'session', 'upcomingEvents'));
    }
}
