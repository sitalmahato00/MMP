@extends('layouts.app')
@section('title', $notice->title)

@section('content')
@php
    $typeMeta = $workspace['is_news_events']
        ? [
            'news' => ['label' => 'News', 'badge' => 'bg-violet-50 text-violet-700 ring-violet-200'],
            'event' => ['label' => 'Event', 'badge' => 'bg-sky-50 text-sky-700 ring-sky-200'],
        ]
        : [
            'general' => ['label' => 'General', 'badge' => 'bg-slate-100 text-slate-700 ring-slate-200'],
            'exam' => ['label' => 'Exam / Result', 'badge' => 'bg-rose-50 text-rose-700 ring-rose-200'],
            'department' => ['label' => 'Department', 'badge' => 'bg-indigo-50 text-indigo-700 ring-indigo-200'],
            'teachers' => ['label' => 'Teachers', 'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
        ];

    $statusKey = $payload['status'] ?? (! $notice->is_published ? 'draft' : (($notice->published_at && $notice->published_at->isFuture()) ? 'scheduled' : 'published'));
    $statusMeta = [
        'published' => ['label' => 'Published', 'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
        'scheduled' => ['label' => 'Scheduled', 'badge' => 'bg-amber-50 text-amber-700 ring-amber-200'],
        'draft' => ['label' => 'Draft', 'badge' => 'bg-slate-100 text-slate-700 ring-slate-200'],
    ];

    $type = $typeMeta[$notice->type] ?? ['label' => \Illuminate\Support\Str::headline($notice->type), 'badge' => 'bg-slate-100 text-slate-700 ring-slate-200'];
    $status = $statusMeta[$statusKey] ?? $statusMeta['draft'];
    $departmentLabel = $payload['department_name'] ?? 'All Departments';
@endphp

<div class="space-y-6">
    <section class="relative overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_24px_70px_rgba(15,23,42,0.07)]">
        <div class="absolute inset-0 bg-gradient-to-br from-rose-50 via-white to-sky-50/60"></div>
        <div class="relative px-6 py-6 sm:px-8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-4xl space-y-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $type['badge'] }}">{{ $type['label'] }}</span>
                        <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $status['badge'] }}">{{ $status['label'] }}</span>
                        @if($departmentLabel)
                            <span class="rounded-full bg-slate-100 px-3 py-1.5 text-[11px] font-semibold text-slate-600">{{ $departmentLabel }}</span>
                        @endif
                    </div>

                    <div>
                        <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">{{ $notice->title }}</h1>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 sm:text-base">{{ $workspace['show_description'] }}</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-700">Author: {{ $payload['author_name'] ?? 'System' }}</span>
                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-700">Published: {{ $payload['published_bs'] ?? 'N/A' }}</span>
                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-700">Updated: {{ $payload['updated_bs'] ?? 'N/A' }}</span>
                        <span class="rounded-full bg-sky-50 px-3 py-1.5 text-sky-700">{{ $payload['attachments_count'] ?? 0 }} file(s)</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route($workspace['index_route']) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                        Back to list
                    </a>
                    @if($notice->created_by === auth()->id())
                        <a href="{{ route($workspace['route_prefix'] . '.edit', $notice) }}" class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#750000]">
                            {{ $workspace['edit_button_label'] }}
                        </a>
                        <form method="POST" action="{{ route($workspace['route_prefix'] . '.destroy', $notice) }}" onsubmit="return confirm('{{ $workspace['delete_confirm_label'] }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-bold text-rose-700 shadow-sm transition hover:bg-rose-100">
                                Delete
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @if($notice->cover_image_url)
        <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-slate-900 shadow-md flex items-center justify-center max-h-[460px]">
            <img src="{{ $notice->cover_image_url }}" alt="{{ $notice->title }}" class="w-full h-auto max-h-[460px] object-contain">
        </section>
    @endif

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Audience</p>
            <p class="mt-2 text-xl font-black tracking-tight text-slate-950">{{ $type['label'] }}</p>
            <p class="mt-1 text-sm text-slate-500">Target notice group</p>
        </article>
        <article class="rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Status</p>
            <p class="mt-2 text-xl font-black tracking-tight text-slate-950">{{ $status['label'] }}</p>
            <p class="mt-1 text-sm text-slate-500">Publishing state</p>
        </article>
        <article class="rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Department</p>
            <p class="mt-2 text-xl font-black tracking-tight text-slate-950">{{ $departmentLabel ?: 'All Departments' }}</p>
            <p class="mt-1 text-sm text-slate-500">Routing scope</p>
        </article>
        <article class="rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Files</p>
            <p class="mt-2 text-xl font-black tracking-tight text-slate-950">{{ $payload['attachments_count'] ?? 0 }}</p>
            <p class="mt-1 text-sm text-slate-500">Attachment count</p>
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <div class="space-y-6">
            @if($workspace['is_news_events'] && !empty($payload['media']) && count($payload['media']) > 0)
            {{-- Media Gallery for News & Events --}}
            <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Media Gallery</p>
                        <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Images & Videos</h2>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600">{{ count($payload['media']) }} item(s)</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($payload['media'] as $media)
                        <div class="group relative rounded-xl overflow-hidden border border-slate-200 bg-white shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                            <div class="relative h-64 bg-gradient-to-br from-slate-50 to-slate-100 overflow-hidden">
                                @if($media['is_image'])
                                    <img src="{{ $media['url'] }}" alt="{{ $media['name'] }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                    {{-- Image icon overlay --}}
                                    <div class="absolute top-3 right-3 bg-white/90 backdrop-blur-sm rounded-lg p-2 shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                        <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @elseif($media['is_video'])
                                    <video src="{{ $media['url'] }}" controls class="w-full h-full bg-black object-contain">
                                        Your browser does not support the video tag.
                                    </video>
                                    {{-- Video icon overlay --}}
                                    <div class="absolute top-3 right-3 bg-white/90 backdrop-blur-sm rounded-lg p-2 shadow-lg">
                                        <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            
                            {{-- Card footer --}}
                            <div class="p-4 bg-white">
                                <p class="text-sm font-semibold text-slate-900 truncate mb-2">{{ $media['name'] }}</p>
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                        @if($media['is_image'])
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        @else
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                        @endif
                                        {{ $media['extension'] }}
                                    </span>
                                    <a href="{{ $media['url'] }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:text-blue-700 transition">
                                        View Full
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>
            @endif

            <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">{{ $workspace['content_heading'] }}</p>
                        <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Published content</h2>
                    </div>
                </div>

                <div class="prose prose-slate mt-5 max-w-none leading-7">
                    {!! $payload['content_html'] !!}
                </div>
            </article>
        </div>

        <div class="space-y-6">
            @if($workspace['is_news_events'] && !empty($payload['documents']) && count($payload['documents']) > 0)
            {{-- Documents for News & Events --}}
            <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Documents</p>
                        <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">Linked files</h2>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600">{{ count($payload['documents']) }} total</span>
                </div>

                <div class="mt-4 space-y-3">
                    @foreach($payload['documents'] as $file)
                        <a href="{{ $file['url'] }}" target="_blank" class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-[#8B0000]/20 hover:bg-rose-50/70">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-900">{{ $file['name'] }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $file['meta'] }}</p>
                            </div>
                            <span class="rounded-full bg-white px-2.5 py-1 text-[11px] font-bold text-slate-500 ring-1 ring-slate-200">{{ $file['extension'] }}</span>
                        </a>
                    @endforeach
                </div>
            </article>
            @elseif(!$workspace['is_news_events'])
            {{-- All Attachments for Regular Notices --}}
            <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Attachments</p>
                        <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">Linked files</h2>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600">{{ $payload['attachments_count'] ?? 0 }} total</span>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse($payload['attachments'] as $file)
                        <a href="{{ $file['url'] }}" target="_blank" class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-[#8B0000]/20 hover:bg-rose-50/70">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-900">{{ $file['name'] }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $file['meta'] }}</p>
                            </div>
                            <span class="rounded-full bg-white px-2.5 py-1 text-[11px] font-bold text-slate-500 ring-1 ring-slate-200">{{ $file['extension'] }}</span>
                        </a>
                    @empty
                        <x-empty-state title="No attachments" :message="'This ' . $workspace['singular_label'] . ' does not have any uploaded files.'"/>
                    @endforelse
                </div>
            </article>
            @endif

            <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Audit Trail</p>
                <div class="mt-4 space-y-3 text-sm text-slate-600">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-bold text-slate-900">Created</p>
                        <p class="mt-1">{{ $payload['created_bs'] ?? 'N/A' }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-bold text-slate-900">Published</p>
                        <p class="mt-1">{{ $payload['published_bs'] ?? 'N/A' }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-bold text-slate-900">Updated</p>
                        <p class="mt-1">{{ $payload['updated_bs'] ?? 'N/A' }}</p>
                    </div>
                </div>
            </article>
        </div>
    </section>
</div>
@endsection
