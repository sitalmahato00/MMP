@extends('layouts.app')

@section('title', 'Alumni Preparation')

@section('content')
@php
    $statusMap = [
        'ready'    => ['label'=>'Ready',     'cls'=>'bg-amber-50 text-amber-700'],
        'prepared' => ['label'=>'Prepared',  'cls'=>'bg-emerald-50 text-emerald-700'],
    ];
@endphp

<div class="space-y-5">

{{-- ── HEADER ─────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900">Alumni Preparation</h1>
        <p class="mt-0.5 text-sm text-slate-500">
            {{ $department->name }} — Prepare graduating students for alumni status
        </p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('hod.alumni.graduating') }}" 
           class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            View All Graduating
        </a>
        <a href="{{ route('hod.alumni.records') }}" 
           class="inline-flex items-center gap-2 rounded-xl bg-[#1d4ed8] px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-[#1e40af] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
            </svg>
            Alumni Records
        </a>
    </div>
</div>

{{-- ── KPI CARDS ───────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
    @php
        $kpis = [
            ['label'=>'Graduating Students',   'value'=>$totalGraduating,  'icon'=>'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z', 'color'=>'blue',   'tag'=>'Total'],
            ['label'=>'Prepared Alumni',  'value'=>$totalPrepared, 'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color'=>'green',  'tag'=>'Active'],
            ['label'=>'Pending Preparation',     'value'=>$pendingPreparation, 'icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'color'=>'amber', 'tag'=>'Pending'],
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
<form method="GET" action="{{ route('hod.alumni.index') }}"
      class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
        {{-- Search --}}
        <div class="relative lg:col-span-2">
            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search by name or email…"
                   class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-700 outline-none focus:border-[#1d4ed8] focus:ring-2 focus:ring-blue-100"/>
        </div>
        {{-- Program + Apply --}}
        <div class="flex gap-2">
            <select name="program_id" class="flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#1d4ed8] focus:ring-2 focus:ring-blue-100">
                <option value="">All Programs</option>
                @foreach($programs as $program)
                    <option value="{{ $program->id }}" @selected(request('program_id') == $program->id)>{{ $program->name }}</option>
                @endforeach
            </select>
            <button type="submit"
                    class="rounded-xl bg-[#1d4ed8] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#1e40af] transition whitespace-nowrap">
                Apply
            </button>
            @if(request()->hasAny(['search','program_id']))
            <a href="{{ route('hod.alumni.index') }}"
               class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-500 hover:bg-slate-50 transition" title="Clear filters">✕</a>
            @endif
        </div>
    </div>
</form>

{{-- Flash Messages --}}
@if(session('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="rounded-xl border border-red-200 bg-red-50 p-4">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
        </div>
    </div>
@endif

{{-- ── GRADUATING STUDENTS TABLE ──────────────────────────── --}}
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="border-b border-slate-100 px-5 py-4 bg-slate-50">
        <h2 class="text-sm font-bold text-slate-900">Graduating Students</h2>
        <p class="text-xs text-slate-500">Students in final semester ready for alumni preparation</p>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Student</th>
                    <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Program</th>
                    <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Semester</th>
                    <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Status</th>
                    <th class="px-5 py-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($graduatingStudents as $student)
                    @php
                        $hasAlumniRecord = \App\Models\Alumni::where('student_id', $student->id)->exists();
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $student->user->avatar_url }}" alt="{{ $student->user->name }}" 
                                     class="h-10 w-10 rounded-full object-cover ring-2 ring-white shadow">
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">{{ $student->user->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $student->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-sm font-medium text-slate-900">{{ $student->program->name }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-sm font-semibold text-slate-900">Semester {{ $student->current_semester }}</div>
                            <div class="text-xs text-emerald-600 font-medium">Final semester</div>
                        </td>
                        <td class="px-5 py-4">
                            @if($hasAlumniRecord)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Alumni Prepared
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    Ready for Preparation
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if(!$hasAlumniRecord)
                                <form method="POST" action="{{ route('hod.alumni.prepare', $student) }}" 
                                      onsubmit="return confirm('Are you sure you want to prepare this student for alumni status?')">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 text-sm font-bold transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Prepare Alumni
                                    </button>
                                </form>
                            @else
                                <span class="text-sm text-slate-400 font-medium">Already prepared</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-16 text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                            </svg>
                            <p class="mt-3 text-sm font-medium text-slate-500">No graduating students found</p>
                            <p class="mt-1 text-xs text-slate-400">Students in final semester will appear here</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($graduatingStudents->hasPages())
        <div class="border-t border-slate-100 px-5 py-4 bg-slate-50">
            {{ $graduatingStudents->links() }}
        </div>
    @endif
</div>

{{-- ── RECENTLY PREPARED ALUMNI ───────────────────────────── --}}
@if($preparedAlumni->count() > 0)
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 px-5 py-4 bg-slate-50">
            <h2 class="text-sm font-bold text-slate-900">Recently Prepared Alumni</h2>
            <p class="text-xs text-slate-500">Latest students prepared for alumni status</p>
        </div>
        
        <div class="p-5">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($preparedAlumni as $alumni)
                    <div class="rounded-xl border border-slate-200 p-4 hover:shadow-md transition">
                        <div class="flex items-center gap-3">
                            <img src="{{ $alumni->user->avatar_url }}" alt="{{ $alumni->user->name }}" 
                                 class="h-12 w-12 rounded-full object-cover ring-2 ring-white shadow">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-bold text-slate-900 truncate">{{ $alumni->user->name }}</div>
                                <div class="text-xs text-slate-500 truncate">{{ $alumni->program->name }}</div>
                            </div>
                        </div>
                        <div class="mt-3 text-xs text-slate-500">
                            Prepared on {{ bsDate($alumni->created_at, 'M d, Y') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @if($preparedAlumni->hasPages())
            <div class="border-t border-slate-100 px-5 py-4 bg-slate-50">
                {{ $preparedAlumni->links() }}
            </div>
        @endif
    </div>
@endif

</div>
@endsection
