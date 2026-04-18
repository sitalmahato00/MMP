@extends('layouts.app')
@section('title', 'Notices')

@section('content')
<x-page-header title="Notices" subtitle="College notices and announcements."/>

<div class="max-w-4xl space-y-4">
    @forelse($notices as $notice)
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <h3 class="text-sm font-bold text-slate-900">{{ $notice->title }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">{{ bsDate($notice->published_at ?? $notice->created_at) }}</p>
                @if($notice->content)
                    <div class="mt-2 text-sm text-slate-600 line-clamp-3">{!! nl2br(e(Str::limit($notice->content, 300))) !!}</div>
                @endif
            </div>
            @if($notice->attachment_path)
                <a href="{{ asset('storage/'.$notice->attachment_path) }}" target="_blank" class="flex-shrink-0 inline-flex items-center gap-1 text-xs font-semibold text-[#8B0000] hover:underline">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Download
                </a>
            @endif
        </div>
    </div>
    @empty
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-8 text-center">
        <svg class="mx-auto w-10 h-10 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        <p class="text-sm text-slate-500 font-semibold">No notices available.</p>
    </div>
    @endforelse

    <div class="mt-4">{{ $notices->links() }}</div>
</div>
@endsection