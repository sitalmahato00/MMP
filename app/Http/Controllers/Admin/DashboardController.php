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
            return [
                'total_students' => Student::active()->count(),
                'total_teachers' => Teacher::active()->count(),
                'total_departments' => Department::active()->count(),
                'total_alumni' => Alumni::count(),
                'active_session' => $session?->name ?? 'None',
            ];
        });

        $recentNotices = Notice::published()->latest()->take(5)->get();
        $recentLogs = AuditLog::with('user')->latest()->take(10)->get();

        return view('admin.dashboard', compact('stats', 'recentNotices', 'recentLogs', 'session'));
    }
}
