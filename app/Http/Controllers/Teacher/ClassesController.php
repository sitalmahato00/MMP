<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Subject;
use App\Models\TimetableSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ClassesController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        if (!$teacher) {
            abort(403, 'Teacher profile not found');
        }

        // Get teacher's subjects for current session
        $query = $teacher->subjects()
            ->wherePivot('academic_session_id', $session?->id)
            ->with(['program', 'marks' => function ($q) {
                $q->where('teacher_id', auth()->user()->teacher->id);
            }])
            ->withCount('marks');

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by program
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        // Filter by semester
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        $subjects = $query->paginate(20);

        // Get programs for filter
        $programs = $teacher->subjects()
            ->wherePivot('academic_session_id', $session?->id)
            ->with('program')
            ->get()
            ->pluck('program')
            ->unique('id');

        return view('teacher.classes.index', compact('subjects', 'programs', 'session'));
    }

    public function show(Subject $subject, Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        // Verify teacher teaches this subject
        if (!$teacher->subjects()->where('subject_id', $subject->id)->wherePivot('academic_session_id', $session?->id)->exists()) {
            abort(403, 'Unauthorized');
        }

        // Get timetable slots
        $slots = TimetableSlot::where('subject_id', $subject->id)
            ->where('teacher_id', $teacher->id)
            ->with(['timetable.program'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Get students enrolled in this subject
        $students = $subject->program->students()
            ->where('semester', $subject->semester)
            ->with(['user:id,name,email', 'attendances' => function ($q) use ($subject) {
                $q->whereHas('attendanceSession', fn ($sq) => $sq->where('subject_id', $subject->id));
            }])
            ->paginate(50);

        return view('teacher.classes.show', compact('subject', 'slots', 'students', 'session'));
    }
}
