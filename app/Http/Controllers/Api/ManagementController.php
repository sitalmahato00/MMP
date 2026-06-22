<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\ParentModel;
use App\Models\User;
use App\Models\Department;
use App\Models\Program;
use App\Models\AcademicSession;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ManagementController extends Controller
{
    // ═══════════════════════════════════════════════════════════════════════════
    // TEACHER CRUD
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * List all teachers
     */
    public function teachersIndex(Request $request): JsonResponse
    {
        try {
            $query = Teacher::with(['user:id,name,email,phone,avatar,is_active', 'department:id,name'])
                ->withCount('subjects');

            if ($request->search) {
                $term = $request->search;
                $query->where(function ($q) use ($term) {
                    $q->where('employee_id', 'like', "%{$term}%")
                        ->orWhere('designation', 'like', "%{$term}%")
                        ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%"));
                });
            }
            if ($request->department_id) {
                $query->where('department_id', $request->department_id);
            }
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            $teachers = $query->latest()->paginate($request->per_page ?? 20);

            return response()->json([
                'success' => true,
                'data' => $teachers->map(fn($t) => [
                    'id' => $t->id,
                    'user_id' => $t->user_id,
                    'name' => $t->user?->name,
                    'email' => $t->user?->email,
                    'phone' => $t->user?->phone,
                    'avatar_url' => $t->user?->avatar_url,
                    'employee_id' => $t->employee_id,
                    'designation' => $t->designation,
                    'department' => $t->department?->name,
                    'department_id' => $t->department_id,
                    'qualification' => $t->qualification,
                    'specialization' => $t->specialization,
                    'employment_type' => $t->employment_type,
                    'is_active' => $t->is_active,
                    'subjects_count' => $t->subjects_count,
                    'join_date' => $t->join_date?->toDateString(),
                ]),
                'meta' => [
                    'current_page' => $teachers->currentPage(),
                    'last_page' => $teachers->lastPage(),
                    'total' => $teachers->total(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch teachers: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single teacher
     */
    public function teachersShow($id): JsonResponse
    {
        try {
            $teacher = Teacher::with(['user', 'department', 'subjects'])
                ->withCount('subjects')
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $teacher->id,
                    'user_id' => $teacher->user_id,
                    'name' => $teacher->user?->name,
                    'email' => $teacher->user?->email,
                    'phone' => $teacher->user?->phone,
                    'avatar_url' => $teacher->user?->avatar_url,
                    'address' => $teacher->user?->address,
                    'gender' => $teacher->user?->gender,
                    'employee_id' => $teacher->employee_id,
                    'designation' => $teacher->designation,
                    'department_id' => $teacher->department_id,
                    'department' => $teacher->department?->name,
                    'qualification' => $teacher->qualification,
                    'specialization' => $teacher->specialization,
                    'employment_type' => $teacher->employment_type,
                    'is_active' => $teacher->is_active,
                    'join_date' => $teacher->join_date?->toDateString(),
                    'subjects' => $teacher->subjects->map(fn($s) => [
                        'id' => $s->id,
                        'name' => $s->name,
                        'code' => $s->code,
                    ]),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Create teacher
     */
    public function teachersStore(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'nullable|string|max:20',
                'password' => 'nullable|string|min:8',
                'address' => 'nullable|string',
                'gender' => 'nullable|in:male,female,other',
                'employee_id' => 'required|string|max:50|unique:teachers,employee_id',
                'department_id' => 'required|exists:departments,id',
                'designation' => 'required|string|max:100',
                'qualification' => 'nullable|string|max:255',
                'specialization' => 'nullable|string|max:255',
                'employment_type' => 'nullable|string|max:50',
                'join_date' => 'nullable|date',
                'is_active' => 'boolean',
            ]);

            $user = null;
            $teacher = null;

            DB::transaction(function () use ($data, $request, &$user, &$teacher) {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'password' => Hash::make($data['password'] ?? Str::random(40)),
                    'is_active' => $data['is_active'] ?? true,
                ]);
                $user->assignRole('teacher');

                $teacherData = [
                'user_id' => $user->id,
                'department_id' => $data['department_id'],
                'employee_id' => $data['employee_id'],
                'designation' => $data['designation'],
                'is_active' => $data['is_active'] ?? true,
            ];
            if (isset($data['qualification'])) {
                $teacherData['qualification'] = $data['qualification'];
            }
            if (isset($data['specialization'])) {
                $teacherData['specialization'] = $data['specialization'];
            }
            if (isset($data['employment_type'])) {
                $teacherData['employment_type'] = $data['employment_type'];
            }
            if (isset($data['join_date'])) {
                $teacherData['join_date'] = $data['join_date'];
            }
            $teacher = Teacher::create($teacherData);
            });

            return response()->json([
                'success' => true,
                'message' => 'Teacher created successfully',
                'data' => ['id' => $teacher->id, 'user_id' => $user->id]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create teacher: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update teacher
     */
    public function teachersUpdate(Request $request, $id): JsonResponse
    {
        try {
            $teacher = Teacher::findOrFail($id);
            $user = $teacher->user;

            $data = $request->validate([
                'name' => 'sometimes|string|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'gender' => 'nullable|in:male,female,other',
                'password' => 'nullable|string|min:8',
                'department_id' => 'sometimes|exists:departments,id',
                'designation' => 'sometimes|string|max:100',
                'qualification' => 'nullable|string|max:255',
                'specialization' => 'nullable|string|max:255',
                'employment_type' => 'nullable|string|max:50',
                'join_date' => 'nullable|date',
                'is_active' => 'boolean',
            ]);

            DB::transaction(function () use ($data, $request, $user, $teacher) {
                $userFields = ['name', 'phone', 'address', 'gender'];
                $userDirty = false;
                foreach ($userFields as $field) {
                    if (isset($data[$field])) {
                        $user->$field = $data[$field];
                        $userDirty = true;
                    }
                }
                if ($userDirty) {
                    $user->save();
                }

                $teacherFields = ['department_id', 'designation', 'qualification', 'specialization', 'employment_type', 'join_date', 'is_active'];
                $teacherDirty = false;
                foreach ($teacherFields as $field) {
                    if (isset($data[$field])) {
                        $teacher->$field = $data[$field];
                        $teacherDirty = true;
                    }
                }
                if ($teacherDirty) {
                    $teacher->save();
                }
            });

            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
                $user->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Teacher updated successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update teacher: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete teacher
     */
    public function teachersDestroy($id): JsonResponse
    {
        try {
            $teacher = Teacher::findOrFail($id);

            DB::transaction(function () use ($teacher) {
                $teacher->subjects()->detach();
                $user = $teacher->user;
                $teacher->delete();
                $user->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Teacher deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete teacher: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // STUDENT CRUD
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * List all students
     */
    public function studentsIndex(Request $request): JsonResponse
    {
        try {
            $query = Student::with([
                'user:id,name,email,phone,avatar,is_active',
                'program:id,name',
                'department:id,name',
                'academicSession:id,name',
            ])->withCount('parents');

            if ($request->search) {
                $term = $request->search;
                $query->where(function ($q) use ($term) {
                    $q->where('student_no', 'like', "%{$term}%")
                        ->orWhere('registration_number', 'like', "%{$term}%")
                        ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%"));
                });
            }
            if ($request->department_id) {
                $query->where('department_id', $request->department_id);
            }
            if ($request->program_id) {
                $query->where('program_id', $request->program_id);
            }
            if ($request->semester) {
                $query->where('current_semester', $request->semester);
            }
            if ($request->status) {
                $query->where('status', $request->status);
            }

            $students = $query->latest()->paginate($request->per_page ?? 20);

            return response()->json([
                'success' => true,
                'data' => $students->map(fn($s) => [
                    'id' => $s->id,
                    'user_id' => $s->user_id,
                    'name' => $s->user?->name,
                    'email' => $s->user?->email,
                    'phone' => $s->user?->phone,
                    'avatar_url' => $s->user?->avatar_url,
                    'student_no' => $s->student_no,
                    'registration_number' => $s->registration_number,
                    'program' => $s->program?->name,
                    'program_id' => $s->program_id,
                    'department' => $s->department?->name,
                    'department_id' => $s->department_id,
                    'current_semester' => $s->current_semester,
                    'section' => $s->section,
                    'batch' => $s->batch,
                    'status' => $s->status,
                    'academic_session' => $s->academicSession?->name,
                    'parents_count' => $s->parents_count,
                ]),
                'meta' => [
                    'current_page' => $students->currentPage(),
                    'last_page' => $students->lastPage(),
                    'total' => $students->total(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch students: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single student
     */
    public function studentsShow($id): JsonResponse
    {
        try {
            $student = Student::with(['user', 'program', 'department', 'academicSession', 'parents.user'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $student->id,
                    'user_id' => $student->user_id,
                    'name' => $student->user?->name,
                    'email' => $student->user?->email,
                    'phone' => $student->user?->phone,
                    'avatar_url' => $student->user?->avatar_url,
                    'address' => $student->user?->address,
                    'gender' => $student->user?->gender,
                    'dob' => $student->user?->dob?->toDateString(),
                    'student_no' => $student->student_no,
                    'registration_number' => $student->registration_number,
                    'program_id' => $student->program_id,
                    'program' => $student->program?->name,
                    'department_id' => $student->department_id,
                    'department' => $student->department?->name,
                    'current_semester' => $student->current_semester,
                    'section' => $student->section,
                    'batch' => $student->batch,
                    'admission_date' => $student->admission_date?->toDateString(),
                    'status' => $student->status,
                    'blood_group' => $student->blood_group,
                    'guardian_name' => $student->guardian_name,
                    'guardian_phone' => $student->guardian_phone,
                    'academic_session' => $student->academicSession?->name,
                    'academic_session_id' => $student->academic_session_id,
                    'parents' => $student->parents->map(fn($p) => [
                        'id' => $p->id,
                        'name' => $p->user?->name,
                        'email' => $p->user?->email,
                        'phone' => $p->user?->phone,
                        'relation' => $p->relation_to_student,
                    ]),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Create student
     */
    public function studentsStore(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'nullable|string|max:20',
                'password' => 'nullable|string|min:8',
                'address' => 'nullable|string',
                'gender' => 'nullable|in:male,female,other',
                'dob' => 'nullable|date',
                'student_no' => 'required|string|max:50|unique:students,student_no',
                'registration_number' => 'nullable|string|max:50',
                'program_id' => 'required|exists:programs,id',
                'current_semester' => 'required|integer|min:1',
                'section' => 'nullable|string|max:10',
                'batch' => 'nullable|string|max:20',
                'admission_date' => 'nullable|date',
                'status' => 'nullable|in:active,inactive,graduated,suspended',
                'blood_group' => 'nullable|string|max:10',
                'guardian_name' => 'nullable|string|max:255',
                'guardian_phone' => 'nullable|string|max:20',
                'parent_ids' => 'nullable|array',
                'parent_ids.*' => 'exists:parents,id',
            ]);

            $user = null;
            $student = null;

            DB::transaction(function () use ($data, &$user, &$student) {
                $program = Program::with('department')->findOrFail($data['program_id']);
                $session = AcademicSession::current();

                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'dob' => $data['dob'] ?? null,
                    'password' => Hash::make($data['password'] ?? Str::random(40)),
                    'is_active' => true,
                ]);
                $user->assignRole('student');

                $student = Student::create([
                    'user_id' => $user->id,
                    'department_id' => $program->department_id,
                    'program_id' => $data['program_id'],
                    'academic_session_id' => $session?->id,
                    'student_no' => $data['student_no'],
                    'registration_number' => $data['registration_number'] ?? null,
                    'current_semester' => $data['current_semester'],
                    'section' => $data['section'] ?? null,
                    'batch' => $data['batch'] ?? null,
                    'admission_date' => $data['admission_date'] ?? null,
                    'status' => $data['status'] ?? 'active',
                    'blood_group' => $data['blood_group'] ?? null,
                    'guardian_name' => $data['guardian_name'] ?? null,
                    'guardian_phone' => $data['guardian_phone'] ?? null,
                ]);

                if (!empty($data['parent_ids'])) {
                    $student->parents()->sync($data['parent_ids']);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Student created successfully',
                'data' => ['id' => $student->id, 'user_id' => $user->id]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create student: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update student
     */
    public function studentsUpdate(Request $request, $id): JsonResponse
    {
        try {
            $student = Student::findOrFail($id);
            $user = $student->user;

            $data = $request->validate([
                'name' => 'sometimes|string|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'gender' => 'nullable|in:male,female,other',
                'dob' => 'nullable|date',
                'password' => 'nullable|string|min:8',
                'current_semester' => 'sometimes|integer|min:1',
                'section' => 'nullable|string|max:10',
                'batch' => 'nullable|string|max:20',
                'status' => 'nullable|in:active,inactive,graduated,suspended',
                'blood_group' => 'nullable|string|max:10',
                'guardian_name' => 'nullable|string|max:255',
                'guardian_phone' => 'nullable|string|max:20',
                'parent_ids' => 'nullable|array',
                'parent_ids.*' => 'exists:parents,id',
            ]);

            DB::transaction(function () use ($data, $user, $student) {
                $userFields = ['name', 'phone', 'address', 'gender', 'dob'];
                $userDirty = false;
                foreach ($userFields as $field) {
                    if (isset($data[$field])) {
                        $user->$field = $data[$field];
                        $userDirty = true;
                    }
                }
                if ($userDirty) {
                    $user->save();
                }

                $studentFields = ['current_semester', 'section', 'batch', 'status', 'blood_group', 'guardian_name', 'guardian_phone'];
                $studentDirty = false;
                foreach ($studentFields as $field) {
                    if (isset($data[$field])) {
                        $student->$field = $data[$field];
                        $studentDirty = true;
                    }
                }
                if ($studentDirty) {
                    $student->save();
                }

                if (isset($data['parent_ids'])) {
                    $student->parents()->sync($data['parent_ids']);
                }
            });

            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
                $user->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update student: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete student
     */
    public function studentsDestroy($id): JsonResponse
    {
        try {
            $student = Student::findOrFail($id);

            DB::transaction(function () use ($student) {
                $student->parents()->detach();
                $user = $student->user;
                $student->delete();
                $user->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // PARENT CRUD
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * List all parents
     */
    public function parentsIndex(Request $request): JsonResponse
    {
        try {
            $query = ParentModel::with(['user:id,name,email,phone,avatar,is_active', 'students.user'])
                ->withCount('students');

            if ($request->search) {
                $term = $request->search;
                $query->whereHas('user', fn($u) => $u->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%"));
            }
            if ($request->has('linked')) {
                if ($request->boolean('linked')) {
                    $query->has('students');
                } else {
                    $query->doesntHave('students');
                }
            }

            $parents = $query->latest()->paginate($request->per_page ?? 20);

            return response()->json([
                'success' => true,
                'data' => $parents->map(fn($p) => [
                    'id' => $p->id,
                    'user_id' => $p->user_id,
                    'name' => $p->user?->name,
                    'email' => $p->user?->email,
                    'phone' => $p->user?->phone,
                    'avatar_url' => $p->user?->avatar_url,
                    'occupation' => $p->occupation,
                    'relation_to_student' => $p->relation_to_student,
                    'is_active' => $p->user?->is_active,
                    'children_count' => $p->students_count,
                    'children' => $p->students->map(fn($s) => [
                        'id' => $s->id,
                        'name' => $s->user?->name,
                        'program' => $s->program?->name,
                        'student_no' => $s->student_no,
                    ]),
                ]),
                'meta' => [
                    'current_page' => $parents->currentPage(),
                    'last_page' => $parents->lastPage(),
                    'total' => $parents->total(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch parents: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single parent
     */
    public function parentsShow($id): JsonResponse
    {
        try {
            $parent = ParentModel::with(['user', 'students.user', 'students.program', 'students.department'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $parent->id,
                    'user_id' => $parent->user_id,
                    'name' => $parent->user?->name,
                    'email' => $parent->user?->email,
                    'phone' => $parent->user?->phone,
                    'avatar_url' => $parent->user?->avatar_url,
                    'address' => $parent->user?->address,
                    'occupation' => $parent->occupation,
                    'relation_to_student' => $parent->relation_to_student,
                    'is_active' => $parent->user?->is_active,
                    'children' => $parent->students->map(fn($s) => [
                        'id' => $s->id,
                        'name' => $s->user?->name,
                        'email' => $s->user?->email,
                        'student_no' => $s->student_no,
                        'program' => $s->program?->name,
                        'department' => $s->department?->name,
                        'current_semester' => $s->current_semester,
                    ]),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Parent not found: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Create parent
     */
    public function parentsStore(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'nullable|string|max:20',
                'password' => 'nullable|string|min:8',
                'address' => 'nullable|string',
                'occupation' => 'nullable|string|max:100',
                'relation_to_student' => 'nullable|string|max:50',
                'student_ids' => 'nullable|array',
                'student_ids.*' => 'exists:students,id',
            ]);

            $user = null;
            $parent = null;

            DB::transaction(function () use ($data, &$user, &$parent) {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'password' => Hash::make($data['password'] ?? Str::random(40)),
                    'is_active' => true,
                ]);
                $user->assignRole('parent');

                $parent = ParentModel::create([
                    'user_id' => $user->id,
                    'occupation' => $data['occupation'] ?? null,
                    'relation_to_student' => $data['relation_to_student'] ?? 'parent',
                ]);

                if (!empty($data['student_ids'])) {
                    $parent->students()->sync($data['student_ids']);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Parent created successfully',
                'data' => ['id' => $parent->id, 'user_id' => $user->id]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create parent: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update parent
     */
    public function parentsUpdate(Request $request, $id): JsonResponse
    {
        try {
            $parent = ParentModel::findOrFail($id);
            $user = $parent->user;

            $data = $request->validate([
                'name' => 'sometimes|string|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'password' => 'nullable|string|min:8',
                'occupation' => 'nullable|string|max:100',
                'relation_to_student' => 'nullable|string|max:50',
                'student_ids' => 'nullable|array',
                'student_ids.*' => 'exists:students,id',
            ]);

            DB::transaction(function () use ($data, $user, $parent) {
                $userFields = ['name', 'phone', 'address'];
                $userDirty = false;
                foreach ($userFields as $field) {
                    if (isset($data[$field])) {
                        $user->$field = $data[$field];
                        $userDirty = true;
                    }
                }
                if ($userDirty) {
                    $user->save();
                }

                $parentFields = ['occupation', 'relation_to_student'];
                $parentDirty = false;
                foreach ($parentFields as $field) {
                    if (isset($data[$field])) {
                        $parent->$field = $data[$field];
                        $parentDirty = true;
                    }
                }
                if ($parentDirty) {
                    $parent->save();
                }

                if (isset($data['student_ids'])) {
                    $parent->students()->sync($data['student_ids']);
                }
            });

            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
                $user->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Parent updated successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update parent: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete parent
     */
    public function parentsDestroy($id): JsonResponse
    {
        try {
            $parent = ParentModel::findOrFail($id);

            DB::transaction(function () use ($parent) {
                $parent->students()->detach();
                $user = $parent->user;
                $parent->delete();
                $user->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Parent deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete parent: ' . $e->getMessage(),
            ], 500);
        }
    }
}
