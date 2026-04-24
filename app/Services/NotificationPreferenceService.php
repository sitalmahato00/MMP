<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Notice;
use App\Models\Student;
use App\Models\User;

class NotificationPreferenceService
{
    private const USER_PREFERENCE_DEFAULTS = [
        'theme' => 'light',
        'language' => 'en',
        'date_format' => 'bs',
        'nepali_numbers' => true,
        'dashboard_layout' => 'comfortable',
        'default_page' => 'dashboard',
        'table_density' => 'normal',
        'pagination_size' => '25',
    ];

    private const ROLE_NOTIFICATION_DEFAULTS = [
        'principal' => [
            'email_system_alerts' => true,
            'email_new_applications' => true,
            'email_attendance_alerts' => true,
            'email_exam_alerts' => true,
            'email_system_warnings' => true,
            'inapp_notices' => true,
            'inapp_comments' => true,
            'inapp_user_creation' => true,
            'inapp_updates' => true,
            'sms_important_alerts' => false,
        ],
        'hod' => [
            'email_student_alerts' => true,
            'email_attendance_alerts' => true,
            'email_exam_alerts' => true,
            'email_teacher_alerts' => true,
            'email_department_updates' => true,
            'inapp_notices' => true,
            'inapp_comments' => true,
            'inapp_department_updates' => true,
            'inapp_updates' => true,
            'sms_important_alerts' => false,
        ],
        'teacher' => [
            'email_class_alerts' => true,
            'email_assignment_alerts' => true,
            'email_exam_alerts' => true,
            'email_attendance_alerts' => true,
            'email_department_updates' => true,
            'inapp_notices' => true,
            'inapp_comments' => true,
            'inapp_assignments' => true,
            'inapp_updates' => true,
            'sms_important_alerts' => false,
        ],
        'student' => [
            'email_assignment_alerts' => true,
            'email_exam_alerts' => true,
            'email_grade_alerts' => true,
            'email_attendance_alerts' => false,
            'email_notice_alerts' => true,
            'inapp_notices' => true,
            'inapp_assignments' => true,
            'inapp_grades' => true,
            'inapp_updates' => true,
            'sms_important_alerts' => false,
        ],
        'parent' => [
            'email_child_alerts' => true,
            'email_attendance_alerts' => true,
            'email_grade_alerts' => true,
            'email_exam_alerts' => true,
            'email_notice_alerts' => true,
            'inapp_notices' => true,
            'inapp_attendance' => true,
            'inapp_grades' => true,
            'inapp_updates' => true,
            'sms_important_alerts' => false,
        ],
        'alumni' => [
            'email_notice_alerts' => true,
            'email_event_alerts' => true,
            'email_career_alerts' => true,
            'inapp_notices' => true,
            'inapp_events' => true,
            'inapp_updates' => true,
            'sms_important_alerts' => false,
        ],
    ];

    public function defaultUserPreferences(): array
    {
        return self::USER_PREFERENCE_DEFAULTS;
    }

    public function primaryRole(User $user): string
    {
        return (string) ($user->primaryRole() ?? 'guest');
    }

    public function notificationKeysFor(User $user): array
    {
        return array_keys($this->defaultNotificationPreferencesFor($user));
    }

    public function defaultNotificationPreferencesFor(User $user): array
    {
        return self::ROLE_NOTIFICATION_DEFAULTS[$this->primaryRole($user)] ?? [];
    }

    public function userPreferences(User $user): array
    {
        return array_replace(
            self::USER_PREFERENCE_DEFAULTS,
            is_array($user->preferences) ? $user->preferences : []
        );
    }

    public function notificationPreferences(User $user): array
    {
        return array_replace(
            $this->defaultNotificationPreferencesFor($user),
            is_array($user->notification_preferences) ? $user->notification_preferences : []
        );
    }

    public function saveUserPreferences(User $user, array $payload): array
    {
        $preferences = [];

        foreach (self::USER_PREFERENCE_DEFAULTS as $key => $default) {
            $preferences[$key] = $payload[$key] ?? $default;
        }

        $user->forceFill(['preferences' => $preferences])->save();

        return $preferences;
    }

    public function saveNotificationPreferences(User $user, array $payload): array
    {
        $defaults = $this->defaultNotificationPreferencesFor($user);
        $preferences = [];

        foreach ($defaults as $key => $default) {
            $preferences[$key] = filter_var($payload[$key] ?? false, FILTER_VALIDATE_BOOL);
        }

        $user->forceFill(['notification_preferences' => $preferences])->save();

        return $preferences;
    }

    public function clearStoredPreferences(User $user): void
    {
        $user->forceFill([
            'preferences' => null,
            'notification_preferences' => null,
        ])->save();
    }

    public function channelsForNotice(User $user, Notice $notice): array
    {
        $role = $this->primaryRole($user);
        $preferences = $this->notificationPreferences($user);
        $isScoped = $notice->department_id || $notice->program_id || $notice->semester;

        [$mailKey, $databaseKey] = match ($role) {
            'principal' => ['email_system_alerts', 'inapp_notices'],
            'hod' => [$isScoped ? 'email_department_updates' : 'email_department_updates', $isScoped ? 'inapp_department_updates' : 'inapp_notices'],
            'teacher' => ['email_department_updates', 'inapp_notices'],
            'student' => ['email_notice_alerts', 'inapp_notices'],
            'parent' => ['email_notice_alerts', 'inapp_notices'],
            'alumni' => ['email_notice_alerts', $notice->type === 'event' ? 'inapp_events' : 'inapp_notices'],
            default => [null, null],
        };

        return $this->channelsFromKeys($preferences, $mailKey, $databaseKey);
    }

    public function channelsForExam(User $user, Exam $exam): array
    {
        $role = $this->primaryRole($user);
        $preferences = $this->notificationPreferences($user);

        [$mailKey, $databaseKey] = match ($role) {
            'principal' => ['email_exam_alerts', 'inapp_updates'],
            'hod' => ['email_exam_alerts', 'inapp_updates'],
            'teacher' => ['email_exam_alerts', 'inapp_updates'],
            'student' => ['email_grade_alerts', 'inapp_grades'],
            'parent' => ['email_grade_alerts', 'inapp_grades'],
            default => [null, null],
        };

        return $this->channelsFromKeys($preferences, $mailKey, $databaseKey);
    }

    public function channelsForCtevt(User $user, bool $isResultNotice): array
    {
        $role = $this->primaryRole($user);
        $preferences = $this->notificationPreferences($user);

        [$mailKey, $databaseKey] = match ($role) {
            'principal' => [$isResultNotice ? 'email_exam_alerts' : 'email_system_alerts', 'inapp_updates'],
            'hod' => [$isResultNotice ? 'email_exam_alerts' : 'email_department_updates', 'inapp_updates'],
            'teacher' => [$isResultNotice ? 'email_exam_alerts' : 'email_department_updates', 'inapp_updates'],
            'student' => [$isResultNotice ? 'email_grade_alerts' : 'email_notice_alerts', $isResultNotice ? 'inapp_grades' : 'inapp_notices'],
            'parent' => [$isResultNotice ? 'email_grade_alerts' : 'email_notice_alerts', $isResultNotice ? 'inapp_grades' : 'inapp_notices'],
            'alumni' => [$isResultNotice ? 'email_event_alerts' : 'email_notice_alerts', 'inapp_updates'],
            default => [null, null],
        };

        return $this->channelsFromKeys($preferences, $mailKey, $databaseKey);
    }

    public function noticeMatchesStudent(Notice $notice, Student $student): bool
    {
        if (in_array($notice->type, ['teachers', 'ctevt'], true)) {
            return false;
        }

        if ($notice->department_id && (int) $notice->department_id !== (int) $student->department_id) {
            return false;
        }

        if ($notice->program_id && (int) $notice->program_id !== (int) $student->program_id) {
            return false;
        }

        if ($notice->semester && (int) $notice->semester !== (int) $student->current_semester) {
            return false;
        }

        return true;
    }

    private function channelsFromKeys(array $preferences, ?string $mailKey, ?string $databaseKey): array
    {
        $channels = [];

        if ($databaseKey && ($preferences[$databaseKey] ?? false)) {
            $channels[] = 'database';
        }

        if ($mailKey && ($preferences[$mailKey] ?? false)) {
            $channels[] = 'mail';
        }

        return $channels;
    }
}
