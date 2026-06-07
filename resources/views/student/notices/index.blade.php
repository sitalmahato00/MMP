@extends('layouts.app')
@section('title', 'Notices')

@section('content')
<div x-data="{
    view: localStorage.getItem('mmp_student_notices_view') ?? 'table',
    setView(v) { this.view = v; localStorage.setItem('mmp_student_notices_view', v); }
}" class="space-y-5">

{{-- ── HEADER ─────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900">Notices</h1>
        <p class="mt-0.5 text-sm text-slate-500">
            {{ $student->program->department->name }} — stay updated with important announcements
        </p>
    </div>
</div>

{{-- Stats Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    @php
        $kpis = [
            ['label'=>'Total Notices',     'value'=>$totalNotices,      'icon'=>'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9', 'color'=>'blue',    'tag'=>'Total'],
            ['label'=>'Department',        'value'=>$departmentNotices, 'icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',                                                                                                                                                                  'color'=>'violet',  'tag'=>'Dept'],
            ['label'=>'Published',         'value'=>$publishedNotices,  'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',                                                                                                                                                                                                                                                'color'=>'emerald', 'tag'=>'Live'],
        ];
    @endphp
    @foreach($kpis as $kpi)
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-{{ $kpi['color'] }}-50">
                <svg class="w-5 h-5 text-{{ $kpi['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $kpi['icon'] }}"/>
                </svg>
            </div>
            <span class="rounded-full bg-{{ $kpi['color'] }}-50 px-2 py-0.5 text-[11px] font-bold text-{{ $kpi['color'] }}-700">{{ $kpi['tag'] }}</span>
        </div>
        <p class="mt-3 text-3xl font-black text-slate-900">{{ number_format($kpi['value']) }}</p>
        <p class="mt-0.5 text-xs text-slate-500">{{ $kpi['label'] }}</p>
    </div>
    @endforeach
</div>

{{-- Filters & Actions --}}
<form method="GET" action="{{ route('student.notices.index') }}"
      class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
        {{-- Search --}}
        <div class="relative lg:col-span-2">
            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search notices..."
                   class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-700 outline-none focus:border-[#1d4ed8] focus:ring-2 focus:ring-blue-100"/>
        </div>
        {{-- Type --}}
        <select name="type" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#1d4ed8] focus:ring-2 focus:ring-blue-100">
            <option value="internal" @selected(request('type', 'internal') === 'internal')>Department Notices</option>
        </select>
        {{-- Status (only for internal notices) --}}
        <select name="status" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#1d4ed8] focus:ring-2 focus:ring-blue-100" {{ false ? 'disabled' : '' }}>
            <option value="">All Status</option>
            <option value="published" @selected(request('status') === 'published')>Published</option>
        </select>
    </div>
    <div class="flex gap-2 mt-3">
        <button type="submit"
                class="rounded-xl bg-[#1d4ed8] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#1e40af] transition whitespace-nowrap">
            Apply Filters
        </button>
        @if(request()->hasAny(['search','type','status']))
        <a href="{{ route('student.notices.index') }}"
           class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-500 hover:bg-slate-50 transition" title="Clear filters">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </a>
        @endif
    </div>
</form>

{{-- ── MAIN CONTENT PANEL ──────────────────────────────────── --}}
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

    {{-- Panel header: result count + view toggle --}}
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5">
        <p class="text-sm text-slate-500">
            @if(false)
                @if($notices->count() > 0)
                    Showing <span class="font-semibold text-slate-700">{{ $notices->count() }}</span> CTEVT notices
                @else
                    No CTEVT notices match your filters
                @endif
            @else
                @if($notices->total() > 0)
                    Showing <span class="font-semibold text-slate-700">{{ $notices->firstItem() }}–{{ $notices->lastItem() }}</span>
                    of <span class="font-semibold text-slate-700">{{ number_format($notices->total()) }}</span> notices
                @else
                    No notices match your filters
                @endif
            @endif
        </p>

        {{-- View toggle --}}
        <div class="flex items-center rounded-xl border border-slate-200 p-1 gap-0.5 flex-shrink-0">
            <button type="button" @click="setView('table')"
                    :class="view === 'table' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-700'"
                    class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"/></svg>
                Table
            </button>
            <button type="button" @click="setView('cards')"
                    :class="view === 'cards' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-700'"
                    class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Cards
            </button>
        </div>
    </div>

    {{-- ── TABLE VIEW ─────────────────────────────────────── --}}
    <div x-show="view === 'table'" x-cloak>
        @if((false && $notices->isEmpty()) || (request('type') !== 'ctevt' && $notices->isEmpty()))
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">No notices found</h3>
                <p class="mt-1 text-sm text-slate-500 max-w-xs">Try adjusting your search or filters to find notices.</p>
            </div>
        @else
        <div class="mmp-table-wrap">
            <table class="mmp-table w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Title</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Type</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Source</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Date</th>
                        <th class="px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @if(false)
                        @foreach($notices as $notice)
                        <tr class="group hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-amber-50">
                                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-900 truncate text-sm">{{ $notice['title'] }}</p>
                                        <p class="text-[11px] text-slate-400 truncate">{{ Str::limit(strip_tags($notice['content']), 60) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                    CTEVT
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-500 hidden lg:table-cell">
                                CTEVT Official
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-400 hidden lg:table-cell">
                                {{ bsDate($notice['published_date'], 'Y, F d') }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1">
                                    @if(isset($notice['file_url']) && $notice['file_url'])
                                        <a href="{{ $notice['file_url'] }}" target="_blank"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-blue-50 hover:text-blue-600 transition" title="Download">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        @foreach($notices as $notice)
                        <tr class="group hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-blue-50">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="font-semibold text-slate-900 truncate text-sm">{{ $notice->title }}</p>
                                            @if($notice->attachment || $notice->attachments->count() > 0)
                                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Has attachments">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-slate-400 truncate">{{ Str::limit(strip_tags($notice->content), 60) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                    {{ ucfirst($notice->type) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-500 hidden lg:table-cell">
                                @if($notice->department)
                                    {{ $notice->department->name }}
                                @elseif($notice->author)
                                    {{ $notice->author->name }}
                                @else
                                    General
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-400 hidden lg:table-cell">
                                {{ bsDate($notice->created_at, 'Y, F d') }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('student.notices.show', $notice) }}"
                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-blue-50 hover:text-blue-600 transition" title="View">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        @if(request('type') !== 'ctevt' && $notices->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">{{ $notices->links() }}</div>
        @endif
        @endif
    </div>

    {{-- ── CARD VIEW ──────────────────────────────────────── --}}
    <div x-show="view === 'cards'" x-cloak>
        @if((false && $notices->isEmpty()) || (request('type') !== 'ctevt' && $notices->isEmpty()))
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <h3 class="text-base font-bold text-slate-800">No notices found</h3>
                <p class="mt-1 text-sm text-slate-500">Try adjusting your filters to find notices.</p>
            </div>
        @else
        <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @if(false)
                @foreach($notices as $notice)
                <div class="group relative rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-slate-300 transition-all duration-150">
                    {{-- Icon --}}
                    <div class="flex flex-col items-center text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"/>
                            </svg>
                        </div>
                        <h3 class="mt-3 text-sm font-bold text-slate-900 leading-tight text-center line-clamp-2">{{ $notice['title'] }}</h3>
                        <p class="mt-1 text-[11px] text-slate-400 line-clamp-2">{{ Str::limit(strip_tags($notice['content']), 80) }}</p>
                    </div>
                    {{-- Badges --}}
                    <div class="mt-3 flex flex-wrap items-center justify-center gap-1.5">
                        <span class="rounded-lg bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700">CTEVT</span>
                    </div>
                    {{-- Meta info --}}
                    <div class="mt-3 space-y-0.5 text-center">
                        <p class="text-[11px] text-slate-400">CTEVT Official</p>
                        <p class="text-[11px] text-slate-400">{{ bsDate($notice['published_date'], 'Y, F d') }}</p>
                    </div>
                    {{-- Actions --}}
                    <div class="mt-4 grid grid-cols-1 gap-2">
                        @if(isset($notice['file_url']) && $notice['file_url'])
                            <a href="{{ $notice['file_url'] }}" target="_blank"
                               class="rounded-lg bg-slate-900 py-1.5 text-center text-xs font-bold text-white hover:bg-slate-700 transition">Download</a>
                        @else
                            <span class="rounded-lg bg-slate-100 py-1.5 text-center text-xs font-semibold text-slate-400 cursor-not-allowed">No File</span>
                        @endif
                    </div>
                </div>
                @endforeach
            @else
                @foreach($notices as $notice)
                <div class="group relative rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-slate-300 transition-all duration-150">
                    {{-- Icon --}}
                    <div class="flex flex-col items-center text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                        <h3 class="mt-3 text-sm font-bold text-slate-900 leading-tight text-center line-clamp-2">{{ $notice->title }}</h3>
                        <p class="mt-1 text-[11px] text-slate-400 line-clamp-2">{{ Str::limit(strip_tags($notice->content), 80) }}</p>
                    </div>
                    {{-- Badges --}}
                    <div class="mt-3 flex flex-wrap items-center justify-center gap-1.5">
                        <span class="rounded-lg bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700">{{ ucfirst($notice->type) }}</span>
                        @if($notice->is_published)
                            <span class="rounded-lg bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">Published</span>
                        @endif
                    </div>
                    {{-- Meta info --}}
                    <div class="mt-3 space-y-0.5 text-center">
                        @if($notice->department)
                            <p class="text-[11px] text-slate-400">{{ $notice->department->name }}</p>
                        @elseif($notice->author)
                            <p class="text-[11px] text-slate-400">{{ $notice->author->name }}</p>
                        @else
                            <p class="text-[11px] text-slate-400">General</p>
                        @endif
                        <p class="text-[11px] text-slate-400">{{ bsDate($notice->created_at, 'Y, F d') }}</p>
                    </div>
                    {{-- Actions --}}
                    <div class="mt-4 grid grid-cols-1 gap-2">
                        <a href="{{ route('student.notices.show', $notice) }}"
                           class="rounded-lg bg-slate-900 py-1.5 text-center text-xs font-bold text-white hover:bg-slate-700 transition">View Notice</a>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
        @if(request('type') !== 'ctevt' && $notices->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">{{ $notices->links() }}</div>
        @endif
        @endif
    </div>

</div>{{-- /panel --}}

</div>{{-- /container --}}
@endsection