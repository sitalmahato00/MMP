<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Attendance;
use App\Models\Mark;
use App\Models\Assignment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TeacherController extends Controller
{
    /**
     * Teacher Dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $teacher = Teacher::where('user_id', $user->id)->firstOrFail();

            $totalClasses = \App\Models\AttendanceSession::where('teacher_id', $teacher->id)->count();
            $totalStudents = \App\Models\TimetableSlot::where('teacher_id', $teacher->id)
                ->whereHas('timetable', fn ($q) => $q->where('is_active', true))
                ->with('timetable.program')
                ->get()
                ->flatMap(function ($slot) {
                    return \App\Models\Student::where('program_id', $slot->timetable?->program_id)
                        ->where('current_semester', $slot->timetable?->semester)
                        ->pluck('id');
                })
                ->unique()
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'teacher_name' => $user->name,
                    'total_classes' => $totalClasses,
                    'total_students' => $totalStudents,
                    'pending_marks' => 0,
                    'pending_assignments' => 0,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Today's Schedule
     */
    public function todaySchedule(Request $request): JsonResponse
    {
        try {
            $user    = $request->user();
            $teacher = Teacher::where('user_id', $user->id)->firstOrFail();

            $today   = now()->format('l'); // e.g. "Wednesday"

            $slots = \App\Models\TimetableSlot::with(['timetable.program', 'subject'])
                ->where('teacher_id', $teacher->id)
                ->where('day_of_week', $today)
                ->whereHas('timetable', fn ($q) => $q->where('is_active', true))
                ->orderBy('start_time')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => [
                    'today'   => now()->toDateString(),
                    'day'     => $today,
                    'classes' => $slots->map(fn ($s) => [
                        'id'           => $s->id,
                        'subject'      => $s->subject?->name,
                        'subject_code' => $s->subject?->code,
                        'program'      => $s->timetable?->program?->name,
                        'semester'     => $s->timetable?->semester,
                        'section'      => $s->timetable?->section,
                        'start_time'   => substr($s->start_time, 0, 5),
                        'end_time'     => substr($s->end_time, 0, 5),
                        'room'         => $s->room_number,
                        'type'         => $s->type,
                    ])->values(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch schedule: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Classes
     */
    public function classes(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $teacher = Teacher::where('user_id', $user->id)->firstOrFail();

            $subjects = $teacher->subjects ?? collect();

            return response()->json([
                'success' => true,
                'data' => $subjects->map(fn($subject) => [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'code' => $subject->code,
                ])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch classes: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get or create an attendance session for a given subject + date.
     * Returns the session so the app can take attendance against it.
     */
    public function startAttendanceSession(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'subject_id' => 'required|integer|exists:subjects,id',
                'date'       => 'nullable|date_format:Y-m-d',
                'period'     => 'nullable|string|max:50',
            ]);

            $user    = $request->user();
            $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
            $subject = \App\Models\Subject::findOrFail($validated['subject_id']);
            $date    = $validated['date'] ?? now()->toDateString();
            $session = \App\Models\AcademicSession::current();

            // Find existing session or create new one
            $attendanceSession = \App\Models\AttendanceSession::firstOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subject->id,
                    'date'       => $date,
                    'period'     => $validated['period'] ?? null,
                ],
                [
                    'academic_session_id' => $session?->id,
                    'program_id'          => $subject->program_id,
                    'semester'            => $subject->semester,
                ]
            );

            // Load existing attendances for this session
            $attendances = $attendanceSession->attendances()->with('student.user')->get();

            return response()->json([
                'success' => true,
                'data'    => [
                    'session_id'  => $attendanceSession->id,
                    'subject'     => $subject->name,
                    'subject_code'=> $subject->code,
                    'date'        => $attendanceSession->date->toDateString(),
                    'period'      => $attendanceSession->period,
                    'is_existing' => !$attendanceSession->wasRecentlyCreated,
                    'attendance_count' => $attendances->count(),
                    'existing_attendance' => $attendances->map(fn($a) => [
                        'student_id' => $a->student_id,
                        'status'     => $a->status,
                    ])->values(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start session: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Attendance Session
     */
    public function attendanceSession(Request $request, $session): JsonResponse
    {
        try {
            $user    = $request->user();
            $teacher = Teacher::where('user_id', $user->id)->firstOrFail();

            $attendanceSession = \App\Models\AttendanceSession::with(['subject', 'attendances.student.user'])
                ->where('teacher_id', $teacher->id)
                ->findOrFail($session);

            return response()->json([
                'success' => true,
                'data'    => [
                    'session_id'  => $attendanceSession->id,
                    'subject'     => $attendanceSession->subject?->name,
                    'subject_code'=> $attendanceSession->subject?->code,
                    'date'        => $attendanceSession->date->toDateString(),
                    'period'      => $attendanceSession->period,
                    'students'    => $attendanceSession->attendances->map(fn($a) => [
                        'id'         => $a->student_id,
                        'name'       => $a->student?->user?->name,
                        'student_no' => $a->student?->student_no,
                        'avatar_url' => $a->student?->user?->avatar_url,
                        'status'     => $a->status,
                        'remarks'    => $a->remarks,
                    ])->values(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attendance session: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark Attendance (single student against a session)
     */
    public function markAttendance(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'attendance_session_id' => 'required|integer|exists:attendance_sessions,id',
                'student_id'            => 'required|integer|exists:students,id',
                'status'                => 'required|in:present,absent,late',
                'remarks'               => 'nullable|string|max:255',
            ]);

            // Upsert — update if already marked, insert if not
            $attendance = Attendance::updateOrCreate(
                [
                    'attendance_session_id' => $validated['attendance_session_id'],
                    'student_id'            => $validated['student_id'],
                ],
                [
                    'status'  => $validated['status'],
                    'remarks' => $validated['remarks'] ?? null,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Attendance marked successfully',
                'data'    => [
                    'id'         => $attendance->id,
                    'student_id' => $attendance->student_id,
                    'status'     => $attendance->status,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark attendance: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk Mark Attendance (all students in one request)
     */
    public function bulkMarkAttendance(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'attendance_session_id'          => 'required|integer|exists:attendance_sessions,id',
                'attendance'                     => 'required|array|min:1',
                'attendance.*.student_id'        => 'required|integer|exists:students,id',
                'attendance.*.status'            => 'required|in:present,absent,late',
                'attendance.*.remarks'           => 'nullable|string|max:255',
            ]);

            $sessionId = $validated['attendance_session_id'];
            $count     = 0;

            foreach ($validated['attendance'] as $record) {
                Attendance::updateOrCreate(
                    [
                        'attendance_session_id' => $sessionId,
                        'student_id'            => $record['student_id'],
                    ],
                    [
                        'status'  => $record['status'],
                        'remarks' => $record['remarks'] ?? null,
                    ]
                );
                $count++;
            }

            return response()->json([
                'success' => true,
                'message' => "$count attendance records saved",
                'data'    => ['records_saved' => $count, 'session_id' => $sessionId],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark attendance: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Attendance History — list of attendance sessions taken by this teacher
     */
    public function attendanceHistory(Request $request): JsonResponse
    {
        try {
            $user    = $request->user();
            $teacher = Teacher::where('user_id', $user->id)->firstOrFail();

            $sessions = \App\Models\AttendanceSession::with(['subject'])
                ->where('teacher_id', $teacher->id)
                ->withCount('attendances')
                ->withCount(['attendances as present_count' => fn($q) => $q->where('status', 'present')])
                ->withCount(['attendances as absent_count'  => fn($q) => $q->where('status', 'absent')])
                ->orderByDesc('date')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data'    => $sessions->map(fn($s) => [
                    'session_id'     => $s->id,
                    'subject'        => $s->subject?->name,
                    'subject_code'   => $s->subject?->code,
                    'date'           => $s->date->toDateString(),
                    'period'         => $s->period,
                    'total_students' => $s->attendances_count,
                    'present'        => $s->present_count,
                    'absent'         => $s->absent_count,
                ])->values(),
                'pagination' => [
                    'current_page' => $sessions->currentPage(),
                    'last_page'    => $sessions->lastPage(),
                    'total'        => $sessions->total(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attendance history: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Mark Components
     */
    public function markComponents(Request $request, $subject): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'components' => [
                        'internal_theory',
                        'external_theory',
                        'internal_practical',
                        'external_practical',
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch mark components: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit Marks
     */
    public function submitMarks(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'student_id' => 'required|integer',
                'exam_id' => 'required|integer',
                'subject_id' => 'required|integer',
                'obtained_marks' => 'required|numeric',
            ]);

            Mark::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Marks submitted successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit marks: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Pending Marks
     */
    public function pendingMarks(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'pending_marks' => [],
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending marks: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Marks History
     */
    public function marksHistory(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'marks_history' => [],
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch marks history: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Assignments
     */
    public function assignments(Request $request): JsonResponse
    {
        try {
            $user    = $request->user();
            $teacher = Teacher::where('user_id', $user->id)->firstOrFail();

            $now         = now();
            $assignments = Assignment::with(['subject', 'submissions'])
                ->where('teacher_id', $teacher->id)
                ->orderBy('due_date', 'asc')
                ->get();

            $total    = $assignments->count();
            $upcoming = $assignments->filter(fn($a) => $a->due_date && \Carbon\Carbon::parse($a->due_date)->gte($now))->count();
            $overdue  = $assignments->filter(fn($a) => $a->due_date && \Carbon\Carbon::parse($a->due_date)->lt($now))->count();

            return response()->json([
                'success' => true,
                'data'    => $assignments->map(fn($a) => [
                    'id'               => $a->id,
                    'title'            => $a->title,
                    'description'      => $a->description,
                    'subject_id'       => $a->subject_id,
                    'subject'          => $a->subject?->name,
                    'subject_code'     => $a->subject?->code,
                    'due_date'         => $a->due_date,
                    'max_marks'        => $a->max_marks,
                    'attachment_url'   => $a->attachment
                        ? \Storage::disk('public')->url($a->attachment)
                        : null,
                    'submissions_count'=> $a->submissions->count(),
                    'is_overdue'       => $a->due_date && \Carbon\Carbon::parse($a->due_date)->lt($now),
                    'created_at'       => $a->created_at,
                ])->values(),
                'meta' => [
                    'total'    => $total,
                    'upcoming' => $upcoming,
                    'overdue'  => $overdue,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch assignments: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create Assignment
     */
    public function createAssignment(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title'       => 'required|string|max:255',
                'description' => 'nullable|string',
                'subject_id'  => 'required|integer|exists:subjects,id',
                'due_date'    => 'required|date_format:Y-m-d',
                'max_marks'   => 'nullable|numeric|min:0',
                'attachment'  => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,jpg,jpeg,png,gif|max:10240',
            ]);

            $user    = $request->user();
            $teacher = Teacher::where('user_id', $user->id)->firstOrFail();

            // Resolve program_id and semester from the subject
            $subject = \App\Models\Subject::findOrFail($validated['subject_id']);

            // Handle file upload
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('assignments', 'public');
            }

            $assignment = Assignment::create([
                'title'       => $validated['title'],
                'description' => $validated['description'] ?? null,
                'subject_id'  => $validated['subject_id'],
                'due_date'    => $validated['due_date'],
                'max_marks'   => $validated['max_marks'] ?? null,
                'attachment'  => $attachmentPath,
                'teacher_id'  => $teacher->id,
                'program_id'  => $subject->program_id ?? 1,
                'semester'    => $subject->semester ?? 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Assignment created successfully',
                'data'    => [
                    'assignment_id'  => $assignment->id,
                    'title'          => $assignment->title,
                    'attachment_url' => $assignment->attachment
                        ? \Storage::disk('public')->url($assignment->attachment)
                        : null,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create assignment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update Assignment
     */
    public function updateAssignment(Request $request, Assignment $assignment): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'string|max:255',
                'description' => 'string',
                'due_date' => 'date_format:Y-m-d',
                'max_marks' => 'numeric',
            ]);

            $assignment->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Assignment updated successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update assignment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete Assignment
     */
    public function deleteAssignment(Request $request, Assignment $assignment): JsonResponse
    {
        try {
            $assignment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Assignment deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete assignment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Assignment Submissions
     */
    public function assignmentSubmissions(Request $request, Assignment $assignment): JsonResponse
    {
        try {
            $submissions = $assignment->submissions()
                ->with('student.user')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => [
                    'assignment' => [
                        'id'             => $assignment->id,
                        'title'          => $assignment->title,
                        'max_marks'      => $assignment->max_marks,
                        'due_date'       => $assignment->due_date,
                        'attachment_url' => $assignment->attachment
                            ? \Storage::disk('public')->url($assignment->attachment)
                            : null,
                    ],
                    'total'       => $submissions->count(),
                    'submissions' => $submissions->map(fn($s) => [
                        'id'               => $s->id,
                        'student_id'       => $s->student_id,
                        'student_name'     => $s->student?->user?->name,
                        'student_no'       => $s->student?->student_no,
                        'avatar_url'       => $s->student?->user?->avatar_url,
                        'student_note'     => $s->student_note,
                        'attachment_url'   => $s->attachment
                            ? \Storage::disk('public')->url($s->attachment)
                            : null,
                        'status'           => $s->status,
                        'marks_obtained'   => $s->marks_obtained,
                        'teacher_feedback' => $s->teacher_feedback,
                        'submitted_at'     => $s->created_at,
                    ])->values(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch submissions: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Grade Submission
     */
    public function gradeSubmission(Request $request, $submission): JsonResponse
    {
        try {
            $validated = $request->validate([
                'marks_obtained' => 'required|numeric|min:0',
                'teacher_feedback' => 'nullable|string|max:1000',
            ]);

            $sub = \App\Models\AssignmentSubmission::findOrFail($submission);
            $sub->update([
                'marks_obtained'   => $validated['marks_obtained'],
                'teacher_feedback' => $validated['teacher_feedback'] ?? null,
                'status'           => 'graded',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Submission graded successfully',
                'data'    => [
                    'marks_obtained'   => $sub->marks_obtained,
                    'teacher_feedback' => $sub->teacher_feedback,
                    'status'           => $sub->status,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to grade submission: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Students
     */
    public function students(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $teacher = Teacher::where('user_id', $user->id)->firstOrFail();

            $students = Student::whereHas('subjects', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $students->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->user?->name,
                    'email' => $s->user?->email,
                    'roll_number' => $s->roll_number,
                ])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch students: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Students by Subject
     * Looks up students enrolled in the subject's program + semester
     */
    public function studentsBySubject(Request $request, $subject): JsonResponse
    {
        try {
            $subjectModel = \App\Models\Subject::findOrFail($subject);

            $students = \App\Models\Student::with('user')
                ->where('program_id', $subjectModel->program_id)
                ->where('current_semester', $subjectModel->semester)
                ->where('status', 'active')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'subject'   => $subjectModel->name,
                    'code'      => $subjectModel->code,
                    'program_id'=> $subjectModel->program_id,
                    'semester'  => $subjectModel->semester,
                    'total'     => $students->count(),
                    'students'  => $students->map(fn($s) => [
                        'id'          => $s->id,
                        'name'        => $s->user?->name,
                        'email'       => $s->user?->email,
                        'avatar_url'  => $s->user?->avatar_url,
                        'student_no'  => $s->student_no,
                        'roll_number' => $s->roll_number ?? null,
                        'section'     => $s->section,
                    ])->values(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch students: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Sections
     */
    public function sections(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'sections' => [],
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sections: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Timetable — returns all slots for this teacher grouped by day.
     *
     * Strategy:
     * 1. Try slots directly assigned to this teacher (teacher_id in timetable_slots).
     * 2. If none found, fall back to active timetables that cover the teacher's
     *    subjects' program + semester (slots may not have teacher_id set yet).
     */
    public function timetable(Request $request): JsonResponse
    {
        try {
            $user    = $request->user();
            $teacher = Teacher::where('user_id', $user->id)->firstOrFail();

            // --- Strategy 1: slots directly assigned to this teacher ---
            $slots = \App\Models\TimetableSlot::with(['timetable.program', 'timetable.academicSession', 'subject'])
                ->where('teacher_id', $teacher->id)
                ->whereHas('timetable', fn($q) => $q->where('is_active', true))
                ->orderBy('start_time')
                ->get();

            // --- Strategy 2: fallback via teacher's subjects ---
            if ($slots->isEmpty()) {
                // Get subject IDs taught by this teacher via subject_teacher pivot
                $subjectIds = \DB::table('subject_teacher')
                    ->where('teacher_id', $teacher->id)
                    ->pluck('subject_id');

                if ($subjectIds->isNotEmpty()) {
                    // Find active timetable slots that contain any of these subjects
                    $slots = \App\Models\TimetableSlot::with(['timetable.program', 'timetable.academicSession', 'subject'])
                        ->whereIn('subject_id', $subjectIds)
                        ->whereHas('timetable', fn($q) => $q->where('is_active', true))
                        ->orderBy('start_time')
                        ->get();
                }
            }

            if ($slots->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data'    => [
                        'has_timetable' => false,
                        'timetable'     => [],
                        'message'       => 'No timetable slots found for your subjects.',
                    ],
                ], 200);
            }

            $days   = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            $result = [];

            foreach ($days as $day) {
                $daySlots = $slots->filter(fn($s) => $s->day_of_week === $day)->values();
                $result[] = [
                    'day'     => $day,
                    'classes' => $daySlots->map(fn($s) => [
                        'id'           => $s->id,
                        'subject'      => $s->subject?->name,
                        'subject_code' => $s->subject?->code,
                        'program'      => $s->timetable?->program?->name,
                        'semester'     => $s->timetable?->semester,
                        'section'      => $s->timetable?->section,
                        'start_time'   => substr($s->start_time, 0, 5),
                        'end_time'     => substr($s->end_time, 0, 5),
                        'room'         => $s->room_number,
                        'type'         => $s->type,
                        'duration'     => $s->duration,
                    ])->values(),
                ];
            }

            $firstTimetable = $slots->first()?->timetable;

            return response()->json([
                'success' => true,
                'data'    => [
                    'has_timetable'    => true,
                    'program'          => $firstTimetable?->program?->name,
                    'semester'         => $firstTimetable?->semester,
                    'section'          => $firstTimetable?->section,
                    'academic_session' => $firstTimetable?->academicSession?->name,
                    'effective_from'   => $firstTimetable?->effective_from?->toDateString(),
                    'timetable'        => $result,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch timetable: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Attendance Report
     */
    public function attendanceReport(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'report' => [],
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch report: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Marks Report
     */
    public function marksReport(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'report' => [],
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch report: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Teacher Profile
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $teacher = Teacher::where('user_id', $user->id)->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar_url' => $user->avatar_url,
                    'employee_id' => $teacher->employee_id,
                    'designation' => $teacher->designation,
                    'department' => $teacher->department?->name,
                    'qualification' => $teacher->qualification,
                    'specialization' => $teacher->specialization,
                    'employment_type' => $teacher->employment_type,
                    'join_date' => $teacher->join_date?->toDateString(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch profile: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update Teacher Profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'phone' => 'sometimes|string|max:20',
                'address' => 'nullable|string|max:500',
                'avatar' => 'nullable|image|max:2048',
                'qualification' => 'nullable|string|max:255',
                'specialization' => 'nullable|string|max:255',
            ]);

            $user = $request->user();
            $teacher = Teacher::where('user_id', $user->id)->firstOrFail();

            if (isset($validated['name'])) {
                $user->name = $validated['name'];
            }
            if (isset($validated['phone'])) {
                $user->phone = $validated['phone'];
            }
            if (isset($validated['address'])) {
                $user->address = $validated['address'];
            }
            if ($request->hasFile('avatar')) {
                $user->avatar = $request->file('avatar')->store('avatars', 'public');
            }
            $user->save();

            if (isset($validated['qualification'])) {
                $teacher->qualification = $validated['qualification'];
            }
            if (isset($validated['specialization'])) {
                $teacher->specialization = $validated['specialization'];
            }
            $teacher->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar_url' => $user->avatar_url,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage(),
            ], 500);
        }
    }
}
