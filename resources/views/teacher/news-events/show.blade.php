@extends('layouts.app')

@section('title', $notice->title)

@section('content')
<div class="space-y-6">
    <x-page-header :title="$notice->title" subtitle="News & event details" back="{{ route('teacher.news-events.index') }}" />

    <section class="grid gap-4 sm:grid-cols-4">
        <x-card>
            <div class="text-center">
                <p class="text-xs font-semibold text-slate-500 uppercase mb-2">Type</p>
                <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold {{ $notice->type === 'event' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700' }}">{{ ucfirst($notice->type) }}</span>
            </div>
        </x-card>
        <x-card>
            <div class="text-center">
                <p class="text-xs font-semibold text-slate-500 uppercase mb-2">Published Date</p>
                <p class="text-sm font-semibold text-slate-900">{{ bsDate($notice->published_at ?? $notice->created_at, 'M d, Y') }}</p>
            </div>
        </x-card>
        <x-card>
            <div class="text-center">
                <p class="text-xs font-semibold text-slate-500 uppercase mb-2">Department</p>
                <p class="text-sm font-semibold text-slate-900">{{ $notice->department?->name ?? 'College-wide' }}</p>
            </div>
        </x-card>
        <x-card>
            <div class="text-center">
                <p class="text-xs font-semibold text-slate-500 uppercase mb-2">Program</p>
                <p class="text-sm font-semibold text-slate-900">{{ $notice->program?->name ?? 'All Programs' }}</p>
            </div>
        </x-card>
        <x-card>
            <div class="text-center">
                <p class="text-xs font-semibold text-slate-500 uppercase mb-2">Semester</p>
                <p class="text-sm font-semibold text-slate-900">{{ $notice->semester ? 'Semester ' . $notice->semester : 'All Semesters' }}</p>
            </div>
        </x-card>
    </section>

    <x-card>
        <div class="prose prose-sm max-w-none text-slate-600">
            {!! nl2br(e($notice->content)) !!}
        </div>
    </x-card>

    @if($notice->attachment || $notice->attachments->isNotEmpty())
        <x-card>
            <div class="mb-4">
                <h2 class="text-sm font-semibold text-slate-900">Attachments</h2>
            </div>
            <div class="space-y-2">
                @if($notice->attachment)
                    <a href="{{ asset('storage/' . $notice->attachment) }}" target="_blank"
                       class="flex items-center gap-3 rounded-lg border border-slate-200 p-3 transition hover:bg-slate-50 hover:border-slate-300">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600 flex-shrink-0">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-900 truncate">{{ basename($notice->attachment) }}</p>
                        </div>
                    </a>
                @endif
                @foreach($notice->attachments as $attachment)
                    <a href="{{ asset('storage/' . ltrim($attachment->file_path, '/')) }}" target="_blank"
                       class="flex items-center gap-3 rounded-lg border border-slate-200 p-3 transition hover:bg-slate-50 hover:border-slate-300">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600 flex-shrink-0">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-900 truncate">{{ $attachment->file_name }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </x-card>
    @endif
</div>
@endsection
