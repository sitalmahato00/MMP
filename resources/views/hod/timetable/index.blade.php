@extends('layouts.app')

@section('title', 'Timetable Management')

@section('content')
<div x-data="{
    view: localStorage.getItem('mmp_hod_timetable_view') ?? 'list',
    setView(v) { this.view = v; localStorage.setItem('mmp_hod_timetable_view', v); }
}" class="space-y-5">

{{-- ── HEADER ─────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900">Timetable Management</h1>
        <p class="mt-0.5 text-sm text-slate-500">
            {{ $department->name }} — create and manage class routines for your department
        </p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('hod.timetable.create') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-[#1d4ed8] px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-[#1e40af] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Create Timetable
        </a>
    </div>
</div>

{{-- ── KPI CARDS ───────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
    @php
        $kpis = [
            ['label'=>'Total Timetables',   'value'=>$totalTimetables,  'icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'color'=>'blue',   'tag'=>'Total'],
            ['label'=>'Active Timetables',  'value'=>$activeTimetables, 'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color'=>'green',  'tag'=>'Active'],
            ['label'=>'Weekly Slots',       'value'=>$thisWeekSlots,    'icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'color'=>'violet', 'tag'=>'Slots'],
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

{{-- ── FILTERS ─────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('hod.timetable.index') }}"
      class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
        {{-- Search --}}
        <div class="relative">
            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search program…"
                   class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-700 outline-none focus:border-[#1d4ed8] focus:ring-2 focus:ring-blue-100"/>
        </div>
        {{-- Program --}}
        <select name="program_id" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#1d4ed8] focus:ring-2 focus:ring-blue-100">
            <option value="">All Programs</option>
            @foreach($programs as $prog)
                <option value="{{ $prog->id }}" @selected(request('program_id') == $prog->id)>{{ $prog->name }}</option>
            @endforeach
        </select>
        {{-- Semester --}}
        <select name="semester" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#1d4ed8] focus:ring-2 focus:ring-blue-100">
            <option value="">All Semesters</option>
            @for($i = 1; $i <= 6; $i++)
                <option value="{{ $i }}" @selected(request('semester') == $i)>Semester {{ $i }}</option>
            @endfor
        </select>
        {{-- Status + Apply --}}
        <div class="flex gap-2">
            <select name="status" class="flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#1d4ed8] focus:ring-2 focus:ring-blue-100">
                <option value="">All Status</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
            <button type="submit"
                    class="rounded-xl bg-[#1d4ed8] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#1e40af] transition whitespace-nowrap">
                Apply
            </button>
            @if(request()->hasAny(['search', 'program_id', 'semester', 'status']))
            <a href="{{ route('hod.timetable.index') }}"
               class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-500 hover:bg-slate-50 transition" title="Clear filters">✕</a>
            @endif
        </div>
    </div>
</form>

{{-- Flash Messages --}}
@if(session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
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

    {{-- Panel header --}}
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5">
        <p class="text-sm text-slate-500">
            @if($timetables->total() > 0)
                Showing <span class="font-semibold text-slate-700">{{ $timetables->firstItem() }}–{{ $timetables->lastItem() }}</span>
                of <span class="font-semibold text-slate-700">{{ number_format($timetables->total()) }}</span> timetables
            @else
                No timetables match your filters
            @endif
        </p>

        {{-- View toggle --}}
        <div class="flex items-center rounded-xl border border-slate-200 p-1 gap-0.5 flex-shrink-0">
            <button type="button" @click="setView('list')"
                    :class="view === 'list' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-700'"
                    class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"/></svg>
                List
            </button>
            <button type="button" @click="setView('cards')"
                    :class="view === 'cards' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-700'"
                    class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Cards
            </button>
        </div>
    </div>

    {{-- ── LIST VIEW ─────────────────────────────────────── --}}
    <div x-show="view === 'list'" x-cloak>
        @if($timetables->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">No timetables found</h3>
                <p class="mt-1 text-sm text-slate-500 max-w-xs">Try adjusting your search or filters, or create a new timetable.</p>
                <a href="{{ route('hod.timetable.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#1d4ed8] px-4 py-2 text-sm font-bold text-white hover:bg-[#1e40af] transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Create Timetable
                </a>
            </div>
        @else
        <div class="mmp-table-wrap">
            <table class="mmp-table w-full text-sm">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">Program</th>
                        <th class="px-5 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">Semester</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Session</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Effective From</th>
                        <th class="px-5 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">Slots</th>
                        <th class="px-5 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-500">Status</th>
                        <th class="px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($timetables as $timetable)
                    <tr class="group hover:bg-slate-50/60 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="text-sm font-semibold text-slate-900">{{ $timetable->program->name }}</div>
                            @if($timetable->section)
                                <div class="text-xs text-slate-500">Section: {{ $timetable->section }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700">
                                Sem {{ $timetable->semester }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-500 hidden lg:table-cell">
                            {{ $timetable->academicSession->name ?? '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-500 hidden lg:table-cell">
                            {{ bsDate($timetable->effective_from, 'Y, F d') }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="text-sm font-semibold text-slate-700">{{ $timetable->slots->count() }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($timetable->is_active)
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-60"></span>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-60"></span>
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('hod.timetable.show', $timetable) }}"
                                   class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-emerald-50 hover:text-emerald-600 transition" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('hod.timetable.edit', $timetable) }}"
                                   class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-blue-50 hover:text-blue-600 transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('hod.timetable.destroy', $timetable) }}" 
                                      onsubmit="return confirm('Are you sure you want to delete this timetable?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 transition" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($timetables->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">{{ $timetables->links() }}</div>
        @endif
        @endif
    </div>

    {{-- ── CARD VIEW ──────────────────────────────────────── --}}
    <div x-show="view === 'cards'" x-cloak>
        @if($timetables->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <h3 class="text-base font-bold text-slate-800">No timetables found</h3>
                <p class="mt-1 text-sm text-slate-500">Try adjusting your filters or create a new timetable.</p>
            </div>
        @else
        <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($timetables as $timetable)
            <div class="group relative rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-slate-300 transition-all duration-150">
                {{-- Icon --}}
                <div class="flex items-start justify-between mb-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    @if($timetable->is_active)
                        <span class="rounded-lg bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700">Active</span>
                    @else
                        <span class="rounded-lg bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-600">Inactive</span>
                    @endif
                </div>
                
                {{-- Timetable Info --}}
                <div class="mb-3">
                    <h3 class="text-sm font-bold text-slate-900 leading-tight">{{ $timetable->program->name }}</h3>
                    <p class="mt-0.5 text-xs text-slate-500">
                        Semester {{ $timetable->semester }}
                        @if($timetable->section) • Section {{ $timetable->section }} @endif
                    </p>
                </div>
                
                {{-- Stats --}}
                <div class="mb-3 rounded-lg bg-slate-50 p-2 text-[11px]">
                    <div class="flex justify-between mb-1">
                        <span class="text-slate-500">Total Slots:</span>
                        <span class="font-semibold text-slate-700">{{ $timetable->slots->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Effective From:</span>
                        <span class="font-semibold text-slate-700">{{ bsDate($timetable->effective_from, 'M d') }}</span>
                    </div>
                </div>
                
                {{-- Actions --}}
                <div class="grid grid-cols-3 gap-2">
                    <a href="{{ route('hod.timetable.show', $timetable) }}"
                       class="rounded-lg bg-emerald-600 py-1.5 text-center text-xs font-bold text-white hover:bg-emerald-700 transition">View</a>
                    <a href="{{ route('hod.timetable.edit', $timetable) }}"
                       class="rounded-lg bg-slate-900 py-1.5 text-center text-xs font-bold text-white hover:bg-slate-700 transition">Edit</a>
                    <form method="POST" action="{{ route('hod.timetable.destroy', $timetable) }}" 
                          onsubmit="return confirm('Are you sure you want to delete this timetable?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full rounded-lg bg-red-600 py-1.5 text-center text-xs font-bold text-white hover:bg-red-700 transition">Delete</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @if($timetables->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">{{ $timetables->links() }}</div>
        @endif
        @endif
    </div>

</div>{{-- /panel --}}

</div>{{-- /x-data --}}
@endsection
