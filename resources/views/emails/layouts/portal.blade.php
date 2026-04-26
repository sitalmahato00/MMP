<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('subject', config('app.name'))</title>
</head>
<body style="margin:0;background:#f8fafc;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f8fafc;padding:40px 16px;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:#ffffff;border-radius:24px;overflow:hidden;border:1px solid #e2e8f0;box-shadow:0 20px 60px rgba(15,23,42,0.12);">
                    <!-- Header with Gradient -->
                    <tr>
                        <td style="padding:36px 32px;background:linear-gradient(135deg,#003d82 0%,#002a5c 50%,#001f4d 100%);color:#ffffff;position:relative;">
                            <!-- Logo -->
                            <div style="margin-bottom:20px;text-align:center;">
                                <img src="{{ route('public.brand-logo') }}?v={{ logoVersion() }}" alt="MMP Logo" style="width:80px;height:80px;border-radius:16px;background:#ffffff;padding:10px;box-shadow:0 4px 12px rgba(0,0,0,0.2);display:inline-block;">
                            </div>
                            <!-- Brand Name -->
                            <div style="margin-bottom:16px;text-align:center;">
                                <div style="display:inline-block;padding:8px 16px;background:rgba(255,255,255,0.15);border-radius:12px;backdrop-filter:blur(10px);">
                                    <span style="font-size:13px;font-weight:700;letter-spacing:0.22em;text-transform:uppercase;color:#ffffff;">MMP PORTAL</span>
                                </div>
                            </div>
                            <!-- Headline -->
                            <div style="font-size:32px;font-weight:700;line-height:1.2;margin-bottom:12px;text-align:center;">@yield('headline')</div>
                            <!-- Subheadline -->
                            @hasSection('subheadline')
                                <div style="font-size:15px;line-height:1.7;color:#dbeafe;opacity:0.95;text-align:center;">@yield('subheadline')</div>
                            @endif
                        </td>
                    </tr>
                    <!-- Content Area -->
                    <tr>
                        <td style="padding:36px 32px;">
                            @yield('content')
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="padding:24px 32px;background:#f8fafc;border-top:1px solid #e2e8f0;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="padding-bottom:16px;text-align:center;">
                                        <img src="{{ route('public.brand-logo') }}?v={{ logoVersion() }}" alt="MMP Logo" style="width:48px;height:48px;border-radius:12px;display:inline-block;">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align:center;">
                                        <p style="margin:0 0 8px;font-size:14px;font-weight:600;color:#0f172a;">Manmohan Memorial Polytechnic</p>
                                        <p style="margin:0 0 4px;font-size:12px;line-height:1.7;color:#64748b;">
                                            Budhiganga-4, Morang, Koshi Province, Nepal
                                        </p>
                                        <p style="margin:0;font-size:12px;line-height:1.7;color:#64748b;">
                                            📞 +977 21 590696, +977 21 590697
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top:16px;border-top:1px solid #e2e8f0;margin-top:16px;">
                                        <p style="margin:0;font-size:11px;line-height:1.7;color:#94a3b8;text-align:center;">
                                            This is an automated message from the MMP portal. Please do not reply to this email.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <!-- Footer Note -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;margin-top:24px;">
                    <tr>
                        <td align="center">
                            <p style="margin:0;font-size:12px;color:#94a3b8;line-height:1.7;">
                                © {{ date('Y') }} Manmohan Memorial Polytechnic. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
