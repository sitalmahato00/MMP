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
            ->view('emails.auth.two-factor-otp', [
                'user' => $notifiable,
                'otp' => $this->otp,
                'expiryMinutes' => $this->expiryMinutes,
            ]);
    }
}
