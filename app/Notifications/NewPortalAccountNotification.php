<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPortalAccountNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $plainPassword,
        private readonly string $roleLabel,
        private readonly ?string $createdByName = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your MMP portal account is ready')
            ->view('emails.accounts.created', [
                'user' => $notifiable,
                'plainPassword' => $this->plainPassword,
                'roleLabel' => $this->roleLabel,
                'createdByName' => $this->createdByName,
                'loginUrl' => route('login'),
            ]);
    }
}
