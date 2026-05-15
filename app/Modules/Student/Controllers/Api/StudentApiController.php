<?php

namespace App\Modules\Student\Controllers\Api;

use App\Core\Base\BaseController;
use App\Modules\Academic\Models\AcademicSession;
use App\Modules\Academic\Models\Program;
use App\Modules\Department\Models\Department;
use App\Modules\Student\Models\Student;
use App\Modules\Student\Requests\StoreStudentRequest;
use App\Modules\Student\Requests\UpdateStudentRequest;
use App\Modules\User\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * StudentApiController
 *
 * RESTful API controller for Student resource.
 * Extends BaseController — uses ApiResponse trait for uniform JSON output.
 * All Blade responses have been removed. Pure JSON only.
 */
class StudentApiController extends BaseController
{
    /**
     * GET /api/v1/students
     * List students with pagination, search, and filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Student::query()
            ->with([
                'user:id,name,email,avatar,phone',
                'program:id,name',
                'department:id,name',
                'academicSession:id,name,name_bs',
            ])
            ->when($request->search, function ($q) use ($request) {
                $term = trim((string) $request->search);
                $q->where(function ($inner) use ($term) {
                    $inner->where('student_no', 'like', "%{$term}%")
                          ->orWhereHas('user', fn ($uq) =>
                              $uq->where('name', 'like', "%{$term}%")
                                 ->orWhere('email', 'like', "%{$term}%")
                          );
                });
            })
            ->when($request->department_id,       fn ($q) => $q->where('department_id', $request->department_id))
            ->when($request->program_id,          fn ($q) => $q->where('program_id', $request->program_id))
            ->when($request->academic_session_id, fn ($q) => $q->where('academic_session_id', $request->academic_session_id))
            ->when($request->semester,            fn ($q) => $q->where('current_semester', $request->semester))
            ->when($request->status,              fn ($q) => $q->where('status', $request->status));

        $perPage  = min((int) ($request->per_page ?? 20), 100);
        $students = $query->latest('id')->paginate($perPage)->withQueryString();

        return $this->success($students, 'Students retrieved successfully.');
    }

    /**
     * POST /api/v1/students
     * Create a new student with linked user account.
     */
    public function store(StoreStudentRequest $request): JsonResponse
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'phone'    => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'role'     => 'student',
                'is_active' => true,
            ]);

            $user->assignRole('student');

            $student = Student::create([
                'user_id'              => $user->id,
                'department_id'        => $data['department_id'],
                'program_id'           => $data['program_id'],
                'academic_session_id'  => $data['academic_session_id'],
                'student_no'           => $data['student_no'],
                'registration_number'  => $data['registration_number'] ?? null,
                'current_semester'     => $data['current_semester'],
                'section'              => $data['section'] ?? null,
                'batch'                => $data['batch'] ?? null,
                'admission_date'       => $data['admission_date'] ?? null,
                'guardian_name'        => $data['guardian_name'] ?? null,
                'guardian_phone'       => $data['guardian_phone'] ?? null,
                'blood_group'          => $data['blood_group'] ?? null,
                'status'               => 'active',
            ]);

            DB::commit();

            return $this->created(
                $student->load(['user:id,name,email,avatar', 'program:id,name', 'department:id,name']),
                'Student created successfully.'
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return $this->error('Failed to create student. Please try again.', 500);
        }
    }

    /**
     * GET /api/v1/students/{student}
     * Show a single student with full details.
     */
    public function show(Student $student): JsonResponse
    {
        $student->load([
            'user:id,name,email,avatar,phone',
            'program:id,name,code',
            'department:id,name',
            'academicSession:id,name,name_bs',
            'parents:id,user_id',
            'parents.user:id,name,email,phone',
        ]);

        return $this->success($student);
    }

    /**
     * PUT /api/v1/students/{student}
     * Update a student's profile.
     */
    public function update(UpdateStudentRequest $request, Student $student): JsonResponse
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            // Update linked user fields if provided
            $userFields = array_filter([
                'name'  => $data['name']  ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
            ]);

            if (!empty($userFields)) {
                $student->user->update($userFields);
            }

            if (isset($data['password'])) {
                $student->user->update(['password' => Hash::make($data['password'])]);
            }

            // Update avatar if present
            if ($request->hasFile('avatar')) {
                $path = $request->file('avatar')->store('avatars/students', 'public');
                $student->user->update(['avatar' => Storage::url($path)]);
            }

            $student->update(array_filter([
                'department_id'       => $data['department_id']       ?? null,
                'program_id'          => $data['program_id']          ?? null,
                'academic_session_id' => $data['academic_session_id'] ?? null,
                'student_no'          => $data['student_no']          ?? null,
                'registration_number' => $data['registration_number'] ?? null,
                'current_semester'    => $data['current_semester']    ?? null,
                'section'             => $data['section']             ?? null,
                'batch'               => $data['batch']               ?? null,
                'admission_date'      => $data['admission_date']      ?? null,
                'guardian_name'       => $data['guardian_name']       ?? null,
                'guardian_phone'      => $data['guardian_phone']      ?? null,
                'blood_group'         => $data['blood_group']         ?? null,
                'status'              => $data['status']              ?? null,
            ], fn ($v) => $v !== null));

            DB::commit();

            return $this->success(
                $student->fresh(['user:id,name,email,avatar', 'program:id,name', 'department:id,name']),
                'Student updated successfully.'
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return $this->error('Failed to update student.', 500);
        }
    }

    /**
     * DELETE /api/v1/students/{student}
     * Soft-delete a student.
     */
    public function destroy(Student $student): JsonResponse
    {
        $student->delete();
        return $this->noContent('Student deleted successfully.');
    }

    /**
     * POST /api/v1/students/{student}/restore
     * Restore a soft-deleted student.
     */
    public function restore(int $id): JsonResponse
    {
        $student = Student::withTrashed()->findOrFail($id);
        $student->restore();
        return $this->success($student, 'Student restored successfully.');
    }

    /**
     * GET /api/v1/students/export
     * Export students as CSV.
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $students = Student::query()
            ->with(['user:id,name,email', 'program:id,name', 'department:id,name', 'academicSession:id,name'])
            ->when($request->department_id, fn ($q) => $q->where('department_id', $request->department_id))
            ->when($request->status,        fn ($q) => $q->where('status', $request->status))
            ->latest('id')
            ->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="students.csv"',
        ];

        return response()->streamDownload(function () use ($students) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Student No', 'Name', 'Email', 'Program', 'Department', 'Semester', 'Status']);
            foreach ($students as $s) {
                fputcsv($handle, [
                    $s->student_no,
                    $s->user->name,
                    $s->user->email,
                    $s->program->name  ?? '',
                    $s->department->name ?? '',
                    $s->current_semester,
                    $s->status,
                ]);
            }
            fclose($handle);
        }, 'students.csv', $headers);
    }
}
