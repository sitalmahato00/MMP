<?php

namespace Tests\Feature\Notifications;

use App\Models\AcademicSession;
use App\Models\Alumni;
use App\Models\Department;
use App\Models\Exam;
use App\Models\Notice;
use App\Models\ParentModel;
use App\Models\Program;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\NotificationPreferenceService;
use App\Services\PortalNotificationService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PortalNotificationDeliveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        Cache::flush();
    }

    public function test_notice_exam_and_ctevt_notifications_are_delivered_to_the_expected_roles(): void
    {
        $session = AcademicSession::create([
            'name' => '2082/83',
            'name_bs' => '2082/83',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(10),
            'is_active' => true,
            'status' => 'active',
            'is_locked' => false,
        ]);

        $principal = User::factory()->create(['is_active' => true]);
        $principal->assignRole('principal');

        $hod = User::factory()->create(['is_active' => true]);
        $hod->assignRole('hod');

        $department = Department::factory()->create([
            'hod_id' => $hod->id,
        ]);

        $program = Program::factory()->create([
            'department_id' => $department->id,
        ]);

        $teacherUser = User::factory()->create(['is_active' => true]);
        $teacherUser->assignRole('teacher');
        Teacher::factory()->create([
            'user_id' => $teacherUser->id,
            'department_id' => $department->id,
        ]);

        $studentUser = User::factory()->create(['is_active' => true]);
        $studentUser->assignRole('student');
        $student = Student::create([
            'user_id' => $studentUser->id,
            'academic_session_id' => $session->id,
            'department_id' => $department->id,
            'program_id' => $program->id,
            'student_no' => 'STU-100',
            'registration_number' => 'REG-100',
            'current_semester' => 2,
            'section' => 'A',
            'batch' => '2082',
            'status' => 'active',
            'is_archived' => false,
        ]);

        $parentUser = User::factory()->create(['is_active' => true]);
        $parentUser->assignRole('parent');
        $parent = ParentModel::create([
            'user_id' => $parentUser->id,
            'relation_to_student' => 'parent',
        ]);
        $parent->children()->attach($student->id);

        $alumniUser = User::factory()->create(['is_active' => true]);
        $alumniUser->assignRole('alumni');
        Alumni::create([
            'user_id' => $alumniUser->id,
            'department_id' => $department->id,
            'program_id' => $program->id,
            'graduation_year' => (string) now()->year,
            'employment_status' => 'employed',
            'visibility' => 'public',
            'is_verified' => true,
        ]);

        foreach ([$principal, $hod, $teacherUser, $studentUser, $parentUser, $alumniUser] as $user) {
            $this->enableDatabaseNotificationsOnly($user);
        }

        $notice = Notice::create([
            'title' => 'Department notice',
            'slug' => 'department-notice',
            'content' => 'A scoped notice for the department portal.',
            'type' => 'general',
            'department_id' => $department->id,
            'program_id' => $program->id,
            'semester' => 2,
            'created_by' => $principal->id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $service = app(PortalNotificationService::class);

        $this->assertSame(6, $service->dispatchNoticePublished($notice));

        foreach ([$principal, $hod, $teacherUser, $studentUser, $parentUser, $alumniUser] as $user) {
            $this->assertSame(1, $user->fresh()->notifications()->count());
        }

        $exam = Exam::create([
            'academic_session_id' => $session->id,
            'department_id' => $department->id,
            'name' => 'Mid Term Assessment',
            'type' => 'internal',
            'category' => 'monthly_assessment',
            'assessment_number' => 1,
            'assessment_full_marks' => 100,
            'assessment_pass_marks' => 40,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(1)->toDateString(),
            'status' => 'results_published',
            'marks_open' => false,
            'is_published' => true,
            'published_at' => now(),
        ]);
        $exam->programs()->attach($program->id, ['semester' => 2]);

        $this->assertSame(5, $service->dispatchExamPublished($exam));

        foreach ([$principal, $hod, $teacherUser, $studentUser, $parentUser] as $user) {
            $this->assertSame(2, $user->fresh()->notifications()->count());
        }
        $this->assertSame(1, $alumniUser->fresh()->notifications()->count());

        $ctevtItems = [[
            'title' => 'Official CTEVT Circular',
            'url' => 'https://example.com/ctevt/circular',
            'publisher' => 'CTEVT',
            'updated_date' => now()->toDateString(),
        ]];

        $this->assertSame(6, $service->dispatchOfficialCtevtItems($ctevtItems, false));

        foreach ([$principal, $hod, $teacherUser, $studentUser, $parentUser, $alumniUser] as $user) {
            $expectedCount = $user->isAlumni() ? 2 : 3;
            $this->assertSame($expectedCount, $user->fresh()->notifications()->count());
        }
    }

    private function enableDatabaseNotificationsOnly(User $user): void
    {
        $service = app(NotificationPreferenceService::class);
        $defaults = $service->defaultNotificationPreferencesFor($user);

        $user->forceFill([
            'notification_preferences' => collect($defaults)
                ->mapWithKeys(fn (bool $value, string $key) => [$key => str_starts_with($key, 'inapp_')])
                ->all(),
        ])->save();
    }
}
