@extends('layouts.app')
@section('title', 'Facilities & Resources')

@section('content')
<div x-data="{
    view: localStorage.getItem('mmp_hod_content_view') ?? 'table',
    setView(v) { this.view = v; localStorage.setItem('mmp_hod_content_view', v); }
}" class="space-y-5">

{{-- ── HEADER ─────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900">
            {{ $department->name }} — Facilities & Resources
        </h1>
        <p class="mt-0.5 text-sm text-slate-500">
            Manage department facilities and resources for {{ $department->name }}
        </p>
    </div>
    <a href="{{ route('hod.facilities.create') }}"
       class="inline-flex items-center gap-2 rounded-xl bg-[#1d4ed8] px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-[#1e40af] transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Create Resource
    </a>
</div>

{{-- Stats Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    @php
        $kpis = [
            ['label'=>'Total Pages',   'value'=>$totalPages,      'icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color'=>'blue',    'tag'=>'Total'],
            ['label'=>'Published',     'value'=>$publishedPages,  'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',                                                                                                                                                'color'=>'emerald', 'tag'=>'Live'],
            ['label'=>'Drafts',        'value'=>$draftPages,      'icon'=>'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',                                                                                   'color'=>'amber',   'tag'=>'Draft'],
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
<form method="GET" action="{{ route('hod.facilities.index') }}"
      class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
        {{-- Search --}}
        <div class="relative lg:col-span-2">
            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search pages..."
                   class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-700 outline-none focus:border-[#1d4ed8] focus:ring-2 focus:ring-blue-100"/>
        </div>
        {{-- Status --}}
        <select name="status" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#1d4ed8] focus:ring-2 focus:ring-blue-100">
            <option value="">All Status</option>
            <option value="published" @selected(request('status') === 'published')>Published</option>
            <option value="draft" @selected(request('status') === 'draft')>Draft</option>
        </select>
    </div>
    <div class="flex gap-2 mt-3">
        <button type="submit"
                class="rounded-xl bg-[#1d4ed8] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#1e40af] transition whitespace-nowrap">
            Apply Filters
        </button>
        @if(request()->hasAny(['search','status']))
        <a href="{{ route('hod.facilities.index') }}"
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
            @if($pages->total() > 0)
                Showing <span class="font-semibold text-slate-700">{{ $pages->firstItem() }}–{{ $pages->lastItem() }}</span>
                of <span class="font-semibold text-slate-700">{{ number_format($pages->total()) }}</span> pages
            @else
                No pages match your filters
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
        @if($pages->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">No resources found</h3>
                <p class="mt-1 text-sm text-slate-500 max-w-xs">Try adjusting your search or filters, or create a new resource.</p>
                <a href="{{ route('hod.facilities.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#1d4ed8] px-4 py-2 text-sm font-bold text-white hover:bg-[#1e40af] transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Create Resource
                </a>
            </div>
        @else
        <div class="mmp-table-wrap">
            <table class="mmp-table w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Title</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Slug</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Status</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden xl:table-cell">Image</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Updated</th>
                        <th class="px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($pages as $page)
                    <tr class="group hover:bg-slate-50/60 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-violet-50">
                                    <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-900 truncate text-sm">{{ $page->title }}</p>
                                    <p class="text-[11px] text-slate-400 truncate">{{ Str::limit(strip_tags($page->content), 60) }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-500 hidden lg:table-cell">
                            <span class="font-mono text-[11px]">{{ Str::limit($page->slug, 30) }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($page->is_published)
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
                        <td class="px-5 py-3.5 text-xs hidden xl:table-cell">
                            @if($page->featured_image)
                                <span class="inline-flex items-center gap-1 text-blue-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Yes
                                </span>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-400 hidden lg:table-cell">
                            {{ bsDate($page->updated_at, 'Y, F d') }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('hod.facilities.show', $page) }}"
                                   class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-blue-50 hover:text-blue-600 transition" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('hod.facilities.edit', $page) }}"
                                   class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-amber-50 hover:text-amber-600 transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('hod.facilities.destroy', $page) }}"
                                      onsubmit="return confirm('Delete {{ addslashes($page->title) }}? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 transition" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($pages->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">{{ $pages->links() }}</div>
        @endif
        @endif
    </div>

    {{-- ── CARD VIEW ──────────────────────────────────────── --}}
    <div x-show="view === 'cards'" x-cloak>
        @if($pages->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <h3 class="text-base font-bold text-slate-800">No resources found</h3>
                <p class="mt-1 text-sm text-slate-500">Try adjusting your filters or create a new resource.</p>
            </div>
        @else
        <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($pages as $page)
            <div class="group relative rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-slate-300 transition-all duration-150">
                {{-- Icon --}}
                <div class="flex flex-col items-center text-center">
                    @if($page->featured_image)
                        <img src="{{ asset('storage/' . $page->featured_image) }}" alt="{{ $page->title }}"
                             class="h-16 w-16 rounded-2xl object-cover ring-2 ring-white shadow"/>
                    @else
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-violet-50">
                            <svg class="w-8 h-8 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    @endif
                    <h3 class="mt-3 text-sm font-bold text-slate-900 leading-tight text-center line-clamp-2">{{ $page->title }}</h3>
                    <p class="mt-1 text-[11px] text-slate-400 line-clamp-2">{{ Str::limit(strip_tags($page->content), 80) }}</p>
                </div>
                {{-- Badges --}}
                <div class="mt-3 flex flex-wrap items-center justify-center gap-1.5">
                    @if($page->is_published)
                        <span class="rounded-lg bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">Published</span>
                    @else
                        <span class="rounded-lg bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700">Draft</span>
                    @endif
                    @if($page->featured_image)
                        <span class="rounded-lg bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700">Image</span>
                    @endif
                </div>
                {{-- Meta info --}}
                <div class="mt-3 space-y-0.5 text-center">
                    <p class="text-[11px] text-slate-400 font-mono truncate">{{ Str::limit($page->slug, 30) }}</p>
                    <p class="text-[11px] text-slate-400">{{ bsDate($page->updated_at, 'Y, F d') }}</p>
                </div>
                {{-- Actions --}}
                <div class="mt-4 grid grid-cols-3 gap-2">
                    <a href="{{ route('hod.facilities.show', $page) }}"
                       class="rounded-lg border border-slate-200 py-1.5 text-center text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">View</a>
                    <a href="{{ route('hod.facilities.edit', $page) }}"
                       class="rounded-lg bg-slate-900 py-1.5 text-center text-xs font-bold text-white hover:bg-slate-700 transition">Edit</a>
                    <form method="POST" action="{{ route('hod.facilities.destroy', $page) }}"
                          onsubmit="return confirm('Delete {{ addslashes($page->title) }}? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="w-full rounded-lg bg-red-600 py-1.5 text-center text-xs font-bold text-white hover:bg-red-700 transition">Delete</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @if($pages->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">{{ $pages->links() }}</div>
        @endif
        @endif
    </div>

</div>{{-- /panel --}}

</div>{{-- /container --}}
@endsection
