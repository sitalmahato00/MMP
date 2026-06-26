<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Notice;
use App\Models\ParentModel;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Get the authenticated user's ParentModel (with children preloaded).
     */
    private function getParent(Request $request): ParentModel
    {
        return ParentModel::where('user_id', $request->user()->id)
            ->with(['children.user', 'children.program', 'children.department'])
            ->firstOrFail();
    }

    /**
     * Verify a child belongs to this parent and return the Student.
     */
    private function authorizedChild(Request $request, int|string $childId): Student
    {
        $parent = ParentModel::where('user_id', $request->user()->id)->firstOrFail();

        $child = $parent->children()->where('students.id', $childId)->firstOrFail();

        return $child;
    }

    // ─── Dashboard ────────────────────────────────────────────────────────────

    public function dashboard(Request $request): JsonResponse
    {
        try {
            $parent   = $this->getParent($request);
            $children = $parent->children;

            $childSummaries = $children->map(function (Student $child) {
                $attendance = $child->attendances;
                $total      = $attendance->count();
                $present    = $attendance->where('status', 'present')->count();
                $pct        = $total > 0 ? round(($present / $total) * 100, 1) : 0;

                return [
                    'id'                   => $child->id,
                    'name'                 => $child->user?->name,
                    'student_id'           => $child->student_no,
                    'program'              => $child->program?->name,
                    'semester'             => $child->current_semester,
                    'section'              => $child->section,
                    'avatar_url'           => $child->user?->avatar_url,
                    'attendance_percent'   => $pct,
                    'attendance_status'    => $pct >= 75 ? 'good' : ($pct >= 60 ? 'medium' : 'low'),
                ];
            });

            return response()->json([
                'success' => true,
                'data'    => [
                    'parent_name'    => $request->user()->name,
                    'children_count' => $children->count(),
                    'children'       => $childSummaries,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── Children ─────────────────────────────────────────────────────────────

    public function children(Request $request): JsonResponse
    {
        try {
            $parent   = $this->getParent($request);
            $children = $parent->children;

            return response()->json([
                'success' => true,
                'data'    => $children->map(fn (Student $c) => [
                    'id'              => $c->id,
                    'name'            => $c->user?->name,
                    'email'           => $c->user?->email,
                    'phone'           => $c->user?->phone,
                    'avatar_url'      => $c->user?->avatar_url,
                    'student_no'      => $c->student_no,
                    'roll_number'     => $c->roll_number,
                    'program'         => $c->program?->name,
                    'department'      => $c->department?->name,
                    'semester'        => $c->current_semester,
                    'section'         => $c->section,
                    'status'          => $c->status,
                    'batch'           => $c->batch,
                ]),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function childDetail(Request $request, $child): JsonResponse
    {
        try {
            $child = $this->authorizedChild($request, $child);

            return response()->json([
                'success' => true,
                'data'    => [
                    'id'                  => $child->id,
                    'name'                => $child->user?->name,
                    'email'               => $child->user?->email,
                    'phone'               => $child->user?->phone,
                    'avatar_url'          => $child->user?->avatar_url,
                    'student_no'          => $child->student_no,
                    'registration_number' => $child->registration_number,
                    'roll_number'         => $child->roll_number,
                    'program'             => $child->program?->name,
                    'department'          => $child->department?->name,
                    'semester'            => $child->current_semester,
                    'section'             => $child->section,
                    'batch'               => $child->batch,
                    'admission_date'      => $child->admission_date,
                    'status'              => $child->status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── Attendance ───────────────────────────────────────────────────────────

    public function childAttendance(Request $request, $child): JsonResponse
    {
        try {
            $child   = $this->authorizedChild($request, $child);
            $records = $child->attendances()
                ->with('attendanceSession.subject')
                ->latest()
                ->paginate(30);

            return response()->json([
                'success' => true,
                'data'    => $records->map(fn ($r) => [
                    'id'      => $r->id,
                    'subject' => $r->attendanceSession?->subject?->name,
                    'date'    => $r->attendanceSession?->date,
                    'status'  => $r->status,
                    'remarks' => $r->remarks,
                ]),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function childAttendanceSummary(Request $request, $child): JsonResponse
    {
        try {
            $child   = $this->authorizedChild($request, $child);
            $records = $child->attendances;
            $total   = $records->count();
            $present = $records->where('status', 'present')->count();
            $late    = $records->where('status', 'late')->count();
            $absent  = $records->where('status', 'absent')->count();
            $pct     = $total > 0 ? round(($present / $total) * 100, 2) : 0;

            return response()->json([
                'success' => true,
                'data'    => [
                    'total_classes'        => $total,
                    'present'              => $present,
                    'late'                 => $late,
                    'absent'               => $absent,
                    'attendance_percentage'=> $pct,
                    'status'               => $pct >= 75 ? 'good' : ($pct >= 60 ? 'medium' : 'low'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function childAttendanceBySubject(Request $request, $child, $subject): JsonResponse
    {
        try {
            $child   = $this->authorizedChild($request, $child);
            $records = $child->attendances()
                ->whereHas('attendanceSession', fn ($q) => $q->where('subject_id', $subject))
                ->get();
            $total   = $records->count();
            $present = $records->where('status', 'present')->count();
            $pct     = $total > 0 ? round(($present / $total) * 100, 2) : 0;

            return response()->json([
                'success' => true,
                'data'    => [
                    'total_classes'         => $total,
                    'present'               => $present,
                    'absent'                => $records->where('status', 'absent')->count(),
                    'late'                  => $records->where('status', 'late')->count(),
                    'attendance_percentage' => $pct,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── Marks ────────────────────────────────────────────────────────────────

    public function childMarks(Request $request, $child): JsonResponse
    {
        try {
            $child = $this->authorizedChild($request, $child);
            $marks = $child->marks()
                ->with(['subject', 'exam'])
                ->whereHas('exam', fn ($q) => $q->where('is_published', true))
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $marks->map(fn ($m) => [
                    'id'              => $m->id,
                    'subject'         => $m->subject?->name,
                    'subject_code'    => $m->subject?->code,
                    'exam'            => $m->exam?->name,
                    'exam_type'       => $m->exam?->type,
                    'obtained_marks'  => $m->assessment_obtained_marks,
                    'full_marks'      => $m->assessment_full_marks,
                    'pass_marks'      => $m->assessment_pass_marks,
                    'is_pass'         => $m->assessment_obtained_marks >= $m->assessment_pass_marks,
                    'is_absent'       => $m->is_absent,
                ]),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function childMarksSummary(Request $request, $child): JsonResponse
    {
        try {
            $child = $this->authorizedChild($request, $child);
            $marks = $child->marks()
                ->whereHas('exam', fn ($q) => $q->where('is_published', true))
                ->get();

            $total   = $marks->count();
            $average = $total > 0 ? round($marks->avg('assessment_obtained_marks'), 2) : 0;
            $passed  = $marks->filter(fn ($m) => $m->assessment_obtained_marks >= $m->assessment_pass_marks)->count();

            return response()->json([
                'success' => true,
                'data'    => [
                    'total_exams'    => $total,
                    'average_marks'  => $average,
                    'passed_count'   => $passed,
                    'failed_count'   => $total - $passed,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function childExamMarks(Request $request, $child, $exam): JsonResponse
    {
        try {
            $child = $this->authorizedChild($request, $child);
            $marks = $child->marks()
                ->with('subject')
                ->where('exam_id', $exam)
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $marks->map(fn ($m) => [
                    'subject'        => $m->subject?->name,
                    'subject_code'   => $m->subject?->code,
                    'obtained_marks' => $m->assessment_obtained_marks,
                    'full_marks'     => $m->assessment_full_marks,
                    'pass_marks'     => $m->assessment_pass_marks,
                    'is_pass'        => $m->assessment_obtained_marks >= $m->assessment_pass_marks,
                    'is_absent'      => $m->is_absent,
                ]),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function childMarksheet(Request $request, $child): JsonResponse
    {
        try {
            $child = $this->authorizedChild($request, $child);

            return response()->json([
                'success' => true,
                'message' => 'Marksheet data',
                'data'    => [
                    'student_name' => $child->user?->name,
                    'student_no'   => $child->student_no,
                    'program'      => $child->program?->name,
                    'semester'     => $child->current_semester,
                    'download_url' => url('/api/v1/parent/child/' . $child->id . '/marks/marksheet'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── Assignments ──────────────────────────────────────────────────────────

    public function childAssignments(Request $request, $child): JsonResponse
    {
        try {
            $child       = $this->authorizedChild($request, $child);
            $assignments = Assignment::with(['subject', 'submissions' => fn ($q) => $q->where('student_id', $child->id)])
                ->where('program_id', $child->program_id)
                ->where('semester', $child->current_semester)
                ->orderByDesc('due_date')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data'    => $assignments->map(fn ($a) => [
                    'id'          => $a->id,
                    'title'       => $a->title,
                    'subject'     => $a->subject?->name,
                    'due_date'    => $a->due_date,
                    'status'      => $a->submissions->first()?->status ?? 'pending',
                    'marks'       => $a->submissions->first()?->marks_obtained,
                    'feedback'    => $a->submissions->first()?->teacher_feedback,
                ]),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function childAssignmentDetail(Request $request, $child, $assignment): JsonResponse
    {
        try {
            $child      = $this->authorizedChild($request, $child);
            $assignment = Assignment::with(['subject', 'submissions' => fn ($q) => $q->where('student_id', $child->id)])
                ->findOrFail($assignment);

            return response()->json([
                'success' => true,
                'data'    => [
                    'id'          => $assignment->id,
                    'title'       => $assignment->title,
                    'description' => $assignment->description,
                    'subject'     => $assignment->subject?->name,
                    'due_date'    => $assignment->due_date,
                    'status'      => $assignment->submissions->first()?->status ?? 'pending',
                    'marks'       => $assignment->submissions->first()?->marks_obtained,
                    'feedback'    => $assignment->submissions->first()?->teacher_feedback,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── Notices ─────────────────────────────────────────────────────────────

    public function notices(Request $request): JsonResponse
    {
        try {
            $notices = Notice::where('is_published', true)
                ->orderByDesc('published_at')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data'    => $notices->map(fn ($n) => [
                    'id'           => $n->id,
                    'title'        => $n->title,
                    'type'         => $n->type,
                    'published_at' => $n->published_at,
                ]),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function noticeDetail(Request $request, Notice $notice): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data'    => [
                    'id'           => $notice->id,
                    'title'        => $notice->title,
                    'content'      => $notice->content,
                    'type'         => $notice->type,
                    'published_at' => $notice->published_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── Timetable ────────────────────────────────────────────────────────────

    public function childTimetable(Request $request, $child): JsonResponse
    {
        try {
            $child     = $this->authorizedChild($request, $child);
            $timetable = \App\Models\Timetable::with(['slots.subject', 'slots.teacher.user'])
                ->where('program_id', $child->program_id)
                ->where('semester', $child->current_semester)
                ->where('section', $child->section)
                ->where('is_active', true)
                ->first();

            if (!$timetable) {
                return response()->json([
                    'success' => true,
                    'data'    => ['has_timetable' => false, 'timetable' => []],
                ]);
            }

            $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
            $grouped = collect($days)->map(fn ($day) => [
                'day'     => $day,
                'classes' => $timetable->slots
                    ->where('day_of_week', $day)
                    ->sortBy('start_time')
                    ->map(fn ($slot) => [
                        'id'           => $slot->id,
                        'subject'      => $slot->subject?->name,
                        'subject_code' => $slot->subject?->code,
                        'teacher'      => $slot->teacher?->user?->name,
                        'start_time'   => $slot->start_time,
                        'end_time'     => $slot->end_time,
                        'room'         => $slot->room_number,
                        'type'         => $slot->type,
                    ])->values(),
            ]);

            return response()->json([
                'success' => true,
                'data'    => [
                    'has_timetable'   => true,
                    'semester'        => $timetable->semester,
                    'section'         => $timetable->section,
                    'effective_from'  => $timetable->effective_from,
                    'timetable'       => $grouped,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── Profile ─────────────────────────────────────────────────────────────

    public function profile(Request $request): JsonResponse
    {
        try {
            $user   = $request->user();
            $parent = ParentModel::where('user_id', $user->id)->with('children.user')->firstOrFail();

            return response()->json([
                'success' => true,
                'data'    => [
                    'name'               => $user->name,
                    'email'              => $user->email,
                    'phone'              => $user->phone,
                    'gender'             => $user->gender,
                    'address'            => $user->address,
                    'avatar_url'         => $user->avatar_url,
                    'occupation'         => $parent->occupation,
                    'relation_to_student'=> $parent->relation_to_student,
                    'children_count'     => $parent->children->count(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name'       => 'sometimes|string|max:255',
                'phone'      => 'sometimes|nullable|string|max:20',
                'address'    => 'sometimes|nullable|string|max:500',
                'avatar'     => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'occupation' => 'sometimes|nullable|string|max:100',
            ]);

            $user   = $request->user();
            $parent = ParentModel::where('user_id', $user->id)->firstOrFail();

            $user->fill(array_intersect_key($validated, array_flip(['name', 'phone', 'address'])));

            if ($request->hasFile('avatar')) {
                if ($user->avatar) \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
                $user->avatar = $request->file('avatar')->store('avatars', 'public');
            }
            $user->save();

            if (isset($validated['occupation'])) {
                $parent->occupation = $validated['occupation'];
                $parent->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data'    => [
                    'name'       => $user->name,
                    'email'      => $user->email,
                    'phone'      => $user->phone,
                    'avatar_url' => $user->avatar_url,
                    'occupation' => $parent->occupation,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
