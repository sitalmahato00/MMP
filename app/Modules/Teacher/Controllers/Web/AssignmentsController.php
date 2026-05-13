<?php

namespace App\Modules\Teacher\Controllers\Web;


use App\Http\Controllers\Controller;
use App\Modules\Academic\Models\AcademicSession;
use App\Modules\Academic\Models\Program;
use App\Modules\Academic\Models\Subject;
use App\Modules\Assignment\Models\Assignment;
use App\Modules\Student\Models\Student;
use App\Modules\Teacher\Models\Teacher;
use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentsController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        if (!$teacher) {
            abort(403, 'Teacher profile not found');
        }

        // Get assignments created by teacher
        $query = Assignment::where('teacher_id', $teacher->id)
            ->with(['subject', 'program', 'submissions'])
            ->withCount('submissions')
            ->when($request->search, fn ($q) => $q->where('title', 'like', '%' . $request->search . '%'))
            ->when($request->subject_id, fn ($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->status, function ($q) use ($request) {
                if ($request->status === 'upcoming') {
                    $q->where('due_date', '>=', now());
                } elseif ($request->status === 'overdue') {
                    $q->where('due_date', '<', now());
                }
            });

        $assignments = $query->latest('due_date')->paginate(20);

        // Get subjects for filter
        $subjects = $teacher->subjects()
            ->wherePivot('academic_session_id', $session?->id)
            ->get();

        // Stats
        $totalAssignments = (clone $query)->count();
        $upcomingAssignments = (clone $query)->where('due_date', '>=', now())->count();
        $overdueAssignments = (clone $query)->where('due_date', '<', now())->count();

        return view('teacher.assignments.index', compact('assignments', 'subjects', 'totalAssignments', 'upcomingAssignments', 'overdueAssignments', 'session'));
    }

    public function create()
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        if (!$teacher) {
            abort(403, 'Teacher profile not found');
        }

        // Get teacher's subjects
        $subjects = $teacher->subjects()
            ->wherePivot('academic_session_id', $session?->id)
            ->with('program')
            ->get();

        return view('teacher.assignments.create', compact('subjects', 'session'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $session = AcademicSession::current();

        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date|after_or_equal:today',
            'attachment' => 'nullable|file|max:10240',
        ]);

        // Verify teacher teaches this subject
        if (!$teacher->subjects()->where('subject_id', $data['subject_id'])->wherePivot('academic_session_id', $session?->id)->exists()) {
            abort(403, 'Unauthorized');
        }

        $subject = Subject::find($data['subject_id']);

        $assignmentData = [
            'teacher_id' => $teacher->id,
            'subject_id' => $data['subject_id'],
            'program_id' => $subject->program_id,
            'semester' => $subject->semester,
            'title' => $data['title'],
            'description' => $data['description'],
            'due_date' => $data['due_date'],
        ];

        if ($request->hasFile('attachment')) {
            $assignmentData['attachment'] = $request->file('attachment')->store('assignments', 'public');
        }

        Assignment::create($assignmentData);

        return redirect()->route('teacher.assignments.index')->with('success', 'Assignment created successfully.');
    }

    public function show(Assignment $assignment)
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        // Verify authorization
        if ($assignment->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized');
        }

        $assignment->load(['subject', 'program', 'submissions.student.user']);

        return view('teacher.assignments.show', compact('assignment'));
    }

    public function edit(Assignment $assignment)
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        // Verify authorization
        if ($assignment->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized');
        }

        $session = AcademicSession::current();
        $subjects = $teacher->subjects()
            ->wherePivot('academic_session_id', $session?->id)
            ->with('program')
            ->get();

        return view('teacher.assignments.edit', compact('assignment', 'subjects', 'session'));
    }

    public function update(Request $request, Assignment $assignment)
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        // Verify authorization
        if ($assignment->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date|after_or_equal:today',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $updateData = [
            'subject_id' => $data['subject_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'due_date' => $data['due_date'],
        ];

        if ($request->hasFile('attachment')) {
            if ($assignment->attachment) {
                Storage::disk('public')->delete($assignment->attachment);
            }
            $updateData['attachment'] = $request->file('attachment')->store('assignments', 'public');
        }

        $assignment->update($updateData);

        return redirect()->route('teacher.assignments.show', $assignment)->with('success', 'Assignment updated successfully.');
    }

    public function destroy(Assignment $assignment)
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        // Verify authorization
        if ($assignment->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized');
        }

        if ($assignment->attachment) {
            Storage::disk('public')->delete($assignment->attachment);
        }

        $assignment->delete();

        return redirect()->route('teacher.assignments.index')->with('success', 'Assignment deleted successfully.');
    }
}
