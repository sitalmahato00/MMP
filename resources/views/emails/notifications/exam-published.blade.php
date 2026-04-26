@extends('emails.layouts.portal')

@section('subject', 'Results published: ' . $exam->name)
@section('headline', 'Results published')
@section('subheadline', $exam->name . ' is now available in the MMP portal.')

@section('content')
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 22px;border:1px solid #dcfce7;background:#f0fdf4;border-radius:18px;">
        <tr>
            <td style="padding:18px 22px;">
                <p style="margin:0 0 10px;font-size:12px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;color:#166534;">Exam Details</p>
                <p style="margin:0 0 6px;font-size:14px;color:#0f172a;"><strong>Exam:</strong> {{ $exam->name }}</p>
                <p style="margin:0;font-size:14px;color:#0f172a;"><strong>Department:</strong> {{ $exam->department?->name ?? 'Academic Portal' }}</p>
            </td>
        </tr>
    </table>

    <a href="{{ $actionUrl }}" style="display:inline-block;padding:14px 28px;border-radius:12px;background:linear-gradient(135deg,#10b981,#059669);color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;box-shadow:0 4px 12px rgba(16,185,129,0.3);transition:all 0.3s ease;">
        {{ $actionLabel }} →
    </a>
@endsection
