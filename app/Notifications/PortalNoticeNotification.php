<?php

namespace App\Notifications;

use App\Models\Notice;
use App\Services\NotificationPreferenceService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class PortalNoticeNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Notice $notice,
    ) {
    }

    public function via(object $notifiable): array
    {
        return app(NotificationPreferenceService::class)->channelsForNotice($notifiable, $this->notice);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->mailSubject())
            ->view('emails.notifications.notice', [
                'user' => $notifiable,
                'notice' => $this->notice,
                'actionUrl' => $this->actionUrlFor($notifiable),
                'actionLabel' => $this->actionLabel(),
                'summary' => Str::limit(strip_tags((string) $this->notice->content), 180),
                'scopeLabel' => $this->scopeLabel(),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->notice->title,
            'message' => Str::limit(strip_tags((string) $this->notice->content), 140),
            'action_url' => $this->actionUrlFor($notifiable),
            'action_label' => $this->actionLabel(),
            'category' => 'notice',
            'icon' => 'bell',
            'color' => 'blue',
            'scope_label' => $this->scopeLabel(),
            'occurred_at' => ($this->notice->published_at ?? $this->notice->updated_at ?? now())->toIso8601String(),
        ];
    }

    private function mailSubject(): string
    {
        return 'New notice: ' . $this->notice->title;
    }

    private function actionLabel(): string
    {
        return in_array($this->notice->type, ['news', 'event'], true) ? 'Open update' : 'Open notice';
    }

    private function scopeLabel(): string
    {
        if ($this->notice->program?->name) {
            return $this->notice->program->name;
        }

        if ($this->notice->department?->name) {
            return $this->notice->department->name;
        }

        return 'All portals';
    }

    private function actionUrlFor(object $notifiable): string
    {
        $role = $notifiable->primaryRole();
        $isNewsEvent = in_array($this->notice->type, ['news', 'event'], true);

        return match ($role) {
            'principal' => $isNewsEvent
                ? route('admin.news-events.show', $this->notice)
                : route('admin.notices.show', $this->notice),
            'hod' => $isNewsEvent
                ? route('hod.news-events.show', $this->notice)
                : route('hod.notices.show', $this->notice),
            'teacher' => $isNewsEvent
                ? route('teacher.news-events.show', $this->notice)
                : route('teacher.notices.show', $this->notice),
            'student' => $isNewsEvent
                ? route('student.news-events.show', $this->notice)
                : route('student.notices.show', $this->notice),
            'parent' => $isNewsEvent
                ? route('parent.news-events.show', $this->notice)
                : route('parent.notices.show', $this->notice),
            'alumni' => $isNewsEvent
                ? route('public.news-events.show', $this->notice->slug)
                : route('public.notice.show', $this->notice->slug),
            default => route('dashboard'),
        };
    }
}
