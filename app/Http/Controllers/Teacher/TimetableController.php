<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\TimetableSlot;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        if (!$teacher) {
            abort(403, 'Teacher profile not found');
        }

        // Get timetable slots for teacher
        $slots = TimetableSlot::where('teacher_id', $teacher->id)
            ->with(['subject', 'timetable.program'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Group by day
        $slotsByDay = $slots->groupBy('day_of_week');

        // Get today's slots
        $today = strtolower(now()->format('l'));
        $todaySlots = $slots->where('day_of_week', $today);

        // Get programs for filter
        $programs = $slots->pluck('timetable.program')->unique('id');

        return view('teacher.timetable.index', compact('slots', 'slotsByDay', 'todaySlots', 'programs', 'session'));
    }

    public function show(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        if (!$teacher) {
            abort(403, 'Teacher profile not found');
        }

        // Get timetable slots
        $slots = TimetableSlot::where('teacher_id', $teacher->id)
            ->with(['subject', 'timetable.program'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Group by day
        $slotsByDay = $slots->groupBy('day_of_week');

        return view('teacher.timetable.show', compact('slots', 'slotsByDay', 'session'));
    }
}
