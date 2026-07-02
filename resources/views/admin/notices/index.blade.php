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
            @php
            $noticeGrads = ['135deg,#475569,#64748B','135deg,#10B981,#22C55E','135deg,#F59E0B,#FBBF24','135deg,#52525B,#71717A','135deg,#DC2626,#EF4444','135deg,#0284C7,#38BDF8','135deg,#7C3AED,#A855F7'];
            $noticeIcons = ['M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z','M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z','M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z','M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5','M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2','M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13','M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'];
            @endphp
            @foreach($overviewCards as $ci => $card)
                <div class="kpi-card relative overflow-hidden rounded-2xl p-3.5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                     style="background: linear-gradient({{ $noticeGrads[$ci % count($noticeGrads)] }});">
                    <div class="pointer-events-none absolute -right-3 -top-3 h-16 w-16 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-2.5">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-white/20 backdrop-blur-sm">
                            <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $noticeIcons[$ci % count($noticeIcons)] }}"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-lg font-black leading-tight text-white">{{ number_format($card['value']) }}</p>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-white/80 truncate">{{ $card['label'] }}</p>
                        </div>
                    </div>
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

    <section class="rounded-lg border border-slate-200 bg-white shadow-sm" x-data="{ viewMode: 'table' }">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <p class="text-sm text-slate-600">
                Showing {{ $notices->firstItem() ?? 0 }}-{{ $notices->lastItem() ?? 0 }} of {{ number_format($notices->total()) }}
            </p>
            
            @if($workspace['is_news_events'])
            <div class="flex items-center gap-2">
                <button type="button" @click="viewMode = 'table'" :class="viewMode === 'table' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                </button>
                <button type="button" @click="viewMode = 'cards'" :class="viewMode === 'cards' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg>
                </button>
            </div>
            @endif
        </div>

        <div class="hidden lg:block overflow-hidden" x-show="viewMode === 'table'" x-cloak>
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
                                        <x-table-actions
                                            :show="route($workspace['route_prefix'] . '.show', $notice)"
                                            :edit="$notice->created_by === auth()->id() ? route($workspace['route_prefix'] . '.edit', $notice) : null"
                                            :destroy="$notice->created_by === auth()->id() ? route($workspace['route_prefix'] . '.destroy', $notice) : null"
                                            name="{{ addslashes($notice->title) }}"
                                        />
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

        {{-- Card View for News & Events --}}
        @if($workspace['is_news_events'])
        <div class="p-4" x-show="viewMode === 'cards'" x-cloak>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($notices as $notice)
                    @php
                        $type = $typeMeta[$notice->type] ?? ['label' => \Illuminate\Support\Str::headline($notice->type), 'badge' => 'bg-slate-100 text-slate-700 ring-slate-200', 'accent' => 'border-l-slate-300'];
                        $statusKey = ! $notice->is_published
                            ? 'draft'
                            : (($notice->published_at && $notice->published_at->isFuture()) ? 'scheduled' : 'published');
                        $status = $statusMeta[$statusKey] ?? $statusMeta['draft'];
                        $noticeDate = $notice->published_at ?? $notice->created_at;
                        $firstImage = $notice->attachments->where('is_image', true)->first();
                        $hasVideo = $notice->attachments->where('file_type', 'mp4')->count() > 0 || 
                                   $notice->attachments->where('file_type', 'webm')->count() > 0;
                    @endphp
                    <article class="group block rounded-lg border border-slate-200 bg-white overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                        {{-- Image/Video Thumbnail --}}
                        <div class="relative h-48 bg-gradient-to-br from-blue-50 to-blue-100 overflow-hidden">
                            @if($firstImage)
                                <img src="{{ $firstImage->url }}" alt="{{ $notice->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-20 h-20 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                            
                            {{-- Video indicator --}}
                            @if($hasVideo)
                                <div class="absolute inset-0 bg-black/30 flex items-center justify-center">
                                    <div class="w-16 h-16 rounded-full bg-white/90 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-blue-600 ml-1" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z"/>
                                        </svg>
                                    </div>
                                </div>
                            @endif
                            
                            {{-- Date badge --}}
                            <div class="absolute top-3 left-3 bg-blue-600 text-white px-3 py-1.5 rounded-lg shadow-lg">
                                <div class="text-xs font-bold">{{ bsDate($noticeDate, 'F') }}</div>
                                <div class="text-2xl font-black leading-none">{{ bsDate($noticeDate, 'd') }}</div>
                            </div>
                            
                            {{-- Type & Status badges --}}
                            <div class="absolute top-3 right-3 flex flex-col gap-2">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $type['badge'] }}">{{ $type['label'] }}</span>
                                <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $status['badge'] }}">{{ $status['label'] }}</span>
                            </div>
                        </div>
                        
                        {{-- Content --}}
                        <div class="p-5">
                            <h3 class="text-lg font-bold text-slate-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
                                {{ $notice->title }}
                            </h3>
                            
                            @if($notice->content)
                                <p class="text-sm text-slate-600 mb-3 line-clamp-2">
                                    {{ Str::limit(strip_tags($notice->content), 120) }}
                                </p>
                            @endif
                            
                            <div class="flex items-center justify-between text-xs text-slate-500 mb-3">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ bsDate($noticeDate, 'Y, F d') }}
                                </span>
                                
                                @if($notice->attachments->count() > 0)
                                    <span class="flex items-center gap-1 text-blue-600 font-semibold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $notice->attachments->count() }}
                                    </span>
                                @endif
                            </div>
                            
                            {{-- Action buttons --}}
                            <div class="flex items-center gap-2">
                                <button type="button" @click="openDrawer({{ $notice->id }})" class="flex-1 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">Preview</button>
                                <a href="{{ route($workspace['route_prefix'] . '.show', $notice) }}" class="flex-1 rounded-lg border border-slate-200 px-3 py-2 text-center text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-600 transition">View</a>
                                @if($notice->created_by === auth()->id())
                                    <a href="{{ route($workspace['route_prefix'] . '.edit', $notice) }}" class="rounded-lg bg-[#8B0000] px-3 py-2 text-xs font-bold text-white hover:bg-[#750000] transition">Edit</a>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full py-16 text-center">
                        <p class="text-4xl mb-4">📰</p>
                        <p class="font-semibold text-slate-500">{{ $workspace['empty_title'] }}</p>
                        <p class="mt-2 text-sm text-slate-400">{{ $workspace['empty_message'] }}</p>
                    </div>
                @endforelse
            </div>
        </div>
        @endif

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

                @if($workspace['is_news_events'])
                {{-- Media Gallery for News & Events --}}
                <section class="rounded-2xl border border-slate-200 p-4" x-show="selectedNotice?.has_media">
                    <div class="flex items-center justify-between gap-3">
                        <h4 class="text-sm font-black text-slate-900">Media Gallery</h4>
                        <span class="text-xs font-semibold text-slate-400" x-text="(selectedNotice?.media_count || 0) + ' item(s)'"></span>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-3" x-show="selectedNotice?.media?.length">
                        <template x-for="media in selectedNotice?.media || []" :key="media.id">
                            <div class="relative rounded-lg overflow-hidden border border-slate-200 bg-slate-50">
                                <template x-if="media.is_image">
                                    <img :src="media.url" :alt="media.name" class="w-full h-40 object-cover">
                                </template>
                                <template x-if="media.is_video">
                                    <video :src="media.url" controls class="w-full h-40 bg-black"></video>
                                </template>
                                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2">
                                    <p class="text-xs text-white font-semibold truncate" x-text="media.name"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </section>
                @endif

                <section class="rounded-2xl border border-slate-200 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <h4 class="text-sm font-black text-slate-900">{{ $workspace['is_news_events'] ? 'Documents' : 'Attachments' }}</h4>
                        <span class="text-xs font-semibold text-slate-400" x-text="(selectedNotice?.documents?.length || 0) + ' file(s)'"></span>
                    </div>

                    <div class="mt-3 space-y-2" x-show="selectedNotice?.documents?.length">
                        <template x-for="file in selectedNotice?.documents || []" :key="file.id">
                            <a :href="file.url" target="_blank" class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 transition hover:border-[#8B0000]/20 hover:bg-rose-50/50">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900" x-text="file.name"></p>
                                    <p class="mt-1 text-xs text-slate-500" x-text="file.meta"></p>
                                </div>
                                <span class="rounded-full bg-white px-2.5 py-1 text-[11px] font-bold text-slate-500 ring-1 ring-slate-200" x-text="file.extension"></span>
                            </a>
                        </template>
                    </div>

                    <p class="mt-3 text-sm text-slate-500" x-show="!selectedNotice?.documents?.length">No files attached to this {{ $workspace['singular_label'] }}.</p>
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
