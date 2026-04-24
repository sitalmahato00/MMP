@extends('layouts.app')
@section('title', $workspace['title'])

@section('content')
@php
    $typeMeta = $workspace['is_news_events']
        ? [
            'news' => ['label' => 'News', 'badge' => 'bg-violet-50 text-violet-700 ring-violet-200', 'accent' => 'border-l-violet-400'],
            'event' => ['label' => 'Event', 'badge' => 'bg-sky-50 text-sky-700 ring-sky-200', 'accent' => 'border-l-sky-400'],
        ]
        : [
            'general' => ['label' => 'General', 'badge' => 'bg-slate-100 text-slate-700 ring-slate-200', 'accent' => 'border-l-slate-300'],
            'exam' => ['label' => 'Exam / Result', 'badge' => 'bg-rose-50 text-rose-700 ring-rose-200', 'accent' => 'border-l-rose-400'],
            'department' => ['label' => 'Department', 'badge' => 'bg-indigo-50 text-indigo-700 ring-indigo-200', 'accent' => 'border-l-indigo-400'],
            'teachers' => ['label' => 'Teachers', 'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'accent' => 'border-l-emerald-400'],
        ];

    $statusMeta = [
        'published' => ['label' => 'Published', 'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
        'scheduled' => ['label' => 'Scheduled', 'badge' => 'bg-amber-50 text-amber-700 ring-amber-200'],
        'draft' => ['label' => 'Draft', 'badge' => 'bg-slate-100 text-slate-700 ring-slate-200'],
    ];

    $overviewCards = $workspace['is_news_events']
        ? [
            ['label' => 'Total Posts', 'value' => $stats['total'], 'tone' => 'slate'],
            ['label' => 'Published', 'value' => $stats['published'], 'tone' => 'emerald'],
            ['label' => 'Scheduled', 'value' => $stats['scheduled'], 'tone' => 'amber'],
            ['label' => 'Drafts', 'value' => $stats['draft'], 'tone' => 'zinc'],
            ['label' => 'News Posts', 'value' => $stats['news'], 'tone' => 'violet'],
            ['label' => 'Events', 'value' => $stats['event'], 'tone' => 'sky'],
        ]
        : [
            ['label' => 'Total Notices', 'value' => $stats['total'], 'tone' => 'slate'],
            ['label' => 'Published', 'value' => $stats['published'], 'tone' => 'emerald'],
            ['label' => 'Scheduled', 'value' => $stats['scheduled'], 'tone' => 'amber'],
            ['label' => 'Drafts', 'value' => $stats['draft'], 'tone' => 'zinc'],
            ['label' => 'Exam Notices', 'value' => $stats['exam'], 'tone' => 'rose'],
            ['label' => 'Files Attached', 'value' => $stats['attachments'], 'tone' => 'sky'],
        ];

    $toneStyles = [
        'slate' => ['chip' => 'bg-slate-100 text-slate-700', 'dot' => 'bg-slate-500'],
        'zinc' => ['chip' => 'bg-zinc-100 text-zinc-700', 'dot' => 'bg-zinc-500'],
        'emerald' => ['chip' => 'bg-emerald-100 text-emerald-700', 'dot' => 'bg-emerald-500'],
        'amber' => ['chip' => 'bg-amber-100 text-amber-700', 'dot' => 'bg-amber-500'],
        'rose' => ['chip' => 'bg-rose-100 text-rose-700', 'dot' => 'bg-rose-500'],
        'sky' => ['chip' => 'bg-sky-100 text-sky-700', 'dot' => 'bg-sky-500'],
        'violet' => ['chip' => 'bg-violet-100 text-violet-700', 'dot' => 'bg-violet-500'],
    ];
@endphp

<div
    x-data="{
        drawer: false,
        selectedNotice: null,
        notices: @js($noticeDrawerPayload),
        openDrawer(id) {
            this.selectedNotice = this.notices.find((item) => item.id === id) || null;
            this.drawer = !! this.selectedNotice;
        },
        closeDrawer() {
            this.drawer = false;
        },
    }"
    class="space-y-6"
    @keydown.escape.window="closeDrawer()"
>
    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4 mb-5">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $workspace['title'] }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $workspace['subtitle'] }}</p>
            </div>

            <a href="{{ route($workspace['route_prefix'] . '.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                {{ $workspace['create_label'] }}
            </a>
        </div>

        <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-6 mb-5">
            @foreach($overviewCards as $card)
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                    <p class="text-xs text-slate-500">{{ $card['label'] }}</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($card['value']) }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route($workspace['index_route']) }}" class="grid gap-3 lg:grid-cols-12">
            <div class="lg:col-span-3">
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search..."
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                >
            </div>

            <div class="lg:col-span-2">
                <select name="type" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">All Types</option>
                    @foreach($typeMeta as $type => $meta)
                        <option value="{{ $type }}" @selected(request('type') === $type)>{{ $meta['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <select name="status" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">All Status</option>
                    @foreach($statusMeta as $status => $meta)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $meta['label'] }}</option>
                    @endforeach
                </select>
            </div>

            @if(! $workspace['is_news_events'])
                <div class="lg:col-span-2">
                    <select name="department_id" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>
                                {{ $department->code ? $department->code . ' - ' : '' }}{{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="lg:col-span-3 flex gap-2">
                <button type="submit" class="flex-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Search</button>
                <a href="{{ route($workspace['index_route']) }}" class="flex-1 rounded-lg border border-slate-200 px-4 py-2 text-center text-sm font-semibold text-slate-600 hover:bg-slate-50">Reset</a>
            </div>
        </form>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <p class="text-sm text-slate-600">
                Showing {{ $notices->firstItem() ?? 0 }}-{{ $notices->lastItem() ?? 0 }} of {{ number_format($notices->total()) }}
            </p>
        </div>

        <div class="hidden lg:block overflow-hidden">
            <div class="mmp-table-wrap">
                <table class="mmp-table divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50/80">
                        <tr class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                            <th class="px-4 py-3 text-left">{{ $workspace['is_news_events'] ? 'Post' : 'Notice' }}</th>
                            <th class="px-4 py-3 text-left">Audience</th>
                            <th class="px-4 py-3 text-left">Department</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Published (BS)</th>
                            <th class="px-4 py-3 text-center">Files</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($notices as $notice)
                            @php
                                $type = $typeMeta[$notice->type] ?? ['label' => \Illuminate\Support\Str::headline($notice->type), 'badge' => 'bg-slate-100 text-slate-700 ring-slate-200', 'accent' => 'border-l-slate-300'];
                                $statusKey = ! $notice->is_published
                                    ? 'draft'
                                    : (($notice->published_at && $notice->published_at->isFuture()) ? 'scheduled' : 'published');
                                $status = $statusMeta[$statusKey] ?? $statusMeta['draft'];
                                $authorName = $notice->author?->name ?? 'System';
                                $departmentLabel = $notice->department?->code
                                    ? $notice->department->code . ' - ' . $notice->department->name
                                    : ($notice->department?->name ?? 'All Departments');
                                $publishedLabel = bsDateTime($notice->published_at ?? $notice->created_at, 'Y, F d', 'h:i A');
                            @endphp
                            <tr class="border-l-4 {{ $type['accent'] }} transition hover:bg-slate-50/70">
                                <td class="px-4 py-3.5">
                                    <button type="button" @click="openDrawer({{ $notice->id }})" class="block text-left">
                                        <p class="font-semibold text-slate-900 transition hover:text-[#8B0000]">{{ $notice->title }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ \Illuminate\Support\Str::limit(trim(strip_tags((string) $notice->content)), 120) }}</p>
                                        <p class="mt-2 text-[11px] text-slate-400">By {{ $authorName }}</p>
                                    </button>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $type['badge'] }}">{{ $type['label'] }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-slate-600">{{ $departmentLabel }}</td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $status['badge'] }}">{{ $status['label'] }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-xs text-slate-500">{{ $publishedLabel ?: 'N/A' }}</td>
                                <td class="px-4 py-3.5 text-center">
                                    @if(($notice->attachments_count ?? 0) > 0)
                                        <span class="inline-flex rounded-full bg-sky-50 px-2.5 py-1 text-[11px] font-bold text-sky-700 ring-1 ring-sky-200">{{ $notice->attachments_count }}</span>
                                    @else
                                        <span class="text-xs text-slate-300">N/A</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" @click="openDrawer({{ $notice->id }})" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" title="Quick View">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>
                                        <a href="{{ route($workspace['route_prefix'] . '.show', $notice) }}" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600" title="Open Page">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3h7m0 0v7m0-7L10 14"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5h5M5 5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-5"/>
                                            </svg>
                                        </a>
                                        @if($notice->created_by === auth()->id())
                                            <a href="{{ route($workspace['route_prefix'] . '.edit', $notice) }}" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-amber-50 hover:text-amber-600" title="Edit">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <form method="POST" action="{{ route($workspace['route_prefix'] . '.destroy', $notice) }}" onsubmit="return confirm('Delete {{ addslashes($notice->title) }}? This cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-red-50 hover:text-red-600" title="Delete">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-empty-state :title="$workspace['empty_title']" :message="$workspace['empty_message']" :action="route($workspace['route_prefix'] . '.create')" :actionLabel="$workspace['create_label']"/>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-3 p-4 lg:hidden">
            @forelse($notices as $notice)
                @php
                    $type = $typeMeta[$notice->type] ?? ['label' => \Illuminate\Support\Str::headline($notice->type), 'badge' => 'bg-slate-100 text-slate-700 ring-slate-200', 'accent' => 'border-l-slate-300'];
                    $statusKey = ! $notice->is_published
                        ? 'draft'
                        : (($notice->published_at && $notice->published_at->isFuture()) ? 'scheduled' : 'published');
                    $status = $statusMeta[$statusKey] ?? $statusMeta['draft'];
                @endphp
                <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm border-l-4 {{ $type['accent'] }}">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-semibold text-slate-900">{{ $notice->title }}</h3>
                            <p class="mt-1 text-xs text-slate-500">{{ \Illuminate\Support\Str::limit(trim(strip_tags((string) $notice->content)), 120) }}</p>
                            <p class="mt-2 text-[11px] text-slate-400">{{ $notice->author?->name ?? 'System' }} | {{ bsDateTime($notice->published_at ?? $notice->created_at, 'Y, F d', 'h:i A') }}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $status['badge'] }}">{{ $status['label'] }}</span>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $type['badge'] }}">{{ $type['label'] }}</span>
                        @if(($notice->attachments_count ?? 0) > 0)
                            <span class="rounded-full bg-sky-50 px-2.5 py-1 text-[11px] font-bold text-sky-700 ring-1 ring-sky-200">{{ $notice->attachments_count }} file(s)</span>
                        @endif
                    </div>

                    <div class="mt-3 flex items-center justify-end gap-2">
                        <button type="button" @click="openDrawer({{ $notice->id }})" class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700">Preview</button>
                        <a href="{{ route($workspace['route_prefix'] . '.show', $notice) }}" class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700">View</a>
                        @if($notice->created_by === auth()->id())
                            <a href="{{ route($workspace['route_prefix'] . '.edit', $notice) }}" class="rounded-xl bg-[#8B0000] px-3 py-1.5 text-xs font-bold text-white">Edit</a>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-slate-200 bg-slate-50">
                    <x-empty-state :title="$workspace['empty_title']" :message="$workspace['empty_message']" :action="route($workspace['route_prefix'] . '.create')" :actionLabel="$workspace['create_label']"/>
                </div>
            @endforelse
        </div>

        @if($notices->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">
                {{ $notices->onEachSide(1)->links() }}
            </div>
        @endif
    </section>

    <div x-show="drawer" x-cloak class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-slate-900/45" @click="closeDrawer()"></div>

        <aside class="absolute right-0 top-0 h-full w-full max-w-2xl overflow-y-auto bg-white shadow-2xl">
            <div class="sticky top-0 z-10 border-b border-slate-100 bg-white px-5 py-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">{{ $workspace['drawer_title'] }}</p>
                        <h3 class="mt-1 text-xl font-black text-slate-900" x-text="selectedNotice?.title"></h3>
                    </div>
                    <button type="button" @click="closeDrawer()" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-600">Close</button>
                </div>
            </div>

            <div class="space-y-5 p-5" x-show="selectedNotice">
                <section class="grid gap-4 md:grid-cols-2">
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <h4 class="text-sm font-black text-slate-900">{{ $workspace['detail_heading'] }}</h4>
                        <dl class="mt-3 space-y-2 text-sm">
                            <div class="flex justify-between gap-2">
                                <dt class="text-slate-500">Type</dt>
                                <dd class="font-semibold text-slate-900" x-text="selectedNotice?.type_label || 'N/A'"></dd>
                            </div>
                            <div class="flex justify-between gap-2">
                                <dt class="text-slate-500">Status</dt>
                                <dd class="font-semibold text-slate-900" x-text="selectedNotice?.status_label || 'N/A'"></dd>
                            </div>
                            <div class="flex justify-between gap-2">
                                <dt class="text-slate-500">Department</dt>
                                <dd class="font-semibold text-slate-900 text-right" x-text="selectedNotice?.department_name || 'All Departments'"></dd>
                            </div>
                        </dl>
                    </article>

                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <h4 class="text-sm font-black text-slate-900">Publishing</h4>
                        <dl class="mt-3 space-y-2 text-sm">
                            <div class="flex justify-between gap-2">
                                <dt class="text-slate-500">Author</dt>
                                <dd class="font-semibold text-slate-900" x-text="selectedNotice?.author_name || 'System'"></dd>
                            </div>
                            <div class="flex justify-between gap-2">
                                <dt class="text-slate-500">Published</dt>
                                <dd class="font-semibold text-slate-900 text-right" x-text="selectedNotice?.published_bs || 'N/A'"></dd>
                            </div>
                            <div class="flex justify-between gap-2">
                                <dt class="text-slate-500">Updated</dt>
                                <dd class="font-semibold text-slate-900 text-right" x-text="selectedNotice?.updated_bs || 'N/A'"></dd>
                            </div>
                        </dl>
                    </article>
                </section>

                <section class="rounded-2xl border border-slate-200 p-4">
                    <h4 class="text-sm font-black text-slate-900">Content</h4>
                    <div class="prose prose-sm mt-3 max-w-none text-slate-600" x-html="selectedNotice?.content_html || ''"></div>
                </section>

                <section class="rounded-2xl border border-slate-200 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <h4 class="text-sm font-black text-slate-900">Attachments</h4>
                        <span class="text-xs font-semibold text-slate-400" x-text="(selectedNotice?.attachments?.length || 0) + ' file(s)'"></span>
                    </div>

                    <div class="mt-3 space-y-2" x-show="selectedNotice?.attachments?.length">
                        <template x-for="file in selectedNotice?.attachments || []" :key="file.id">
                            <a :href="file.url" target="_blank" class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 transition hover:border-[#8B0000]/20 hover:bg-rose-50/50">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900" x-text="file.name"></p>
                                    <p class="mt-1 text-xs text-slate-500" x-text="file.meta"></p>
                                </div>
                                <span class="rounded-full bg-white px-2.5 py-1 text-[11px] font-bold text-slate-500 ring-1 ring-slate-200" x-text="file.extension"></span>
                            </a>
                        </template>
                    </div>

                    <p class="mt-3 text-sm text-slate-500" x-show="!selectedNotice?.attachments?.length">No files attached to this {{ $workspace['singular_label'] }}.</p>
                </section>

                <section class="rounded-2xl border border-slate-200 p-4">
                    <div class="grid gap-2 sm:grid-cols-3">
                        <a :href="selectedNotice?.show_url || '#'" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Open Page</a>
                        <template x-if="selectedNotice && selectedNotice.created_by === {{ auth()->id() }}">
                        <a :href="selectedNotice?.edit_url || '#'" class="inline-flex items-center justify-center rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#750000]">{{ $workspace['edit_button_label'] }}</a>
                        </template>
                        <template x-if="selectedNotice && selectedNotice.created_by === {{ auth()->id() }}">
                            <form method="POST" :action="selectedNotice?.delete_url || '#'" onsubmit="return confirm('{{ $workspace['delete_confirm_label'] }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-bold text-rose-700 transition hover:bg-rose-100">Delete</button>
                            </form>
                        </template>
                    </div>
                </section>
            </div>
        </aside>
    </div>
</div>
@endsection
