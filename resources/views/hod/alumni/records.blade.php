@extends('layouts.app')

@section('title', 'Alumni Records')

@section('content')
<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('hod.alumni.index') }}" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-2xl font-black tracking-tight text-slate-900">Alumni Records</h1>
            </div>
            <p class="mt-0.5 text-sm text-slate-500">
                {{ $department->name }} — View and manage department alumni information
            </p>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $kpis = [
                ['label'=>'Total Alumni',   'value'=>$totalAlumni,  'icon'=>'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z', 'color'=>'blue',   'tag'=>'Total'],
                ['label'=>'Recent Graduates',  'value'=>$recentGraduates, 'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color'=>'green',  'tag'=>'Active'],
                ['label'=>'Employed',     'value'=>$employedAlumni, 'icon'=>'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'color'=>'violet', 'tag'=>'New'],
                ['label'=>'Entrepreneurs',           'value'=>$entrepreneurAlumni,    'icon'=>'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z', 'color'=>'amber',  'tag'=>'Business'],
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
<form method="GET" action="{{ route('hod.alumni.records') }}"
      class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-5">
        {{-- Search --}}
        <div class="relative lg:col-span-2">
            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search alumni…"
                   class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-700 outline-none focus:border-[#1d4ed8] focus:ring-2 focus:ring-blue-100"/>
        </div>
        {{-- Year --}}
        <select name="graduation_year" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#1d4ed8] focus:ring-2 focus:ring-blue-100">
            <option value="">All Years</option>
            @foreach($graduationYears as $year)
                <option value="{{ $year }}" @selected(request('graduation_year') == $year)>{{ $year }}</option>
            @endforeach
        </select>
        {{-- Program --}}
        <select name="program_id" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#1d4ed8] focus:ring-2 focus:ring-blue-100">
            <option value="">All Programs</option>
            @foreach($programs as $program)
                <option value="{{ $program->id }}" @selected(request('program_id') == $program->id)>{{ $program->name }}</option>
            @endforeach
        </select>
        {{-- Status + Apply --}}
        <div class="flex gap-2">
            <select name="status" class="flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#1d4ed8] focus:ring-2 focus:ring-blue-100">
                <option value="">All Status</option>
                <option value="recent_graduate" @selected(request('status') == 'recent_graduate')>Recent Graduate</option>
                <option value="employed" @selected(request('status') == 'employed')>Employed</option>
                <option value="entrepreneur" @selected(request('status') == 'entrepreneur')>Entrepreneur</option>
                <option value="further_study" @selected(request('status') == 'further_study')>Further Study</option>
                <option value="unemployed" @selected(request('status') == 'unemployed')>Unemployed</option>
            </select>
            <button type="submit"
                    class="rounded-xl bg-[#1d4ed8] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#1e40af] transition whitespace-nowrap">
                Apply
            </button>
            @if(request()->hasAny(['search','graduation_year','program_id','status']))
            <a href="{{ route('hod.alumni.records') }}"
               class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-500 hover:bg-slate-50 transition" title="Clear filters">✕</a>
            @endif
        </div>
    </div>
</form>

    {{-- Alumni Grid --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($alumni as $alumnus)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-start gap-4">
                    <img src="{{ $alumnus->user->avatar_url }}" alt="{{ $alumnus->user->name }}" 
                         class="h-14 w-14 rounded-full object-cover ring-2 ring-white shadow">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-bold text-slate-900 truncate">{{ $alumnus->user->name }}</h3>
                        <p class="text-xs text-slate-500 truncate">{{ $alumnus->program->name }}</p>
                        <div class="mt-2 flex items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700">
                                {{ $alumnus->graduation_year }}
                            </span>
                            @if($alumnus->current_status)
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 capitalize">
                                    {{ str_replace('_', ' ', $alumnus->current_status) }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-3 border-t border-slate-100 pt-4">
                    <div class="text-center">
                        <div class="text-lg font-black text-slate-900">{{ $alumnus->achievements_count }}</div>
                        <div class="text-[10px] text-slate-500 font-medium">Achievements</div>
                    </div>
                    <div class="text-center">
                        <div class="text-lg font-black text-slate-900">{{ $alumnus->employments_count }}</div>
                        <div class="text-[10px] text-slate-500 font-medium">Jobs</div>
                    </div>
                    <div class="text-center">
                        <div class="text-lg font-black text-slate-900">{{ $alumnus->projects_count }}</div>
                        <div class="text-[10px] text-slate-500 font-medium">Projects</div>
                    </div>
                </div>

                @if($alumnus->current_job || $alumnus->company_name)
                    <div class="mt-4 rounded-lg bg-slate-50 p-3">
                        <div class="text-xs font-bold text-slate-900">{{ $alumnus->current_job ?? 'Position' }}</div>
                        <div class="text-xs text-slate-500">{{ $alumnus->company_name ?? 'Company' }}</div>
                    </div>
                @endif
            </div>
        @empty
            <div class="col-span-full rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 p-16 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                </svg>
                <p class="mt-3 text-sm font-medium text-slate-500">No alumni records found</p>
                <p class="mt-1 text-xs text-slate-400">Alumni records will appear here once students are prepared</p>
            </div>
        @endforelse
    </div>

    @if($alumni->hasPages())
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            {{ $alumni->links() }}
        </div>
    @endif
</div>
@endsection
