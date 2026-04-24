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

    <a href="{{ $actionUrl }}" style="display:inline-block;padding:13px 22px;border-radius:999px;background:#003d82;color:#ffffff;text-decoration:none;font-size:14px;font-weight:700;">
        {{ $actionLabel }}
    </a>
@endsection
