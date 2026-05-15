<?php

namespace App\Modules\Teacher\Controllers\Api;

use App\Core\Base\BaseController;
use App\Modules\Teacher\Models\Teacher;
use App\Modules\User\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TeacherApiController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $teachers = Teacher::query()
            ->with(['user:id,name,email,avatar,phone', 'department:id,name'])
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->whereHas('user', fn ($uq) =>
                    $uq->where('name', 'like', "%{$term}%")
                       ->orWhere('email', 'like', "%{$term}%")
                );
            })
            ->when($request->department_id, fn ($q) => $q->where('department_id', $request->department_id))
            ->when($request->status,        fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->paginate(min((int) ($request->per_page ?? 20), 100))
            ->withQueryString();

        return $this->success($teachers);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'unique:users,email'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'password'      => ['required', 'string', 'min:8'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'employee_id'   => ['nullable', 'string', 'max:50', 'unique:teachers,employee_id'],
            'designation'   => ['nullable', 'string', 'max:255'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'joining_date'  => ['nullable', 'date'],
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name'      => $data['name'],
                'email'     => $data['email'],
                'phone'     => $data['phone'] ?? null,
                'password'  => Hash::make($data['password']),
                'role'      => 'teacher',
                'is_active' => true,
            ]);
            $user->assignRole('teacher');

            $teacher = Teacher::create([
                'user_id'       => $user->id,
                'department_id' => $data['department_id'],
                'employee_id'   => $data['employee_id'] ?? null,
                'designation'   => $data['designation'] ?? null,
                'qualification' => $data['qualification'] ?? null,
                'joining_date'  => $data['joining_date'] ?? null,
                'status'        => 'active',
            ]);

            DB::commit();
            return $this->created(
                $teacher->load(['user:id,name,email,avatar', 'department:id,name']),
                'Teacher created successfully.'
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return $this->error('Failed to create teacher.', 500);
        }
    }

    public function show(Teacher $teacher): JsonResponse
    {
        return $this->success(
            $teacher->load(['user:id,name,email,avatar,phone', 'department:id,name'])
        );
    }

    public function update(Request $request, Teacher $teacher): JsonResponse
    {
        $data = $request->validate([
            'name'          => ['sometimes', 'string', 'max:255'],
            'email'         => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($teacher->user_id)],
            'phone'         => ['nullable', 'string', 'max:20'],
            'department_id' => ['sometimes', 'integer', 'exists:departments,id'],
            'designation'   => ['nullable', 'string', 'max:255'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'joining_date'  => ['nullable', 'date'],
            'status'        => ['nullable', Rule::in(['active', 'inactive', 'on_leave'])],
        ]);

        DB::beginTransaction();
        try {
            $teacher->user->update(array_filter([
                'name'  => $data['name']  ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
            ]));

            $teacher->update(array_filter([
                'department_id' => $data['department_id'] ?? null,
                'designation'   => $data['designation']   ?? null,
                'qualification' => $data['qualification'] ?? null,
                'joining_date'  => $data['joining_date']  ?? null,
                'status'        => $data['status']        ?? null,
            ], fn ($v) => $v !== null));

            DB::commit();
            return $this->success(
                $teacher->fresh(['user:id,name,email,avatar', 'department:id,name']),
                'Teacher updated.'
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error('Update failed.', 500);
        }
    }

    public function destroy(Teacher $teacher): JsonResponse
    {
        $teacher->delete();
        return $this->noContent('Teacher deleted.');
    }
}
