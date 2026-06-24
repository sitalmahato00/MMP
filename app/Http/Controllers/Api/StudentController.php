<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Mark;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Notice;
use App\Models\Download;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    /**
     * Student Dashboard with KPI cards
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $student = Student::where('user_id', $user->id)->firstOrFail();

            // Calculate attendance percentage
            $attendanceRecords = Attendance::where('student_id', $student->id)->get();
            $totalClasses = $attendanceRecords->count();
            $presentClasses = $attendanceRecords->where('status', 'present')->count();
            $attendancePercentage = $totalClasses > 0 ? ($presentClasses / $totalClasses) * 100 : 0;

            // Calculate average marks
            $marks = Mark::where('student_id', $student->id)->get();
            $averageMarks = $marks->count() > 0 ? $marks->avg('obtained_marks') : 0;

            // Count pending assignments
            $pendingAssignments = Assignment::whereHas('subject', function ($q) use ($student) {
                $q->where('program_id', $student->program_id)
                  ->where('semester', $student->current_semester);
            })->where(function ($q) use ($student) {
                $q->whereDoesntHave('submissions', function ($sq) use ($student) {
                    $sq->where('student_id', $student->id);
                })->orWhereHas('submissions', function ($sq) use ($student) {
                    $sq->where('student_id', $student->id)
                       ->where('status', 'pending');
                });
            })->count();

            // Count unread notices
            $unreadNotices = Notice::where('created_at', '>=', now()->subDays(7))->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'student_name' => $student->user->name,
                    'student_id' => $student->id,
                    'email' => $student->user->email,
                    'phone' => $student->user->phone,
                    'avatar_url' => $student->user->avatar_url,
                    'program' => $student->program?->name,
                    'semester' => $student->semester,
                    'kpi_cards' => [
                        'attendance_percentage' => round($attendancePercentage, 2),
                        'average_marks' => round($averageMarks, 2),
                        'pending_assignments' => $pendingAssignments,
                        'unread_notices' => $unreadNotices,
                    ]
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
     * Attendance Summary
     */
    public function attendanceSummary(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $student = Student::where('user_id', $user->id)->firstOrFail();

            $attendanceRecords = Attendance::where('student_id', $student->id)->get();
            $totalClasses   = $attendanceRecords->count();
            $presentClasses = $attendanceRecords->where('status', 'present')->count();
            $absentClasses  = $attendanceRecords->where('status', 'absent')->count();
            $lateClasses    = $attendanceRecords->where('status', 'late')->count();

            // Percentage: late counts as present (student was physically there)
            $effectivePresent    = $presentClasses + $lateClasses;
            $attendancePercentage = $totalClasses > 0 ? ($effectivePresent / $totalClasses) * 100 : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'student_id'             => $student->id,
                    'student_no'             => $student->student_no,
                    'total_classes'          => $totalClasses,
                    'present'                => $presentClasses,
                    'late'                   => $lateClasses,
                    'absent'                 => $absentClasses,
                    'attendance_percentage'  => round($attendancePercentage, 2),
                    'status' => $attendancePercentage >= 75 ? 'good' : ($attendancePercentage >= 60 ? 'medium' : 'low'),
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
     * Detailed Attendance Records
     */
    public function attendanceDetail(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $student = Student::where('user_id', $user->id)->firstOrFail();

            $attendanceRecords = Attendance::with(['attendanceSession.subject'])
                ->where('student_id', $student->id)
                ->orderByDesc('created_at')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $attendanceRecords->map(fn($record) => [
                    'id'      => $record->id,
                    'subject' => $record->attendanceSession?->subject?->name,
                    'date'    => $record->attendanceSession?->date?->toDateString()
                                 ?? $record->created_at->toDateString(),
                    'period'  => $record->attendanceSession?->period,
                    'status'  => $record->status,
                    'remarks' => $record->remarks,
                ]),
                'pagination' => [
                    'current_page' => $attendanceRecords->currentPage(),
                    'last_page'    => $attendanceRecords->lastPage(),
                    'per_page'     => $attendanceRecords->perPage(),
                    'total'        => $attendanceRecords->total(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attendance details: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Attendance by Subject
     */
    public function attendanceBySubject(Request $request, Subject $subject): JsonResponse
    {
        try {
            $user = $request->user();
            $student = Student::where('user_id', $user->id)->firstOrFail();

            $records = Attendance::whereHas('attendanceSession', fn($q) => $q->where('subject_id', $subject->id))
                ->where('student_id', $student->id)
                ->get();

            $total   = $records->count();
            $present = $records->where('status', 'present')->count();
            $late    = $records->where('status', 'late')->count();
            $absent  = $records->where('status', 'absent')->count();
            $percentage = $total > 0 ? (($present + $late) / $total) * 100 : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'subject'               => $subject->name,
                    'total_classes'         => $total,
                    'present'               => $present,
                    'late'                  => $late,
                    'absent'                => $absent,
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
     * Marks Summary — grouped by exam, using real Mark model columns
     */
    public function marksSummary(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $student = Student::where('user_id', $user->id)->firstOrFail();

            // Get distinct exam IDs for this student, then load marks per exam
            $examIds = Mark::where('student_id', $student->id)
                ->distinct()
                ->pluck('exam_id');

            $results = [];
            foreach ($examIds as $examId) {
                $marks = Mark::with(['subject', 'exam'])
                    ->where('student_id', $student->id)
                    ->where('exam_id', $examId)
                    ->get();

                $exam = $marks->first()?->exam;
                if (!$exam) continue;

                $results[] = [
                    'exam_id'              => $examId,
                    'exam_name'            => $exam->name,
                    'category'             => $exam->category ?? null,
                    'start_date'           => $exam->start_date?->toDateString(),
                    'assessment_full_marks'=> $exam->assessment_full_marks ?? null,
                    'assessment_pass_marks'=> $exam->assessment_pass_marks ?? null,
                    'subjects'             => $marks->map(fn($m) => [
                        'subject'                    => $m->subject?->name,
                        'code'                       => $m->subject?->code,
                        'internal_theory'            => $m->internal_theory_marks,
                        'external_theory'            => $m->external_theory_marks,
                        'internal_practical'         => $m->internal_practical_marks,
                        'external_practical'         => $m->external_practical_marks,
                        'total'                      => $m->total_marks,
                        'full_marks'                 => $m->assessment_full_marks ?? null,
                        'pass_marks'                 => $m->assessment_pass_marks ?? null,
                        'attendance_percent'         => $m->assessment_attendance_percent ?? null,
                        'result'                     => $m->result_remark,
                        'is_passed'                  => $m->is_passed,
                        'is_absent'                  => $m->is_absent,
                    ]),
                ];
            }

            return response()->json([
                'success' => true,
                'data'    => $results,
                'meta'    => ['total_exams' => count($results)],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch marks summary: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Exam Marks Detail
     */
    public function examMarks(Request $request, $exam): JsonResponse
    {
        try {
            $user = $request->user();
            $student = Student::where('user_id', $user->id)->firstOrFail();

            $marks = Mark::with('subject', 'exam')
                ->where('student_id', $student->id)
                ->where('exam_id', $exam)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $marks->map(fn($mark) => [
                    'subject' => $mark->subject?->name,
                    'obtained_marks' => $mark->obtained_marks,
                    'total_marks' => $mark->exam?->total_marks,
                    'percentage' => round(($mark->obtained_marks / ($mark->exam?->total_marks ?? 1)) * 100, 2),
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
     * Subject Marks
     */
    public function subjectMarks(Request $request, Subject $subject): JsonResponse
    {
        try {
            $user = $request->user();
            $student = Student::where('user_id', $user->id)->firstOrFail();

            $marks = Mark::with('exam')
                ->where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'subject' => $subject->name,
                    'marks' => $marks->map(fn($mark) => [
                        'exam' => $mark->exam?->name,
                        'obtained_marks' => $mark->obtained_marks,
                        'total_marks' => $mark->exam?->total_marks,
                    ])
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subject marks: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download Marksheet PDF
     */
    public function downloadMarksheet(Request $request): JsonResponse
    {
        try {
            // Placeholder - would generate actual PDF in production
            return response()->json([
                'success' => true,
                'message' => 'Marksheet download link generated',
                'data' => [
                    'download_url' => '/api/v1/student/marksheet-pdf?token=' . $request->user()->currentAccessToken()->token,
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
     * Subject Detail — full info including marks breakdown, teachers, documents, syllabus
     */
    public function subjectDetail(Request $request, Subject $subject): JsonResponse
    {
        try {
            $user = $request->user();
            $student = Student::where('user_id', $user->id)->firstOrFail();

            // Security: subject must belong to the student's program
            if ($subject->program_id !== $student->program_id) {
                return response()->json(['success' => false, 'message' => 'Subject not found.'], 404);
            }

            // Load assigned teachers via pivot
            $teachers = $subject->teachers()->get()->map(fn($t) => [
                'id'         => $t->id,
                'name'       => $t->user?->name,
                'avatar_url' => $t->user?->avatar_url,
                'designation'=> $t->designation,
            ]);

            // Load subject-specific documents
            $documents = Download::where('subject_id', $subject->id)
                ->where('is_public', true)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($d) => [
                    'id'          => $d->id,
                    'title'       => $d->title,
                    'description' => $d->description,
                    'category'    => $d->category,
                    'file_url'    => $d->file_url,
                    'file_type'   => $d->file_type,
                    'uploaded_at' => $d->created_at,
                ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id'                      => $subject->id,
                    'name'                    => $subject->name,
                    'code'                    => $subject->code,
                    'type'                    => $subject->type,
                    'credit_hours'            => $subject->credit_hours,
                    'details'                 => $subject->details,
                    'syllabus_url'            => $subject->syllabus_url,
                    'marks' => [
                        'full_marks_theory'       => $subject->full_marks_internal_theory + $subject->full_marks_external_theory,
                        'full_marks_practical'     => $subject->full_marks_internal_practical + $subject->full_marks_external_practical,
                        'pass_marks_theory'        => $subject->pass_marks_internal_theory + $subject->pass_marks_external_theory,
                        'pass_marks_practical'     => $subject->pass_marks_internal_practical + $subject->pass_marks_external_practical,
                        'internal_theory'          => $subject->full_marks_internal_theory,
                        'external_theory'          => $subject->full_marks_external_theory,
                        'internal_practical'       => $subject->full_marks_internal_practical,
                        'external_practical'       => $subject->full_marks_external_practical,
                    ],
                    'teachers'   => $teachers,
                    'documents'  => $documents,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subject details: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Subjects — fetched by student's program + current semester
     */
    public function subjects(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $student = Student::where('user_id', $user->id)->firstOrFail();

            $subjects = Subject::where('program_id', $student->program_id)
                ->where('semester', $student->current_semester)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $subjects->map(fn($subject) => [
                    'id'                         => $subject->id,
                    'name'                       => $subject->name,
                    'code'                       => $subject->code,
                    'type'                       => $subject->type,
                    'credit_hours'               => $subject->credit_hours,
                    'full_marks_theory'          => $subject->full_marks_internal_theory + $subject->full_marks_external_theory,
                    'full_marks_practical'       => $subject->full_marks_internal_practical + $subject->full_marks_external_practical,
                    'pass_marks_theory'          => $subject->pass_marks_internal_theory + $subject->pass_marks_external_theory,
                    'pass_marks_practical'       => $subject->pass_marks_internal_practical + $subject->pass_marks_external_practical,
                    'syllabus_url'               => $subject->syllabus_url,
                ]),
                'meta' => [
                    'program'  => $student->program?->name,
                    'semester' => $student->current_semester,
                    'total'    => $subjects->count(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subjects: ' . $e->getMessage(),
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
            $student = Student::where('user_id', $user->id)->firstOrFail();

            $assignments = Assignment::with(['subject', 'submissions' => function ($q) use ($student) {
                $q->where('student_id', $student->id);
            }])
            ->whereHas('subject', function ($q) use ($student) {
                $q->where('program_id', $student->program_id)
                  ->where('semester', $student->current_semester);
            })
            ->orderBy('due_date', 'asc')
            ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $assignments->getCollection()->map(function ($a) {
                    $submission = $a->submissions->first();
                    $status = 'not_submitted';
                    if ($submission) {
                        if ($submission->status === 'graded') {
                            $status = 'graded';
                        } elseif ($submission->status === 'pending') {
                            $status = 'pending';
                        } else {
                            $status = 'submitted';
                        }
                    }

                    return [
                        'id' => $a->id,
                        'title' => $a->title,
                        'subject' => $a->subject?->name,
                        'description' => $a->description,
                        'due_date' => $a->due_date,
                        'max_marks' => $a->max_marks,
                        'status' => $status,
                    ];
                }),
                'pagination' => [
                    'current_page' => $assignments->currentPage(),
                    'last_page'    => $assignments->lastPage(),
                    'per_page'     => $assignments->perPage(),
                    'total'        => $assignments->total(),
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
     * Assignment Detail
     */
    public function assignmentDetail(Request $request, Assignment $assignment): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $assignment->id,
                    'title' => $assignment->title,
                    'subject' => $assignment->subject?->name,
                    'description' => $assignment->description,
                    'due_date' => $assignment->due_date,
                    'max_marks' => $assignment->max_marks,
                    'attachment_url' => $assignment->attachment_url,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch assignment details: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit Assignment
     */
    public function submitAssignment(Request $request, Assignment $assignment): JsonResponse
    {
        try {
            $validated = $request->validate([
                'content' => 'nullable|string',
                'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            ]);

            $user = $request->user();
            $student = Student::where('user_id', $user->id)->firstOrFail();

            // Check if already submitted
            $existing = AssignmentSubmission::where('assignment_id', $assignment->id)
                ->where('student_id', $student->id)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assignment already submitted.',
                    'data' => [
                        'submission_id' => $existing->id,
                        'status' => $existing->status,
                    ]
                ], 409);
            }

            $attachment = null;
            if ($request->hasFile('file')) {
                $attachment = $request->file('file')->store('assignments', 'public');
            }

            $submission = AssignmentSubmission::create([
                'assignment_id' => $assignment->id,
                'student_id'    => $student->id,
                'student_note'  => $validated['content'] ?? null,
                'attachment'    => $attachment,
                'status'        => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Assignment submitted successfully',
                'data' => [
                    'submission_id'  => $submission->id,
                    'status'         => $submission->status,
                    'attachment_url' => $submission->attachment
                        ? \Storage::disk('public')->url($submission->attachment)
                        : null,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit assignment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submission Status
     */
    public function submissionStatus(Request $request, AssignmentSubmission $submission): JsonResponse
    {
        try {
            $submission->load('assignment');
            return response()->json([
                'success' => true,
                'data' => [
                    'id'             => $submission->id,
                    'status'         => $submission->status,
                    'student_note'   => $submission->student_note,
                    'attachment_url' => $submission->attachment
                        ? \Storage::disk('public')->url($submission->attachment)
                        : null,
                    'marks_obtained' => $submission->marks_obtained,
                    'max_marks'      => $submission->assignment?->max_marks ?? null,
                    'feedback'       => $submission->teacher_feedback,
                    'created_at'     => $submission->created_at,
                    'updated_at'     => $submission->updated_at,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch submission status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Timetable
     */
    public function timetable(Request $request): JsonResponse
    {
        try {
            // Placeholder - would fetch actual timetable data
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
     * Get Timetable by Day
     */
    public function timetableByDay(Request $request, $day): JsonResponse
    {
        try {
            // Placeholder - would fetch timetable for specific day
            return response()->json([
                'success' => true,
                'data' => [
                    'day' => $day,
                    'classes' => [],
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
     * Get Downloads — optionally filtered by ?subject_id=
     */
    public function downloads(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $student = Student::where('user_id', $user->id)->firstOrFail();

            $query = Download::visibleToStudent($student)
                ->where('is_public', true);

            // Optional subject filter
            if ($request->filled('subject_id')) {
                $query->where('subject_id', (int) $request->subject_id);
            }

            $downloads = $query->orderBy('created_at', 'desc')->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $downloads->map(fn($d) => [
                    'id'          => $d->id,
                    'title'       => $d->title,
                    'description' => $d->description,
                    'category'    => $d->category,
                    'file_url'    => $d->file_url,
                    'file_type'   => $d->file_type,
                    'subject_id'  => $d->subject_id,
                    'uploaded_at' => $d->created_at,
                ]),
                'pagination' => [
                    'current_page' => $downloads->currentPage(),
                    'last_page'    => $downloads->lastPage(),
                    'per_page'     => $downloads->perPage(),
                    'total'        => $downloads->total(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch downloads: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download File
     */
    public function downloadFile(Request $request, Download $download): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'file_url' => $download->file_url,
                    'file_name' => $download->title,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Notices
     */
    public function notices(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $student = Student::where('user_id', $user->id)->firstOrFail();

            $notices = Notice::with('attachments')
                ->where('is_published', true)
                ->visibleToStudent($student)
                ->orderBy('published_at', 'desc')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $notices->map(fn($n) => [
                    'id'               => $n->id,
                    'title'            => $n->title,
                    'content'          => $n->content,
                    'type'             => $n->type,
                    'attachment_count' => $n->attachments->count(),
                    'published_at'     => $n->published_at ?? $n->created_at,
                ]),
                'pagination' => [
                    'current_page' => $notices->currentPage(),
                    'last_page'    => $notices->lastPage(),
                    'per_page'     => $notices->perPage(),
                    'total'        => $notices->total(),
                ],
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
            $notice->load('attachments');
            return response()->json([
                'success' => true,
                'data' => [
                    'id'           => $notice->id,
                    'title'        => $notice->title,
                    'content'      => $notice->content,
                    'type'         => $notice->type,
                    'attachments'  => $notice->attachments->map(fn($a) => [
                        'id'        => $a->id,
                        'file_name' => $a->file_name,
                        'file_type' => $a->file_type,
                        'file_size' => $a->file_size,
                        'url'       => $a->url,
                        'is_image'  => $a->is_image,
                        'is_pdf'    => $a->is_pdf,
                    ]),
                    'published_at' => $notice->published_at ?? $notice->created_at,
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
     * Notices by Category/Type
     */
    public function noticesByCategory(Request $request, $category): JsonResponse
    {
        try {
            $user = $request->user();
            $student = Student::where('user_id', $user->id)->firstOrFail();

            $notices = Notice::where('type', $category)
                ->where('is_published', true)
                ->visibleToStudent($student)
                ->orderBy('published_at', 'desc')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $notices->map(fn($n) => [
                    'id'           => $n->id,
                    'title'        => $n->title,
                    'content'      => $n->content,
                    'type'         => $n->type,
                    'published_at' => $n->published_at ?? $n->created_at,
                ]),
                'pagination' => [
                    'current_page' => $notices->currentPage(),
                    'last_page'    => $notices->lastPage(),
                    'per_page'     => $notices->perPage(),
                    'total'        => $notices->total(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notices: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Profile
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $student = Student::where('user_id', $user->id)->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar_url' => $user->avatar_url,
                    'student_id' => $student->id,
                    'program' => $student->program?->name,
                    'semester' => $student->semester,
                    'roll_number' => $student->roll_number,
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
     * Update Profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'phone' => 'sometimes|string|max:20',
                'address' => 'nullable|string|max:500',
                'avatar' => 'nullable|image|max:2048',
                'guardian_name' => 'nullable|string|max:255',
                'guardian_phone' => 'nullable|string|max:20',
            ]);

            $user = $request->user();
            $student = Student::where('user_id', $user->id)->firstOrFail();

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

            if (isset($validated['guardian_name'])) {
                $student->guardian_name = $validated['guardian_name'];
            }
            if (isset($validated['guardian_phone'])) {
                $student->guardian_phone = $validated['guardian_phone'];
            }
            $student->save();

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
