<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\{AcademicSession, Notice, Assignment};
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $student = $user->student;
        $session = AcademicSession::current();
        $departmentId = $student?->department_id ?? 'none';
        $programId = $student?->program_id ?? 'none';
        $semester = $student?->current_semester ?? 'none';

        $recentNotices = Cache::remember("student_dashboard_notices:{$departmentId}", 300, function () use ($student) {
            return Notice::published()
                ->forDepartment($student?->department_id)
                ->latest()->take(5)->get();
        });

        $upcomingAssignments = Cache::remember("student_dashboard_assignments:{$programId}:{$semester}", 300, function () use ($student) {
            return $student ? Assignment::where('program_id', $student->program_id)
                ->where('semester', $student->current_semester)
                ->upcoming()
                ->latest()->take(5)->get() : collect();
        });

        return view('student.dashboard', compact('student', 'session', 'recentNotices', 'upcomingAssignments'));
    }
}
