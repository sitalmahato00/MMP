<?php

namespace Tests\Feature\Notifications;

use App\Models\AcademicSession;
use App\Models\Department;
use App\Models\Program;
use App\Models\User;
use App\Notifications\NewPortalAccountNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AccountCreationNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        AcademicSession::create([
            'name' => '2082/83',
            'name_bs' => '2082/83',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(10),
            'is_active' => true,
            'status' => 'active',
            'is_locked' => false,
        ]);
    }

    public function test_hod_student_creation_sends_credentials_to_student_and_parent(): void
    {
        Notification::fake();

        $hod = User::factory()->create();
        $hod->assignRole('hod');

        $department = Department::factory()->create([
            'hod_id' => $hod->id,
        ]);
        $program = Program::factory()->create([
            'department_id' => $department->id,
        ]);

        $response = $this->actingAs($hod)->post(route('hod.students.store'), [
            'name' => 'Portal Student',
            'email' => 'portal-student@example.com',
            'password' => 'password123',
            'student_no' => 'STU-501',
            'roll_number' => 'IT-501',
            'program_id' => $program->id,
            'current_semester' => 1,
            'status' => 'active',
            'create_parent' => '1',
            'parent_name' => 'Portal Parent',
            'parent_email' => 'portal-parent@example.com',
            'parent_relation' => 'parent',
        ]);

        $response->assertRedirect(route('hod.students.index'));

        $studentUser = User::query()->where('email', 'portal-student@example.com')->firstOrFail();
        $parentUser = User::query()->where('email', 'portal-parent@example.com')->firstOrFail();

        Notification::assertSentTo($studentUser, NewPortalAccountNotification::class);
        Notification::assertSentTo($parentUser, NewPortalAccountNotification::class);
    }
}
