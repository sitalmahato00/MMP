<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\{AcademicSession, Notice, Assignment};
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $student = $user->student;
        $session = AcademicSession::current();

        $recentNotices = Notice::published()
            ->forDepartment($student?->department_id)
            ->latest()->take(5)->get();

        $upcomingAssignments = $student ? Assignment::where('program_id', $student->program_id)
            ->where('semester', $student->current_semester)
            ->upcoming()
            ->latest()->take(5)->get() : collect();

        return view('student.dashboard', compact('student', 'session', 'recentNotices', 'upcomingAssignments'));
    }
}
