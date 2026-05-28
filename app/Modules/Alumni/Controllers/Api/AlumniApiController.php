<?php

namespace App\Modules\Alumni\Controllers\Api;

use App\Core\Base\BaseController;
use App\Modules\Alumni\Models\Alumni;
use App\Modules\User\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AlumniApiController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $alumni = Alumni::with(['user', 'department:id,name', 'program:id,name', 'projects'])
            ->when($request->search, function ($q) use ($request) {
                $s = $request->search;
                $q->where(function ($qq) use ($s) {
                    $qq->whereHas('user', fn($u) => $u->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"))
                       ->orWhere('id', $s);
                });
            })
            ->when($request->filled('department_id'), fn($q) => $q->where('department_id', $request->department_id))
            ->when($request->filled('program_id'), fn($q) => $q->where('program_id', $request->program_id))
            ->when($request->filled('graduation_year'), fn($q) => $q->where('graduation_year', $request->graduation_year))
            ->when($request->filled('employment_status'), fn($q) => $q->where('employment_status', $request->employment_status))
            ->when($request->filled('is_featured'), fn($q) => $q->where('is_featured', $request->is_featured === '1'))
            ->latest()
            ->paginate(min((int) ($request->per_page ?? 20), 100))
            ->withQueryString();

        return $this->success($alumni);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:users,email',
            'phone'             => 'nullable|string|max:20',
            'address'           => 'nullable|string|max:500',
            'avatar'            => 'nullable|image|max:2048',
            'password'          => 'required|string|min:8',
            'department_id'     => 'required|exists:departments,id',
            'program_id'        => 'required|exists:programs,id',
            'graduation_year'   => 'required|string|max:4',
            'admission_year'    => 'nullable|string|max:4',
            'roll_number'       => 'nullable|string|max:50',
            'current_job'       => 'nullable|string|max:255',
            'company_name'      => 'nullable|string|max:255',
            'work_location'     => 'nullable|string|max:255',
            'employment_status' => 'nullable|string',
            'bio'               => 'nullable|string|max:2000',
            'linkedin_url'      => 'nullable|url|max:255',
            'github_url'        => 'nullable|url|max:255',
            'portfolio_url'     => 'nullable|url|max:255',
            'achievements'      => 'nullable|string|max:2000',
            'is_featured'       => 'boolean',
        ]);

        try {
            $alumni = DB::transaction(function () use ($data, $request) {
                $avatarPath = $request->hasFile('avatar')
                    ? $request->file('avatar')->store('avatars', 'public')
                    : null;

                $user = User::create([
                    'name'      => $data['name'],
                    'email'     => $data['email'],
                    'phone'     => $data['phone'] ?? null,
                    'address'   => $data['address'] ?? null,
                    'avatar'    => $avatarPath,
                    'password'  => Hash::make($data['password']),
                    'is_active' => true,
                ]);
                $user->assignRole('alumni');

                return Alumni::create([
                    'user_id'           => $user->id,
                    'department_id'     => $data['department_id'],
                    'program_id'        => $data['program_id'],
                    'graduation_year'   => $data['graduation_year'],
                    'admission_year'    => $data['admission_year'] ?? null,
                    'roll_number'       => $data['roll_number'] ?? null,
                    'current_job'       => $data['current_job'] ?? null,
                    'company_name'      => $data['company_name'] ?? null,
                    'work_location'     => $data['work_location'] ?? null,
                    'employment_status' => $data['employment_status'] ?? 'unknown',
                    'bio'               => $data['bio'] ?? null,
                    'linkedin_url'      => $data['linkedin_url'] ?? null,
                    'github_url'        => $data['github_url'] ?? null,
                    'portfolio_url'     => $data['portfolio_url'] ?? null,
                    'achievements'      => $data['achievements'] ?? null,
                    'is_featured'       => $data['is_featured'] ?? false,
                    'is_verified'       => true,
                    'visibility'        => 'public',
                ]);
            });

            return $this->created(
                $alumni->load(['user:id,name,email,avatar', 'department:id,name', 'program:id,name']),
                'Alumni created successfully.'
            );
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to create alumni.', 500);
        }
    }

    public function show(Alumni $alumnus): JsonResponse
    {
        $alumnus->load(['user', 'department', 'program', 'student', 'projects', 'achievementRecords', 'employmentHistory']);
        return $this->success($alumnus);
    }

    public function update(Request $request, Alumni $alumnus): JsonResponse
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => ['required', 'email', Rule::unique('users', 'email')->ignore($alumnus->user_id)],
            'phone'             => 'nullable|string|max:20',
            'address'           => 'nullable|string|max:500',
            'avatar'            => 'nullable|image|max:2048',
            'department_id'     => 'required|exists:departments,id',
            'program_id'        => 'required|exists:programs,id',
            'graduation_year'   => 'required|string|max:4',
            'admission_year'    => 'nullable|string|max:4',
            'roll_number'       => 'nullable|string|max:50',
            'current_job'       => 'nullable|string|max:255',
            'company_name'      => 'nullable|string|max:255',
            'work_location'     => 'nullable|string|max:255',
            'employment_status' => 'nullable|string',
            'bio'               => 'nullable|string|max:2000',
            'linkedin_url'      => 'nullable|url|max:255',
            'github_url'        => 'nullable|url|max:255',
            'portfolio_url'     => 'nullable|url|max:255',
            'achievements'      => 'nullable|string|max:2000',
            'is_featured'       => 'boolean',
            'is_active'         => 'boolean',
            'visibility'        => 'nullable|string|in:public,private',
        ]);

        try {
            DB::transaction(function () use ($data, $request, $alumnus) {
                $user = $alumnus->user;
                if ($request->hasFile('avatar')) {
                    if ($user->avatar) Storage::disk('public')->delete($user->avatar);
                    $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
                }
                $user->update([
                    'name'      => $data['name'],
                    'email'     => $data['email'],
                    'phone'     => $data['phone'] ?? null,
                    'address'   => $data['address'] ?? null,
                    'avatar'    => $data['avatar_path'] ?? $user->avatar,
                    'is_active' => $data['is_active'] ?? $user->is_active,
                ]);
                $alumnus->update([
                    'department_id'     => $data['department_id'],
                    'program_id'        => $data['program_id'],
                    'graduation_year'   => $data['graduation_year'],
                    'admission_year'    => $data['admission_year'] ?? null,
                    'roll_number'       => $data['roll_number'] ?? null,
                    'current_job'       => $data['current_job'] ?? null,
                    'company_name'      => $data['company_name'] ?? null,
                    'work_location'     => $data['work_location'] ?? null,
                    'employment_status' => $data['employment_status'] ?? 'unknown',
                    'bio'               => $data['bio'] ?? null,
                    'linkedin_url'      => $data['linkedin_url'] ?? null,
                    'github_url'        => $data['github_url'] ?? null,
                    'portfolio_url'     => $data['portfolio_url'] ?? null,
                    'achievements'      => $data['achievements'] ?? null,
                    'is_featured'       => $data['is_featured'] ?? false,
                    'visibility'        => $data['visibility'] ?? $alumnus->visibility,
                ]);
            });

            return $this->success(
                $alumnus->fresh(['user:id,name,email,avatar', 'department:id,name', 'program:id,name']),
                'Alumni updated.'
            );
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to update alumni.', 500);
        }
    }

    public function destroy(Alumni $alumnus): JsonResponse
    {
        DB::transaction(function () use ($alumnus) {
            $user = $alumnus->user;
            if ($user?->avatar) Storage::disk('public')->delete($user->avatar);
            if ($alumnus->cv_path) Storage::disk('public')->delete($alumnus->cv_path);
            $alumnus->projects()->delete();
            $alumnus->achievementRecords()->delete();
            $alumnus->employmentHistory()->delete();
            $alumnus->forceDelete();
            $user?->forceDelete();
        });

        return $this->success(['message' => 'Alumni deleted.']);
    }

    public function toggleFeatured(Alumni $alumnus): JsonResponse
    {
        $alumnus->update(['is_featured' => !$alumnus->is_featured]);
        return $this->success($alumnus, $alumnus->is_featured ? 'Featured' : 'Unfeatured');
    }
}
