@extends('layouts.app')
@section('title', 'Notices')

@section('content')
<div x-data="{
    view: localStorage.getItem('mmp_hod_notices_view') ?? 'table',
    setView(v) { this.view = v; localStorage.setItem('mmp_hod_notices_view', v); }
}" class="space-y-5">

{{-- ── HEADER ─────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900">Notices</h1>
        <p class="mt-0.5 text-sm text-slate-500">
            {{ $department->name }} — manage department and program notices
        </p>
    </div>
    <a href="{{ route('hod.notices.create') }}"
       class="inline-flex items-center gap-2 rounded-xl bg-[#1d4ed8] px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-[#1e40af] transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Create Notice
    </a>
</div>

{{-- Stats Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    @php
        $kpis = [
            ['label'=>'Total Notices',   'value'=>$totalNotices,      'icon'=>'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9', 'color'=>'blue',    'tag'=>'Total'],
            ['label'=>'Published',       'value'=>$publishedNotices,  'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',                                                                                                                                                                                                                                                'color'=>'emerald', 'tag'=>'Live'],
            ['label'=>'Drafts',          'value'=>$draftNotices,      'icon'=>'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',                                                                                                                                                                                   'color'=>'amber',   'tag'=>'Draft'],
            ['label'=>'Department',      'value'=>$departmentNotices, 'icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',                                                                                                                                                                  'color'=>'violet',  'tag'=>'Dept'],
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
<form method="GET" action="{{ route('hod.notices.index') }}"
      class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-5">
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
            <option value="">All Types</option>
            <option value="general" @selected(request('type') === 'general')>General</option>
            <option value="exam" @selected(request('type') === 'exam')>Exam</option>
            <option value="department" @selected(request('type') === 'department')>Department</option>
            <option value="program" @selected(request('type') === 'program')>Program</option>
            <option value="academic" @selected(request('type') === 'academic')>Academic</option>
        </select>
        {{-- Status --}}
        <select name="status" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#1d4ed8] focus:ring-2 focus:ring-blue-100">
            <option value="">All Status</option>
            <option value="published" @selected(request('status') === 'published')>Published</option>
            <option value="draft" @selected(request('status') === 'draft')>Draft</option>
        </select>
        {{-- Program --}}
        <select name="program_id" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#1d4ed8] focus:ring-2 focus:ring-blue-100">
            <option value="">All Programs</option>
            @foreach($programs as $program)
                <option value="{{ $program->id }}" @selected(request('program_id') == $program->id)>{{ $program->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex gap-2 mt-3">
        <button type="submit"
                class="rounded-xl bg-[#1d4ed8] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#1e40af] transition whitespace-nowrap">
            Apply Filters
        </button>
        @if(request()->hasAny(['search','type','status','program_id']))
        <a href="{{ route('hod.notices.index') }}"
           class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-500 hover:bg-slate-50 transition" title="Clear filters">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </a>
        @endif
    </div>
</form>

@if(session('success'))
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
        <div class="flex items-center gap-3">
            <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
        </div>
    </div>
@endif

{{-- ── MAIN CONTENT PANEL ──────────────────────────────────── --}}
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

    {{-- Panel header: result count + view toggle --}}
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5">
        <p class="text-sm text-slate-500">
            @if($notices->total() > 0)
                Showing <span class="font-semibold text-slate-700">{{ $notices->firstItem() }}–{{ $notices->lastItem() }}</span>
                of <span class="font-semibold text-slate-700">{{ number_format($notices->total()) }}</span> notices
            @else
                No notices match your filters
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
        @if($notices->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">No notices found</h3>
                <p class="mt-1 text-sm text-slate-500 max-w-xs">Try adjusting your search or filters, or create a new notice.</p>
                <a href="{{ route('hod.notices.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#1d4ed8] px-4 py-2 text-sm font-bold text-white hover:bg-[#1e40af] transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Create Notice
                </a>
            </div>
        @else
        <div class="mmp-table-wrap">
            <table class="mmp-table w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Title</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Type</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Program</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Status</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden xl:table-cell">Author</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Date</th>
                        <th class="px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($notices as $notice)
                    <tr class="group hover:bg-slate-50/60 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3 min-w-0">
                                @if($notice->cover_image_url)
                                    <img src="{{ $notice->cover_image_url }}" alt="{{ $notice->title }}" class="h-10 w-14 object-cover rounded-xl border border-slate-200 flex-shrink-0 shadow-xs">
                                @else
                                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-blue-50">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-900 truncate text-sm">{{ $notice->title }}</p>
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
                            {{ $notice->program?->name ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5">
                            @if($notice->is_published)
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-60"></span>
                                    Published
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-60"></span>
                                    Draft
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-500 hidden xl:table-cell">
                            {{ $notice->author?->name ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-400 hidden lg:table-cell">
                            {{ bsDate($notice->created_at, 'Y, F d') }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('hod.notices.show', $notice) }}"
                                   class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-blue-50 hover:text-blue-600 transition" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                @if($notice->created_by === auth()->id())
                                    <a href="{{ route('hod.notices.edit', $notice) }}"
                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-amber-50 hover:text-amber-600 transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('hod.notices.destroy', $notice) }}"
                                          onsubmit="return confirm('Delete {{ addslashes($notice->title) }}? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 transition" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($notices->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">{{ $notices->links() }}</div>
        @endif
        @endif
    </div>

    {{-- ── CARD VIEW ──────────────────────────────────────── --}}
    <div x-show="view === 'cards'" x-cloak>
        @if($notices->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <h3 class="text-base font-bold text-slate-800">No notices found</h3>
                <p class="mt-1 text-sm text-slate-500">Try adjusting your filters or create a new notice.</p>
            </div>
        @else
        <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($notices as $notice)
            <div class="group relative rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:shadow-md hover:border-slate-300 transition-all duration-150 flex flex-col justify-between">
                <div>
                    {{-- Cover Image or Icon --}}
                    @if($notice->cover_image_url)
                        <div class="w-full h-32 rounded-xl overflow-hidden bg-slate-900 mb-3 border border-slate-100 flex items-center justify-center">
                            <img src="{{ $notice->cover_image_url }}" alt="{{ $notice->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                    @else
                        <div class="flex flex-col items-center text-center pt-2">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            </div>
                        </div>
                    @endif
                    <h3 class="mt-1 text-sm font-bold text-slate-900 leading-tight text-center line-clamp-2">{{ $notice->title }}</h3>
                    <p class="mt-1 text-[11px] text-slate-400 line-clamp-2 text-center">{{ Str::limit(strip_tags($notice->content), 80) }}</p>
                </div>
                {{-- Badges --}}
                <div class="mt-3 flex flex-wrap items-center justify-center gap-1.5">
                    <span class="rounded-lg bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700">{{ ucfirst($notice->type) }}</span>
                    @if($notice->is_published)
                        <span class="rounded-lg bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">Published</span>
                    @else
                        <span class="rounded-lg bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700">Draft</span>
                    @endif
                </div>
                {{-- Meta info --}}
                <div class="mt-3 space-y-0.5 text-center">
                    @if($notice->program)
                        <p class="text-xs text-slate-600 font-medium truncate">{{ $notice->program->name }}</p>
                    @endif
                    <p class="text-[11px] text-slate-400">{{ $notice->author?->name ?? '—' }}</p>
                    <p class="text-[11px] text-slate-400">{{ bsDate($notice->created_at, 'Y, F d') }}</p>
                </div>
                {{-- Actions --}}
                @if($notice->created_by === auth()->id())
                    <div class="mt-4 grid grid-cols-3 gap-2">
                        <a href="{{ route('hod.notices.show', $notice) }}"
                           class="rounded-lg border border-slate-200 py-1.5 text-center text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">View</a>
                        <a href="{{ route('hod.notices.edit', $notice) }}"
                           class="rounded-lg bg-slate-900 py-1.5 text-center text-xs font-bold text-white hover:bg-slate-700 transition">Edit</a>
                        <form method="POST" action="{{ route('hod.notices.destroy', $notice) }}"
                              onsubmit="return confirm('Delete {{ addslashes($notice->title) }}? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="w-full rounded-lg bg-red-600 py-1.5 text-center text-xs font-bold text-white hover:bg-red-700 transition">Delete</button>
                        </form>
                    </div>
                @else
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <a href="{{ route('hod.notices.show', $notice) }}"
                           class="rounded-lg border border-slate-200 py-1.5 text-center text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">View</a>
                        <span class="rounded-lg bg-slate-100 py-1.5 text-center text-xs font-semibold text-slate-400 cursor-not-allowed">Edit</span>
                    </div>
                @endif
            </div>
            @endforeach
        </div>
        @if($notices->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">{{ $notices->links() }}</div>
        @endif
        @endif
    </div>

</div>{{-- /panel --}}

</div>{{-- /container --}}
@endsection
