@extends('layouts.app')

@section('title', $notice->title)

@section('content')
<div class="space-y-6">
    <section class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex items-center gap-3 mb-3">
                <a href="{{ route('student.news-events.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-600 hover:text-slate-900">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to News & Events
                </a>
            </div>
            <div>
                <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">{{ ucfirst($notice->type) }}</p>
                <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">{{ $notice->title }}</h1>
                <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-slate-600">
                    <span>Published: {{ bsDate($notice->published_at ?? $notice->created_at, 'F d, Y') }}</span>
                    @if($notice->author)
                        <span>-</span>
                        <span>By: {{ $notice->author->name }}</span>
                    @endif
                    @if($notice->attachments->count() > 0)
                        <span>-</span>
                        <span>{{ $notice->attachments->count() }} attachment(s)</span>
                    @elseif($notice->attachment)
                        <span>-</span>
                        <span>1 attachment</span>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-4">
        <div class="lg:col-span-3">
            <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm p-6">
                <div class="prose prose-sm max-w-none text-slate-700">
                    {!! nl2br(e($notice->content)) !!}
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm p-6">
                <h2 class="text-sm font-semibold text-slate-900 mb-4">Information</h2>
                <div class="space-y-3">
                    <div>
                        <span class="text-xs text-slate-600">Type</span>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ ucfirst($notice->type) }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-slate-600">Department</span>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $notice->department?->name ?? 'College-wide' }}</p>
                    </div>
                    @if($notice->program)
                        <div>
                            <span class="text-xs text-slate-600">Program</span>
                            <p class="mt-1 text-sm font-medium text-slate-900">{{ $notice->program->name }}</p>
                        </div>
                    @endif
                    @if($notice->semester)
                        <div>
                            <span class="text-xs text-slate-600">Semester</span>
                            <p class="mt-1 text-sm font-medium text-slate-900">Semester {{ $notice->semester }}</p>
                        </div>
                    @endif
                </div>
            </section>

            @if($notice->attachment || $notice->attachments->count() > 0)
                <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm p-6">
                    <h2 class="text-sm font-semibold text-slate-900 mb-4">Attachments</h2>
                    <div class="space-y-3">
                        @if($notice->attachment)
                            <a href="{{ asset('storage/' . $notice->attachment) }}" target="_blank"
                               class="flex items-center gap-3 rounded-lg border border-slate-200 p-3 hover:border-blue-300 hover:bg-blue-50/50 transition-colors">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50">
                                    <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-900">{{ basename($notice->attachment) }}</p>
                                </div>
                            </a>
                        @endif
                        @foreach($notice->attachments as $attachment)
                            <a href="{{ Storage::url($attachment->file_path) }}" target="_blank"
                               class="flex items-center gap-3 rounded-lg border border-slate-200 p-3 hover:border-blue-300 hover:bg-blue-50/50 transition-colors">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50">
                                    <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-900">{{ $attachment->file_name }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</div>
@endsection
