<?php

namespace App\Modules\Hod\Controllers\Api;

use App\Core\Base\BaseController;
use App\Modules\Department\Models\Department;
use App\Modules\User\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class HodApiController extends BaseController
{
    public function stats(): JsonResponse
    {
        return $this->success([
            'total_hods'          => User::role('hod')->count(),
            'active_hods'         => User::role('hod')->where('is_active', true)->count(),
            'assigned_departments' => Department::whereNotNull('hod_id')->count(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $hods = User::role('hod')
            ->with(['hodDepartment:id,name'])
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->filled('status'), fn($q) => $q->where('is_active', $request->status))
            ->latest()
            ->paginate(min((int) ($request->per_page ?? 20), 100))
            ->withQueryString();

        return $this->success($hods);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => ['required', 'email', Rule::unique('users')->whereNull('deleted_at')],
            'phone'         => 'nullable|string|max:20',
            'gender'        => 'nullable|in:male,female,other',
            'dob'           => 'nullable|string|max:10',
            'address'       => 'nullable|string',
            'avatar'        => 'nullable|image|max:2048',
            'department_id' => 'nullable|exists:departments,id',
            'is_active'     => 'boolean',
            'password'      => 'required|string|min:8|confirmed',
        ]);

        if (!empty($data['department_id'])) {
            $dept = Department::find($data['department_id']);
            if ($dept && $dept->hod_id) {
                return $this->error('This department already has an HOD assigned.', 422);
            }
        }

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
            'is_active' => $data['is_active'] ?? true,
            'password'  => Hash::make($data['password']),
        ]);

        $user->assignRole('hod');

        if (!empty($data['department_id'])) {
            Department::find($data['department_id'])->update(['hod_id' => $user->id]);
        }

        return $this->created(
            $user->load('hodDepartment'),
            'HOD created successfully.'
        );
    }

    public function show(User $hod): JsonResponse
    {
        if (!$hod->hasRole('hod')) {
            return $this->error('User is not an HOD.', 404);
        }
        $hod->load('hodDepartment');
        return $this->success($hod);
    }

    public function update(Request $request, User $hod): JsonResponse
    {
        if (!$hod->hasRole('hod')) {
            return $this->error('User is not an HOD.', 404);
        }

        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => ['required', 'email', Rule::unique('users')->ignore($hod->id)],
            'phone'         => 'nullable|string|max:20',
            'gender'        => 'nullable|in:male,female,other',
            'dob'           => 'nullable|string|max:10',
            'address'       => 'nullable|string',
            'avatar'        => 'nullable|image|max:2048',
            'department_id' => 'nullable|exists:departments,id',
            'is_active'     => 'boolean',
            'password'      => 'nullable|string|min:8|confirmed',
        ]);

        if (!empty($data['department_id'])) {
            $dept = Department::find($data['department_id']);
            if ($dept && $dept->hod_id && $dept->hod_id !== $hod->id) {
                return $this->error('This department already has a different HOD assigned.', 422);
            }
        }

        if ($request->hasFile('avatar')) {
            if ($hod->avatar && Storage::disk('public')->exists($hod->avatar)) {
                Storage::disk('public')->delete($hod->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            unset($data['avatar']);
        }

        $hod->update([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'] ?? null,
            'gender'    => $data['gender'] ?? null,
            'dob'       => $data['dob'] ?? null,
            'address'   => $data['address'] ?? null,
            'is_active' => $data['is_active'] ?? $hod->is_active,
        ] + (isset($data['avatar']) ? ['avatar' => $data['avatar']] : []));

        if (!empty($data['password'])) {
            $hod->update(['password' => Hash::make($data['password'])]);
        }

        $currentDept = $hod->hodDepartment;
        if ($currentDept && $currentDept->id !== ($data['department_id'] ?? null)) {
            $currentDept->update(['hod_id' => null]);
        }
        if (!empty($data['department_id'])) {
            Department::find($data['department_id'])->update(['hod_id' => $hod->id]);
        }

        return $this->success($hod->fresh('hodDepartment'), 'HOD updated.');
    }

    public function destroy(User $hod): JsonResponse
    {
        if (!$hod->hasRole('hod')) {
            return $this->error('User is not an HOD.', 404);
        }

        if ($hod->hodDepartment) {
            $hod->hodDepartment->update(['hod_id' => null]);
        }
        if ($hod->avatar && Storage::disk('public')->exists($hod->avatar)) {
            Storage::disk('public')->delete($hod->avatar);
        }
        $hod->forceDelete();

        return $this->success(['message' => 'HOD deleted.']);
    }
}
