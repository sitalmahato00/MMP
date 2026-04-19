@extends('layouts.app')
@section('title', 'Notices')

@section('content')
@php
    $typeMeta = [
        'general' => ['label' => 'General', 'badge' => 'bg-slate-100 text-slate-700 ring-slate-200', 'accent' => 'border-l-slate-300'],
        'exam' => ['label' => 'Exam / Result', 'badge' => 'bg-rose-50 text-rose-700 ring-rose-200', 'accent' => 'border-l-rose-400'],
        'department' => ['label' => 'Department', 'badge' => 'bg-indigo-50 text-indigo-700 ring-indigo-200', 'accent' => 'border-l-indigo-400'],
        'class' => ['label' => 'Class / Section', 'badge' => 'bg-amber-50 text-amber-700 ring-amber-200', 'accent' => 'border-l-amber-400'],
        'teachers' => ['label' => 'Teachers', 'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'accent' => 'border-l-emerald-400'],
        'news' => ['label' => 'News', 'badge' => 'bg-violet-50 text-violet-700 ring-violet-200', 'accent' => 'border-l-violet-400'],
        'event' => ['label' => 'Event', 'badge' => 'bg-sky-50 text-sky-700 ring-sky-200', 'accent' => 'border-l-sky-400'],
    ];

    $statusMeta = [
        'published' => ['label' => 'Published', 'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
        'scheduled' => ['label' => 'Scheduled', 'badge' => 'bg-amber-50 text-amber-700 ring-amber-200'],
        'draft' => ['label' => 'Draft', 'badge' => 'bg-slate-100 text-slate-700 ring-slate-200'],
    ];

    $overviewCards = [
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
    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-7">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-slate-950">Notice Board</h1>
                <p class="mt-2 text-sm text-slate-500">Manage published, scheduled, and draft notices from the same admin workspace used across the rest of the portal.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.notices.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#750000]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Add Notice
                </a>
            </div>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
            @foreach($overviewCards as $card)
                @php $tone = $toneStyles[$card['tone']] ?? $toneStyles['slate']; @endphp
                <article class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">{{ $card['label'] }}</p>
                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold {{ $tone['chip'] }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $tone['dot'] }}"></span>
                            Live
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-black text-slate-950">{{ number_format($card['value']) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Current notice board snapshot</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <form method="GET" action="{{ route('admin.notices.index') }}" class="grid gap-3 lg:grid-cols-12">
            <div class="lg:col-span-3">
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search title or content..."
                    class="w-full rounded-full border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100"
                >
            </div>

            <div class="lg:col-span-2">
                <select name="type" class="w-full rounded-full border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                    <option value="">All Types</option>
                    @foreach($typeMeta as $type => $meta)
                        <option value="{{ $type }}" @selected(request('type') === $type)>{{ $meta['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <select name="status" class="w-full rounded-full border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                    <option value="">All Status</option>
                    @foreach($statusMeta as $status => $meta)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $meta['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <select name="department_id" class="w-full rounded-full border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        @php
                            $departmentLabel = $department->code
                                ? $department->code . ' - ' . $department->name
                                : $department->name;
                        @endphp
                        <option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>{{ $departmentLabel }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-3 flex gap-2">
                <button type="submit" class="w-full rounded-full bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#750000]">Search</button>
                <a href="{{ route('admin.notices.index') }}" class="w-full rounded-full border border-slate-200 bg-white px-4 py-2.5 text-center text-sm font-bold text-slate-600 transition hover:bg-slate-50">Reset</a>
            </div>

            <div class="lg:col-span-2">
                <x-bs-date-picker name="date_from" :value="request('date_from')" placeholder="From BS date" class="rounded-full border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100"/>
            </div>

            <div class="lg:col-span-2">
                <x-bs-date-picker name="date_to" :value="request('date_to')" placeholder="To BS date" class="rounded-full border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100"/>
            </div>

            <div class="lg:col-span-8 flex items-center text-xs text-slate-500">
                Filter by published date in BS. Draft notices without a publish date use their created date.
            </div>
        </form>
    </section>

    <section class="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
            <p class="text-sm text-slate-500">
                Showing <span class="font-semibold text-slate-700">{{ $notices->firstItem() ?? 0 }}-{{ $notices->lastItem() ?? 0 }}</span>
                of <span class="font-semibold text-slate-700">{{ number_format($notices->total()) }}</span> notices
            </p>

            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="rounded-full bg-slate-100 px-3 py-1.5 font-semibold text-slate-600">Published list shows BS dates</span>
                <span class="rounded-full bg-slate-100 px-3 py-1.5 font-semibold text-slate-600">Open preview drawer from any row</span>
            </div>
        </div>

        <div class="hidden lg:block overflow-hidden">
            <div class="mmp-table-wrap">
                <table class="mmp-table divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50/80">
                        <tr class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                            <th class="px-4 py-3 text-left">Notice</th>
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
                                <td class="px-4 py-3.5 text-xs text-slate-500">{{ $publishedLabel ?: '—' }}</td>
                                <td class="px-4 py-3.5 text-center">
                                    @if(($notice->attachments_count ?? 0) > 0)
                                        <span class="inline-flex rounded-full bg-sky-50 px-2.5 py-1 text-[11px] font-bold text-sky-700 ring-1 ring-sky-200">{{ $notice->attachments_count }}</span>
                                    @else
                                        <span class="text-xs text-slate-300">—</span>
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
                                        <a href="{{ route('admin.notices.show', $notice) }}" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600" title="Open Page">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3h7m0 0v7m0-7L10 14"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5h5M5 5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-5"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.notices.edit', $notice) }}" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-amber-50 hover:text-amber-600" title="Edit">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('admin.notices.destroy', $notice) }}" onsubmit="return confirm('Delete {{ addslashes($notice->title) }}? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-red-50 hover:text-red-600" title="Delete">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-empty-state title="No notices found" message="Adjust your filters or create a new notice to populate the board." action="{{ route('admin.notices.create') }}" actionLabel="Add Notice"/>
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
                            <p class="mt-2 text-[11px] text-slate-400">{{ $notice->author?->name ?? 'System' }} · {{ bsDateTime($notice->published_at ?? $notice->created_at, 'Y, F d', 'h:i A') }}</p>
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
                        <a href="{{ route('admin.notices.show', $notice) }}" class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700">View</a>
                        <a href="{{ route('admin.notices.edit', $notice) }}" class="rounded-xl bg-[#8B0000] px-3 py-1.5 text-xs font-bold text-white">Edit</a>
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-slate-200 bg-slate-50">
                    <x-empty-state title="No notices found" message="Adjust your filters or create a new notice to populate the board." action="{{ route('admin.notices.create') }}" actionLabel="Add Notice"/>
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
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Notice Preview</p>
                        <h3 class="mt-1 text-xl font-black text-slate-900" x-text="selectedNotice?.title"></h3>
                    </div>
                    <button type="button" @click="closeDrawer()" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-600">Close</button>
                </div>
            </div>

            <div class="space-y-5 p-5" x-show="selectedNotice">
                <section class="grid gap-4 md:grid-cols-2">
                    <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <h4 class="text-sm font-black text-slate-900">Notice Details</h4>
                        <dl class="mt-3 space-y-2 text-sm">
                            <div class="flex justify-between gap-2">
                                <dt class="text-slate-500">Type</dt>
                                <dd class="font-semibold text-slate-900" x-text="selectedNotice?.type_label || '—'"></dd>
                            </div>
                            <div class="flex justify-between gap-2">
                                <dt class="text-slate-500">Status</dt>
                                <dd class="font-semibold text-slate-900" x-text="selectedNotice?.status_label || '—'"></dd>
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
                                <dd class="font-semibold text-slate-900 text-right" x-text="selectedNotice?.published_bs || '—'"></dd>
                            </div>
                            <div class="flex justify-between gap-2">
                                <dt class="text-slate-500">Updated</dt>
                                <dd class="font-semibold text-slate-900 text-right" x-text="selectedNotice?.updated_bs || '—'"></dd>
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

                    <p class="mt-3 text-sm text-slate-500" x-show="!selectedNotice?.attachments?.length">No files attached to this notice.</p>
                </section>

                <section class="rounded-2xl border border-slate-200 p-4">
                    <div class="grid gap-2 sm:grid-cols-3">
                        <a :href="selectedNotice?.show_url || '#'" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Open Page</a>
                        <a :href="selectedNotice?.edit_url || '#'" class="inline-flex items-center justify-center rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#750000]">Edit Notice</a>
                        <form method="POST" :action="selectedNotice?.delete_url || '#'" onsubmit="return confirm('Delete this notice? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-bold text-rose-700 transition hover:bg-rose-100">Delete</button>
                        </form>
                    </div>
                </section>
            </div>
        </aside>
    </div>
</div>
@endsection
