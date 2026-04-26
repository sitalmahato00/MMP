@extends('emails.layouts.portal')

@section('subject', '🔐 Your Login Verification Code')
@section('headline', 'Login Verification')
@section('subheadline', 'Your two-factor authentication code is ready.')

@section('content')
    <p style="margin:0 0 18px;font-size:15px;line-height:1.8;color:#334155;">Hello {{ $user->name }},</p>
    <p style="margin:0 0 24px;font-size:15px;line-height:1.8;color:#334155;">
        Your two-factor authentication code is:
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 24px;">
        <tr>
            <td align="center" style="padding:24px;background:linear-gradient(135deg,#003d82,#001f4d);border-radius:18px;">
                <div style="font-size:36px;font-weight:700;letter-spacing:0.15em;color:#ffffff;font-family:'Courier New',monospace;">{{ $otp }}</div>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 24px;border:1px solid #fef3c7;background:#fffbeb;border-radius:18px;">
        <tr>
            <td style="padding:18px 22px;">
                <p style="margin:0;font-size:14px;line-height:1.8;color:#92400e;">
                    ⏱️ This code will expire in <strong>{{ $expiryMinutes }} minutes</strong>.
                </p>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 0;border:1px solid #fee2e2;background:#fef2f2;border-radius:18px;">
        <tr>
            <td style="padding:18px 22px;">
                <p style="margin:0 0 10px;font-size:13px;font-weight:700;color:#991b1b;">🔒 Security Notice:</p>
                <p style="margin:0 0 10px;font-size:13px;line-height:1.8;color:#7f1d1d;">
                    If you did not attempt to log in, please ignore this email or contact support immediately if you have concerns about your account security.
                </p>
                <p style="margin:0;font-size:13px;line-height:1.8;color:#7f1d1d;">
                    Never share this code with anyone. Our team will never ask for your verification code.
                </p>
            </td>
        </tr>
    </table>
@endsection
