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

        <div class="space-y-6">
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
