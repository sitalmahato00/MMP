<?php

namespace App\Http\Controllers\HOD;

use App\Models\AttendanceSession;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Program;
use App\Models\AcademicSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * HOD attendance management (department-scoped).
 * 
 * HODs can view attendance data for their department only.
 */
class AttendanceController extends HodController
{
    // ── Index ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Get attendance sessions for department
        $query = AttendanceSession::query()
            ->whereHas('subject', function ($q) use ($deptId) {
                $q->whereHas('program', function ($pq) use ($deptId) {
                    $pq->where('department_id', $deptId);
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

        // Overall attendance rate for department
        $attendanceRate = DB::table('attendances')
            ->join('attendance_sessions', 'attendances.attendance_session_id', '=', 'attendance_sessions.id')
            ->join('subjects', 'attendance_sessions.subject_id', '=', 'subjects.id')
            ->join('programs', 'subjects.program_id', '=', 'programs.id')
            ->where('programs.department_id', $deptId)
            ->where('attendances.status', 'present')
            ->count();

        $totalAttendanceRecords = DB::table('attendances')
            ->join('attendance_sessions', 'attendances.attendance_session_id', '=', 'attendance_sessions.id')
            ->join('subjects', 'attendance_sessions.subject_id', '=', 'subjects.id')
            ->join('programs', 'subjects.program_id', '=', 'programs.id')
            ->where('programs.department_id', $deptId)
            ->count();

        $overallAttendanceRate = $totalAttendanceRecords > 0 ? round(($attendanceRate / $totalAttendanceRecords) * 100, 1) : 0;

        // Subjects for filter
        $subjects = Subject::whereHas('program', fn ($q) => $q->where('department_id', $deptId))
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();

        return view('hod.attendance.index', compact(
            'sessions', 'department', 'subjects',
            'totalSessions', 'todaySessions', 'thisWeekSessions', 'overallAttendanceRate'
        ));
    }

    // ── Sessions ───────────────────────────────────────────────────────────
    public function sessions(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Get detailed session view with student attendance
        $sessionId = $request->session_id;
        
        if (!$sessionId) {
            return redirect()->route('hod.attendance.index')
                ->with('error', 'Please select a session to view details.');
        }

        $session = AttendanceSession::with([
            'subject:id,name,code',
            'teacher.user:id,name',
            'attendances.student.user:id,name,email'
        ])
        ->whereHas('subject', function ($q) use ($deptId) {
            $q->whereHas('program', function ($pq) use ($deptId) {
                $pq->where('department_id', $deptId);
            });
        })
        ->findOrFail($sessionId);

        $presentCount = $session->attendances->where('status', 'present')->count();
        $absentCount = $session->attendances->where('status', 'absent')->count();
        $totalStudents = $session->attendances->count();
        $attendanceRate = $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100, 1) : 0;

        return view('hod.attendance.sessions', compact(
            'session', 'department', 'presentCount', 'absentCount', 'totalStudents', 'attendanceRate'
        ));
    }

    // ── Reports ────────────────────────────────────────────────────────────
    public function reports(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Student-wise attendance summary
        $students = Student::where('department_id', $deptId)
            ->with(['user:id,name,email', 'program:id,name'])
            ->withCount([
                'attendances as total_sessions',
                'attendances as present_sessions' => fn ($q) => $q->where('status', 'present'),
                'attendances as absent_sessions' => fn ($q) => $q->where('status', 'absent')
            ])
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->whereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"));
            })
            ->when($request->program_id, fn ($q) => $q->where('program_id', $request->program_id))
            ->when($request->semester, fn ($q) => $q->where('semester', $request->semester))
            ->paginate(20)
            ->withQueryString();

        // Add attendance rate to each student
        $students->getCollection()->transform(function ($student) {
            $student->attendance_rate = $student->total_sessions > 0 
                ? round(($student->present_sessions / $student->total_sessions) * 100, 1) 
                : 0;
            return $student;
        });

        // Programs for filter
        $programs = DB::table('programs')
            ->where('department_id', $deptId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('hod.attendance.reports', compact('students', 'department', 'programs'));
    }

    // ── Mark Attendance ────────────────────────────────────────────────────
    public function mark(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Get programs for the department
        $programs = Program::where('department_id', $deptId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // Get subjects for selected program (if any)
        $subjects = collect();
        if ($request->program_id) {
            $subjects = Subject::where('program_id', $request->program_id)
                ->when($request->semester, fn ($q) => $q->where('semester', $request->semester))
                ->select('id', 'name', 'code', 'type')
                ->orderBy('name')
                ->get();
        }

        // Get students for selected program and semester
        $students = collect();
        if ($request->program_id && $request->semester) {
            $students = Student::where('department_id', $deptId)
                ->where('program_id', $request->program_id)
                ->where('current_semester', $request->semester)
                ->when($request->section, fn ($q) => $q->where('section', $request->section))
                ->with(['user:id,name,email'])
                ->orderBy('roll_number')
                ->get();
        }

        // Get teachers for the department
        $teachers = Teacher::where('department_id', $deptId)
            ->where('is_active', true)
            ->with('user:id,name')
            ->orderBy('user_id')
            ->get();

        // Get active academic session
        $academicSession = AcademicSession::where('is_active', true)->first();

        return view('hod.attendance.mark', compact(
            'department', 'programs', 'subjects', 'students', 'teachers', 'academicSession'
        ));
    }

    // ── Store Attendance ───────────────────────────────────────────────────
    public function store(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        $data = $request->validate([
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'program_id' => 'required|exists:programs,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'semester' => 'required|integer|min:1|max:8',
            'section' => 'nullable|string|max:10',
            'date' => 'required|string|max:10', // BS date format
            'period' => 'required|string|max:50',
            'attendance_type' => 'required|in:class,lab',
            'attendances' => 'required|array',
            'attendances.*' => 'required|in:present,absent,late,excused',
            'remarks' => 'nullable|array',
            'remarks.*' => 'nullable|string|max:255',
        ]);

        // Convert BS date to AD date
        $adDate = \App\Helpers\NepaliDateHelper::toAD($data['date']);
        if (!$adDate) {
            return redirect()->back()
                ->withErrors(['date' => 'Invalid BS date format. Please use YYYY-MM-DD format.'])
                ->withInput();
        }
        $data['date'] = $adDate->format('Y-m-d');

        // Verify program belongs to department
        $program = Program::where('id', $data['program_id'])
            ->where('department_id', $deptId)
            ->firstOrFail();

        // Verify teacher belongs to department
        $teacher = Teacher::where('id', $data['teacher_id'])
            ->where('department_id', $deptId)
            ->firstOrFail();

        // Verify subject belongs to program
        $subject = Subject::where('id', $data['subject_id'])
            ->where('program_id', $data['program_id'])
            ->firstOrFail();

        // Check if lab attendance is allowed for this subject
        if ($data['attendance_type'] === 'lab' && !in_array($subject->type, ['practical', 'both'])) {
            return redirect()->back()
                ->withErrors(['attendance_type' => 'This subject does not have lab/practical sessions.'])
                ->withInput();
        }

        DB::transaction(function () use ($data, $deptId) {
            // Check if attendance session already exists
            $existingSession = AttendanceSession::where([
                'academic_session_id' => $data['academic_session_id'],
                'program_id' => $data['program_id'],
                'subject_id' => $data['subject_id'],
                'semester' => $data['semester'],
                'section' => $data['section'],
                'date' => $data['date'],
                'period' => $data['period'] . ' (' . ucfirst($data['attendance_type']) . ')',
            ])->first();

            if ($existingSession) {
                // Update existing session
                $existingSession->update([
                    'teacher_id' => $data['teacher_id'],
                ]);
                $attendanceSession = $existingSession;

                // Delete existing attendance records (overwrite)
                Attendance::where('attendance_session_id', $attendanceSession->id)->delete();
            } else {
                // Create new attendance session
                $attendanceSession = AttendanceSession::create([
                    'academic_session_id' => $data['academic_session_id'],
                    'teacher_id' => $data['teacher_id'],
                    'subject_id' => $data['subject_id'],
                    'program_id' => $data['program_id'],
                    'semester' => $data['semester'],
                    'section' => $data['section'],
                    'date' => $data['date'],
                    'period' => $data['period'] . ' (' . ucfirst($data['attendance_type']) . ')',
                ]);
            }

            // Create attendance records
            foreach ($data['attendances'] as $studentId => $status) {
                // Verify student belongs to department and program
                $student = Student::where('id', $studentId)
                    ->where('department_id', $deptId)
                    ->where('program_id', $data['program_id'])
                    ->where('current_semester', $data['semester'])
                    ->firstOrFail();

                Attendance::create([
                    'attendance_session_id' => $attendanceSession->id,
                    'student_id' => $studentId,
                    'status' => $status,
                    'remarks' => $data['remarks'][$studentId] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('hod.attendance.index')
            ->with('success', 'Attendance marked successfully.');
    }

    // ── Edit Attendance ────────────────────────────────────────────────────
    public function edit(Request $request, AttendanceSession $attendanceSession)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Verify session belongs to department
        if ($attendanceSession->program->department_id !== $deptId) {
            abort(403, 'Unauthorized access to attendance session.');
        }

        $attendanceSession->load([
            'subject:id,name,code,type',
            'program:id,name',
            'teacher.user:id,name',
            'attendances.student.user:id,name,email'
        ]);

        // Get all students for this program/semester
        $allStudents = Student::where('department_id', $deptId)
            ->where('program_id', $attendanceSession->program_id)
            ->where('current_semester', $attendanceSession->semester)
            ->when($attendanceSession->section, fn ($q) => $q->where('section', $attendanceSession->section))
            ->with(['user:id,name,email'])
            ->orderBy('roll_number')
            ->get();

        // Get teachers for the department
        $teachers = Teacher::where('department_id', $deptId)
            ->where('is_active', true)
            ->with('user:id,name')
            ->orderBy('user_id')
            ->get();

        // Determine attendance type from period
        $attendanceType = str_contains(strtolower($attendanceSession->period), 'lab') ? 'lab' : 'class';

        return view('hod.attendance.edit', compact(
            'attendanceSession', 'allStudents', 'teachers', 'department', 'attendanceType'
        ));
    }

    // ── Update Attendance ──────────────────────────────────────────────────
    public function update(Request $request, AttendanceSession $attendanceSession)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Verify session belongs to department
        if ($attendanceSession->program->department_id !== $deptId) {
            abort(403, 'Unauthorized access to attendance session.');
        }

        $data = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'date' => 'required|string|max:10', // BS date format
            'period' => 'required|string|max:50',
            'attendance_type' => 'required|in:class,lab',
            'attendances' => 'required|array',
            'attendances.*' => 'required|in:present,absent,late,excused',
            'remarks' => 'nullable|array',
            'remarks.*' => 'nullable|string|max:255',
        ]);

        // Convert BS date to AD date
        $adDate = \App\Helpers\NepaliDateHelper::toAD($data['date']);
        if (!$adDate) {
            return redirect()->back()
                ->withErrors(['date' => 'Invalid BS date format. Please use YYYY-MM-DD format.'])
                ->withInput();
        }
        $data['date'] = $adDate->format('Y-m-d');

        // Verify teacher belongs to department
        $teacher = Teacher::where('id', $data['teacher_id'])
            ->where('department_id', $deptId)
            ->firstOrFail();

        // Check if lab attendance is allowed for this subject
        if ($data['attendance_type'] === 'lab' && !in_array($attendanceSession->subject->type, ['practical', 'both'])) {
            return redirect()->back()
                ->withErrors(['attendance_type' => 'This subject does not have lab/practical sessions.'])
                ->withInput();
        }

        DB::transaction(function () use ($data, $attendanceSession, $deptId) {
            // Update attendance session
            $attendanceSession->update([
                'teacher_id' => $data['teacher_id'],
                'date' => $data['date'],
                'period' => $data['period'] . ' (' . ucfirst($data['attendance_type']) . ')',
            ]);

            // Delete existing attendance records
            Attendance::where('attendance_session_id', $attendanceSession->id)->delete();

            // Create new attendance records
            foreach ($data['attendances'] as $studentId => $status) {
                // Verify student belongs to department
                $student = Student::where('id', $studentId)
                    ->where('department_id', $deptId)
                    ->firstOrFail();

                Attendance::create([
                    'attendance_session_id' => $attendanceSession->id,
                    'student_id' => $studentId,
                    'status' => $status,
                    'remarks' => $data['remarks'][$studentId] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('hod.attendance.index')
            ->with('success', 'Attendance updated successfully.');
    }

    // ── Destroy ────────────────────────────────────────────────────────────
    public function destroy(Request $request, AttendanceSession $attendanceSession)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Verify session belongs to department
        $belongsToDept = $attendanceSession->subject()
            ->whereHas('program', fn ($q) => $q->where('department_id', $deptId))
            ->exists();

        if (!$belongsToDept) {
            abort(403, 'This attendance session does not belong to your department.');
        }

        // Delete child attendance records then the session
        $attendanceSession->attendances()->delete();
        $attendanceSession->delete();

        return redirect()->route('hod.attendance.index')
            ->with('success', 'Attendance session deleted successfully.');
    }
}