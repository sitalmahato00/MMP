<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $teachers = Teacher::with(['user', 'department'])
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$request->search}%"));
            })
            ->when($request->department_id, fn($q) => $q->where('department_id', $request->department_id))
            ->latest()
            ->paginate(20);

        $departments = Department::orderBy('name')->get();
        return view('admin.teachers.index', compact('teachers', 'departments'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('admin.teachers.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'phone'          => 'nullable|string|max:20',
            'gender'         => 'nullable|in:male,female,other',
            'dob'            => 'nullable|date',
            'address'        => 'nullable|string',
            'avatar'         => 'nullable|image|max:2048',
            'password'       => 'required|string|min:8',
            'department_id'  => 'required|exists:departments,id',
            'qualification'  => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'hire_date'      => 'nullable|date',
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'] ?? null,
            'gender'    => $data['gender'] ?? null,
            'dob'       => $data['dob'] ?? null,
            'address'   => $data['address'] ?? null,
            'avatar'    => $data['avatar'] ?? null,
            'password'  => Hash::make($data['password']),
            'is_active' => true,
        ]);
        $user->assignRole('teacher');

        $teacher = Teacher::create([
            'user_id'        => $user->id,
            'department_id'  => $data['department_id'],
            'qualification'  => $data['qualification'] ?? null,
            'specialization' => $data['specialization'] ?? null,
            'hire_date'      => $data['hire_date'] ?? null,
        ]);

        return redirect()->route('admin.teachers.index')->with('success', 'Teacher added successfully.');
    }

    public function show(Teacher $teacher)
    {
        $teacher->load(['user', 'department']);
        return view('admin.teachers.show', compact('teacher'));
    }

    public function edit(Teacher $teacher)
    {
        $departments = Department::orderBy('name')->get();
        return view('admin.teachers.edit', compact('teacher', 'departments'));
    }

    public function update(Request $request, Teacher $teacher)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => ['required', 'email', Rule::unique('users')->ignore($teacher->user_id)],
            'phone'          => 'nullable|string|max:20',
            'gender'         => 'nullable|in:male,female,other',
            'dob'            => 'nullable|date',
            'address'        => 'nullable|string',
            'avatar'         => 'nullable|image|max:2048',
            'department_id'  => 'required|exists:departments,id',
            'qualification'  => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'hire_date'      => 'nullable|date',
        ]);

        if ($request->hasFile('avatar')) {
            if ($teacher->user->avatar && Storage::disk('public')->exists($teacher->user->avatar)) {
                Storage::disk('public')->delete($teacher->user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            unset($data['avatar']);
        }

        $teacher->user->update([
            'name'    => $data['name'],
            'email'   => $data['email'],
            'phone'   => $data['phone'] ?? null,
            'gender'  => $data['gender'] ?? null,
            'dob'     => $data['dob'] ?? null,
            'address' => $data['address'] ?? null,
        ] + (isset($data['avatar']) ? ['avatar' => $data['avatar']] : []));

        $teacher->update([
            'department_id'  => $data['department_id'],
            'qualification'  => $data['qualification'] ?? null,
            'specialization' => $data['specialization'] ?? null,
            'hire_date'      => $data['hire_date'] ?? null,
        ]);

        return redirect()->route('admin.teachers.index')->with('success', 'Teacher updated successfully.');
    }

    public function destroy(Teacher $teacher)
    {
        if ($teacher->user->avatar && Storage::disk('public')->exists($teacher->user->avatar)) {
            Storage::disk('public')->delete($teacher->user->avatar);
        }
        $teacher->user->delete();
        $teacher->delete();
        return redirect()->route('admin.teachers.index')->with('success', 'Teacher removed.');
    }
}
