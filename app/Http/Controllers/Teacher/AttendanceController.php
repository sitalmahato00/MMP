<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Subject;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        if (!$teacher) {
            abort(403, 'Teacher profile not found');
        }

        // Get attendance sessions for teacher's subjects
        $query = AttendanceSession::query()
            ->whereHas('subject', function ($q) use ($teacher, $session) {
                $q->whereHas('teachers', fn ($tq) => $tq->where('teachers.id', $teacher->id)
                    ->where('subject_teacher.academic_session_id', $session?->id));
            })
            ->with(['subject:id,name,code', 'teacher.user:id,name'])
            ->withCount('attendances');

        // Search
        if ($request->filled('search')) {
            $query->whereHas('subject', fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        }

        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Filter by date
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $sessions = $query->latest('date')->paginate(20);

        // Get subjects for filter
        $subjects = $teacher->subjects()
            ->wherePivot('academic_session_id', $session?->id)
            ->get();

        return view('teacher.attendance.index', compact('sessions', 'subjects', 'session'));
    }

    public function create(Request $request)
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

        return view('teacher.attendance.create', compact('subjects', 'session'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'date' => 'required|date',
            'period' => 'nullable|string',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:students,id',
            'attendances.*.status' => 'required|in:present,absent',
            'attendances.*.remarks' => 'nullable|string',
        ]);

        // Verify teacher teaches this subject
        if (!$teacher->subjects()->where('subject_id', $data['subject_id'])->wherePivot('academic_session_id', $session?->id)->exists()) {
            abort(403, 'Unauthorized');
        }

        // Create attendance session
        $attendanceSession = AttendanceSession::create([
            'subject_id' => $data['subject_id'],
            'teacher_id' => $teacher->id,
            'date' => $data['date'],
            'period' => $data['period'] ?? null,
        ]);

        // Create attendance records
        foreach ($data['attendances'] as $attendance) {
            Attendance::create([
                'attendance_session_id' => $attendanceSession->id,
                'student_id' => $attendance['student_id'],
                'status' => $attendance['status'],
                'remarks' => $attendance['remarks'] ?? null,
            ]);
        }

        return redirect()->route('teacher.attendance.index')->with('success', 'Attendance recorded successfully.');
    }

    public function show(AttendanceSession $attendanceSession)
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        // Verify authorization
        if ($attendanceSession->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized');
        }

        $attendanceSession->load(['subject', 'teacher.user', 'attendances.student.user']);

        return view('teacher.attendance.show', compact('attendanceSession'));
    }

    public function edit(AttendanceSession $attendanceSession)
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        // Verify authorization
        if ($attendanceSession->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized');
        }

        $attendanceSession->load(['subject', 'attendances.student.user']);

        return view('teacher.attendance.edit', compact('attendanceSession'));
    }

    public function update(Request $request, AttendanceSession $attendanceSession)
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        // Verify authorization
        if ($attendanceSession->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'date' => 'required|date',
            'period' => 'nullable|string',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:students,id',
            'attendances.*.status' => 'required|in:present,absent',
            'attendances.*.remarks' => 'nullable|string',
        ]);

        // Update session
        $attendanceSession->update([
            'date' => $data['date'],
            'period' => $data['period'] ?? null,
        ]);

        // Delete old attendance records
        $attendanceSession->attendances()->delete();

        // Create new attendance records
        foreach ($data['attendances'] as $attendance) {
            Attendance::create([
                'attendance_session_id' => $attendanceSession->id,
                'student_id' => $attendance['student_id'],
                'status' => $attendance['status'],
                'remarks' => $attendance['remarks'] ?? null,
            ]);
        }

        return redirect()->route('teacher.attendance.index')->with('success', 'Attendance updated successfully.');
    }

    public function destroy(AttendanceSession $attendanceSession)
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        // Verify authorization
        if ($attendanceSession->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized');
        }

        $attendanceSession->delete();

        return redirect()->route('teacher.attendance.index')->with('success', 'Attendance deleted successfully.');
    }
}
