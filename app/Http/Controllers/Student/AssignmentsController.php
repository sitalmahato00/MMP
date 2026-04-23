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

        // Get filters
        $status = $request->get('status');
        $subjectId = $request->get('subject_id');

        // Get assignments for student's program and semester
        $assignmentsQuery = Assignment::with(['subject', 'teacher.user', 'submissions' => function($q) use ($student) {
                $q->where('student_id', $student->id);
            }])
            ->where('program_id', $student->program_id)
            ->where('semester', $student->current_semester);

        if ($subjectId) {
            $assignmentsQuery->where('subject_id', $subjectId);
        }

        $assignments = $assignmentsQuery->latest('due_date')->paginate(15);

        // Add status to each assignment
        $assignments->getCollection()->transform(function($assignment) use ($student) {
            $submission = $assignment->submissions->first();
            
            if ($submission) {
                if ($submission->marks_obtained !== null) {
                    $assignment->submission_status = 'graded';
                } else {
                    $assignment->submission_status = 'submitted';
                }
            } else {
                if ($assignment->due_date < now()) {
                    $assignment->submission_status = 'overdue';
                } else {
                    $assignment->submission_status = 'pending';
                }
            }
            
            $assignment->my_submission = $submission;
            return $assignment;
        });

        // Filter by status if requested
        if ($status) {
            $assignments->setCollection($assignments->getCollection()->filter(function($assignment) use ($status) {
                return $assignment->submission_status === $status;
            }));
        }

        // Calculate statistics
        $allAssignments = Assignment::where('program_id', $student->program_id)
            ->where('semester', $student->current_semester)
            ->get();

        $totalAssignments = $allAssignments->count();
        
        $submittedCount = AssignmentSubmission::where('student_id', $student->id)
            ->whereIn('assignment_id', $allAssignments->pluck('id'))
            ->count();

        $pendingCount = $allAssignments->filter(function($assignment) use ($student) {
            return !AssignmentSubmission::where('student_id', $student->id)
                ->where('assignment_id', $assignment->id)
                ->exists() && $assignment->due_date >= now();
        })->count();

        $gradedCount = AssignmentSubmission::where('student_id', $student->id)
            ->whereIn('assignment_id', $allAssignments->pluck('id'))
            ->whereNotNull('marks_obtained')
            ->count();

        // Get subjects for filter
        $subjects = $allAssignments->pluck('subject')->unique('id')->sortBy('name');

        return view('student.assignments.index', compact(
            'student',
            'assignments',
            'totalAssignments',
            'submittedCount',
            'pendingCount',
            'gradedCount',
            'subjects'
        ));
    }

    public function show($id)
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            abort(403, 'Student profile not found');
        }

        $assignment = Assignment::with(['subject', 'teacher.user'])
            ->where('program_id', $student->program_id)
            ->where('semester', $student->current_semester)
            ->findOrFail($id);

        // Get student's submission if exists
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
            ->where('semester', $student->current_semester)
            ->findOrFail($id);

        $request->validate([
            'student_note' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240', // 10MB max
        ]);

        // Check if already submitted
        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        if ($submission && $submission->marks_obtained !== null) {
            return back()->with('error', 'Cannot resubmit a graded assignment');
        }

        $data = [
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'student_note' => $request->student_note,
            'status' => 'submitted',
        ];

        // Handle file upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('assignments/submissions', $filename, 'public');
            $data['attachment'] = $path;
        }

        if ($submission) {
            // Update existing submission
            $submission->update($data);
            $message = 'Assignment resubmitted successfully';
        } else {
            // Create new submission
            AssignmentSubmission::create($data);
            $message = 'Assignment submitted successfully';
        }

        return redirect()->route('student.assignments.show', $assignment->id)
            ->with('success', $message);
    }
}
