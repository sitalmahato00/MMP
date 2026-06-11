<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Notice;
use App\Models\User;
use App\Notifications\ExamPublishedNotification;
use App\Notifications\NewPortalAccountNotification;
use App\Notifications\OfficialCtevtNoticeNotification;
use App\Notifications\PortalNoticeNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Testing\Fakes\NotificationFake;

class PortalNotificationService
{
    public function __construct(
        private readonly NotificationPreferenceService $preferences,
        private readonly PublicDataService $publicDataService,
    ) {
    }

    public function sendNewAccountCredentials(User $user, ?User $createdBy = null): void
    {
        if (! filled($user->email) || $this->shouldSuppressMailDuringTests()) {
            return;
        }

        $token = Password::broker()->createToken($user);

        $user->notify(new NewPortalAccountNotification(
            token: $token,
            roleLabel: $this->roleLabel($user),
            createdByName: $createdBy?->name,
        ));
    }

    public function dispatchNoticePublished(Notice $notice): int
    {
        $notice->loadMissing(['department:id,name,code', 'program:id,name,code,department_id']);

        if (! $notice->is_published) {
            return 0;
        }

        if ($notice->published_at && $notice->published_at->isFuture()) {
            return 0;
        }

        $dispatchKey = 'notice:' . $notice->id . ':' . ($notice->published_at?->timestamp ?? $notice->created_at?->timestamp ?? now()->timestamp);

        if (! $this->rememberDispatch($dispatchKey)) {
            return 0;
        }

        $recipients = $this->recipientUsersForNotice($notice)
            ->filter(fn (User $user) => $this->preferences->channelsForNotice($user, $notice) !== [])
            ->values();

        if ($recipients->isEmpty()) {
            return 0;
        }

        Notification::send($recipients, new PortalNoticeNotification($notice));

        return $recipients->count();
    }

    public function dispatchExamPublished(Exam $exam): int
    {
        $exam->loadMissing(['department:id,name,code', 'programs:id,name,code,department_id']);

        if (! $exam->isPublishedState) {
            return 0;
        }

        $dispatchKey = 'exam:' . $exam->id . ':' . ($exam->published_at?->timestamp ?? $exam->created_at?->timestamp ?? now()->timestamp);

        if (! $this->rememberDispatch($dispatchKey)) {
            return 0;
        }

        $recipients = $this->recipientUsersForExam($exam)
            ->filter(fn (User $user) => $this->preferences->channelsForExam($user, $exam) !== [])
            ->values();

        if ($recipients->isEmpty()) {
            return 0;
        }

        Notification::send($recipients, new ExamPublishedNotification($exam));

        return $recipients->count();
    }

    public function dispatchOfficialCtevtFeeds(): int
    {
        $general = $this->publicDataService->getCtevtGeneralNotices(10);
        $results = $this->publicDataService->getCtevtResultNotices(10);

        return $this->dispatchOfficialCtevtItems($general['items'] ?? [], false)
            + $this->dispatchOfficialCtevtItems($results['items'] ?? [], true);
    }

    public function dispatchOfficialCtevtItems(array $items, bool $isResultNotice): int
    {
        $recipients = $this->recipientUsersForCtevt()
            ->filter(fn (User $user) => $this->preferences->channelsForCtevt($user, $isResultNotice) !== [])
            ->values();

        if ($recipients->isEmpty()) {
            return 0;
        }

        $sent = 0;

        foreach ($items as $item) {
            $title = trim((string) ($item['title'] ?? ''));
            $url = trim((string) ($item['url'] ?? data_get($item, 'files.0.url', '')));

            if ($title === '' || $url === '') {
                continue;
            }

            $dispatchKey = 'ctevt:' . ($isResultNotice ? 'result' : 'general') . ':' . sha1($title . '|' . $url);

            if (! $this->rememberDispatch($dispatchKey)) {
                continue;
            }

            Notification::send($recipients, new OfficialCtevtNoticeNotification($item, $isResultNotice));
            $sent += $recipients->count();
        }

        return $sent;
    }

    private function recipientUsersForNotice(Notice $notice): Collection
    {
        return $this->activePortalUsers()->filter(function (User $user) use ($notice) {
            return match ($this->preferences->primaryRole($user)) {
                'principal' => true,
                'hod' => $this->staffCanSeeNotice($notice, $user->hodDepartment?->id),
                'teacher' => $this->staffCanSeeNotice($notice, $user->teacher?->department_id),
                'student' => $user->student && $this->preferences->noticeMatchesStudent($notice, $user->student),
                'parent' => (bool) $user->parentProfile?->children?->contains(fn ($student) => $this->preferences->noticeMatchesStudent($notice, $student)),
                'alumni' => $this->alumniCanSeeNotice($notice, $user),
                default => false,
            };
        })->values();
    }

    private function recipientUsersForExam(Exam $exam): Collection
    {
        return $this->activePortalUsers()->filter(function (User $user) use ($exam) {
            return match ($this->preferences->primaryRole($user)) {
                'principal' => true,
                'hod' => (int) $user->hodDepartment?->id === (int) $exam->department_id,
                'teacher' => (int) $user->teacher?->department_id === (int) $exam->department_id,
                'student' => $user->student && $this->studentCanSeeExam($exam, $user->student),
                'parent' => (bool) $user->parentProfile?->children?->contains(fn ($student) => $this->studentCanSeeExam($exam, $student)),
                default => false,
            };
        })->values();
    }

    private function recipientUsersForCtevt(): Collection
    {
        return $this->activePortalUsers()->values();
    }

    private function activePortalUsers(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->with([
                'roles:id,name',
                'teacher:id,user_id,department_id',
                'hodDepartment:id,hod_id',
                'student:id,user_id,department_id,program_id,current_semester',
                'parentProfile:id,user_id',
                'parentProfile.children:id,department_id,program_id,current_semester',
                'alumnus:id,user_id,department_id,program_id',
            ])
            ->get();
    }

    private function staffCanSeeNotice(Notice $notice, ?int $departmentId): bool
    {
        if (! $departmentId) {
            return false;
        }

        if ($notice->type === 'teachers') {
            return ! $notice->department_id
                || (int) $notice->department_id === (int) $departmentId
                || (int) ($notice->program?->department_id ?? 0) === (int) $departmentId;
        }

        if (! in_array($notice->type, ['general', 'exam', 'department', 'program', 'academic', 'news', 'event'], true)) {
            return false;
        }

        if ($notice->department_id && (int) $notice->department_id !== (int) $departmentId) {
            return false;
        }

        if ($notice->program_id && (int) ($notice->program?->department_id ?? 0) !== (int) $departmentId) {
            return false;
        }

        return true;
    }

    private function alumniCanSeeNotice(Notice $notice, User $user): bool
    {
        $alumnus = $user->alumnus;

        if (! $alumnus || $notice->type === 'teachers') {
            return false;
        }

        if ($notice->department_id && (int) $notice->department_id !== (int) $alumnus->department_id) {
            return false;
        }

        if ($notice->program_id && (int) $notice->program_id !== (int) $alumnus->program_id) {
            return false;
        }

        return in_array($notice->type, ['general', 'exam', 'department', 'program', 'academic', 'news', 'event'], true);
    }

    private function studentCanSeeExam(Exam $exam, $student): bool
    {
        if ((int) $student->department_id !== (int) $exam->department_id) {
            return false;
        }

        if ($exam->programs->isEmpty()) {
            return true;
        }

        $program = $exam->programs->firstWhere('id', $student->program_id);

        if (! $program) {
            return false;
        }

        $targetSemester = (int) ($program->pivot?->semester ?? 0);

        return $targetSemester === 0 || $targetSemester === (int) $student->current_semester;
    }

    private function rememberDispatch(string $key): bool
    {
        return Cache::add('portal-notification:' . $key, true, now()->addYear());
    }

    private function roleLabel(User $user): string
    {
        return match ($this->preferences->primaryRole($user)) {
            'principal' => 'Administrator',
            'hod' => 'Head of Department',
            'teacher' => 'Teacher',
            'student' => 'Student',
            'parent' => 'Parent',
            'alumni' => 'Alumni',
            default => 'Portal User',
        };
    }

    private function shouldSuppressMailDuringTests(): bool
    {
        if (! app()->runningUnitTests()) {
            return false;
        }

        return ! (Notification::getFacadeRoot() instanceof NotificationFake);
    }
}
