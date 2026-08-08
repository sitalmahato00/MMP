<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentsController extends Controller
{
    public function index(Request $request)
    {
        $student = auth()->user()->student;

        if (!$student) {
            abort(403, 'Student profile not found');
        }

        // Semester selector
        $currentSemester  = $student->current_semester;
        $selectedSemester = (int) $request->get('semester', $currentSemester);
        $selectedSemester = max(1, min($selectedSemester, $currentSemester));
        $semesterOptions  = range(1, $currentSemester);

        $status    = $request->get('status');
        $subjectId = $request->get('subject_id');

        $assignmentsQuery = Assignment::with([
                'subject',
                'teacher.user',
                'submissions' => fn ($q) => $q->where('student_id', $student->id),
            ])
            ->where('program_id', $student->program_id)
            ->where('semester', $selectedSemester);

        if ($subjectId) {
            $assignmentsQuery->where('subject_id', $subjectId);
        }

        $assignments = $assignmentsQuery->latest('due_date')->paginate(15);

        $assignments->getCollection()->transform(function ($assignment) {
            $submission = $assignment->submissions->first();

            if ($submission) {
                $assignment->submission_status = $submission->marks_obtained !== null ? 'graded' : 'submitted';
            } else {
                $assignment->submission_status = $assignment->due_date < now() ? 'overdue' : 'pending';
            }

            $assignment->my_submission = $submission;
            return $assignment;
        });

        if ($status) {
            $assignments->setCollection(
                $assignments->getCollection()->filter(fn ($a) => $a->submission_status === $status)
            );
        }

        // Stats for selected semester
        $allAssignments = Assignment::where('program_id', $student->program_id)
            ->where('semester', $selectedSemester)
            ->get();

        $totalAssignments = $allAssignments->count();

        $submittedCount = AssignmentSubmission::where('student_id', $student->id)
            ->whereIn('assignment_id', $allAssignments->pluck('id'))
            ->count();

        $pendingCount = $allAssignments->filter(function ($assignment) use ($student) {
            return !AssignmentSubmission::where('student_id', $student->id)
                ->where('assignment_id', $assignment->id)
                ->exists() && $assignment->due_date >= now();
        })->count();

        $gradedCount = AssignmentSubmission::where('student_id', $student->id)
            ->whereIn('assignment_id', $allAssignments->pluck('id'))
            ->whereNotNull('marks_obtained')
            ->count();

        $subjects = $allAssignments->pluck('subject')->unique('id')->sortBy('name');

        return view('student.assignments.index', compact(
            'student', 'assignments', 'totalAssignments', 'submittedCount',
            'pendingCount', 'gradedCount', 'subjects',
            'selectedSemester', 'currentSemester', 'semesterOptions'
        ));
    }

    public function show($id)
    {
        $student = auth()->user()->student;

        if (!$student) {
            abort(403, 'Student profile not found');
        }

        // Allow viewing assignments from any past semester
        $assignment = Assignment::with(['subject', 'teacher.user'])
            ->where('program_id', $student->program_id)
            ->where('semester', '<=', $student->current_semester)
            ->findOrFail($id);

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        return view('student.assignments.show', compact('student', 'assignment', 'submission'));
    }

    public function submit(Request $request, $id)
    {
        $student = auth()->user()->student;

        if (!$student) {
            abort(403, 'Student profile not found');
        }

        $assignment = Assignment::where('program_id', $student->program_id)
            ->where('semester', '<=', $student->current_semester)
            ->findOrFail($id);

        $request->validate([
            'student_note' => 'nullable|string',
            'attachment'   => 'nullable|file|max:10240',
        ]);

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        if ($submission && $submission->marks_obtained !== null) {
            return back()->with('error', 'Cannot resubmit a graded assignment');
        }

        $data = [
            'assignment_id' => $assignment->id,
            'student_id'    => $student->id,
            'student_note'  => $request->student_note,
            'status'        => 'submitted',
        ];

        if ($request->hasFile('attachment')) {
            $file     = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path     = $file->storeAs('assignments/submissions', $filename, 'public');
            $data['attachment'] = $path;
        }

        if ($submission) {
            $submission->update($data);
            $message = 'Assignment resubmitted successfully';
        } else {
            AssignmentSubmission::create($data);
            $message = 'Assignment submitted successfully';
        }

        return redirect()->route('student.assignments.show', $assignment->id)
            ->with('success', $message);
    }
}
