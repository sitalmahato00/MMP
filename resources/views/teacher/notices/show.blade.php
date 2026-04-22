@extends('layouts.app')

@section('title', $notice->title)

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl lg:rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-violet-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Notice</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        {{ $notice->title }}
                    </h1>
                </div>
                <a href="{{ route('teacher.notices.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back
                </a>
            </div>
        </div>
    </section>

    {{-- Notice Info --}}
    <div class="grid gap-4 sm:grid-cols-4">
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase">Status</p>
            <p class="mt-2 text-lg font-semibold text-slate-900 capitalize">{{ $notice->status }}</p>
        </div>
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase">Type</p>
            <p class="mt-2 text-lg font-semibold text-slate-900 capitalize">{{ $notice->type ?? 'General' }}</p>
        </div>
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase">Published Date</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">{{ bsDate($notice->created_at, 'M d, Y') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase">Author</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">{{ $notice->author->name ?? 'System' }}</p>
        </div>
    </div>

    {{-- Content --}}
    <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-sm">
        <div class="prose prose-sm max-w-none text-slate-600">
            {!! nl2br(e($notice->content)) !!}
        </div>
    </div>

    {{-- Attachments --}}
    @if($notice->attachments->isNotEmpty())
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-900 mb-4">Attachments</h2>
            <div class="space-y-2">
                @foreach($notice->attachments as $attachment)
                    <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="flex items-center gap-3 rounded-lg border border-slate-200 p-3 transition hover:bg-slate-50">
                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-900 truncate">{{ $attachment->file_name }}</p>
                            <p class="text-xs text-slate-500">{{ number_format($attachment->file_size / 1024, 2) }} KB</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
