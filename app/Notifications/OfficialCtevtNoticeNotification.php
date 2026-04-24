<?php

namespace App\Notifications;

use App\Services\NotificationPreferenceService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class OfficialCtevtNoticeNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly array $item,
        private readonly bool $isResultNotice,
    ) {
    }

    public function via(object $notifiable): array
    {
        return app(NotificationPreferenceService::class)->channelsForCtevt($notifiable, $this->isResultNotice);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(($this->isResultNotice ? 'CTEVT result notice: ' : 'CTEVT official notice: ') . $this->title())
            ->view('emails.notifications.ctevt', [
                'user' => $notifiable,
                'title' => $this->title(),
                'summary' => Str::limit((string) ($this->item['publisher'] ?? ''), 180),
                'actionUrl' => $this->actionUrl(),
                'actionLabel' => $this->isResultNotice ? 'Open result notice' : 'Open official notice',
                'updatedDate' => $this->item['updated_date'] ?? null,
                'isResultNotice' => $this->isResultNotice,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title(),
            'message' => Str::limit((string) ($this->item['publisher'] ?? 'Official CTEVT notice'), 140),
            'action_url' => $this->actionUrl(),
            'action_label' => $this->isResultNotice ? 'Open result notice' : 'Open official notice',
            'category' => 'ctevt',
            'icon' => 'document-text',
            'color' => 'amber',
            'scope_label' => $this->isResultNotice ? 'CTEVT Result Notice' : 'CTEVT Official Notice',
            'occurred_at' => now()->toIso8601String(),
        ];
    }

    private function title(): string
    {
        return trim((string) ($this->item['title'] ?? 'CTEVT Notice'));
    }

    private function actionUrl(): string
    {
        return trim((string) ($this->item['url'] ?? data_get($this->item, 'files.0.url', route('public.notices'))));
    }
}
