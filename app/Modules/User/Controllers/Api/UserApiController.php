<?php

namespace App\Modules\User\Controllers\Api;

use App\Core\Base\BaseController;
use App\Modules\User\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserApiController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $users = User::with(['roles', 'hodDepartment'])
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->role, fn($q) => $q->role($request->role))
            ->when($request->filled('status'), fn($q) => $q->where('is_active', $request->status))
            ->latest()
            ->paginate(min((int) ($request->per_page ?? 20), 100))
            ->withQueryString();

        return $this->success($users);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users',
            'phone'     => 'nullable|string|max:20',
            'gender'    => 'nullable|in:male,female,other',
            'dob'       => 'nullable|string|max:10',
            'address'   => 'nullable|string',
            'avatar'    => 'nullable|image|max:2048',
            'role'      => 'required|in:principal,hod,teacher,student,parent,alumni,staff',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $password = Str::password(12);

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'] ?? null,
            'gender'    => $data['gender'] ?? null,
            'dob'       => $data['dob'] ?? null,
            'address'   => $data['address'] ?? null,
            'avatar'    => $data['avatar'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'password'  => Hash::make($password),
        ]);

        $user->assignRole($data['role']);

        return $this->created(
            $user->load('roles'),
            "User {$user->name} created. Password: {$password}"
        );
    }

    public function show(User $user): JsonResponse
    {
        $user->load('roles');
        return $this->success($user);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone'     => 'nullable|string|max:20',
            'gender'    => 'nullable|in:male,female,other',
            'dob'       => 'nullable|string|max:10',
            'address'   => 'nullable|string',
            'avatar'    => 'nullable|image|max:2048',
            'role'      => 'required|in:principal,hod,teacher,student,parent,alumni,staff',
            'is_active' => 'boolean',
            'password'  => 'nullable|string|min:8|confirmed',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            unset($data['avatar']);
        }

        $user->update([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'] ?? null,
            'gender'    => $data['gender'] ?? null,
            'dob'       => $data['dob'] ?? null,
            'address'   => $data['address'] ?? null,
            'is_active' => $data['is_active'] ?? $user->is_active,
        ] + (isset($data['avatar']) ? ['avatar' => $data['avatar']] : []));

        if (!empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        $user->syncRoles([$data['role']]);

        return $this->success($user->load('roles'), 'User updated.');
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()->id) {
            return $this->error('Cannot delete your own account.', 403);
        }
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
        $user->forceDelete();
        return $this->success(['message' => 'User deleted.']);
    }
}
