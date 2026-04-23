@extends('layouts.app')

@section('title', $notice->title)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center gap-3 mb-4">
        <a href="{{ route('student.news-events.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-600 hover:text-slate-900">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to News & Events
        </a>
    </div>

    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-3">
                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $notice->type === 'event' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ ucfirst($notice->type) }}
                    </span>
                </div>
                <h1 class="text-2xl font-bold text-slate-800 mb-3">{{ $notice->title }}</h1>
                <div class="flex flex-wrap items-center gap-3 text-sm text-slate-600">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ bsDate($notice->published_at ?? $notice->created_at, 'Y, F d') }}</span>
                    </div>
                    @if($notice->author)
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span>{{ $notice->author->name }}</span>
                        </div>
                    @endif
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span>{{ $notice->department?->name ?? 'College-wide' }}</span>
                    </div>
                    @if($notice->program)
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <span>{{ $notice->program->name }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Content</h2>
        <div class="prose prose-slate max-w-none">
            {!! nl2br(e($notice->content)) !!}
        </div>
    </section>

    @if($notice->attachment || $notice->attachments->count() > 0)
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Attachments</h2>
            <div class="space-y-3">
                @if($notice->attachment)
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
                        <a href="{{ asset('storage/'.$notice->attachment) }}" 
                           class="flex-shrink-0 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition"
                           target="_blank">
                            Download
                        </a>
                    </div>
                @endif
                
                @foreach($notice->attachments as $attachment)
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
        </section>
    @endif
</div>
@endsection
