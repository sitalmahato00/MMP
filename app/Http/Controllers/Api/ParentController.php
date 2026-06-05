<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Mark;
use App\Models\Assignment;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ParentController extends Controller
{
    /**
     * Parent Dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $children = Student::where('parent_id', $user->id)->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'children_count' => $children->count(),
                    'children' => $children->map(fn($child) => [
                        'id' => $child->id,
                        'name' => $child->user?->name,
                        'program' => $child->program?->name,
                    ])
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
     * Get Children
     */
    public function children(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $children = Student::where('parent_id', $user->id)->get();

            return response()->json([
                'success' => true,
                'data' => $children->map(fn($child) => [
                    'id' => $child->id,
                    'name' => $child->user?->name,
                    'email' => $child->user?->email,
                    'roll_number' => $child->roll_number,
                    'program' => $child->program?->name,
                    'semester' => $child->semester,
                ])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch children: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Child Detail
     */
    public function childDetail(Request $request, Student $child): JsonResponse
    {
        try {
            // Verify child belongs to parent
            if ($child->parent_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $child->id,
                    'name' => $child->user?->name,
                    'email' => $child->user?->email,
                    'roll_number' => $child->roll_number,
                    'program' => $child->program?->name,
                    'semester' => $child->semester,
                    'phone' => $child->user?->phone,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch child detail: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Child Attendance
     */
    public function childAttendance(Request $request, Student $child): JsonResponse
    {
        try {
            if ($child->parent_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $records = Attendance::where('student_id', $child->id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $records->map(fn($r) => [
                    'id' => $r->id,
                    'subject' => $r->subject?->name,
                    'status' => $r->status,
                    'date' => $r->created_at->toDateString(),
                ])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attendance: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Child Attendance Summary
     */
    public function childAttendanceSummary(Request $request, Student $child): JsonResponse
    {
        try {
            if ($child->parent_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $records = Attendance::where('student_id', $child->id)->get();
            $total = $records->count();
            $present = $records->where('status', 'present')->count();
            $percentage = $total > 0 ? ($present / $total) * 100 : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_classes' => $total,
                    'present' => $present,
                    'absent' => $records->where('status', 'absent')->count(),
                    'late' => $records->where('status', 'late')->count(),
                    'attendance_percentage' => round($percentage, 2),
                    'status' => $percentage >= 75 ? 'good' : ($percentage >= 60 ? 'medium' : 'low'),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attendance summary: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Child Attendance by Subject
     */
    public function childAttendanceBySubject(Request $request, Student $child, $subject): JsonResponse
    {
        try {
            if ($child->parent_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $records = Attendance::where('student_id', $child->id)
                ->where('subject_id', $subject)
                ->get();

            $total = $records->count();
            $present = $records->where('status', 'present')->count();
            $percentage = $total > 0 ? ($present / $total) * 100 : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_classes' => $total,
                    'present' => $present,
                    'attendance_percentage' => round($percentage, 2),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subject attendance: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Child Marks
     */
    public function childMarks(Request $request, Student $child): JsonResponse
    {
        try {
            if ($child->parent_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $marks = Mark::with('subject', 'exam')
                ->where('student_id', $child->id)
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $marks->map(fn($m) => [
                    'id' => $m->id,
                    'subject' => $m->subject?->name,
                    'exam' => $m->exam?->name,
                    'obtained_marks' => $m->obtained_marks,
                    'total_marks' => $m->exam?->total_marks,
                ])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch marks: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Child Marks Summary
     */
    public function childMarksSummary(Request $request, Student $child): JsonResponse
    {
        try {
            if ($child->parent_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $marks = Mark::where('student_id', $child->id)->get();
            $average = $marks->count() > 0 ? $marks->avg('obtained_marks') : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'average_marks' => round($average, 2),
                    'total_exams' => $marks->count(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch marks summary: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Child Exam Marks
     */
    public function childExamMarks(Request $request, Student $child, $exam): JsonResponse
    {
        try {
            if ($child->parent_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $marks = Mark::where('student_id', $child->id)
                ->where('exam_id', $exam)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $marks->map(fn($m) => [
                    'subject' => $m->subject?->name,
                    'obtained_marks' => $m->obtained_marks,
                    'total_marks' => $m->exam?->total_marks,
                ])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch exam marks: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Child Marksheet
     */
    public function childMarksheet(Request $request, Student $child): JsonResponse
    {
        try {
            if ($child->parent_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Marksheet download link generated',
                'data' => [
                    'download_url' => '/api/v1/parent/child/' . $child->id . '/marksheet-pdf',
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate marksheet: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Child Assignments
     */
    public function childAssignments(Request $request, Student $child): JsonResponse
    {
        try {
            if ($child->parent_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $assignments = Assignment::with('subject')
                ->whereHas('submissions', function ($q) use ($child) {
                    $q->where('student_id', $child->id);
                })
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $assignments->map(fn($a) => [
                    'id' => $a->id,
                    'title' => $a->title,
                    'subject' => $a->subject?->name,
                    'due_date' => $a->due_date,
                    'status' => $a->submissions->first()?->status ?? 'pending',
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
     * Child Assignment Detail
     */
    public function childAssignmentDetail(Request $request, Student $child, Assignment $assignment): JsonResponse
    {
        try {
            if ($child->parent_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'description' => $assignment->description,
                    'due_date' => $assignment->due_date,
                    'status' => $assignment->submissions->first()?->status ?? 'pending',
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch assignment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Notices
     */
    public function notices(Request $request): JsonResponse
    {
        try {
            $notices = Notice::where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $notices->map(fn($n) => [
                    'id' => $n->id,
                    'title' => $n->title,
                    'description' => $n->description,
                    'category' => $n->category,
                    'published_at' => $n->created_at,
                ])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notices: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Notice Detail
     */
    public function noticeDetail(Request $request, Notice $notice): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $notice->id,
                    'title' => $notice->title,
                    'description' => $notice->description,
                    'category' => $notice->category,
                    'published_at' => $notice->created_at,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notice: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Child Timetable
     */
    public function childTimetable(Request $request, Student $child): JsonResponse
    {
        try {
            if ($child->parent_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

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
}
