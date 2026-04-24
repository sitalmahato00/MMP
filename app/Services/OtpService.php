<?php

namespace App\Services;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OtpService
{
    /**
     * OTP expiry time in minutes
     */
    private const OTP_EXPIRY_MINUTES = 5;

    /**
     * Maximum verification attempts
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * Generate a 6-digit OTP
     */
    public function generateOtp(): string
    {
        return str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Send OTP to phone number or email
     * 
     * @param string $identifier (phone or email)
     * @param string $method ('phone' or 'email')
     * @param User|null $user
     * @return array
     */
    public function sendOtp(string $identifier, string $method = 'phone', $user = null): array
    {
        // Delete any existing OTPs for this identifier
        Otp::where($method === 'email' ? 'phone' : 'phone', $identifier)->delete();

        // Generate new OTP
        $otpCode = $this->generateOtp();

        // Store hashed OTP
        $otp = Otp::create([
            'phone' => $identifier, // Using phone column for both phone and email
            'otp' => Hash::make($otpCode),
            'attempts' => 0,
            'expires_at' => Carbon::now()->addMinutes(self::OTP_EXPIRY_MINUTES),
        ]);

        if ($method === 'email' && $user) {
            // Send via email
            $user->notify(new \App\Notifications\TwoFactorOtpNotification($otpCode, self::OTP_EXPIRY_MINUTES));
            Log::info("2FA OTP sent via email to {$identifier}");
        } else {
            // Send via SMS
            // TODO: Integrate with SMS provider (Twilio, SNS, etc.)
            Log::info("OTP for {$identifier}: {$otpCode}");
            // In production: $this->sendSms($identifier, "Your OTP is: {$otpCode}. Valid for " . self::OTP_EXPIRY_MINUTES . " minutes.");
        }

        return [
            'success' => true,
            'message' => 'OTP sent successfully',
            'expires_in' => self::OTP_EXPIRY_MINUTES * 60, // seconds
            'otp' => config('app.debug') ? $otpCode : null, // Only in debug mode
        ];
    }

    /**
     * Verify OTP
     * 
     * @param string $phone
     * @param string $otpCode
     * @return array
     */
    public function verifyOtp(string $phone, string $otpCode): array
    {
        // Find the latest OTP for this phone
        $otp = Otp::where('phone', $phone)
            ->latest()
            ->first();

        if (!$otp) {
            Log::warning("OTP verification failed: No OTP found for phone {$phone}");
            return [
                'success' => false,
                'message' => 'No OTP found. Please request a new one.',
            ];
        }

        // Check if OTP is expired
        if ($otp->isExpired()) {
            Log::warning("OTP verification failed: Expired OTP for phone {$phone}");
            $otp->delete();
            return [
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.',
            ];
        }

        // Check if OTP is blocked due to max attempts
        if ($otp->isBlocked()) {
            Log::warning("OTP verification failed: Max attempts reached for phone {$phone}");
            return [
                'success' => false,
                'message' => 'Maximum verification attempts reached. Please request a new OTP.',
            ];
        }

        // Verify OTP
        if (!Hash::check($otpCode, $otp->otp)) {
            // Increment attempts
            $otp->incrementAttempts();
            
            $remainingAttempts = self::MAX_ATTEMPTS - $otp->attempts;
            
            Log::warning("OTP verification failed: Invalid OTP for phone {$phone}. Attempts: {$otp->attempts}");
            
            return [
                'success' => false,
                'message' => 'Invalid OTP.',
                'remaining_attempts' => max(0, $remainingAttempts),
            ];
        }

        // OTP is valid - delete it
        $otp->delete();

        Log::info("OTP verified successfully for phone {$phone}");

        return [
            'success' => true,
            'message' => 'OTP verified successfully',
        ];
    }

    /**
     * Check rate limiting for phone number
     * 
     * @param string $phone
     * @return bool
     */
    public function isRateLimited(string $phone): bool
    {
        // Check if there's a recent OTP (within last minute)
        $recentOtp = Otp::where('phone', $phone)
            ->where('created_at', '>', Carbon::now()->subMinute())
            ->exists();

        return $recentOtp;
    }

    /**
     * Send SMS (placeholder for actual SMS integration)
     * 
     * @param string $phone
     * @param string $message
     * @return void
     */
    private function sendSms(string $phone, string $message): void
    {
        // TODO: Integrate with SMS provider
        // Example with Twilio:
        // $twilio = new Client(config('services.twilio.sid'), config('services.twilio.token'));
        // $twilio->messages->create($phone, [
        //     'from' => config('services.twilio.from'),
        //     'body' => $message
        // ]);
    }
}
