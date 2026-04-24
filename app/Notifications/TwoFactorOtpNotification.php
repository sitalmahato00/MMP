<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorOtpNotification extends Notification
{
    use Queueable;

    protected string $otp;
    protected int $expiryMinutes;

    public function __construct(string $otp, int $expiryMinutes = 5)
    {
        $this->otp = $otp;
        $this->expiryMinutes = $expiryMinutes;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🔐 Your Login Verification Code')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your two-factor authentication code is:')
            ->line('## **' . $this->otp . '**')
            ->line('This code will expire in **' . $this->expiryMinutes . ' minutes**.')
            ->line('---')
            ->line('🔒 **Security Notice:**')
            ->line('If you did not attempt to log in, please ignore this email or contact support immediately if you have concerns about your account security.')
            ->line('Never share this code with anyone. Our team will never ask for your verification code.')
            ->salutation('Best regards,  
' . config('app.name') . ' Team');
    }
}
