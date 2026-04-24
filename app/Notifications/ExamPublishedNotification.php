<?php

namespace App\Notifications;

use App\Models\Exam;
use App\Services\NotificationPreferenceService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExamPublishedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Exam $exam,
    ) {
    }

    public function via(object $notifiable): array
    {
        return app(NotificationPreferenceService::class)->channelsForExam($notifiable, $this->exam);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Results published: ' . $this->exam->name)
            ->view('emails.notifications.exam-published', [
                'user' => $notifiable,
                'exam' => $this->exam,
                'actionUrl' => $this->actionUrlFor($notifiable),
                'actionLabel' => $this->actionLabel(),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Results published',
            'message' => $this->exam->name . ' results are now available.',
            'action_url' => $this->actionUrlFor($notifiable),
            'action_label' => $this->actionLabel(),
            'category' => 'exam',
            'icon' => 'chart-bar',
            'color' => 'emerald',
            'scope_label' => $this->exam->department?->name ?? 'Academic portal',
            'occurred_at' => ($this->exam->published_at ?? $this->exam->updated_at ?? now())->toIso8601String(),
        ];
    }

    private function actionLabel(): string
    {
        return 'View results';
    }

    private function actionUrlFor(object $notifiable): string
    {
        return match ($notifiable->primaryRole()) {
            'principal' => route('admin.exams.show', $this->exam),
            'hod' => route('hod.exams.results'),
            'teacher' => route('teacher.exams.index'),
            'student' => route('student.marks.index'),
            'parent' => route('parent.results.index'),
            default => route('dashboard'),
        };
    }
}
