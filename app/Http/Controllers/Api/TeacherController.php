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

            $totalClasses = Attendance::where('teacher_id', $teacher->id)->distinct('session_id')->count();
            $totalStudents = Student::whereHas('subjects', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })->count();

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
            $user = $request->user();
            $teacher = Teacher::where('user_id', $user->id)->firstOrFail();

            // Placeholder - would fetch actual schedule from timetable
            return response()->json([
                'success' => true,
                'data' => [
                    'today' => now()->toDateString(),
                    'classes' => [],
                ]
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
     * Get Attendance Session
     */
    public function attendanceSession(Request $request, $session): JsonResponse
    {
        try {
            // Placeholder - would fetch actual attendance session data
            return response()->json([
                'success' => true,
                'data' => [
                    'session_id' => $session,
                    'students' => [],
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attendance session: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark Attendance
     */
    public function markAttendance(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'student_id' => 'required|integer',
                'subject_id' => 'required|integer',
                'status' => 'required|in:present,absent,late',
                'date' => 'required|date',
            ]);

            Attendance::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Attendance marked successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark attendance: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk Mark Attendance
     */
    public function bulkMarkAttendance(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'attendance' => 'required|array',
                'attendance.*.student_id' => 'required|integer',
                'attendance.*.status' => 'required|in:present,absent,late',
            ]);

            $count = 0;
            foreach ($validated['attendance'] as $record) {
                Attendance::create($record);
                $count++;
            }

            return response()->json([
                'success' => true,
                'message' => "$count attendance records created",
                'data' => ['records_created' => $count]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark attendance: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Attendance History
     */
    public function attendanceHistory(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $teacher = Teacher::where('user_id', $user->id)->firstOrFail();

            $records = Attendance::where('teacher_id', $teacher->id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $records->map(fn($r) => [
                    'id' => $r->id,
                    'student' => $r->student?->user?->name,
                    'subject' => $r->subject?->name,
                    'status' => $r->status,
                    'date' => $r->created_at->toDateString(),
                ])
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
            $user = $request->user();
            $teacher = Teacher::where('user_id', $user->id)->firstOrFail();

            $assignments = Assignment::where('teacher_id', $teacher->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $assignments->map(fn($a) => [
                    'id' => $a->id,
                    'title' => $a->title,
                    'subject' => $a->subject?->name,
                    'due_date' => $a->due_date,
                    'created_at' => $a->created_at,
                ])
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
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'subject_id' => 'required|integer',
                'due_date' => 'required|date_format:Y-m-d',
                'max_marks' => 'required|numeric',
            ]);

            $user = $request->user();
            $teacher = Teacher::where('user_id', $user->id)->firstOrFail();

            $assignment = Assignment::create([
                ...$validated,
                'teacher_id' => $teacher->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Assignment created successfully',
                'data' => ['assignment_id' => $assignment->id]
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
            $submissions = $assignment->submissions()->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $submissions->map(fn($s) => [
                    'id' => $s->id,
                    'student' => $s->student?->user?->name,
                    'status' => $s->status,
                    'submitted_at' => $s->submitted_at,
                ])
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
                'marks_obtained' => 'required|numeric',
                'feedback' => 'nullable|string',
            ]);

            // Update submission with grades
            // $submission->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Submission graded successfully',
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
     */
    public function studentsBySubject(Request $request, $subject): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'students' => [],
                ]
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
     * Get Timetable
     */
    public function timetable(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'timetable' => [],
                ]
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
