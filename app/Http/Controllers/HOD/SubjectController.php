<?php

namespace App\Http\Controllers\HOD;

use App\Models\Subject;
use App\Models\Program;
use App\Models\Teacher;
use App\Models\AcademicSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectController extends HodController
{
    /**
     * Display a listing of subjects for the department
     */
    public function index(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Get subjects for programs in this department
        $query = Subject::whereHas('program', function ($q) use ($deptId) {
            $q->where('department_id', $deptId);
        })->with(['program:id,name,code,department_id']);

        // Apply filters
        if ($request->search) {
            $term = trim((string) $request->search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('code', 'like', "%{$term}%");
            });
        }

        if ($request->program_id) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->semester) {
            $query->where('semester', $request->semester);
        }

        $subjects = $query->orderBy('program_id')
            ->orderBy('semester')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        // Get programs for filter
        $programs = Program::where('department_id', $deptId)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view('hod.subjects.index', compact(
            'subjects',
            'programs',
            'department'
        ));
    }

    /**
     * Show the form for creating a new subject
     */
    public function create(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Get programs for this department
        $programs = Program::where('department_id', $deptId)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        // Get teachers for this department
        $teachers = Teacher::where('department_id', $deptId)
            ->where('is_active', true)
            ->with('user:id,name,email')
            ->orderBy('user_id')
            ->get();

        // Get current academic session
        $currentSession = AcademicSession::current();

        return view('hod.subjects.create', compact('department', 'programs', 'teachers', 'currentSession'));
    }

    /**
     * Store a newly created subject in storage
     */
    public function store(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        $validated = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'semester' => 'required|integer|min:1|max:8',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects,code',
            'type' => 'required|in:theory,practical,both',
            'credit_hours' => 'nullable|integer|min:0',
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

        // Verify program belongs to department
        $program = Program::findOrFail($validated['program_id']);
        if ($program->department_id !== $deptId) {
            abort(403, 'This program does not belong to your department.');
        }

        $validated['is_active'] = $request->has('is_active');

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
            ->route('hod.subjects.show', $subject)
            ->with('success', 'Subject created successfully.');
    }

    /**
     * Display the specified subject
     */
    public function show(Request $request, Subject $subject)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Verify subject belongs to department
        if ($subject->program->department_id !== $deptId) {
            abort(403, 'This subject does not belong to your department.');
        }

        $subject->load(['program:id,name,code,department_id']);

        // Get current academic session
        $currentSession = AcademicSession::current();

        // Get assigned teachers for current session
        $assignedTeachers = $subject->teachers()
            ->where('subject_teacher.academic_session_id', $currentSession?->id)
            ->with('user:id,name,email')
            ->get();

        // Get available teachers from department
        $availableTeachers = Teacher::where('department_id', $deptId)
            ->where('is_active', true)
            ->with('user:id,name,email')
            ->orderBy('user_id')
            ->get();

        return view('hod.subjects.show', compact(
            'subject',
            'department',
            'currentSession',
            'assignedTeachers',
            'availableTeachers'
        ));
    }

    /**
     * Display subject details in drawer (AJAX)
     */
    public function drawer(Request $request, Subject $subject)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Verify subject belongs to department
        if ($subject->program->department_id !== $deptId) {
            abort(403, 'This subject does not belong to your department.');
        }

        $subject->load(['program:id,name,code,department_id']);

        // Get current academic session
        $currentSession = AcademicSession::current();

        // Get assigned teachers for current session
        $assignedTeachers = $subject->teachers()
            ->where('subject_teacher.academic_session_id', $currentSession?->id)
            ->with('user:id,name,email')
            ->get();

        return view('hod.subjects.drawer', compact(
            'subject',
            'currentSession',
            'assignedTeachers'
        ));
    }

    /**
     * Show the form for editing the specified subject
     */
    public function edit(Request $request, Subject $subject)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Verify subject belongs to department
        if ($subject->program->department_id !== $deptId) {
            abort(403, 'This subject does not belong to your department.');
        }

        $subject->load('program:id,name,code,department_id');

        // Get programs for this department
        $programs = Program::where('department_id', $deptId)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        // Get current academic session
        $currentSession = AcademicSession::current();

        // Get assigned teachers for current session
        $assignedTeachers = $subject->teachers()
            ->where('subject_teacher.academic_session_id', $currentSession?->id)
            ->with('user:id,name,email')
            ->get();

        // Get available teachers from department
        $availableTeachers = Teacher::where('department_id', $deptId)
            ->where('is_active', true)
            ->with('user:id,name,email')
            ->orderBy('user_id')
            ->get();

        return view('hod.subjects.edit', compact(
            'subject',
            'department',
            'programs',
            'currentSession',
            'assignedTeachers',
            'availableTeachers'
        ));
    }

    /**
     * Update the specified subject in storage
     */
    public function update(Request $request, Subject $subject)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Verify subject belongs to department
        if ($subject->program->department_id !== $deptId) {
            abort(403, 'This subject does not belong to your department.');
        }

        $validated = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'semester' => 'required|integer|min:1|max:8',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects,code,' . $subject->id,
            'type' => 'required|in:theory,practical,both',
            'credit_hours' => 'nullable|integer|min:0',
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

        // Verify program belongs to department
        $program = Program::findOrFail($validated['program_id']);
        if ($program->department_id !== $deptId) {
            abort(403, 'This program does not belong to your department.');
        }

        $validated['is_active'] = $request->has('is_active');

        $subject->update($validated);

        return redirect()
            ->route('hod.subjects.show', $subject)
            ->with('success', 'Subject updated successfully.');
    }

    /**
     * Assign a teacher to the subject
     */
    public function assignTeacher(Request $request, Subject $subject)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Verify subject belongs to department
        if ($subject->program->department_id !== $deptId) {
            abort(403, 'This subject does not belong to your department.');
        }

        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'section' => 'nullable|string|max:50',
            'role' => 'required|string|max:100',
        ]);

        // Verify teacher belongs to department
        $teacher = Teacher::findOrFail($validated['teacher_id']);
        if ($teacher->department_id !== $deptId) {
            abort(403, 'This teacher does not belong to your department.');
        }

        // Get current academic session
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

        // Assign teacher
        $subject->teachers()->attach($validated['teacher_id'], [
            'academic_session_id' => $currentSession->id,
            'section' => $validated['section'] ?? null,
            'role' => $validated['role'],
        ]);

        return back()->with('success', 'Teacher assigned successfully.');
    }

    /**
     * Remove a teacher assignment from the subject
     */
    public function removeTeacher(Request $request, Subject $subject, Teacher $teacher)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        // Verify subject belongs to department
        if ($subject->program->department_id !== $deptId) {
            abort(403, 'This subject does not belong to your department.');
        }

        // Verify teacher belongs to department
        if ($teacher->department_id !== $deptId) {
            abort(403, 'This teacher does not belong to your department.');
        }

        // Get current academic session
        $currentSession = AcademicSession::current();
        if (!$currentSession) {
            return back()->with('error', 'No active academic session found.');
        }

        // Remove assignment
        $subject->teachers()
            ->wherePivot('teacher_id', $teacher->id)
            ->wherePivot('academic_session_id', $currentSession->id)
            ->detach($teacher->id);

        return back()->with('success', 'Teacher assignment removed successfully.');
    }
}
