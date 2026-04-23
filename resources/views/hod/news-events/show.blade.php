@extends('layouts.app')
@section('title', $newsEvent->title)

@section('content')
<x-page-header title="News & Event Details" subtitle="View post information and content."
               back="{{ route('hod.news-events.index') }}"/>

<div class="max-w-4xl space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-slate-800 mb-3">{{ $newsEvent->title }}</h1>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $newsEvent->type === 'event' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ ucfirst($newsEvent->type) }}
                    </span>
                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $newsEvent->is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $newsEvent->is_published ? 'Published' : 'Draft' }}
                    </span>
                </div>
            </div>

            @if($newsEvent->created_by === auth()->id())
                <div class="flex gap-2">
                    <a href="{{ route('hod.news-events.edit', $newsEvent) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Edit</a>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 text-sm">
            <div class="text-slate-600">{{ $newsEvent->author?->name }}</div>
            <div class="text-slate-600">{{ bsDate($newsEvent->published_at ?? $newsEvent->created_at, 'Y, F d') }}</div>
            <div class="text-slate-600">{{ $newsEvent->department?->name ?? 'College-wide' }}</div>
            <div class="text-slate-600">
                {{ $newsEvent->program?->name ?? 'All Programs' }}
                @if($newsEvent->semester)
                    · Semester {{ $newsEvent->semester }}
                @endif
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Content</h2>
        <div class="prose prose-slate max-w-none">
            {!! nl2br(e($newsEvent->content)) !!}
        </div>
    </div>

    @if($newsEvent->attachment || $newsEvent->attachments->count() > 0)
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Attachments</h2>
            <div class="space-y-3">
                @if($newsEvent->attachment)
                    <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg border border-slate-200">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-900">Main Attachment</p>
                            <p class="text-xs text-slate-500">Click to download</p>
                        </div>
                        <a href="{{ asset('storage/'.$newsEvent->attachment) }}" 
                           class="flex-shrink-0 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition"
                           target="_blank">
                            Download
                        </a>
                    </div>
                @endif
                
                @foreach($newsEvent->attachments as $attachment)
                    <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg border border-slate-200">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-900">{{ $attachment->file_name }}</p>
                            <p class="text-xs text-slate-500">
                                @if($attachment->file_size)
                                    {{ number_format($attachment->file_size / 1024, 1) }} KB
                                @endif
                                @if($attachment->file_type)
                                    • {{ strtoupper($attachment->file_type) }}
                                @endif
                            </p>
                        </div>
                        <a href="{{ asset('storage/'.$attachment->file_path) }}" 
                           class="flex-shrink-0 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition"
                           target="_blank">
                            Download
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
