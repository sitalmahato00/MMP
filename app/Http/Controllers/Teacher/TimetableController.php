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

        // Strategy 1: timetables where teacher is directly assigned to a slot
        $teacherTimetables = \App\Models\Timetable::whereHas('slots', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->with('program:id,name,code')
            ->get();

        // Strategy 2: fallback — find timetables via teacher's subjects (when slots
        // exist but teacher_id is not yet set on individual slots in production)
        if ($teacherTimetables->isEmpty()) {
            $subjectIds = \DB::table('subject_teacher')
                ->where('teacher_id', $teacher->id)
                ->pluck('subject_id');

            if ($subjectIds->isNotEmpty()) {
                $teacherTimetables = \App\Models\Timetable::whereHas('slots', function($q) use ($subjectIds) {
                        $q->whereIn('subject_id', $subjectIds);
                    })
                    ->where('is_active', true)
                    ->with('program:id,name,code')
                    ->get();
            }
        }

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
        // Try by teacher_id first, then fall back to subject-based lookup
        $today = strtolower(now()->format('l'));
        $myTodaySlots = TimetableSlot::where('teacher_id', $teacher->id)
            ->whereIn('timetable_id', $selectedSemester['timetable_ids'] ?? [])
            ->where('day_of_week', $today)
            ->with(['subject:id,name,code', 'timetable:id,section'])
            ->orderBy('start_time')
            ->get();

        // Fallback: if no slots found by teacher_id, use subject-based lookup
        if ($myTodaySlots->isEmpty()) {
            $subjectIds = \DB::table('subject_teacher')
                ->where('teacher_id', $teacher->id)
                ->pluck('subject_id');

            if ($subjectIds->isNotEmpty()) {
                $myTodaySlots = TimetableSlot::whereIn('subject_id', $subjectIds)
                    ->whereIn('timetable_id', $selectedSemester['timetable_ids'] ?? [])
                    ->where('day_of_week', $today)
                    ->with(['subject:id,name,code', 'timetable:id,section'])
                    ->orderBy('start_time')
                    ->get();
            }
        }

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

        // Get timetable slots — try by teacher_id first, fallback to subject-based
        $slots = TimetableSlot::where('teacher_id', $teacher->id)
            ->with(['subject', 'timetable.program'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Fallback via teacher's subjects if no direct slots found
        if ($slots->isEmpty()) {
            $subjectIds = \DB::table('subject_teacher')
                ->where('teacher_id', $teacher->id)
                ->pluck('subject_id');

            if ($subjectIds->isNotEmpty()) {
                $slots = TimetableSlot::whereIn('subject_id', $subjectIds)
                    ->whereHas('timetable', fn($q) => $q->where('is_active', true))
                    ->with(['subject', 'timetable.program'])
                    ->orderBy('day_of_week')
                    ->orderBy('start_time')
                    ->get();
            }
        }

        // Group by day
        $slotsByDay = $slots->groupBy('day_of_week');

        return view('teacher.timetable.show', compact('slots', 'slotsByDay', 'session'));
    }
}
