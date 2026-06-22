<?php

namespace Tests\Feature\Api;

use App\Models\AcademicSession;
use App\Models\Department;
use App\Models\ParentModel;
use App\Models\Program;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CrudEndpointsTest extends TestCase
{
    use RefreshDatabase;

    private array $tokens = [];
    private Department $department;
    private Program $program;
    private AcademicSession $session;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'principal', 'guard_name' => 'web']);
        Role::create(['name' => 'hod', 'guard_name' => 'web']);
        Role::create(['name' => 'teacher', 'guard_name' => 'web']);
        Role::create(['name' => 'student', 'guard_name' => 'web']);
        Role::create(['name' => 'parent', 'guard_name' => 'web']);
        Role::create(['name' => 'alumni', 'guard_name' => 'web']);
        Role::create(['name' => 'admin', 'guard_name' => 'web']);

        $this->department = Department::factory()->create();
        $this->program = Program::factory()->create(['department_id' => $this->department->id]);
        $this->session = AcademicSession::create([
            'name' => '2080/81',
            'is_active' => true,
            'status' => 'active',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);
    }

    private function createUserWithRole(string $role, array $overrides = []): User
    {
        $user = User::factory()->create(array_merge([
            'password' => Hash::make('password'),
            'is_active' => true,
        ], $overrides));

        $user->assignRole($role);
        return $user;
    }

    private function loginAs(User $user): string
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        return $response->json('data.token');
    }

    private function authHeaders(string $token): array
    {
        return ['Authorization' => 'Bearer ' . $token];
    }

    // ═══════════════════════════════════════════════════════════════
    // STUDENT PROFILE CRUD
    // ═══════════════════════════════════════════════════════════════

    public function test_student_can_view_profile()
    {
        $user = $this->createUserWithRole('student', ['phone' => '9800000001']);
        Student::factory()->create([
            'user_id' => $user->id,
            'department_id' => $this->department->id,
            'program_id' => $this->program->id,
            'academic_session_id' => $this->session->id,
        ]);
        $token = $this->loginAs($user);

        $response = $this->withHeaders($this->authHeaders($token))
            ->getJson('/api/v1/student/profile');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['name', 'email', 'phone', 'program', 'semester', 'roll_number']])
            ->assertJsonPath('success', true);
    }

    public function test_student_can_update_own_profile()
    {
        $user = $this->createUserWithRole('student', ['phone' => '9800000002']);
        Student::factory()->create([
            'user_id' => $user->id,
            'department_id' => $this->department->id,
            'program_id' => $this->program->id,
            'academic_session_id' => $this->session->id,
        ]);
        $token = $this->loginAs($user);

        $response = $this->withHeaders($this->authHeaders($token))
            ->putJson('/api/v1/student/profile', [
                'name' => 'Updated Student Name',
                'phone' => '9800000099',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Profile updated successfully')
            ->assertJsonPath('data.name', 'Updated Student Name');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Student Name', 'phone' => '9800000099']);
    }

    public function test_unauthorized_user_cannot_access_student_profile()
    {
        $teacher = $this->createUserWithRole('teacher');
        $token = $this->loginAs($teacher);

        $response = $this->withHeaders($this->authHeaders($token))
            ->getJson('/api/v1/student/profile');

        $response->assertStatus(403);
    }

    // ═══════════════════════════════════════════════════════════════
    // TEACHER PROFILE CRUD
    // ═══════════════════════════════════════════════════════════════

    public function test_teacher_can_view_profile()
    {
        $user = $this->createUserWithRole('teacher');
        Teacher::factory()->create([
            'user_id' => $user->id,
            'department_id' => $this->department->id,
        ]);
        $token = $this->loginAs($user);

        $response = $this->withHeaders($this->authHeaders($token))
            ->getJson('/api/v1/teacher/profile');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['name', 'email', 'employee_id', 'designation', 'department']])
            ->assertJsonPath('success', true);
    }

    public function test_teacher_can_update_own_profile()
    {
        $user = $this->createUserWithRole('teacher');
        Teacher::factory()->create([
            'user_id' => $user->id,
            'department_id' => $this->department->id,
        ]);
        $token = $this->loginAs($user);

        $response = $this->withHeaders($this->authHeaders($token))
            ->putJson('/api/v1/teacher/profile', [
                'name' => 'Updated Teacher Name',
                'phone' => '9800000055',
                'qualification' => 'M.Sc.',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Updated Teacher Name');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Teacher Name']);
    }

    // ═══════════════════════════════════════════════════════════════
    // PARENT PROFILE CRUD
    // ═══════════════════════════════════════════════════════════════

    public function test_parent_can_view_profile()
    {
        $user = $this->createUserWithRole('parent');
        ParentModel::create([
            'user_id' => $user->id,
            'occupation' => 'Engineer',
            'relation_to_student' => 'father',
        ]);
        $token = $this->loginAs($user);

        $response = $this->withHeaders($this->authHeaders($token))
            ->getJson('/api/v1/parent/profile');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['name', 'email', 'occupation', 'relation_to_student']])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.occupation', 'Engineer');
    }

    public function test_parent_can_update_own_profile()
    {
        $user = $this->createUserWithRole('parent');
        ParentModel::create([
            'user_id' => $user->id,
            'occupation' => 'Engineer',
            'relation_to_student' => 'father',
        ]);
        $token = $this->loginAs($user);

        $response = $this->withHeaders($this->authHeaders($token))
            ->putJson('/api/v1/parent/profile', [
                'name' => 'Updated Parent Name',
                'phone' => '9800000033',
                'occupation' => 'Doctor',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Updated Parent Name');
    }

    // ═══════════════════════════════════════════════════════════════
    // ADMIN TEACHER CRUD
    // ═══════════════════════════════════════════════════════════════

    public function test_admin_can_list_teachers()
    {
        $admin = $this->createUserWithRole('admin', ['email' => 'admin@test.com']);
        $token = $this->loginAs($admin);

        Teacher::factory(3)->create(['department_id' => $this->department->id]);

        $response = $this->withHeaders($this->authHeaders($token))
            ->getJson('/api/v1/admin/teachers');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'total']]);
    }

    public function test_admin_can_create_teacher()
    {
        $admin = $this->createUserWithRole('admin', ['email' => 'admin2@test.com']);
        $token = $this->loginAs($admin);

        $response = $this->withHeaders($this->authHeaders($token))
            ->postJson('/api/v1/admin/teachers', [
                'name' => 'New Teacher',
                'email' => 'teacher.new@test.com',
                'employee_id' => 'T-999',
                'department_id' => $this->department->id,
                'designation' => 'Lecturer',
                'password' => 'password',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('users', ['email' => 'teacher.new@test.com']);
        $this->assertDatabaseHas('teachers', ['employee_id' => 'T-999']);
    }

    public function test_admin_create_teacher_validation_fails()
    {
        $admin = $this->createUserWithRole('admin', ['email' => 'admin-valid@test.com']);
        $token = $this->loginAs($admin);

        $response = $this->withHeaders($this->authHeaders($token))
            ->postJson('/api/v1/admin/teachers', []);

        $response->assertStatus(500);
    }

    public function test_admin_create_teacher_duplicate_email_fails()
    {
        $admin = $this->createUserWithRole('admin', ['email' => 'admin-dup@test.com']);
        $token = $this->loginAs($admin);

        User::factory()->create(['email' => 'existing@test.com']);

        $response = $this->withHeaders($this->authHeaders($token))
            ->postJson('/api/v1/admin/teachers', [
                'name' => 'Duplicate',
                'email' => 'existing@test.com',
                'employee_id' => 'T-888',
                'department_id' => $this->department->id,
                'designation' => 'Lecturer',
            ]);

        $response->assertStatus(500);
    }

    public function test_admin_can_show_teacher()
    {
        $admin = $this->createUserWithRole('admin', ['email' => 'admin3@test.com']);
        $token = $this->loginAs($admin);

        $teacher = Teacher::factory()->create(['department_id' => $this->department->id]);

        $response = $this->withHeaders($this->authHeaders($token))
            ->getJson("/api/v1/admin/teachers/{$teacher->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'employee_id', 'designation']]);
    }

    public function test_admin_can_update_teacher()
    {
        $admin = $this->createUserWithRole('admin', ['email' => 'admin4@test.com']);
        $token = $this->loginAs($admin);

        $teacher = Teacher::factory()->create(['department_id' => $this->department->id]);

        $response = $this->withHeaders($this->authHeaders($token))
            ->putJson("/api/v1/admin/teachers/{$teacher->id}", [
                'designation' => 'Senior Lecturer',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('teachers', ['id' => $teacher->id, 'designation' => 'Senior Lecturer']);
    }

    public function test_admin_can_delete_teacher()
    {
        $admin = $this->createUserWithRole('admin', ['email' => 'admin5@test.com']);
        $token = $this->loginAs($admin);

        $teacher = Teacher::factory()->create(['department_id' => $this->department->id]);
        $userId = $teacher->user_id;

        $response = $this->withHeaders($this->authHeaders($token))
            ->deleteJson("/api/v1/admin/teachers/{$teacher->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('teachers', ['id' => $teacher->id]);
        $this->assertSoftDeleted('users', ['id' => $userId]);
    }

    // ═══════════════════════════════════════════════════════════════
    // ADMIN STUDENT CRUD
    // ═══════════════════════════════════════════════════════════════

    public function test_admin_can_list_students()
    {
        $admin = $this->createUserWithRole('admin', ['email' => 'admin6@test.com']);
        $token = $this->loginAs($admin);

        Student::factory(3)->create([
            'department_id' => $this->department->id,
            'program_id' => $this->program->id,
            'academic_session_id' => $this->session->id,
        ]);

        $response = $this->withHeaders($this->authHeaders($token))
            ->getJson('/api/v1/admin/students');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_admin_can_create_student()
    {
        $admin = $this->createUserWithRole('admin', ['email' => 'admin7@test.com']);
        $token = $this->loginAs($admin);

        $response = $this->withHeaders($this->authHeaders($token))
            ->postJson('/api/v1/admin/students', [
                'name' => 'New Student',
                'email' => 'student.new@test.com',
                'student_no' => 'S-999',
                'program_id' => $this->program->id,
                'current_semester' => 1,
                'password' => 'password',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('users', ['email' => 'student.new@test.com']);
        $this->assertDatabaseHas('students', ['student_no' => 'S-999']);
    }

    public function test_admin_can_show_student()
    {
        $admin = $this->createUserWithRole('admin', ['email' => 'admin8@test.com']);
        $token = $this->loginAs($admin);

        $student = Student::factory()->create([
            'department_id' => $this->department->id,
            'program_id' => $this->program->id,
            'academic_session_id' => $this->session->id,
        ]);

        $response = $this->withHeaders($this->authHeaders($token))
            ->getJson("/api/v1/admin/students/{$student->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'student_no', 'program']]);
    }

    public function test_admin_can_update_student()
    {
        $admin = $this->createUserWithRole('admin', ['email' => 'admin9@test.com']);
        $token = $this->loginAs($admin);

        $student = Student::factory()->create([
            'department_id' => $this->department->id,
            'program_id' => $this->program->id,
            'academic_session_id' => $this->session->id,
        ]);

        $response = $this->withHeaders($this->authHeaders($token))
            ->putJson("/api/v1/admin/students/{$student->id}", [
                'current_semester' => 3,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('students', ['id' => $student->id, 'current_semester' => 3]);
    }

    public function test_admin_can_delete_student()
    {
        $admin = $this->createUserWithRole('admin', ['email' => 'admin10@test.com']);
        $token = $this->loginAs($admin);

        $student = Student::factory()->create([
            'department_id' => $this->department->id,
            'program_id' => $this->program->id,
            'academic_session_id' => $this->session->id,
        ]);
        $userId = $student->user_id;

        $response = $this->withHeaders($this->authHeaders($token))
            ->deleteJson("/api/v1/admin/students/{$student->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('students', ['id' => $student->id]);
        $this->assertSoftDeleted('users', ['id' => $userId]);
    }

    // ═══════════════════════════════════════════════════════════════
    // ADMIN PARENT CRUD
    // ═══════════════════════════════════════════════════════════════

    public function test_admin_can_list_parents()
    {
        $admin = $this->createUserWithRole('admin', ['email' => 'admin11@test.com']);
        $token = $this->loginAs($admin);

        $user = $this->createUserWithRole('parent', ['email' => 'parent1@test.com']);
        ParentModel::create(['user_id' => $user->id]);

        $user2 = $this->createUserWithRole('parent', ['email' => 'parent2@test.com']);
        ParentModel::create(['user_id' => $user2->id]);

        $response = $this->withHeaders($this->authHeaders($token))
            ->getJson('/api/v1/admin/parents');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_admin_can_create_parent()
    {
        $admin = $this->createUserWithRole('admin', ['email' => 'admin12@test.com']);
        $token = $this->loginAs($admin);

        $student = Student::factory()->create([
            'department_id' => $this->department->id,
            'program_id' => $this->program->id,
            'academic_session_id' => $this->session->id,
        ]);

        $response = $this->withHeaders($this->authHeaders($token))
            ->postJson('/api/v1/admin/parents', [
                'name' => 'New Parent',
                'email' => 'parent.new@test.com',
                'phone' => '9800000011',
                'occupation' => 'Teacher',
                'relation_to_student' => 'mother',
                'student_ids' => [$student->id],
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('users', ['email' => 'parent.new@test.com']);
        $this->assertDatabaseHas('parents', ['occupation' => 'Teacher']);
    }

    public function test_admin_can_show_parent()
    {
        $admin = $this->createUserWithRole('admin', ['email' => 'admin13@test.com']);
        $token = $this->loginAs($admin);

        $user = $this->createUserWithRole('parent', ['email' => 'parent.show@test.com']);
        $parent = ParentModel::create(['user_id' => $user->id, 'occupation' => 'Doctor']);

        $response = $this->withHeaders($this->authHeaders($token))
            ->getJson("/api/v1/admin/parents/{$parent->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.occupation', 'Doctor');
    }

    public function test_admin_can_update_parent()
    {
        $admin = $this->createUserWithRole('admin', ['email' => 'admin14@test.com']);
        $token = $this->loginAs($admin);

        $user = $this->createUserWithRole('parent', ['email' => 'parent.update@test.com']);
        $parent = ParentModel::create(['user_id' => $user->id, 'occupation' => 'Engineer']);

        $response = $this->withHeaders($this->authHeaders($token))
            ->putJson("/api/v1/admin/parents/{$parent->id}", [
                'occupation' => 'Architect',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('parents', ['id' => $parent->id, 'occupation' => 'Architect']);
    }

    public function test_admin_can_delete_parent()
    {
        $admin = $this->createUserWithRole('admin', ['email' => 'admin15@test.com']);
        $token = $this->loginAs($admin);

        $user = $this->createUserWithRole('parent', ['email' => 'parent.delete@test.com']);
        $parent = ParentModel::create(['user_id' => $user->id]);
        $userId = $parent->user_id;

        $response = $this->withHeaders($this->authHeaders($token))
            ->deleteJson("/api/v1/admin/parents/{$parent->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('parents', ['id' => $parent->id]);
        $this->assertSoftDeleted('users', ['id' => $userId]);
    }

    // ═══════════════════════════════════════════════════════════════
    // AUTHORIZATION — Non-admin cannot access admin CRUD
    // ═══════════════════════════════════════════════════════════════

    public function test_non_admin_cannot_access_admin_teacher_crud()
    {
        $teacher = $this->createUserWithRole('teacher', ['email' => 'teacher.auth@test.com']);
        $token = $this->loginAs($teacher);

        $this->withHeaders($this->authHeaders($token))
            ->getJson('/api/v1/admin/teachers')
            ->assertStatus(403);

        $this->withHeaders($this->authHeaders($token))
            ->postJson('/api/v1/admin/teachers', [])
            ->assertStatus(403);
    }

    public function test_non_admin_cannot_access_admin_student_crud()
    {
        $student = $this->createUserWithRole('student', ['email' => 'student.auth@test.com']);
        $token = $this->loginAs($student);

        $this->withHeaders($this->authHeaders($token))
            ->getJson('/api/v1/admin/students')
            ->assertStatus(403);
    }

    public function test_non_admin_cannot_access_admin_parent_crud()
    {
        $parent = $this->createUserWithRole('parent', ['email' => 'parent.auth@test.com']);
        $token = $this->loginAs($parent);

        $this->withHeaders($this->authHeaders($token))
            ->getJson('/api/v1/admin/parents')
            ->assertStatus(403);
    }

    // ═══════════════════════════════════════════════════════════════
    // EXISTING ENDPOINTS STILL WORK
    // ═══════════════════════════════════════════════════════════════

    public function test_student_endpoints_still_work_for_students()
    {
        $user = $this->createUserWithRole('student', ['email' => 'student.func@test.com']);
        Student::factory()->create([
            'user_id' => $user->id,
            'department_id' => $this->department->id,
            'program_id' => $this->program->id,
            'academic_session_id' => $this->session->id,
        ]);
        $token = $this->loginAs($user);

        $this->withHeaders($this->authHeaders($token))
            ->getJson('/api/v1/student/dashboard')
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_parent_endpoints_still_work_for_parents()
    {
        $user = $this->createUserWithRole('parent', ['email' => 'parent.func@test.com']);
        ParentModel::create(['user_id' => $user->id]);
        $token = $this->loginAs($user);

        $this->withHeaders($this->authHeaders($token))
            ->getJson('/api/v1/parent/dashboard')
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
