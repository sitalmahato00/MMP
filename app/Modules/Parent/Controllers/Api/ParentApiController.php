<?php

namespace App\Modules\Parent\Controllers\Api;

use App\Core\Base\BaseController;
use App\Modules\Academic\Models\Program;
use App\Modules\Department\Models\Department;
use App\Modules\Parent\Models\ParentModel;
use App\Modules\Student\Models\Student;
use App\Modules\User\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ParentApiController extends BaseController
{
    public function stats(): JsonResponse
    {
        $totalParents = ParentModel::count();
        $linkedChildren = ParentModel::has('students')->count();
        $unlinkedParents = ParentModel::doesntHave('students')->count();
        $recentlyAdded = ParentModel::where('created_at', '>=', now()->subDays(30))->count();

        return $this->success([
            'total_parents'   => $totalParents,
            'linked_children'  => $linkedChildren,
            'unlinked_parents' => $unlinkedParents,
            'recently_added'   => $recentlyAdded,
        ]);
    }

    public function filters(): JsonResponse
    {
        return $this->success([
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'programs'    => Program::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $parents = ParentModel::with(['user', 'students.user', 'students.department', 'students.program'])
            ->withCount('students')
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%"));
            })
            ->latest()
            ->paginate(min((int) ($request->per_page ?? 20), 100))
            ->withQueryString();

        return $this->success($parents);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'email'               => 'required|email|unique:users,email',
            'phone'               => 'nullable|string|max:20',
            'address'             => 'nullable|string',
            'avatar'              => 'nullable|image|max:2048',
            'password'            => 'required|string|min:8',
            'occupation'          => 'nullable|string|max:100',
            'relation_to_student' => 'nullable|string|max:50',
            'student_ids'         => 'nullable|array',
            'student_ids.*'       => 'exists:students,id',
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        try {
            $parent = DB::transaction(function () use ($data) {
                $user = User::create([
                    'name'      => $data['name'],
                    'email'     => $data['email'],
                    'phone'     => $data['phone'] ?? null,
                    'address'   => $data['address'] ?? null,
                    'avatar'    => $data['avatar'] ?? null,
                    'password'  => Hash::make($data['password']),
                    'is_active' => true,
                ]);
                $user->assignRole('parent');

                $parent = ParentModel::create([
                    'user_id'             => $user->id,
                    'occupation'          => $data['occupation'] ?? null,
                    'relation_to_student' => $data['relation_to_student'] ?? 'parent',
                ]);

                if (!empty($data['student_ids'])) {
                    $parent->students()->sync($data['student_ids']);
                }

                return $parent;
            });

            return $this->created(
                $parent->load(['user', 'students.user', 'students.department', 'students.program']),
                'Parent created successfully.'
            );
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to create parent.', 500);
        }
    }

    public function show(ParentModel $parent): JsonResponse
    {
        $parent->load([
            'user',
            'students.user',
            'students.department',
            'students.program',
            'students.attendances',
            'students.marks',
        ]);
        return $this->success($parent);
    }

    public function update(Request $request, ParentModel $parent): JsonResponse
    {
        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'email'               => ['required', 'email', Rule::unique('users', 'email')->ignore($parent->user_id)],
            'phone'               => 'nullable|string|max:20',
            'address'             => 'nullable|string',
            'avatar'              => 'nullable|image|max:2048',
            'occupation'          => 'nullable|string|max:100',
            'relation_to_student' => 'nullable|string|max:50',
            'student_ids'         => 'nullable|array',
            'student_ids.*'       => 'exists:students,id',
            'is_active'           => 'nullable|boolean',
        ]);

        if ($request->hasFile('avatar')) {
            if ($parent->user?->avatar) {
                Storage::disk('public')->delete($parent->user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        try {
            DB::transaction(function () use ($data, $parent, $request) {
                $parent->user->update([
                    'name'      => $data['name'],
                    'email'     => $data['email'],
                    'phone'     => $data['phone'] ?? null,
                    'address'   => $data['address'] ?? null,
                    'is_active' => $data['is_active'] ?? $parent->user->is_active,
                    ...($request->hasFile('avatar') ? ['avatar' => $data['avatar']] : []),
                ]);

                $parent->update([
                    'occupation'          => $data['occupation'] ?? null,
                    'relation_to_student' => $data['relation_to_student'] ?? 'parent',
                ]);

                $parent->students()->sync($data['student_ids'] ?? []);
            });

            return $this->success(
                $parent->fresh(['user', 'students.user', 'students.department', 'students.program']),
                'Parent updated.'
            );
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to update parent.', 500);
        }
    }

    public function destroy(ParentModel $parent): JsonResponse
    {
        DB::transaction(function () use ($parent) {
            $parent->students()->detach();
            if ($parent->user?->avatar) {
                Storage::disk('public')->delete($parent->user->avatar);
            }
            $parent->user?->forceDelete();
            $parent->forceDelete();
        });

        return $this->success(['message' => 'Parent deleted.']);
    }
}
