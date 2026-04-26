@extends('emails.layouts.portal')

@section('subject', 'Reset your MMP portal password')
@section('headline', 'Reset your password')
@section('subheadline', 'A password reset request was received for your MMP portal account.')

@section('content')
    <p style="margin:0 0 18px;font-size:15px;line-height:1.8;color:#334155;">Hello {{ $user->name }},</p>
    <p style="margin:0 0 20px;font-size:15px;line-height:1.8;color:#334155;">
        Click the button below to create a new password for your account. This link will expire in {{ $expiryMinutes }} minutes.
    </p>

    <a href="{{ $resetUrl }}" style="display:inline-block;padding:14px 28px;border-radius:12px;background:linear-gradient(135deg,#dc2626,#b91c1c);color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;box-shadow:0 4px 12px rgba(220,38,38,0.3);transition:all 0.3s ease;">
        Reset Password →
    </a>

    <p style="margin:24px 0 0;font-size:13px;line-height:1.8;color:#64748b;">
        If you did not request a password reset, you can ignore this email and your password will stay unchanged.
    </p>
@endsection
