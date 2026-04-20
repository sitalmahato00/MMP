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
        return view('hod.teachers.create', compact('department'));
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
            'gender' => 'nullable|in:male,female,other',
            'dob' => 'nullable|string|max:10',
            'address' => 'nullable|string',
            'avatar' => 'nullable|image|max:2048',
            'password' => 'required|string|min:8',
            // Professional
            'employee_id' => 'required|string|max:50|unique:teachers,employee_id',
            'designation' => 'required|in:Teacher,HOD',
            'qualification' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'join_date' => 'nullable|string|max:10',
            'employment_type' => 'nullable|in:permanent,contract,part-time',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        DB::transaction(function () use ($data, $deptId) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'gender' => $data['gender'] ?? null,
                'dob' => NepaliDateHelper::toAD($data['dob'] ?? null),
                'address' => $data['address'] ?? null,
                'avatar' => $data['avatar'] ?? null,
                'password' => Hash::make($data['password']),
                'is_active' => true,
            ]);

            // Assign role based on designation
            if ($data['designation'] === 'HOD') {
                $user->assignRole('hod');
            } else {
                $user->assignRole('teacher');
            }

            $teacher = Teacher::create([
                'user_id' => $user->id,
                'department_id' => $deptId,
                'employee_id' => $data['employee_id'],
                'designation' => $data['designation'],
                'qualification' => $data['qualification'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'join_date' => NepaliDateHelper::toAD($data['join_date'] ?? null),
                'employment_type' => $data['employment_type'] ?? 'permanent',
                'is_active' => $data['is_active'] ?? true,
            ]);

            // If creating HOD, update department
            if ($data['designation'] === 'HOD') {
                Department::where('id', $deptId)->update(['hod_id' => $user->id]);
            }
        });

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
        
        return view('hod.teachers.edit', compact('teacher', 'department'));
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
            'gender' => 'nullable|in:male,female,other',
            'dob' => 'nullable|string|max:10',
            'address' => 'nullable|string',
            'avatar' => 'nullable|image|max:2048',
            'employee_id' => ['required', 'string', 'max:50', Rule::unique('teachers')->ignore($teacher->id)],
            'designation' => 'required|in:Teacher,HOD',
            'qualification' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'join_date' => 'nullable|string|max:10',
            'employment_type' => 'nullable|in:permanent,contract,part-time',
            'is_active' => 'nullable|boolean',
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
                'gender' => $data['gender'] ?? null,
                'dob' => NepaliDateHelper::toAD($data['dob'] ?? null),
                'address' => $data['address'] ?? null,
            ] + (isset($data['avatar']) ? ['avatar' => $data['avatar']] : []));

            $teacher->update([
                'employee_id' => $data['employee_id'],
                'designation' => $data['designation'],
                'qualification' => $data['qualification'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'join_date' => NepaliDateHelper::toAD($data['join_date'] ?? null),
                'employment_type' => $data['employment_type'] ?? $teacher->employment_type,
                'is_active' => $data['is_active'] ?? $teacher->is_active,
            ]);

            // Update roles
            $teacher->user->syncRoles($data['designation'] === 'HOD' ? ['hod'] : ['teacher']);

            // Update department HOD if needed
            if ($data['designation'] === 'HOD') {
                Department::where('id', $deptId)->update(['hod_id' => $teacher->user_id]);
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