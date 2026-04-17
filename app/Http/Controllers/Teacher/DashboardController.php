<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\{AcademicSession, Teacher, TimetableSlot, Notice};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
            $todaySlots = Cache::remember("teacher_dashboard_slots:{$teacher->id}:{$today}", 300, function () use ($teacher, $today) {
                return TimetableSlot::with(['subject', 'timetable.program'])
                    ->where('teacher_id', $teacher->id)
                    ->where('day_of_week', $today)
                    ->orderBy('start_time')
                    ->get();
            });
        }

        $subjects = $teacher?->currentSubjects() ?? collect();
        $recentNotices = Cache::remember('teacher_dashboard_notices', 300, function () {
            return Notice::published()->latest()->take(5)->get();
        });

        return view('teacher.dashboard', compact('teacher', 'session', 'todaySlots', 'subjects', 'recentNotices'));
    }
}
