<?php

namespace App\Modules\Teacher\Controllers\Web;


use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\AcademicSession;
use App\Modules\Academic\Models\Program;
use App\Modules\Academic\Models\Subject;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Models\AttendanceSession;
use App\Modules\Exam\Models\Mark;
use App\Modules\Student\Models\Student;
use App\Modules\Teacher\Models\Teacher;
use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $query = AttendanceSession::query()
            ->where(function ($sessionQuery) use ($teacher) {
                $sessionQuery
                    ->where('teacher_id', $teacher->id)
                    ->orWhereExists(function ($assignmentQuery) use ($teacher) {
                        $assignmentQuery
                            ->select(DB::raw(1))
                            ->from('subject_teacher')
                            ->whereColumn('subject_teacher.subject_id', 'attendance_sessions.subject_id')
                            ->whereColumn('subject_teacher.academic_session_id', 'attendance_sessions.academic_session_id')
                            ->where('subject_teacher.teacher_id', $teacher->id);
                    });
            })
            ->with([
                'subject:id,name,code',
                'teacher.user:id,name',
                'attendances'
            ])
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->whereHas('subject', fn ($sq) => $sq->where('name', 'like', "%{$term}%"))
                  ->orWhereHas('teacher.user', fn ($tq) => $tq->where('name', 'like', "%{$term}%"));
            })
            ->when($request->subject_id, fn ($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->date_from, function ($q) use ($request) {
                $adDate = \App\Helpers\NepaliDateHelper::toAD($request->date_from);
                if ($adDate) {
                    $q->where('date', '>=', $adDate->format('Y-m-d'));
                }
            })
            ->when($request->date_to, function ($q) use ($request) {
                $adDate = \App\Helpers\NepaliDateHelper::toAD($request->date_to);
                if ($adDate) {
                    $q->where('date', '<=', $adDate->format('Y-m-d'));
                }
            });

        $sessions = (clone $query)
            ->latest('date')
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        // Stats
        $totalSessions = (clone $query)->count();
        $todaySessions = (clone $query)->whereDate('date', today())->count();
        $thisWeekSessions = (clone $query)->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->count();

        // Overall attendance rate for teacher's subjects
        $attendanceRate = DB::table('attendances')
            ->join('attendance_sessions', 'attendances.attendance_session_id', '=', 'attendance_sessions.id')
            ->join('subjects', 'attendance_sessions.subject_id', '=', 'subjects.id')
            ->join('subject_teacher', 'subjects.id', '=', 'subject_teacher.subject_id')
            ->where('subject_teacher.teacher_id', $teacher->id)
            ->where('subject_teacher.academic_session_id', $session?->id)
            ->where('attendances.status', 'present')
            ->count();

        $totalAttendanceRecords = DB::table('attendances')
            ->join('attendance_sessions', 'attendances.attendance_session_id', '=', 'attendance_sessions.id')
            ->join('subjects', 'attendance_sessions.subject_id', '=', 'subjects.id')
            ->join('subject_teacher', 'subjects.id', '=', 'subject_teacher.subject_id')
            ->where('subject_teacher.teacher_id', $teacher->id)
            ->where('subject_teacher.academic_session_id', $session?->id)
            ->count();

        $overallAttendanceRate = $totalAttendanceRecords > 0 ? round(($attendanceRate / $totalAttendanceRecords) * 100, 1) : 0;

        // Subjects for filter (all assigned subjects)
        $subjects = $teacher->subjects()
            ->wherePivot('academic_session_id', $session?->id)
            ->select('subjects.id', 'subjects.name', 'subjects.code')
            ->orderBy('subjects.name')
            ->get();

        return view('teacher.attendance.index', compact(
            'sessions', 'subjects', 'session', 'teacher',
            'totalSessions', 'todaySessions', 'thisWeekSessions', 'overallAttendanceRate'
        ));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        if (!$teacher) {
            abort(403, 'Teacher profile not found');
        }

        // Get teacher's all subjects
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

        try {
            $data = $request->validate([
                'subject_id' => 'required|exists:subjects,id',
                'date' => 'required|string',
                'period' => 'required|string',
                'category' => 'required|in:class,lab',
                'attendances' => 'required|array',
                'attendances.*.student_id' => 'required|exists:students,id',
                'attendances.*.status' => 'required|in:present,absent,late',
                'attendances.*.remarks' => 'nullable|string',
            ]);

            // Verify teacher teaches this subject
            if (!$teacher->subjects()->where('subject_id', $data['subject_id'])->wherePivot('academic_session_id', $session?->id)->exists()) {
                return back()->with('error', 'You are not authorized to mark attendance for this subject.');
            }

            // Convert BS date to AD
            $adDate = \App\Helpers\NepaliDateHelper::toAD($data['date']);
            if (!$adDate) {
                return back()->withErrors(['date' => 'Invalid date format. Please use YYYY-MM-DD format.']);
            }

            // Get subject with program
            $subject = Subject::findOrFail($data['subject_id']);

            // Create attendance session
            $attendanceSession = AttendanceSession::create([
                'academic_session_id' => $session?->id,
                'subject_id' => $data['subject_id'],
                'teacher_id' => $teacher->id,
                'program_id' => $subject->program_id,
                'semester' => $subject->semester,
                'date' => $adDate->format('Y-m-d'),
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

            return redirect()->route('teacher.attendance.index')->with('success', 'Attendance recorded successfully for ' . count($data['attendances']) . ' students.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            \Log::error('Attendance Store Error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'An error occurred while saving attendance: ' . $e->getMessage());
        }
    }

    public function show(AttendanceSession $attendance)
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (! $teacher) {
            abort(403, 'Teacher profile not found');
        }

        if (! $this->canManageAttendanceSession($teacher, $attendance)) {
            return redirect()->route('teacher.attendance.index')->with('error', 'You are not authorized to view this attendance record.');
        }

        $attendance->load(['subject', 'teacher.user', 'attendances.student.user']);

        return view('teacher.attendance.show', ['attendanceSession' => $attendance]);
    }

    public function edit(AttendanceSession $attendance)
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (! $teacher) {
            abort(403, 'Teacher profile not found');
        }

        if (! $this->canManageAttendanceSession($teacher, $attendance)) {
            return redirect()->route('teacher.attendance.index')->with('error', 'You are not authorized to edit this attendance record.');
        }

        $attendance->load(['subject', 'attendances.student.user']);

        return view('teacher.attendance.edit', ['attendanceSession' => $attendance]);
    }

    public function update(Request $request, AttendanceSession $attendance)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $attendanceSession = $attendance;

        if (! $teacher) {
            abort(403, 'Teacher profile not found');
        }

        if (! $this->canManageAttendanceSession($teacher, $attendanceSession)) {
            return back()->with('error', 'You are not authorized to edit this attendance record.');
        }

        try {
            $data = $request->validate([
                'date' => 'required|string',
                'period' => 'required|string',
                'attendances' => 'required|array',
                'attendances.*.student_id' => 'required|exists:students,id',
                'attendances.*.status' => 'required|in:present,absent,late',
                'attendances.*.remarks' => 'nullable|string',
            ]);

            // Convert BS date to AD
            $adDate = \App\Helpers\NepaliDateHelper::toAD($data['date']);
            if (!$adDate) {
                return back()->withErrors(['date' => 'Invalid date format. Please use YYYY-MM-DD format.']);
            }

            // Update session
            $attendanceSession->update([
                'date' => $adDate->format('Y-m-d'),
                'period' => $data['period'] ?? null,
            ]);

            // Delete old attendance records
            $attendanceSession->attendances()->delete();

            // Create new attendance records
            foreach ($data['attendances'] as $attendanceRow) {
                Attendance::create([
                    'attendance_session_id' => $attendanceSession->id,
                    'student_id' => $attendanceRow['student_id'],
                    'status' => $attendanceRow['status'],
                    'remarks' => $attendanceRow['remarks'] ?? null,
                ]);
            }

            return redirect()->route('teacher.attendance.index')->with('success', 'Attendance updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while updating attendance. Please try again.');
        }
    }

    public function destroy(AttendanceSession $attendance)
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        if (! $teacher) {
            abort(403, 'Teacher profile not found');
        }

        if (! $this->canManageAttendanceSession($teacher, $attendance)) {
            abort(403, 'Unauthorized');
        }

        $attendance->delete();

        return redirect()->route('teacher.attendance.index')->with('success', 'Attendance deleted successfully.');
    }

    public function loadStudents(Subject $subject)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        // Verify teacher teaches this subject
        if (!$teacher->subjects()->where('subject_id', $subject->id)->wherePivot('academic_session_id', $session?->id)->exists()) {
            abort(403, 'Unauthorized');
        }

        $students = $subject->program->students()
            ->where('current_semester', $subject->semester)
            ->with(['user:id,name,email'])
            ->get(['id', 'user_id', 'student_no', 'program_id', 'current_semester']);

        return response()->json($students);
    }

    private function canManageAttendanceSession(Teacher $teacher, AttendanceSession $attendanceSession): bool
    {
        if ((int) $attendanceSession->teacher_id === (int) $teacher->id) {
            return true;
        }

        return $teacher->subjects()
            ->where('subjects.id', $attendanceSession->subject_id)
            ->wherePivot('academic_session_id', $attendanceSession->academic_session_id)
            ->exists();
    }
}
