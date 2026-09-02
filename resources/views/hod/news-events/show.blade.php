@extends('layouts.app')
@section('title', $newsEvent->title)

@section('content')
<x-page-header title="News & Event Details" subtitle="View post information and content."
               back="{{ route('hod.news-events.index') }}"/>

<div class="max-w-4xl space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-xs">
        @php
            $newsFirstImg = $newsEvent->attachments->where('is_image', true)->first();
            $newsCoverImg = $newsFirstImg?->url ?? ($newsEvent->attachment && preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $newsEvent->attachment) ? asset('storage/' . $newsEvent->attachment) : null);
        @endphp
        @if($newsCoverImg)
            <div class="w-full bg-slate-900 border-b border-slate-200 overflow-hidden flex items-center justify-center max-h-[380px]">
                <img src="{{ $newsCoverImg }}" alt="{{ $newsEvent->title }}" class="w-full h-auto max-h-[380px] object-contain">
            </div>
        @endif
        <div class="p-6">
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
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Content</h2>
        <div class="prose prose-slate max-w-none">
            {!! nl2br(e($newsEvent->content)) !!}
        </div>
    </div>

    @php
        $images   = $newsEvent->attachments->where('is_image', true);
        $videos   = $newsEvent->attachments->whereIn('file_type', ['mp4', 'webm', 'mov', 'avi']);
        $otherFiles = $newsEvent->attachments->reject(fn($a) => $a->is_image || in_array($a->file_type, ['mp4','webm','mov','avi']));
        $legacyFile = $newsEvent->attachment;
    @endphp

    @if($images->count() > 0 || $videos->count() > 0)
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="mb-4 flex items-center gap-2 text-lg font-bold text-slate-800">
                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Media Gallery
            </h2>

            @if($images->count() > 0)
                <div class="grid grid-cols-2 gap-4 md:grid-cols-3 mb-6">
                    @foreach($images as $image)
                        <a href="{{ $image->url }}" target="_blank"
                           class="group relative aspect-video overflow-hidden rounded-lg bg-slate-200 shadow-md hover:shadow-xl transition-all duration-300">
                            <img src="{{ $image->url }}" alt="{{ $image->file_name }}"
                                 class="h-full w-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors flex items-center justify-center">
                                <svg class="h-10 w-10 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                </svg>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            @if($videos->count() > 0)
                <div class="space-y-4">
                    @foreach($videos as $video)
                        <div class="overflow-hidden rounded-lg bg-black shadow-lg">
                            <video controls class="w-full" preload="metadata">
                                <source src="{{ $video->url }}" type="video/{{ $video->file_type }}">
                                Your browser does not support the video tag.
                            </video>
                            <div class="bg-slate-800 px-4 py-2 text-sm text-slate-300">{{ $video->file_name }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @if($otherFiles->count() > 0 || $legacyFile)
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="mb-4 flex items-center gap-2 text-lg font-bold text-slate-800">
                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                </svg>
                Downloads
            </h2>
            <div class="space-y-2">
                @if($legacyFile)
                    <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-blue-100">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-slate-900">Attachment</p>
                        </div>
                        <a href="{{ asset('storage/'.$legacyFile) }}" target="_blank"
                           class="flex-shrink-0 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                            Download
                        </a>
                    </div>
                @endif
                @foreach($otherFiles as $file)
                    <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-blue-100">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $file->file_name }}</p>
                            <p class="text-xs text-slate-500">
                                {{ strtoupper($file->file_type) }}
                                @if($file->file_size)· {{ number_format($file->file_size / 1024, 1) }} KB @endif
                            </p>
                        </div>
                        <a href="{{ $file->url }}" target="_blank"
                           class="flex-shrink-0 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                            Download
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
