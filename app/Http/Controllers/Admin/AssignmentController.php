<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Department;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    /**
     * Display a listing of all assignments across the institution.
     */
    public function index(Request $request)
    {
        $query = Assignment::with(['subject.program.department', 'teacher.user', 'submissions']);

        if ($request->filled('department_id')) {
            $query->whereHas('subject.program', fn ($q) => $q->where('department_id', $request->department_id));
        }

        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'upcoming') {
                $query->where('due_date', '>=', now());
            } elseif ($request->status === 'overdue') {
                $query->where('due_date', '<', now());
            }
        }

        if ($request->filled('search')) {
            $term = trim((string) $request->search);
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhereHas('subject', fn ($sq) => $sq->where('name', 'like', "%{$term}%"));
            });
        }

        $assignments = $query->latest('due_date')->paginate(20)->withQueryString();

        // Statistics
        $totalAssignments = Assignment::count();
        $upcomingCount = Assignment::where('due_date', '>=', now())->count();
        $overdueCount = Assignment::where('due_date', '<', now())->count();
        $totalSubmissions = AssignmentSubmission::count();

        // Dropdowns
        $departments = Department::orderBy('name')->get();
        $programs = Program::orderBy('name')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        $teachers = Teacher::with('user')->where('is_active', true)->get();

        return view('admin.assignments.index', compact(
            'assignments',
            'departments',
            'programs',
            'subjects',
            'teachers',
            'totalAssignments',
            'upcomingCount',
            'overdueCount',
            'totalSubmissions'
        ));
    }

    /**
     * Show the form for creating a new assignment.
     */
    public function create()
    {
        $programs = Program::with('department')->orderBy('name')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        $teachers = Teacher::with('user', 'department')->where('is_active', true)->get();

        return view('admin.assignments.create', compact('programs', 'subjects', 'teachers'));
    }

    /**
     * Store a newly created assignment in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'teacher_id'  => 'required|exists:teachers,id',
            'subject_id'  => 'required|exists:subjects,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date'    => 'required|date',
            'section'     => 'nullable|string|max:50',
            'attachment'  => 'nullable|file|max:10240',
        ]);

        $subject = Subject::findOrFail($data['subject_id']);

        $data['program_id'] = $subject->program_id;
        $data['semester'] = $subject->semester;

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('assignments/attachments', 'public');
        }

        $assignment = Assignment::create($data);

        return redirect()->route('admin.assignments.show', $assignment)
            ->with('success', 'Assignment created successfully.');
    }

    /**
     * Display the specified assignment and its student submissions.
     */
    public function show(Assignment $assignment)
    {
        $assignment->load([
            'subject.program.department',
            'teacher.user',
            'submissions.student.user'
        ]);

        return view('admin.assignments.show', compact('assignment'));
    }

    /**
     * Show the form for editing the specified assignment.
     */
    public function edit(Assignment $assignment)
    {
        $assignment->load(['subject', 'teacher']);
        $programs = Program::orderBy('name')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        $teachers = Teacher::with('user')->where('is_active', true)->get();

        return view('admin.assignments.edit', compact('assignment', 'programs', 'subjects', 'teachers'));
    }

    /**
     * Update the specified assignment in storage.
     */
    public function update(Request $request, Assignment $assignment)
    {
        $data = $request->validate([
            'teacher_id'  => 'required|exists:teachers,id',
            'subject_id'  => 'required|exists:subjects,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date'    => 'required|date',
            'section'     => 'nullable|string|max:50',
            'attachment'  => 'nullable|file|max:10240',
        ]);

        $subject = Subject::findOrFail($data['subject_id']);
        $data['program_id'] = $subject->program_id;
        $data['semester'] = $subject->semester;

        if ($request->hasFile('attachment')) {
            if ($assignment->attachment) {
                Storage::disk('public')->delete($assignment->attachment);
            }
            $data['attachment'] = $request->file('attachment')->store('assignments/attachments', 'public');
        }

        $assignment->update($data);

        return redirect()->route('admin.assignments.show', $assignment)
            ->with('success', 'Assignment updated successfully.');
    }

    /**
     * Grade a student submission.
     */
    public function gradeSubmission(Request $request, Assignment $assignment, AssignmentSubmission $submission)
    {
        $validated = $request->validate([
            'marks_obtained'   => 'nullable|numeric|min:0',
            'teacher_feedback' => 'nullable|string|max:1000',
            'status'           => 'required|in:submitted,graded,resubmit',
        ]);

        $submission->update($validated);

        return back()->with('success', 'Submission graded successfully.');
    }

    /**
     * Delete the specified assignment.
     */
    public function destroy(Assignment $assignment)
    {
        if ($assignment->attachment) {
            Storage::disk('public')->delete($assignment->attachment);
        }

        // Delete submissions files
        foreach ($assignment->submissions as $sub) {
            if ($sub->attachment) {
                Storage::disk('public')->delete($sub->attachment);
            }
            $sub->delete();
        }

        $assignment->delete();

        return redirect()->route('admin.assignments.index')
            ->with('success', 'Assignment deleted successfully.');
    }
}
