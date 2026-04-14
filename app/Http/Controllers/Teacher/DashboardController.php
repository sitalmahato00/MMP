<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\{AcademicSession, Teacher, TimetableSlot, Notice};
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        $todaySlots = collect();
        if ($teacher) {
            $today = strtolower(now()->format('l'));
            $todaySlots = TimetableSlot::with(['subject', 'timetable.program'])
                ->where('teacher_id', $teacher->id)
                ->where('day_of_week', $today)
                ->orderBy('start_time')
                ->get();
        }

        $subjects = $teacher?->currentSubjects() ?? collect();
        $recentNotices = Notice::published()->latest()->take(5)->get();

        return view('teacher.dashboard', compact('teacher', 'session', 'todaySlots', 'subjects', 'recentNotices'));
    }
}
