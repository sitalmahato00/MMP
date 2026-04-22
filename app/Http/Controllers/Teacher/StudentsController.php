<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\Mark;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;

class StudentsController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        if (!$teacher) {
            abort(403, 'Teacher profile not found');
        }

        // Get teacher's subjects
        $subjects = $teacher->subjects()
            ->wherePivot('academic_session_id', $session?->id)
            ->with('program')
            ->get();

        // Get students from teacher's subjects
        $query = Student::query()
            ->whereHas('program', function ($q) use ($subjects) {
                $q->whereIn('id', $subjects->pluck('program_id'));
            })
            ->with(['user:id,name,email,avatar', 'program:id,name', 'attendances', 'marks'])
            ->withCount(['attendances', 'marks']);

        // Filter by subject
        if ($request->filled('subject_id')) {
            $subject = Subject::find($request->subject_id);
            if ($subject && $subjects->contains($subject)) {
                $query->where('program_id', $subject->program_id)
                      ->where('semester', $subject->semester);
            }
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('student_no', 'like', '%' . $request->search . '%')
                  ->orWhere('roll_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%'));
            });
        }

        $students = $query->paginate(50);

        return view('teacher.students.index', compact('students', 'subjects', 'session'));
    }

    public function show(Student $student, Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        // Verify teacher has access to this student
        $hasAccess = $teacher->subjects()
            ->wherePivot('academic_session_id', $session?->id)
            ->whereHas('program', fn ($q) => $q->where('id', $student->program_id))
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Unauthorized');
        }

        $student->load(['user:id,name,email,avatar', 'program', 'parents', 'attendances', 'marks']);

        // Get attendance summary
        $attendanceSummary = Attendance::whereHas('attendanceSession', function ($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })
        ->where('student_id', $student->id)
        ->selectRaw('status, COUNT(*) as count')
        ->groupBy('status')
        ->get()
        ->keyBy('status');

        // Get marks summary
        $marksSummary = Mark::where('student_id', $student->id)
            ->where('teacher_id', $teacher->id)
            ->with('subject')
            ->latest('created_at')
            ->take(10)
            ->get();

        return view('teacher.students.show', compact('student', 'attendanceSummary', 'marksSummary', 'session'));
    }
}
