<?php

namespace Tests\Feature\Notifications;

use App\Models\AcademicSession;
use App\Models\Alumni;
use App\Models\Department;
use App\Models\ParentModel;
use App\Models\Program;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class NotificationSettingsPersistenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    #[DataProvider('roleSettingsProvider')]
    public function test_role_settings_pages_persist_user_and_notification_preferences(
        string $role,
        string $preferencesRoute,
        string $notificationsRoute,
        array $notificationPayload,
        array $expectedNotifications,
    ): void {
        $user = User::factory()->create([
            'is_active' => true,
        ]);
        $user->assignRole($role);

        $this->attachRoleProfile($role, $user);

        $preferencesPayload = [
            'theme' => 'dark',
            'language' => 'ne',
            'date_format' => 'ad',
            'nepali_numbers' => '0',
            'dashboard_layout' => 'compact',
            'default_page' => 'dashboard',
            'table_density' => 'compact',
            'pagination_size' => '50',
        ];

        $this->actingAs($user)
            ->patch(route($preferencesRoute), $preferencesPayload)
            ->assertRedirect();

        $this->actingAs($user)
            ->patch(route($notificationsRoute), $notificationPayload)
            ->assertRedirect();

        $user->refresh();

        $this->assertSame([
            'theme' => 'dark',
            'language' => 'ne',
            'date_format' => 'ad',
            'nepali_numbers' => false,
            'dashboard_layout' => 'compact',
            'default_page' => 'dashboard',
            'table_density' => 'compact',
            'pagination_size' => '50',
        ], $user->preferences);

        $this->assertSame($expectedNotifications, $user->notification_preferences);
    }

    public static function roleSettingsProvider(): array
    {
        return [
            'admin' => [
                'principal',
                'admin.settings.preferences.update',
                'admin.settings.notifications.update',
                [
                    'email_system_alerts' => '1',
                    'email_new_applications' => '0',
                    'email_attendance_alerts' => '1',
                    'email_exam_alerts' => '0',
                    'email_system_warnings' => '1',
                    'inapp_notices' => '1',
                    'inapp_comments' => '0',
                    'inapp_user_creation' => '1',
                    'inapp_updates' => '0',
                    'sms_important_alerts' => '0',
                ],
                [
                    'email_system_alerts' => true,
                    'email_new_applications' => false,
                    'email_attendance_alerts' => true,
                    'email_exam_alerts' => false,
                    'email_system_warnings' => true,
                    'inapp_notices' => true,
                    'inapp_comments' => false,
                    'inapp_user_creation' => true,
                    'inapp_updates' => false,
                    'sms_important_alerts' => false,
                ],
            ],
            'hod' => [
                'hod',
                'hod.settings.preferences.update',
                'hod.settings.notifications.update',
                [
                    'email_student_alerts' => '1',
                    'email_attendance_alerts' => '0',
                    'email_exam_alerts' => '1',
                    'email_teacher_alerts' => '1',
                    'email_department_updates' => '0',
                    'inapp_notices' => '1',
                    'inapp_comments' => '0',
                    'inapp_department_updates' => '1',
                    'inapp_updates' => '0',
                    'sms_important_alerts' => '0',
                ],
                [
                    'email_student_alerts' => true,
                    'email_attendance_alerts' => false,
                    'email_exam_alerts' => true,
                    'email_teacher_alerts' => true,
                    'email_department_updates' => false,
                    'inapp_notices' => true,
                    'inapp_comments' => false,
                    'inapp_department_updates' => true,
                    'inapp_updates' => false,
                    'sms_important_alerts' => false,
                ],
            ],
            'teacher' => [
                'teacher',
                'teacher.settings.preferences.update',
                'teacher.settings.notifications.update',
                [
                    'email_class_alerts' => '1',
                    'email_assignment_alerts' => '1',
                    'email_exam_alerts' => '0',
                    'email_attendance_alerts' => '0',
                    'email_department_updates' => '1',
                    'inapp_notices' => '1',
                    'inapp_comments' => '0',
                    'inapp_assignments' => '1',
                    'inapp_updates' => '0',
                    'sms_important_alerts' => '0',
                ],
                [
                    'email_class_alerts' => true,
                    'email_assignment_alerts' => true,
                    'email_exam_alerts' => false,
                    'email_attendance_alerts' => false,
                    'email_department_updates' => true,
                    'inapp_notices' => true,
                    'inapp_comments' => false,
                    'inapp_assignments' => true,
                    'inapp_updates' => false,
                    'sms_important_alerts' => false,
                ],
            ],
            'student' => [
                'student',
                'student.settings.preferences.update',
                'student.settings.notifications.update',
                [
                    'email_assignment_alerts' => '1',
                    'email_exam_alerts' => '0',
                    'email_grade_alerts' => '1',
                    'email_attendance_alerts' => '0',
                    'email_notice_alerts' => '1',
                    'inapp_notices' => '1',
                    'inapp_assignments' => '0',
                    'inapp_grades' => '1',
                    'inapp_updates' => '0',
                    'sms_important_alerts' => '0',
                ],
                [
                    'email_assignment_alerts' => true,
                    'email_exam_alerts' => false,
                    'email_grade_alerts' => true,
                    'email_attendance_alerts' => false,
                    'email_notice_alerts' => true,
                    'inapp_notices' => true,
                    'inapp_assignments' => false,
                    'inapp_grades' => true,
                    'inapp_updates' => false,
                    'sms_important_alerts' => false,
                ],
            ],
            'parent' => [
                'parent',
                'parent.settings.preferences.update',
                'parent.settings.notifications.update',
                [
                    'email_child_alerts' => '1',
                    'email_attendance_alerts' => '1',
                    'email_grade_alerts' => '0',
                    'email_exam_alerts' => '1',
                    'email_notice_alerts' => '0',
                    'inapp_notices' => '1',
                    'inapp_attendance' => '1',
                    'inapp_grades' => '0',
                    'inapp_updates' => '1',
                    'sms_important_alerts' => '0',
                ],
                [
                    'email_child_alerts' => true,
                    'email_attendance_alerts' => true,
                    'email_grade_alerts' => false,
                    'email_exam_alerts' => true,
                    'email_notice_alerts' => false,
                    'inapp_notices' => true,
                    'inapp_attendance' => true,
                    'inapp_grades' => false,
                    'inapp_updates' => true,
                    'sms_important_alerts' => false,
                ],
            ],
            'alumni' => [
                'alumni',
                'alumni.settings.preferences.update',
                'alumni.settings.notifications.update',
                [
                    'email_notice_alerts' => '1',
                    'email_event_alerts' => '0',
                    'email_career_alerts' => '1',
                    'inapp_notices' => '1',
                    'inapp_events' => '0',
                    'inapp_updates' => '1',
                    'sms_important_alerts' => '0',
                ],
                [
                    'email_notice_alerts' => true,
                    'email_event_alerts' => false,
                    'email_career_alerts' => true,
                    'inapp_notices' => true,
                    'inapp_events' => false,
                    'inapp_updates' => true,
                    'sms_important_alerts' => false,
                ],
            ],
        ];
    }

    private function attachRoleProfile(string $role, User $user): void
    {
        if ($role === 'hod') {
            Department::factory()->create([
                'hod_id' => $user->id,
            ]);

            return;
        }

        if ($role === 'teacher') {
            Teacher::factory()->create([
                'user_id' => $user->id,
            ]);

            return;
        }

        if ($role === 'student') {
            $department = Department::factory()->create();
            $program = Program::factory()->create([
                'department_id' => $department->id,
            ]);
            $session = AcademicSession::create([
                'name' => '2082/83',
                'name_bs' => '2082/83',
                'start_date' => now()->subMonth(),
                'end_date' => now()->addMonths(10),
                'is_active' => true,
                'status' => 'active',
                'is_locked' => false,
            ]);

            Student::create([
                'user_id' => $user->id,
                'academic_session_id' => $session->id,
                'department_id' => $department->id,
                'program_id' => $program->id,
                'student_no' => 'STU-' . $user->id,
                'registration_number' => 'REG-' . $user->id,
                'current_semester' => 2,
                'section' => 'A',
                'batch' => '2082',
                'status' => 'active',
                'is_archived' => false,
            ]);

            return;
        }

        if ($role === 'parent') {
            ParentModel::create([
                'user_id' => $user->id,
                'relation_to_student' => 'parent',
            ]);

            return;
        }

        if ($role === 'alumni') {
            $department = Department::factory()->create();
            $program = Program::factory()->create([
                'department_id' => $department->id,
            ]);

            Alumni::create([
                'user_id' => $user->id,
                'department_id' => $department->id,
                'program_id' => $program->id,
                'graduation_year' => '2080',
                'visibility' => 'public',
                'is_active' => true,
            ]);
        }
    }
}
