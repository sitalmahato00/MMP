@extends('layouts.app')
@section('title', $notice->title)

@section('content')
<x-page-header title="Notice Details" subtitle="View notice information and content."
               back="{{ route('hod.notices.index') }}"/>

<div class="max-w-4xl space-y-6">
    {{-- Notice Header --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-slate-800 mb-3">{{ $notice->title }}</h1>
                <div class="flex flex-wrap items-center gap-3">
                    @if($notice->is_published)
                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-700">
                            Published
                        </span>
                    @else
                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-700">
                            Draft
                        </span>
                    @endif
                    <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700">
                        {{ ucfirst($notice->type) }} Notice
                    </span>
                </div>
            </div>

            @if($notice->type === 'department' && $notice->created_by === auth()->id())
                <div class="flex gap-2">
                    <a href="{{ route('hod.notices.edit', $notice) }}" 
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </a>
                </div>
            @endif
        </div>

        {{-- Notice Meta --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 text-sm">
            <div class="flex items-center gap-2 text-slate-600">
                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span>{{ $notice->author?->name }}</span>
            </div>
            <div class="flex items-center gap-2 text-slate-600">
                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>{{ $notice->created_at->format('M d, Y') }}</span>
            </div>
            @if($notice->department)
                <div class="flex items-center gap-2 text-slate-600">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span>{{ $notice->department->name }}</span>
                </div>
            @endif
            @if($notice->program)
                <div class="flex items-center gap-2 text-slate-600">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span>{{ $notice->program->name }}</span>
                    @if($notice->semester)
                        <span class="text-slate-400">• Semester {{ $notice->semester }}</span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Notice Content --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Content</h2>
        <div class="prose prose-slate max-w-none">
            {!! nl2br(e($notice->content)) !!}
        </div>
    </div>

    {{-- Attachment --}}
    @if($notice->attachment)
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Attachment</h2>
            <div class="flex items-center gap-4 rounded-lg border border-slate-200 bg-slate-50 p-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100">
                    <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-slate-800">Notice Attachment</p>
                    <p class="text-sm text-slate-500">Click to download or view</p>
                </div>
                <a href="{{ asset('storage/' . $notice->attachment) }}" target="_blank"
                   class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download
                </a>
            </div>
        </div>
    @endif

    {{-- Publication Info --}}
    @if($notice->is_published && $notice->published_at)
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm font-semibold text-emerald-800">
                    Published on {{ $notice->published_at->format('F d, Y \a\t g:i A') }}
                </span>
            </div>
        </div>
    @endif
</div>
@endsection