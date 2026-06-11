<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\NepaliDateHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with(['roles', 'teacher.department', 'hodDepartment'])
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->role, fn($q) => $q->role($request->role))
            ->when($request->filled('status'), fn($q) => $q->where('is_active', $request->status))
            ->latest()
            ->paginate(20);

        // Get additional statistics for header
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $inactiveUsers = User::where('is_active', false)->count();
        $usersByRole = [
            'students' => User::role('student')->count(),
            'teachers' => User::role('teacher')->count(),
            'parents' => User::role('parent')->count(),
            'alumni' => User::role('alumni')->count(),
        ];
        $usersWithDepartments = User::whereHas('teacher.department')
            ->orWhereHas('hodDepartment')
            ->count();

        return view('admin.users.index', compact(
            'users', 
            'totalUsers', 
            'activeUsers', 
            'inactiveUsers', 
            'usersByRole',
            'usersWithDepartments'
        ));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'phone'    => 'nullable|string|max:20',
            'gender'   => 'nullable|in:male,female,other',
            'dob'      => 'nullable|string|max:10',
            'address'  => 'nullable|string',
            'avatar'   => 'nullable|image|max:2048',
            'role'     => 'required|in:principal,hod,teacher,student,parent,alumni',
            'is_active'=> 'boolean',
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'] ?? null,
            'gender'    => $data['gender'] ?? null,
            'dob'       => NepaliDateHelper::toAD($data['dob'] ?? null),
            'address'   => $data['address'] ?? null,
            'avatar'    => $data['avatar'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'password'  => Hash::make(Str::random(40)),
        ]);

        $user->assignRole($data['role']);

        app(\App\Services\PortalNotificationService::class)
            ->sendNewAccountCredentials($user, auth()->user());

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} created successfully.");
    }

    public function show(User $user)
    {
        $user->load('roles');
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required','email', Rule::unique('users')->ignore($user->id)],
            'phone'     => 'nullable|string|max:20',
            'gender'    => 'nullable|in:male,female,other',
            'dob'       => 'nullable|string|max:10',
            'address'   => 'nullable|string',
            'avatar'    => 'nullable|image|max:2048',
            'role'      => 'required|in:principal,hod,teacher,student,parent,alumni',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            unset($data['avatar']); // Prevent overwriting existing avatar with null if no new file
        }

        $user->update([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'] ?? null,
            'gender'    => $data['gender'] ?? null,
            'dob'       => NepaliDateHelper::toAD($data['dob'] ?? null),
            'address'   => $data['address'] ?? null,
            'is_active' => $data['is_active'] ?? $user->is_active,
        ] + (isset($data['avatar']) ? ['avatar' => $data['avatar']] : []));

        $user->syncRoles([$data['role']]);

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} updated.");
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'Cannot delete your own account.');
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
        $user->delete();
        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted.');
    }
}
