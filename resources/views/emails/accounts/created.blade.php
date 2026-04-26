@extends('emails.layouts.portal')

@section('subject', 'Your MMP portal account is ready')
@section('headline', 'Your account is ready')
@section('subheadline', 'Login credentials for your new portal access are below. Please change your password after your first sign-in.')

@section('content')
    <p style="margin:0 0 18px;font-size:15px;line-height:1.8;color:#334155;">Hello {{ $user->name }},</p>
    <p style="margin:0 0 20px;font-size:15px;line-height:1.8;color:#334155;">
        Your {{ $roleLabel }} portal account has been created{{ $createdByName ? ' by ' . $createdByName : '' }}.
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 24px;border:1px solid #dbeafe;background:#eff6ff;border-radius:18px;">
        <tr>
            <td style="padding:20px 22px;">
                <p style="margin:0 0 10px;font-size:12px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;color:#64748b;">Login Credentials</p>
                <p style="margin:0 0 8px;font-size:14px;color:#0f172a;"><strong>Email:</strong> {{ $user->email }}</p>
                <p style="margin:0;font-size:14px;color:#0f172a;"><strong>Temporary Password:</strong> {{ $plainPassword }}</p>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 24px;font-size:14px;line-height:1.8;color:#475569;">
        Use the button below to sign in. For security, update this password from your account settings as soon as you enter the portal.
    </p>

    <a href="{{ $loginUrl }}" style="display:inline-block;padding:14px 28px;border-radius:12px;background:linear-gradient(135deg,#003d82,#002a5c);color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;box-shadow:0 4px 12px rgba(0,61,130,0.3);transition:all 0.3s ease;">
        Open Portal Login →
    </a>
@endsection
