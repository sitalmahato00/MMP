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

    @if($newsEvent->attachment)
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Attachment</h2>
            <a href="{{ asset('storage/' . $newsEvent->attachment) }}" target="_blank"
               class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                Download
            </a>
        </div>
    @endif
</div>
@endsection
