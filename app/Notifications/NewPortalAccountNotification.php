<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPortalAccountNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $token,
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
            ->subject('Activate your MMP portal account')
            ->view('emails.accounts.created', [
                'user' => $notifiable,
                'roleLabel' => $this->roleLabel,
                'createdByName' => $this->createdByName,
                'resetUrl' => route('password.reset', [
                    'token' => $this->token,
                    'email' => $notifiable->getEmailForPasswordReset(),
                ]),
                'expiryMinutes' => (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60),
            ]);
    }
}
