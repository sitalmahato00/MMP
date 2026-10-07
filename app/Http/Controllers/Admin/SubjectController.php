<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Department;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubjectController extends Controller
{
    /**
     * Display a listing of all subjects across all departments and programs.
     */
    public function index(Request $request)
    {
        $query = Subject::with(['program.department', 'teachers.user']);

        // Search
        if ($request->filled('search')) {
            $term = trim((string) $request->search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('code', 'like', "%{$term}%");
            });
        }

        // Filter by Department
        if ($request->filled('department_id')) {
            $query->whereHas('program', fn ($q) => $q->where('department_id', $request->department_id));
        }

        // Filter by Program
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        // Filter by Semester
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        // Filter by Type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $subjects = $query->orderBy('program_id')
            ->orderBy('semester')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        // Statistics
        $totalSubjects = Subject::count();
        $theoryCount = Subject::whereIn('type', ['theory', 'both'])->count();
        $practicalCount = Subject::whereIn('type', ['practical', 'both'])->count();
        $activeCount = Subject::where('is_active', true)->count();

        // Dropdown Data
        $departments = Department::orderBy('name')->get();
        $programs = Program::orderBy('name')->get();

        return view('admin.subjects.index', compact(
            'subjects',
            'departments',
            'programs',
            'totalSubjects',
            'theoryCount',
            'practicalCount',
            'activeCount'
        ));
    }

    /**
     * Show the form for creating a new subject.
     */
    public function create()
    {
        $departments = Department::with('programs')->orderBy('name')->get();
        $programs = Program::with('department')->orderBy('name')->get();
        $teachers = Teacher::with('user', 'department')->where('is_active', true)->get();
        $currentSession = AcademicSession::current();

        return view('admin.subjects.create', compact('departments', 'programs', 'teachers', 'currentSession'));
    }

    /**
     * Store a newly created subject in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'semester' => 'nullable|integer|min:1|max:6',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects,code',
            'type' => 'required|in:theory,practical,both',
            'credit_hours' => 'nullable|integer|min:0',
            'details' => 'nullable|string',
            'syllabus' => 'nullable|file|mimes:pdf|max:10240',
            'full_marks_internal_theory' => 'nullable|numeric|min:0',
            'pass_marks_internal_theory' => 'nullable|numeric|min:0',
            'full_marks_external_theory' => 'nullable|numeric|min:0',
            'pass_marks_external_theory' => 'nullable|numeric|min:0',
            'full_marks_internal_practical' => 'nullable|numeric|min:0',
            'pass_marks_internal_practical' => 'nullable|numeric|min:0',
            'full_marks_external_practical' => 'nullable|numeric|min:0',
            'pass_marks_external_practical' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'teachers' => 'nullable|array',
            'teachers.*.teacher_id' => 'required|exists:teachers,id',
            'teachers.*.role' => 'required|string|max:100',
            'teachers.*.section' => 'nullable|string|max:50',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['credit_hours'] = $validated['credit_hours'] ?? 0;
        $validated['details'] = filled($validated['details'] ?? null) ? trim((string) $validated['details']) : null;

        if ($request->hasFile('syllabus')) {
            $validated['syllabus'] = $request->file('syllabus')->store('subjects/syllabi', 'public');
        } else {
            unset($validated['syllabus']);
        }

        $subject = Subject::create($validated);

        // Assign teachers if provided
        if (!empty($validated['teachers'])) {
            $currentSession = AcademicSession::current();
            if ($currentSession) {
                foreach ($validated['teachers'] as $teacherData) {
                    $subject->teachers()->attach($teacherData['teacher_id'], [
                        'academic_session_id' => $currentSession->id,
                        'section' => $teacherData['section'] ?? null,
                        'role' => $teacherData['role'],
                    ]);
                }
            }
        }

        return redirect()
            ->route('admin.subjects.show', $subject)
            ->with('success', "Subject '{$subject->name}' created successfully.");
    }

    /**
     * Display the specified subject.
     */
    public function show(Subject $subject)
    {
        $subject->load(['program.department', 'assignments.teacher.user', 'markingScheme']);

        $currentSession = AcademicSession::current();

        $assignedTeachers = $subject->teachers()
            ->when($currentSession, fn ($q) => $q->where('subject_teacher.academic_session_id', $currentSession->id))
            ->with('user', 'department')
            ->get();

        $availableTeachers = Teacher::with('user', 'department')
            ->where('is_active', true)
            ->get();

        return view('admin.subjects.show', compact('subject', 'currentSession', 'assignedTeachers', 'availableTeachers'));
    }

    /**
     * Display subject details in drawer (AJAX).
     */
    public function drawer(Subject $subject)
    {
        $subject->load(['program.department']);
        $currentSession = AcademicSession::current();

        $assignedTeachers = $subject->teachers()
            ->when($currentSession, fn ($q) => $q->where('subject_teacher.academic_session_id', $currentSession->id))
            ->with('user')
            ->get();

        return view('admin.subjects.drawer', compact('subject', 'currentSession', 'assignedTeachers'));
    }

    /**
     * Show the form for editing the specified subject.
     */
    public function edit(Subject $subject)
    {
        $subject->load('program.department');
        $departments = Department::orderBy('name')->get();
        $programs = Program::with('department')->orderBy('name')->get();
        $currentSession = AcademicSession::current();

        $assignedTeachers = $subject->teachers()
            ->when($currentSession, fn ($q) => $q->where('subject_teacher.academic_session_id', $currentSession->id))
            ->with('user')
            ->get();

        $availableTeachers = Teacher::with('user', 'department')->where('is_active', true)->get();

        return view('admin.subjects.edit', compact(
            'subject',
            'departments',
            'programs',
            'currentSession',
            'assignedTeachers',
            'availableTeachers'
        ));
    }

    /**
     * Update the specified subject in storage.
     */
    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'semester' => 'nullable|integer|min:1|max:6',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects,code,' . $subject->id,
            'type' => 'required|in:theory,practical,both',
            'credit_hours' => 'nullable|integer|min:0',
            'details' => 'nullable|string',
            'syllabus' => 'nullable|file|mimes:pdf|max:10240',
            'full_marks_internal_theory' => 'nullable|numeric|min:0',
            'pass_marks_internal_theory' => 'nullable|numeric|min:0',
            'full_marks_external_theory' => 'nullable|numeric|min:0',
            'pass_marks_external_theory' => 'nullable|numeric|min:0',
            'full_marks_internal_practical' => 'nullable|numeric|min:0',
            'pass_marks_internal_practical' => 'nullable|numeric|min:0',
            'full_marks_external_practical' => 'nullable|numeric|min:0',
            'pass_marks_external_practical' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['credit_hours'] = $validated['credit_hours'] ?? 0;
        $validated['details'] = filled($validated['details'] ?? null) ? trim((string) $validated['details']) : null;

        if ($request->hasFile('syllabus')) {
            if ($subject->syllabus) {
                Storage::disk('public')->delete($subject->syllabus);
            }
            $validated['syllabus'] = $request->file('syllabus')->store('subjects/syllabi', 'public');
        }

        $subject->update($validated);

        return redirect()
            ->route('admin.subjects.show', $subject)
            ->with('success', "Subject '{$subject->name}' updated successfully.");
    }

    /**
     * Assign teacher to the subject.
     */
    public function assignTeacher(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'section' => 'nullable|string|max:50',
            'role' => 'required|string|max:100',
        ]);

        $currentSession = AcademicSession::current();
        if (!$currentSession) {
            return back()->with('error', 'No active academic session found.');
        }

        // Check if already assigned with same role
        $exists = $subject->teachers()
            ->where('teacher_id', $validated['teacher_id'])
            ->where('academic_session_id', $currentSession->id)
            ->where('section', $validated['section'] ?? null)
            ->where('role', $validated['role'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Teacher is already assigned to this subject with the same role.');
        }

        $subject->teachers()->attach($validated['teacher_id'], [
            'academic_session_id' => $currentSession->id,
            'section' => $validated['section'] ?? null,
            'role' => $validated['role'],
        ]);

        return back()->with('success', 'Teacher assigned successfully.');
    }

    /**
     * Remove teacher from the subject.
     */
    public function removeTeacher(Request $request, Subject $subject, Teacher $teacher)
    {
        $currentSession = AcademicSession::current();
        if (!$currentSession) {
            return back()->with('error', 'No active academic session found.');
        }

        $subject->teachers()
            ->wherePivot('teacher_id', $teacher->id)
            ->wherePivot('academic_session_id', $currentSession->id)
            ->detach($teacher->id);

        return back()->with('success', 'Teacher assignment removed successfully.');
    }

    /**
     * Delete the specified subject.
     */
    public function destroy(Subject $subject)
    {
        if ($subject->syllabus) {
            Storage::disk('public')->delete($subject->syllabus);
        }

        $name = $subject->name;
        $subject->delete();

        return redirect()
            ->route('admin.subjects.index')
            ->with('success', "Subject '{$name}' deleted successfully.");
    }
}
