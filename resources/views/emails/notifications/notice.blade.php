@extends('emails.layouts.portal')

@section('subject', 'New notice: ' . $notice->title)
@section('headline', $notice->title)
@section('subheadline', 'A new portal notice has been published' . ($scopeLabel ? ' for ' . $scopeLabel : '') . '.')

@section('content')
    <p style="margin:0 0 20px;font-size:14px;line-height:1.9;color:#475569;">{{ $summary }}</p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 22px;border:1px solid #e2e8f0;background:#f8fafc;border-radius:18px;">
        <tr>
            <td style="padding:18px 22px;">
                <p style="margin:0 0 8px;font-size:12px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;color:#64748b;">Scope</p>
                <p style="margin:0;font-size:14px;color:#0f172a;">{{ $scopeLabel }}</p>
            </td>
        </tr>
    </table>

    <a href="{{ $actionUrl }}" style="display:inline-block;padding:14px 28px;border-radius:12px;background:linear-gradient(135deg,#003d82,#002a5c);color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;box-shadow:0 4px 12px rgba(0,61,130,0.3);transition:all 0.3s ease;">
        {{ $actionLabel }} →
    </a>
@endsection
