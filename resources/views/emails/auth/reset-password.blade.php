@extends('emails.layouts.portal')

@section('subject', 'Reset your MMP portal password')
@section('headline', 'Reset your password')
@section('subheadline', 'A password reset request was received for your MMP portal account.')

@section('content')
    <p style="margin:0 0 18px;font-size:15px;line-height:1.8;color:#334155;">Hello {{ $user->name }},</p>
    <p style="margin:0 0 20px;font-size:15px;line-height:1.8;color:#334155;">
        Click the button below to create a new password for your account. This link will expire in {{ $expiryMinutes }} minutes.
    </p>

    <a href="{{ $resetUrl }}" style="display:inline-block;padding:13px 22px;border-radius:999px;background:#003d82;color:#ffffff;text-decoration:none;font-size:14px;font-weight:700;">
        Reset Password
    </a>

    <p style="margin:24px 0 0;font-size:13px;line-height:1.8;color:#64748b;">
        If you did not request a password reset, you can ignore this email and your password will stay unchanged.
    </p>
@endsection
