@extends('emails.layouts.portal')

@section('subject', 'Activate your MMP portal account')
@section('headline', 'Activate your account')
@section('subheadline', 'Set your password to start using the portal.')

@section('content')
    <p style="margin:0 0 18px;font-size:15px;line-height:1.8;color:#334155;">Hello {{ $user->name }},</p>
    <p style="margin:0 0 20px;font-size:15px;line-height:1.8;color:#334155;">
        Your {{ $roleLabel }} portal account has been created{{ $createdByName ? ' by ' . $createdByName : '' }}.
    </p>

    <p style="margin:0 0 8px;font-size:14px;color:#0f172a;"><strong>Email:</strong> {{ $user->email }}</p>

    <p style="margin:0 0 24px;font-size:14px;line-height:1.8;color:#475569;">
        Click the button below to choose a password for your account. This link will expire in {{ $expiryMinutes }} minutes.
    </p>

    <a href="{{ $resetUrl }}" style="display:inline-block;padding:14px 28px;border-radius:12px;background:linear-gradient(135deg,#003d82,#002a5c);color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;box-shadow:0 4px 12px rgba(0,61,130,0.3);transition:all 0.3s ease;">
        Set Your Password →
    </a>

    <p style="margin:24px 0 0;font-size:13px;line-height:1.8;color:#64748b;">
        If you did not expect this account, please contact your administrator.
    </p>
@endsection
