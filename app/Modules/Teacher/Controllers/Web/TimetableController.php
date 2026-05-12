<?php

namespace App\Modules\Teacher\Controllers\Web;

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

        // Get all timetables where teacher has at least one slot
        $teacherTimetables = \App\Models\Timetable::whereHas('slots', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->with('program:id,name,code')
            ->get();

        // Group by program and semester (ignore sections)
        $semesterOptions = $teacherTimetables->groupBy(function($tt) {
            return $tt->program_id . '-' . $tt->semester;
        })->map(function($group) {
            $first = $group->first();
            return [
                'program_id' => $first->program_id,
                'program_name' => $first->program->name,
                'semester' => $first->semester,
                'timetable_ids' => $group->pluck('id')->toArray(),
            ];
        })->values();

        // Get selected semester key from request or default to first
        $selectedKey = $request->get('semester_key', 0);
        $selectedSemester = $semesterOptions->get($selectedKey);
        
        if (!$selectedSemester) {
            $selectedSemester = $semesterOptions->first();
            $selectedKey = 0;
        }

        // Get ALL slots for ALL sections of the selected semester
        $slots = TimetableSlot::whereIn('timetable_id', $selectedSemester['timetable_ids'] ?? [])
            ->with(['subject:id,name,code', 'teacher.user:id,name', 'timetable:id,section'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Get teacher's OWN today's slots for the selected semester (all sections)
        $today = strtolower(now()->format('l'));
        $myTodaySlots = TimetableSlot::where('teacher_id', $teacher->id)
            ->whereIn('timetable_id', $selectedSemester['timetable_ids'] ?? [])
            ->where('day_of_week', $today)
            ->with(['subject:id,name,code', 'timetable:id,section'])
            ->orderBy('start_time')
            ->get();

        // Get unique subjects and teachers for the grid component
        $subjects = $slots->pluck('subject')->unique('id')->filter();
        $teachers = $slots->pluck('teacher')->unique('id')->filter();

        return view('teacher.timetable.index', compact('slots', 'myTodaySlots', 'subjects', 'teachers', 'session', 'semesterOptions', 'selectedSemester', 'selectedKey'));
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
