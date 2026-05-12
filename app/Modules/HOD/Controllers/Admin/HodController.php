<?php

namespace App\Modules\HOD\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\NepaliDateHelper;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class HodController extends Controller
{
    public function index(Request $request)
    {
        $hods = User::role('hod')
            ->with(['roles', 'hodDepartment'])
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->filled('status'), fn($q) => $q->where('is_active', $request->status))
            ->latest()
            ->paginate(20);

        return view('admin.hods.index', compact('hods'));
    }

    public function create()
    {
        $departments = Department::active()
            ->whereNull('hod_id')
            ->orderBy('name')
            ->get();
        
        return view('admin.hods.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users',
            'phone'         => 'nullable|string|max:20',
            'gender'        => 'nullable|in:male,female,other',
            'dob'           => 'nullable|string|max:10',
            'address'       => 'nullable|string',
            'avatar'        => 'nullable|image|max:2048',
            'department_id' => 'nullable|exists:departments,id',
            'is_active'     => 'boolean',
            'password'      => 'required|string|min:8|confirmed',
        ]);

        // Validate department doesn't already have an HOD
        if (!empty($data['department_id'])) {
            $department = Department::find($data['department_id']);
            if ($department && $department->hod_id) {
                return back()->withErrors(['department_id' => 'This department already has an HOD assigned.'])->withInput();
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
            'dob'       => NepaliDateHelper::toAD($data['dob'] ?? null),
            'address'   => $data['address'] ?? null,
            'avatar'    => $data['avatar'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'password'  => Hash::make($data['password']),
        ]);

        $user->assignRole('hod');

        // Assign department if provided
        if (!empty($data['department_id'])) {
            Department::find($data['department_id'])->update(['hod_id' => $user->id]);
        }

        app(\App\Services\PortalNotificationService::class)
            ->sendNewAccountCredentials($user, $data['password'], auth()->user());

        return redirect()->route('admin.hods.index')
            ->with('success', "HOD {$user->name} created successfully.");
    }

    public function show(User $hod)
    {
        abort_unless($hod->hasRole('hod'), 404);
        $hod->load(['roles', 'hodDepartment']);
        return view('admin.hods.show', compact('hod'));
    }

    public function edit(User $hod)
    {
        abort_unless($hod->hasRole('hod'), 404);
        
        $departments = Department::active()
            ->where(function($q) use ($hod) {
                $q->whereNull('hod_id')
                  ->orWhere('hod_id', $hod->id);
            })
            ->orderBy('name')
            ->get();
        
        return view('admin.hods.edit', compact('hod', 'departments'));
    }

    public function update(Request $request, User $hod)
    {
        abort_unless($hod->hasRole('hod'), 404);
        
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => ['required','email', Rule::unique('users')->ignore($hod->id)],
            'phone'         => 'nullable|string|max:20',
            'gender'        => 'nullable|in:male,female,other',
            'dob'           => 'nullable|string|max:10',
            'address'       => 'nullable|string',
            'avatar'        => 'nullable|image|max:2048',
            'department_id' => 'nullable|exists:departments,id',
            'is_active'     => 'boolean',
            'password'      => 'nullable|string|min:8|confirmed',
        ]);

        // Validate department doesn't already have a different HOD
        if (!empty($data['department_id'])) {
            $department = Department::find($data['department_id']);
            if ($department && $department->hod_id && $department->hod_id != $hod->id) {
                return back()->withErrors(['department_id' => 'This department already has an HOD assigned.'])->withInput();
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
            'dob'       => NepaliDateHelper::toAD($data['dob'] ?? null),
            'address'   => $data['address'] ?? null,
            'is_active' => $data['is_active'] ?? $hod->is_active,
        ] + (isset($data['avatar']) ? ['avatar' => $data['avatar']] : []));

        if (!empty($data['password'])) {
            $hod->update(['password' => Hash::make($data['password'])]);
        }

        // Update department assignment
        $currentDepartment = $hod->hodDepartment;
        
        if ($currentDepartment && $currentDepartment->id != ($data['department_id'] ?? null)) {
            $currentDepartment->update(['hod_id' => null]);
        }
        
        if (!empty($data['department_id'])) {
            Department::find($data['department_id'])->update(['hod_id' => $hod->id]);
        }

        return redirect()->route('admin.hods.index')
            ->with('success', "HOD {$hod->name} updated successfully.");
    }

    public function destroy(User $hod)
    {
        abort_unless($hod->hasRole('hod'), 404);
        abort_if($hod->id === auth()->id(), 403, 'Cannot delete your own account.');
        
        // Remove HOD assignment from department
        if ($hod->hodDepartment) {
            $hod->hodDepartment->update(['hod_id' => null]);
        }
        
        if ($hod->avatar && Storage::disk('public')->exists($hod->avatar)) {
            Storage::disk('public')->delete($hod->avatar);
        }
        
        $hod->delete();
        
        return redirect()->route('admin.hods.index')
            ->with('success', 'HOD deleted successfully.');
    }
}
