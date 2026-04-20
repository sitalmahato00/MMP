<?php

namespace Tests\Feature\HOD;

use App\Models\AcademicSession;
use App\Models\Department;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $hod;
    protected Department $department;
    protected Program $program;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        // Active academic session
        AcademicSession::create([
            'name'       => '2081/82',
            'name_bs'    => '२०८१/८२',
            'start_date' => now()->subMonth(),
            'end_date'   => now()->addYear(),
            'is_active'  => true,
            'status'     => 'active',
            'is_locked'  => false,
        ]);
        // Clear cached active session from previous tests
        cache()->forget('active_academic_session');

        // HOD + department + program
        $this->hod = User::factory()->create();
        $this->hod->assignRole('hod');

        $this->department = Department::factory()->create(['hod_id' => $this->hod->id]);
        $this->program    = Program::factory()->create(['department_id' => $this->department->id]);
    }

    // ── Helpers ────────────────────────────────────────────────────────────
    protected function validStudentPayload(array $overrides = []): array
    {
        return array_merge([
            'name'             => 'Alice Sharma',
            'email'            => 'alice@example.com',
            'password'         => 'password123',
            'student_no'       => 'STU-001',
            'roll_number'      => 'DIT-081-99',
            'program_id'       => $this->program->id,
            'current_semester' => 1,
            'status'           => 'active',
        ], $overrides);
    }

    // ── Tests ──────────────────────────────────────────────────────────────
    public function test_index_lists_only_students_from_own_department(): void
    {
        // Student in HOD's department
        $ownUser    = User::factory()->create(['name' => 'Own Student']);
        $ownStudent = Student::factory()->create([
            'user_id'       => $ownUser->id,
            'department_id' => $this->department->id,
            'program_id'    => $this->program->id,
        ]);

        // Student in another department
        $otherDept    = Department::factory()->create();
        $otherProgram = Program::factory()->create(['department_id' => $otherDept->id]);
        $otherUser    = User::factory()->create(['name' => 'Other Student']);
        $otherStudent = Student::factory()->create([
            'user_id'       => $otherUser->id,
            'department_id' => $otherDept->id,
            'program_id'    => $otherProgram->id,
        ]);

        $response = $this->actingAs($this->hod)->get(route('hod.students.index'));

        $response->assertOk();
        $response->assertSeeText('Own Student');
        $response->assertDontSeeText('Other Student');
    }

    public function test_create_form_only_lists_own_department_programs(): void
    {
        $otherDept    = Department::factory()->create();
        $otherProgram = Program::factory()->create([
            'department_id' => $otherDept->id,
            'name'          => 'Foreign Program XYZ',
        ]);

        $response = $this->actingAs($this->hod)->get(route('hod.students.create'));

        $response->assertOk();
        $response->assertSee($this->program->name);
        $response->assertDontSee('Foreign Program XYZ');
    }

    public function test_hod_can_store_a_student_in_their_own_department(): void
    {
        $response = $this->actingAs($this->hod)
            ->post(route('hod.students.store'), $this->validStudentPayload());

        $response->assertRedirect(route('hod.students.index'));

        $this->assertDatabaseHas('users', ['email' => 'alice@example.com']);
        $this->assertDatabaseHas('students', [
            'student_no'    => 'STU-001',
            'roll_number'   => 'DIT-081-99',
            'department_id' => $this->department->id,
            'program_id'    => $this->program->id,
        ]);

        $created = User::where('email', 'alice@example.com')->first();
        $this->assertTrue($created->hasRole('student'));
    }

    public function test_hod_cannot_store_a_student_using_another_departments_program(): void
    {
        $otherDept    = Department::factory()->create();
        $otherProgram = Program::factory()->create(['department_id' => $otherDept->id]);

        $response = $this->actingAs($this->hod)
            ->post(route('hod.students.store'), $this->validStudentPayload([
                'program_id' => $otherProgram->id,
            ]));

        $response->assertSessionHasErrors('program_id');
        $this->assertDatabaseMissing('users', ['email' => 'alice@example.com']);
    }

    public function test_hod_can_view_own_department_student(): void
    {
        $user    = User::factory()->create(['name' => 'Bob Student']);
        $student = Student::factory()->create([
            'user_id'       => $user->id,
            'department_id' => $this->department->id,
            'program_id'    => $this->program->id,
        ]);

        $response = $this->actingAs($this->hod)->get(route('hod.students.show', $student));

        $response->assertOk();
        $response->assertSeeText('Bob Student');
    }

    public function test_hod_cannot_view_student_from_another_department(): void
    {
        $otherDept    = Department::factory()->create();
        $otherProgram = Program::factory()->create(['department_id' => $otherDept->id]);
        $otherUser    = User::factory()->create();
        $otherStudent = Student::factory()->create([
            'user_id'       => $otherUser->id,
            'department_id' => $otherDept->id,
            'program_id'    => $otherProgram->id,
        ]);

        $response = $this->actingAs($this->hod)->get(route('hod.students.show', $otherStudent));

        $response->assertNotFound();
    }

    public function test_hod_can_update_own_department_student_including_roll_number(): void
    {
        $user    = User::factory()->create(['name' => 'Old Name']);
        $student = Student::factory()->create([
            'user_id'       => $user->id,
            'department_id' => $this->department->id,
            'program_id'    => $this->program->id,
        ]);

        $response = $this->actingAs($this->hod)
            ->put(route('hod.students.update', $student), [
                'name'             => 'New Name',
                'email'            => $user->email,
                'student_no'       => $student->student_no,
                'roll_number'      => 'HOD-NEW-ROLL',
                'program_id'       => $this->program->id,
                'current_semester' => 2,
                'status'           => 'active',
            ]);

        $response->assertRedirect(route('hod.students.index'));
        $this->assertDatabaseHas('users',    ['id' => $user->id,    'name' => 'New Name']);
        $this->assertDatabaseHas('students', ['id' => $student->id, 'roll_number' => 'HOD-NEW-ROLL', 'current_semester' => 2]);
    }

    public function test_hod_cannot_update_student_from_another_department(): void
    {
        $otherDept    = Department::factory()->create();
        $otherProgram = Program::factory()->create(['department_id' => $otherDept->id]);
        $otherUser    = User::factory()->create(['name' => 'Do Not Touch']);
        $otherStudent = Student::factory()->create([
            'user_id'       => $otherUser->id,
            'department_id' => $otherDept->id,
            'program_id'    => $otherProgram->id,
        ]);

        $response = $this->actingAs($this->hod)
            ->put(route('hod.students.update', $otherStudent), [
                'name'             => 'Hacked Name',
                'email'            => $otherUser->email,
                'student_no'       => $otherStudent->student_no,
                'program_id'       => $this->program->id,
                'current_semester' => 3,
            ]);

        $response->assertNotFound();
        $this->assertDatabaseHas('users', ['id' => $otherUser->id, 'name' => 'Do Not Touch']);
    }

    public function test_hod_can_delete_own_department_student(): void
    {
        $user    = User::factory()->create();
        $student = Student::factory()->create([
            'user_id'       => $user->id,
            'department_id' => $this->department->id,
            'program_id'    => $this->program->id,
        ]);

        $response = $this->actingAs($this->hod)->delete(route('hod.students.destroy', $student));

        $response->assertRedirect(route('hod.students.index'));
        $this->assertSoftDeleted('students', ['id' => $student->id]);
    }

    public function test_hod_cannot_delete_student_from_another_department(): void
    {
        $otherDept    = Department::factory()->create();
        $otherProgram = Program::factory()->create(['department_id' => $otherDept->id]);
        $otherUser    = User::factory()->create();
        $otherStudent = Student::factory()->create([
            'user_id'       => $otherUser->id,
            'department_id' => $otherDept->id,
            'program_id'    => $otherProgram->id,
        ]);

        $response = $this->actingAs($this->hod)->delete(route('hod.students.destroy', $otherStudent));

        $response->assertNotFound();
        $this->assertDatabaseHas('students', ['id' => $otherStudent->id, 'deleted_at' => null]);
    }

    public function test_non_hod_user_cannot_access_hod_student_routes(): void
    {
        $plainUser = User::factory()->create();
        $plainUser->assignRole('student');

        $this->actingAs($plainUser)
            ->get(route('hod.students.index'))
            ->assertForbidden();
    }
}
