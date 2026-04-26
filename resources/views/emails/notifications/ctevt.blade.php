@extends('emails.layouts.portal')

@section('subject', ($isResultNotice ? 'CTEVT result notice: ' : 'CTEVT official notice: ') . $title)
@section('headline', $title)
@section('subheadline', $isResultNotice ? 'A new official CTEVT result notice was detected.' : 'A new official CTEVT notice was detected.')

@section('content')
    @if($summary)
        <p style="margin:0 0 20px;font-size:14px;line-height:1.9;color:#475569;">{{ $summary }}</p>
    @endif

    @if($updatedDate)
        <p style="margin:0 0 22px;font-size:13px;color:#64748b;"><strong>Updated:</strong> {{ $updatedDate }}</p>
    @endif

    <a href="{{ $actionUrl }}" style="display:inline-block;padding:14px 28px;border-radius:12px;background:linear-gradient(135deg,#f59e0b,#d97706);color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;box-shadow:0 4px 12px rgba(245,158,11,0.3);transition:all 0.3s ease;">
        {{ $actionLabel }} →
    </a>
@endsection
