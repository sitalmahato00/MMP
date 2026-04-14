<?php

namespace App\Http\Controllers\HOD;

use App\Http\Controllers\Controller;
use App\Models\{Student, Teacher, Department, AcademicSession, Notice, Attendance};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $deptId = $request->get('department_id');
        $session = AcademicSession::current();

        $cacheKey = "hod_dashboard_{$deptId}";
        $stats = Cache::remember($cacheKey, 300, function () use ($deptId) {
            return [
                'student_count' => Student::active()->inDepartment($deptId)->count(),
                'teacher_count' => Teacher::active()->inDepartment($deptId)->count(),
            ];
        });

        $department = Department::find($deptId);
        $recentNotices = Notice::published()->forDepartment($deptId)->latest()->take(5)->get();

        return view('hod.dashboard', compact('stats', 'department', 'session', 'recentNotices'));
    }
}
