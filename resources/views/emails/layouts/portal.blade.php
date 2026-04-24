<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('subject', config('app.name'))</title>
</head>
<body style="margin:0;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f8fafc;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:#ffffff;border-radius:24px;overflow:hidden;border:1px solid #e2e8f0;box-shadow:0 12px 40px rgba(15,23,42,0.08);">
                    <tr>
                        <td style="padding:28px 32px;background:linear-gradient(135deg,#003d82,#001f4d);color:#ffffff;">
                            <div style="font-size:12px;font-weight:700;letter-spacing:0.22em;text-transform:uppercase;color:#bfdbfe;">MMP Portal</div>
                            <div style="margin-top:8px;font-size:28px;font-weight:700;line-height:1.2;">@yield('headline')</div>
                            @hasSection('subheadline')
                                <div style="margin-top:10px;font-size:14px;line-height:1.7;color:#dbeafe;">@yield('subheadline')</div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:22px 32px;background:#f8fafc;border-top:1px solid #e2e8f0;">
                            <p style="margin:0;font-size:12px;line-height:1.7;color:#64748b;">
                                Manmohan Memorial Polytechnic
                                <br>
                                This is an automated message from the MMP portal.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
