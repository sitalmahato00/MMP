<?php

namespace App\Modules\Parent\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class AssignmentsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $parent = $user->parentProfile;
        
        if (!$parent) {
            abort(403, 'Parent profile not found');
        }
        
        // Get all children of the parent
        $children = $parent->children()
            ->with(['program', 'department', 'user'])
            ->get();

        // Get assignments for each child
        $childrenAssignments = [];
        
        foreach ($children as $child) {
            $assignments = Assignment::where('program_id', $child->program_id)
                ->where('semester', $child->current_semester)
                ->where('section', $child->section)
                ->with(['subject', 'teacher.user', 'submissions' => function($query) use ($child) {
                    $query->where('student_id', $child->id);
                }])
                ->orderBy('due_date', 'desc')
                ->get();

            // Group assignments by subject
            $assignmentsBySubject = $assignments->groupBy('subject_id')->map(function($subjectAssignments) use ($child) {
                return [
                    'subject' => $subjectAssignments->first()->subject,
                    'assignments' => $subjectAssignments->map(function($assignment) use ($child) {
                        $submission = $assignment->submissions->first();
                        
                        return [
                            'id' => $assignment->id,
                            'title' => $assignment->title,
                            'description' => $assignment->description,
                            'due_date' => $assignment->due_date,
                            'attachment' => $assignment->attachment,
                            'teacher_name' => $assignment->teacher->user->name ?? 'N/A',
                            'submission' => $submission,
                            'status' => $submission ? $submission->status : 'pending',
                            'marks_obtained' => $submission ? $submission->marks_obtained : null,
                            'is_overdue' => !$submission && $assignment->due_date < now(),
                        ];
                    })
                ];
            });

            $childrenAssignments[] = [
                'child' => $child,
                'assignments_by_subject' => $assignmentsBySubject,
                'total_assignments' => $assignments->count(),
                'pending_count' => $assignments->filter(function($a) use ($child) {
                    return !$a->submissions->first();
                })->count(),
                'submitted_count' => $assignments->filter(function($a) use ($child) {
                    $sub = $a->submissions->first();
                    return $sub && $sub->status === 'submitted';
                })->count(),
                'graded_count' => $assignments->filter(function($a) use ($child) {
                    $sub = $a->submissions->first();
                    return $sub && $sub->status === 'graded';
                })->count(),
            ];
        }

        return view('parent.assignments', compact('childrenAssignments'));
    }
}
