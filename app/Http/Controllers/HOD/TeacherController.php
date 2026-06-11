<?php

namespace App\Http\Controllers\HOD;

use App\Helpers\NepaliDateHelper;
use App\Models\Department;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Str;
use Illuminate\Validation\Rule;

/**
 * HOD teacher management (department-scoped).
 * 
 * HODs can manage teachers within their department only.
 */
class TeacherController extends HodController
{
    // ── Index ──────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        $query = Teacher::query()
            ->where('department_id', $deptId)
            ->with([
                'user:id,name,email,avatar,phone',
                'department:id,name',
            ])
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->where(function ($inner) use ($term) {
                    $inner->where('employee_id', 'like', "%{$term}%")
                        ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%"));
                });
            })
            ->when($request->designation, fn ($q) => $q->where('designation', $request->designation))
            ->when($request->status, function ($q) use ($request) {
                if ($request->status === 'active') {
                    $q->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $q->where('is_active', false);
                }
            });

        $teachers = (clone $query)
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        // Stats
        $stats = Teacher::query()
            ->where('department_id', $deptId)
            ->selectRaw('COUNT(*) as total_teachers')
            ->selectRaw("SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_teachers")
            ->selectRaw("SUM(CASE WHEN designation = 'HOD' THEN 1 ELSE 0 END) as hod_count")
            ->first();

        $totalTeachers = (int) ($stats->total_teachers ?? 0);
        $activeTeachers = (int) ($stats->active_teachers ?? 0);
        $hodCount = (int) ($stats->hod_count ?? 0);
        $regularTeachers = $totalTeachers - $hodCount;

        return view('hod.teachers.index', compact(
            'teachers', 'department',
            'totalTeachers', 'activeTeachers', 'hodCount', 'regularTeachers'
        ));
    }

    // ── Create ─────────────────────────────────────────────────────────────
    public function create(Request $request)
    {
        $department = $this->currentDepartment($request);
        
        // Get subjects for this department through programs
        $subjects = \App\Models\Subject::whereHas('program', function ($query) use ($department) {
                $query->where('department_id', $department->id);
            })
            ->orderBy('name')
            ->get();
            
        return view('hod.teachers.create', compact('department', 'subjects'));
    }

    public function store(Request $request)
    {
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        $data = $request->validate([
            // Personal
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'gender' => 'required|in:male,female,other', // Made mandatory
            'dob' => 'nullable|string|max:10',
            'address' => 'nullable|string',
            'avatar' => 'nullable|image|max:2048',
            // Professional
            'employee_id' => 'required|string|max:50|unique:teachers,employee_id', // Already required
            'designation' => 'required|in:Teacher', // HODs can only create regular teachers
            'qualification' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'join_date' => 'nullable|string|max:10',
            'employment_type' => 'nullable|in:permanent,contract,part-time',
            'is_active' => 'nullable|boolean',
            // Subject assignments
            'subjects' => 'nullable|array',
            'subjects.*' => [
                'exists:subjects,id',
                function ($attribute, $value, $fail) use ($department) {
                    $subject = \App\Models\Subject::find($value);
                    if ($subject && $subject->program->department_id !== $department->id) {
                        $fail('The selected subject does not belong to this department.');
                    }
                }
            ],
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $createdUser = null;

        DB::transaction(function () use ($data, $deptId, &$createdUser) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'gender' => $data['gender'],
                'dob' => NepaliDateHelper::toAD($data['dob'] ?? null),
                'address' => $data['address'] ?? null,
                'avatar' => $data['avatar'] ?? null,
                'password' => Hash::make(Str::random(40)),
                'is_active' => true,
            ]);

            // HODs can only create regular teachers, not other HODs
            $user->assignRole('teacher');
            $createdUser = $user;

            $teacher = Teacher::create([
                'user_id' => $user->id,
                'department_id' => $deptId,
                'employee_id' => $data['employee_id'],
                'designation' => 'Teacher', // Force to Teacher
                'qualification' => $data['qualification'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'join_date' => NepaliDateHelper::toAD($data['join_date'] ?? null),
                'employment_type' => $data['employment_type'] ?? 'permanent',
                'is_active' => $data['is_active'] ?? true,
            ]);

            // Assign subjects if provided
            if (!empty($data['subjects'])) {
                $currentSession = \App\Models\AcademicSession::current();
                if ($currentSession) {
                    $subjectData = [];
                    foreach ($data['subjects'] as $subjectId) {
                        $subjectData[$subjectId] = [
                            'academic_session_id' => $currentSession->id,
                            'role' => 'teacher',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    $teacher->subjects()->attach($subjectData);
                }
            }
        });

        if ($createdUser) {
            app(\App\Services\PortalNotificationService::class)
                ->sendNewAccountCredentials($createdUser, auth()->user());
        }

        return redirect()
            ->route('hod.teachers.index')
            ->with('success', 'Teacher created successfully.');
    }

    // ── Show ───────────────────────────────────────────────────────────────
    public function show(Request $request, Teacher $teacher)
    {
        $this->authorizeDepartment($request, $teacher);

        $teacher->load([
            'user',
            'department',
        ]);

        // Teaching stats
        $subjectsCount = DB::table('subject_teacher')
            ->where('teacher_id', $teacher->id)
            ->count();

        $attendanceSessionsCount = DB::table('attendance_sessions')
            ->where('teacher_id', $teacher->id)
            ->count();

        $assignmentsCount = DB::table('assignments')
            ->join('subjects', 'assignments.subject_id', '=', 'subjects.id')
            ->join('subject_teacher', 'subjects.id', '=', 'subject_teacher.subject_id')
            ->where('subject_teacher.teacher_id', $teacher->id)
            ->count();

        // Recent activity
        $recentAssignments = DB::table('assignments')
            ->join('subjects', 'assignments.subject_id', '=', 'subjects.id')
            ->where('assignments.teacher_id', $teacher->id)
            ->select('assignments.*', 'subjects.name as subject_name')
            ->orderByDesc('assignments.created_at')
            ->limit(5)
            ->get();

        $recentAttendance = DB::table('attendance_sessions')
            ->join('subjects', 'attendance_sessions.subject_id', '=', 'subjects.id')
            ->where('attendance_sessions.teacher_id', $teacher->id)
            ->select('attendance_sessions.*', 'subjects.name as subject_name')
            ->orderByDesc('attendance_sessions.date')
            ->limit(5)
            ->get();

        // Timeline
        $timeline = DB::table('audit_logs')
            ->where('model_type', 'App\\Models\\Teacher')
            ->where('model_id', $teacher->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('hod.teachers.show', compact(
            'teacher',
            'subjectsCount',
            'attendanceSessionsCount', 
            'assignmentsCount',
            'recentAssignments',
            'recentAttendance',
            'timeline'
        ));
    }

    // ── Edit ───────────────────────────────────────────────────────────────
    public function edit(Request $request, Teacher $teacher)
    {
        $this->authorizeDepartment($request, $teacher);
        $department = $this->currentDepartment($request);
        
        // Get subjects for this department through programs
        $subjects = \App\Models\Subject::whereHas('program', function ($query) use ($department) {
                $query->where('department_id', $department->id);
            })
            ->orderBy('name')
            ->get();
            
        // Get currently assigned subjects
        $assignedSubjects = $teacher->subjects()
            ->wherePivot('academic_session_id', \App\Models\AcademicSession::current()?->id)
            ->pluck('subjects.id')
            ->toArray();
        
        return view('hod.teachers.edit', compact('teacher', 'department', 'subjects', 'assignedSubjects'));
    }

    public function update(Request $request, Teacher $teacher)
    {
        $this->authorizeDepartment($request, $teacher);
        $department = $this->currentDepartment($request);
        $deptId = $department->id;

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($teacher->user_id)],
            'phone' => 'nullable|string|max:20',
            'gender' => 'required|in:male,female,other', // Made mandatory
            'dob' => 'nullable|string|max:10',
            'address' => 'nullable|string',
            'avatar' => 'nullable|image|max:2048',
            'employee_id' => ['required', 'string', 'max:50', Rule::unique('teachers')->ignore($teacher->id)], // Already required
            'designation' => 'required|in:Teacher', // HODs can only manage regular teachers
            'qualification' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'join_date' => 'nullable|string|max:10',
            'employment_type' => 'nullable|in:permanent,contract,part-time',
            'is_active' => 'nullable|boolean',
            // Subject assignments
            'subjects' => 'nullable|array',
            'subjects.*' => [
                'exists:subjects,id',
                function ($attribute, $value, $fail) use ($department) {
                    $subject = \App\Models\Subject::find($value);
                    if ($subject && $subject->program->department_id !== $department->id) {
                        $fail('The selected subject does not belong to this department.');
                    }
                }
            ],
        ]);

        if ($request->hasFile('avatar')) {
            if ($teacher->user->avatar && Storage::disk('public')->exists($teacher->user->avatar)) {
                Storage::disk('public')->delete($teacher->user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            unset($data['avatar']);
        }

        DB::transaction(function () use ($data, $teacher, $deptId) {
            $teacher->user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'gender' => $data['gender'],
                'dob' => NepaliDateHelper::toAD($data['dob'] ?? null),
                'address' => $data['address'] ?? null,
            ] + (isset($data['avatar']) ? ['avatar' => $data['avatar']] : []));

            $teacher->update([
                'employee_id' => $data['employee_id'],
                'designation' => 'Teacher', // Force to Teacher
                'qualification' => $data['qualification'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'join_date' => NepaliDateHelper::toAD($data['join_date'] ?? null),
                'employment_type' => $data['employment_type'] ?? $teacher->employment_type,
                'is_active' => $data['is_active'] ?? $teacher->is_active,
            ]);

            // Ensure role is teacher (HODs cannot change roles)
            $teacher->user->syncRoles(['teacher']);

            // Update subject assignments
            $currentSession = \App\Models\AcademicSession::current();
            if ($currentSession) {
                // Remove existing assignments for current session
                $teacher->subjects()->wherePivot('academic_session_id', $currentSession->id)->detach();
                
                // Add new assignments
                if (!empty($data['subjects'])) {
                    $subjectData = [];
                    foreach ($data['subjects'] as $subjectId) {
                        $subjectData[$subjectId] = [
                            'academic_session_id' => $currentSession->id,
                            'role' => 'teacher',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    $teacher->subjects()->attach($subjectData);
                }
            }
        });

        return redirect()
            ->route('hod.teachers.index')
            ->with('success', 'Teacher updated successfully.');
    }

    // ── Drawer ─────────────────────────────────────────────────────────────
    public function drawer(Request $request, Teacher $teacher)
    {
        $this->authorizeDepartment($request, $teacher);

        $teacher->load([
            'user',
            'department',
        ]);

        // Quick stats - subjects use pivot table
        $subjectsCount = DB::table('subject_teacher')->where('teacher_id', $teacher->id)->count();
        $studentsCount = DB::table('students')->where('department_id', $teacher->department_id)->count();
        $assignmentsCount = DB::table('assignments')
            ->join('subjects', 'assignments.subject_id', '=', 'subjects.id')
            ->join('subject_teacher', 'subjects.id', '=', 'subject_teacher.subject_id')
            ->where('subject_teacher.teacher_id', $teacher->id)
            ->count();

        return view('hod.teachers.drawer', compact('teacher', 'subjectsCount', 'studentsCount', 'assignmentsCount'));
    }

    // ── Delete ─────────────────────────────────────────────────────────────
    public function destroy(Request $request, Teacher $teacher)
    {
        $this->authorizeDepartment($request, $teacher);

        if ($teacher->user && $teacher->user->avatar && Storage::disk('public')->exists($teacher->user->avatar)) {
            Storage::disk('public')->delete($teacher->user->avatar);
        }

        $teacher->user?->delete();
        $teacher->delete();

        return redirect()
            ->route('hod.teachers.index')
            ->with('success', 'Teacher deleted.');
    }
}
