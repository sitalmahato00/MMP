@extends('layouts.app')
@section('title', $program->name . ' — Program')

@section('content')
@php
    $gradients = [
        'from-[#8B0000] to-rose-700',
        'from-violet-600 to-purple-700',
        'from-blue-600 to-indigo-700',
        'from-emerald-600 to-teal-700',
        'from-amber-500 to-orange-600',
        'from-cyan-600 to-sky-700',
    ];
    $icons = ['📘','🎓','🔬','💻','🏛️','🧪','📐','🌐','⚙️','🎨'];
    $grad = $gradients[$program->id % count($gradients)];
    $icon = $icons[$program->id % count($icons)];

    // Health score color
    $hColor = $stats['healthScore'] >= 80 ? 'text-emerald-500' : ($stats['healthScore'] >= 60 ? 'text-amber-500' : 'text-red-500');
    $hBg    = $stats['healthScore'] >= 80 ? 'stroke-emerald-500' : ($stats['healthScore'] >= 60 ? 'stroke-amber-500' : 'stroke-red-500');
    // SVG circle progress (r=40, circumference≈251.2)
    $circum = 251.2;
    $dashOff = $circum - ($stats['healthScore'] / 100 * $circum);
@endphp

<div x-data="{ tab: 'overview', confirmDelete: false }" class="space-y-6">

    {{-- ── HERO BANNER ── --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br {{ $grad }} shadow-xl">
        {{-- Decorative pattern --}}
        <div class="absolute inset-0 opacity-[0.07]" style="background-image:url('data:image/svg+xml,%3Csvg width%3D%2260%22 height%3D%2260%22 viewBox%3D%220 0 60 60%22 xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cg fill%3D%22%23fff%22 fill-opacity%3D%221%22%3E%3Cpath d%3D%22M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z%22%2F%3E%3C%2Fg%3E%3C%2Fsvg%3E')"></div>

        <div class="relative px-6 py-8 sm:px-10 sm:py-10">
            <div class="flex flex-wrap items-start gap-6">
                {{-- Program Icon --}}
                <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-white/20 text-5xl shadow-lg ring-4 ring-white/30">
                    {{ $icon }}
                </div>

                {{-- Program Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="rounded-lg bg-white/20 px-2.5 py-0.5 text-xs font-bold text-white font-mono">{{ $program->code }}</span>
                        @if($program->affiliation_type)
                        <span class="rounded-lg bg-white/15 px-2.5 py-0.5 text-xs font-bold text-white/80">{{ $program->affiliation_type }}</span>
                        @endif
                        @if($program->ctevt_code)
                        <span class="rounded-lg bg-white/15 px-2.5 py-0.5 text-xs font-bold text-white/70">CTEVT: {{ $program->ctevt_code }}</span>
                        @endif
                        @if($program->is_active)
                        <span class="rounded-full bg-emerald-400/30 px-2.5 py-0.5 text-[11px] font-bold text-white">● Active</span>
                        @else
                        <span class="rounded-full bg-white/15 px-2.5 py-0.5 text-[11px] font-bold text-white/60">● Inactive</span>
                        @endif
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-white leading-tight">{{ $program->name }}</h1>
                    <p class="mt-1.5 text-white/70 text-sm">
                        {{ $program->department?->name ?? 'No Department' }} &bull;
                        {{ $program->duration_years }} {{ Str::plural('Year', $program->duration_years) }} &bull;
                        {{ $program->total_semesters }} Semesters
                        @if($program->coordinator?->user)
                        &bull; Coordinator: <strong class="text-white">{{ $program->coordinator->user->name }}</strong>
                        @endif
                    </p>

                    {{-- Running semesters --}}
                    @if($stats['runningSemesters']->isNotEmpty())
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="text-[11px] font-bold text-white/60 uppercase tracking-wider">Running:</span>
                        @foreach($stats['runningSemesters'] as $sem)
                        <span class="rounded-full bg-white/25 px-2 py-0.5 text-[11px] font-bold text-white">Sem {{ $sem }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex shrink-0 flex-wrap gap-2 self-start">
                    <a href="{{ route('admin.programs.edit', $program) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-white/20 hover:bg-white/30 px-4 py-2 text-sm font-bold text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                    <button type="button" @click="confirmDelete = true"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-500/30 hover:bg-red-500/50 px-4 py-2 text-sm font-bold text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete
                    </button>
                    <a href="{{ route('admin.programs.index') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-white/20 hover:bg-white/30 px-4 py-2 text-sm font-bold text-white transition">
                        ← Back
                    </a>
                </div>
            </div>

            {{-- ── KPI STRIP ── --}}
            <div class="mt-8 grid grid-cols-2 sm:grid-cols-4 gap-3">
                @php $heroKpis = [
                    ['val' => $stats['totalStudents'],  'lbl' => 'Total Students'],
                    ['val' => $program->subjects->count(), 'lbl' => 'Total Subjects'],
                    ['val' => $stats['passRate'].'%',   'lbl' => 'Pass Rate'],
                    ['val' => $stats['healthScore'].'%','lbl' => 'Health Score'],
                ]; @endphp
                @foreach($heroKpis as $kpi)
                <div class="rounded-xl bg-white/15 backdrop-blur-sm px-4 py-3 text-center">
                    <p class="text-2xl font-black text-white">{{ $kpi['val'] }}</p>
                    <p class="text-[11px] text-white/65 font-medium">{{ $kpi['lbl'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── TAB BAR ── --}}
    <div class="flex overflow-x-auto gap-1 rounded-2xl border border-slate-100 bg-white p-1.5 shadow-sm">
        @php $tabs = [
            ['id'=>'overview',   'label'=>'Overview',          'icon'=>'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['id'=>'subjects',   'label'=>'Subjects',          'icon'=>'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
            ['id'=>'semesters',  'label'=>'Semester Structure', 'icon'=>'M4 6h16M4 10h16M4 14h16M4 18h16'],
            ['id'=>'students',   'label'=>'Students',          'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
            ['id'=>'teachers',   'label'=>'Teachers',          'icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
            ['id'=>'syllabus',   'label'=>'Syllabus',          'icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['id'=>'activity',   'label'=>'Activity Log',      'icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0'],
            ['id'=>'settings',   'label'=>'Settings',          'icon'=>'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
        ]; @endphp
        @foreach($tabs as $t)
        <button type="button" @click="tab = '{{ $t['id'] }}'"
                :class="tab === '{{ $t['id'] }}' ? 'bg-[#8B0000] text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100'"
                class="flex shrink-0 items-center gap-2 rounded-xl px-3 py-2 text-xs font-bold transition whitespace-nowrap">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $t['icon'] }}"/>
            </svg>
            {{ $t['label'] }}
        </button>
        @endforeach
    </div>

    {{-- ════════════════════════════════════════ --}}
    {{-- TAB: OVERVIEW                           --}}
    {{-- ════════════════════════════════════════ --}}
    <div x-show="tab === 'overview'" x-cloak class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left: Description + details --}}
            <div class="lg:col-span-2 space-y-5">
                {{-- Description --}}
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-black text-slate-900 mb-3">About This Program</h3>
                    @if($program->description)
                    <p class="text-sm text-slate-600 leading-relaxed">{{ $program->description }}</p>
                    @else
                    <p class="text-sm text-slate-400 italic">No description available.</p>
                    @endif

                    @if($program->eligibility)
                    <div class="mt-4 rounded-xl bg-amber-50 border border-amber-100 px-4 py-3">
                        <p class="text-xs font-bold text-amber-800 mb-1">Eligibility Criteria</p>
                        <p class="text-sm text-amber-700">{{ $program->eligibility }}</p>
                    </div>
                    @endif
                </div>

                {{-- Quick stats grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @php $qKpis = [
                        ['v'=>$stats['totalStudents'],    'l'=>'Students',       'c'=>'bg-blue-50 text-blue-600',    'ic'=>'bg-blue-100'],
                        ['v'=>$program->subjects->count(),'l'=>'Subjects',       'c'=>'bg-emerald-50 text-emerald-600','ic'=>'bg-emerald-100'],
                        ['v'=>$stats['theoryCount'],       'l'=>'Theory',         'c'=>'bg-violet-50 text-violet-600', 'ic'=>'bg-violet-100'],
                        ['v'=>$stats['practicalCount'],    'l'=>'Practical',      'c'=>'bg-amber-50 text-amber-600',   'ic'=>'bg-amber-100'],
                    ]; @endphp
                    @foreach($qKpis as $qk)
                    <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm text-center">
                        <p class="text-3xl font-black text-slate-900">{{ $qk['v'] }}</p>
                        <p class="mt-1 text-xs font-bold text-slate-500">{{ $qk['l'] }}</p>
                    </div>
                    @endforeach
                </div>

                {{-- Enrollment Trend Bar Chart (CSS) --}}
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-black text-slate-900">Enrollment by Semester</h3>
                        <span class="text-xs text-slate-400">{{ $stats['totalStudents'] }} total</span>
                    </div>
                    @if($stats['enrollmentBySemester']->sum() > 0)
                    @php $maxEnroll = max($stats['enrollmentBySemester']->values()->all()) ?: 1; @endphp
                    <div class="flex items-end gap-2 h-32">
                        @foreach($stats['enrollmentBySemester'] as $sem => $count)
                        @php $pct = $maxEnroll > 0 ? round(($count / $maxEnroll) * 100) : 0; @endphp
                        <div class="flex flex-1 flex-col items-center gap-1">
                            <span class="text-[11px] font-bold text-slate-700">{{ $count }}</span>
                            <div class="w-full rounded-t-lg bg-gradient-to-t {{ $grad }} transition-all"
                                 style="height: {{ max($pct, 4) }}%"></div>
                            <span class="text-[10px] text-slate-400">S{{ $sem }}</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-center text-slate-400 py-8">No enrollment data yet.</p>
                    @endif
                </div>
            </div>

            {{-- Right: Health Score + Program Details --}}
            <div class="space-y-5">
                {{-- Health Score Radial --}}
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm text-center">
                    <h3 class="text-sm font-black text-slate-900 mb-4">Program Health Score</h3>
                    <div class="relative mx-auto flex h-32 w-32 items-center justify-center">
                        <svg class="absolute inset-0 -rotate-90 h-32 w-32" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="40" fill="none" stroke="#f1f5f9" stroke-width="10"/>
                            <circle cx="50" cy="50" r="40" fill="none" class="{{ $hBg }}" stroke-width="10"
                                    stroke-linecap="round"
                                    stroke-dasharray="{{ $circum }}"
                                    stroke-dashoffset="{{ $dashOff }}"/>
                        </svg>
                        <div class="text-center">
                            <p class="text-2xl font-black {{ $hColor }}">{{ $stats['healthScore'] }}%</p>
                            <p class="text-[10px] text-slate-400">Health</p>
                        </div>
                    </div>
                    <div class="mt-4 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Pass Rate</span>
                            <span class="font-bold text-slate-800">{{ $stats['passRate'] }}%</span>
                        </div>
                        <div class="h-1.5 w-full rounded-full bg-slate-100">
                            <div class="h-1.5 rounded-full bg-emerald-500 transition-all" style="width:{{ $stats['passRate'] }}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-xs mt-2">
                            <span class="text-slate-500">Subject Completion</span>
                            <span class="font-bold text-slate-800">{{ $stats['subjectCompletionRate'] }}%</span>
                        </div>
                        <div class="h-1.5 w-full rounded-full bg-slate-100">
                            <div class="h-1.5 rounded-full bg-blue-500 transition-all" style="width:{{ $stats['subjectCompletionRate'] }}%"></div>
                        </div>
                    </div>
                </div>

                {{-- Program Details --}}
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-black text-slate-900 mb-4">Program Details</h3>
                    <dl class="space-y-3">
                        @php $details = [
                            ['l'=>'Code',            'v'=> $program->code],
                            ['l'=>'Department',      'v'=> $program->department?->name ?? '—'],
                            ['l'=>'Duration',        'v'=> $program->duration_years.' '.Str::plural('Year',$program->duration_years)],
                            ['l'=>'Semesters',       'v'=> $program->total_semesters],
                            ['l'=>'Affiliation',     'v'=> $program->affiliation_type ?? '—'],
                            ['l'=>'CTEVT Code',      'v'=> $program->ctevt_code ?? '—'],
                            ['l'=>'Created',         'v'=> bsDate($program->created_at, 'Y, F d')],
                            ['l'=>'Last Updated',    'v'=> bsDate($program->updated_at, 'Y, F d')],
                        ]; @endphp
                        @foreach($details as $d)
                        <div class="flex items-center justify-between gap-2">
                            <dt class="text-xs font-semibold text-slate-500">{{ $d['l'] }}</dt>
                            <dd class="text-xs font-bold text-slate-800 text-right truncate">{{ $d['v'] }}</dd>
                        </div>
                        @endforeach
                        @if($program->coordinator?->user)
                        <div class="pt-2 border-t border-slate-100">
                            <p class="text-xs font-semibold text-slate-500 mb-2">Coordinator</p>
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br {{ $grad }} text-xs font-black text-white">
                                    {{ strtoupper(substr($program->coordinator->user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-800">{{ $program->coordinator->user->name }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $program->coordinator->designation ?? 'Teacher' }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════ --}}
    {{-- TAB: SUBJECTS                           --}}
    {{-- ════════════════════════════════════════ --}}
    <div x-show="tab === 'subjects'" x-cloak class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-black text-slate-900">Subjects by Semester</h3>
            <span class="rounded-xl bg-[#8B0000]/10 px-3 py-1 text-xs font-bold text-[#8B0000]">
                {{ $program->subjects->count() }} total subjects
            </span>
        </div>

        @forelse($stats['subjectsBySemester'] as $sem => $subjects)
        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            {{-- Semester header --}}
            <div class="flex items-center justify-between bg-slate-50/80 px-5 py-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-gradient-to-br {{ $grad }} text-xs font-black text-white shadow-sm">
                        {{ $sem }}
                    </div>
                    <h4 class="text-sm font-black text-slate-800">Semester {{ $sem }}</h4>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-500">{{ $subjects->count() }} subjects</span>
                    @if(isset($stats['creditsBySemester'][$sem]))
                    <span class="rounded-lg bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600">{{ $stats['creditsBySemester'][$sem] }} credits</span>
                    @endif
                </div>
            </div>
            {{-- Subjects table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-slate-50">
                    <thead>
                        <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                            <th class="px-5 py-2.5 text-left">Subject</th>
                            <th class="px-5 py-2.5 text-left">Code</th>
                            <th class="px-5 py-2.5 text-left">Type</th>
                            <th class="px-5 py-2.5 text-left">Credits</th>
                            <th class="px-5 py-2.5 text-left">Full Marks</th>
                            <th class="px-5 py-2.5 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($subjects as $subject)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-5 py-3 font-semibold text-slate-900">{{ $subject->name }}</td>
                            <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $subject->code }}</td>
                            <td class="px-5 py-3">
                                @php $typeColors = ['theory'=>'bg-blue-50 text-blue-700','practical'=>'bg-amber-50 text-amber-700','both'=>'bg-emerald-50 text-emerald-700']; @endphp
                                <span class="rounded-lg {{ $typeColors[$subject->type] ?? 'bg-slate-100 text-slate-600' }} px-2 py-0.5 text-[11px] font-bold">
                                    {{ ucfirst($subject->type) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-sm font-semibold text-slate-700">{{ $subject->credit_hours }}</td>
                            <td class="px-5 py-3 text-sm text-slate-600">{{ $subject->total_full_marks }}</td>
                            <td class="px-5 py-3">
                                @if($subject->is_active)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">● Active</span>
                                @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">● Inactive</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @empty
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white py-16 text-center">
            <p class="text-sm text-slate-400">No subjects assigned yet.</p>
        </div>
        @endforelse
    </div>

    {{-- ════════════════════════════════════════ --}}
    {{-- TAB: SEMESTER STRUCTURE                 --}}
    {{-- ════════════════════════════════════════ --}}
    <div x-show="tab === 'semesters'" x-cloak>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @for($s = 1; $s <= $program->total_semesters; $s++)
            @php
                $semSubjects = $stats['subjectsBySemester']->get($s, collect());
                $credits     = $stats['creditsBySemester']->get($s, 0);
                $hasStudents = $stats['runningSemesters']->contains($s);
            @endphp
            <div class="rounded-2xl border {{ $hasStudents ? 'border-[#8B0000]/20 ring-1 ring-[#8B0000]/10' : 'border-slate-100' }} bg-white p-5 shadow-sm">
                {{-- Header --}}
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br {{ $grad }} text-sm font-black text-white shadow-sm">
                            {{ $s }}
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-slate-900">Semester {{ $s }}</h4>
                            @if($hasStudents)
                            <span class="text-[10px] font-bold text-[#8B0000]">● Currently Running</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-black text-slate-900">{{ $semSubjects->count() }}</p>
                        <p class="text-[10px] text-slate-400">subjects</p>
                    </div>
                </div>

                {{-- Subjects list --}}
                @if($semSubjects->isNotEmpty())
                <div class="space-y-1.5">
                    @foreach($semSubjects as $sub)
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="h-1.5 w-1.5 rounded-full {{ $sub->type === 'theory' ? 'bg-blue-500' : ($sub->type === 'practical' ? 'bg-amber-500' : 'bg-emerald-500') }} shrink-0"></div>
                            <span class="text-xs font-semibold text-slate-700 truncate">{{ $sub->name }}</span>
                        </div>
                        <span class="shrink-0 text-[10px] text-slate-400 ml-2">{{ $sub->credit_hours }}cr</span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-xs text-center text-slate-400 py-4">No subjects assigned</p>
                @endif

                {{-- Footer --}}
                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                    <span class="text-[11px] text-slate-400">Total Credits</span>
                    <span class="text-xs font-black text-slate-800">{{ $credits }}</span>
                </div>
            </div>
            @endfor
        </div>
    </div>

    {{-- ════════════════════════════════════════ --}}
    {{-- TAB: STUDENTS                           --}}
    {{-- ════════════════════════════════════════ --}}
    <div x-show="tab === 'students'" x-cloak>
        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h3 class="text-sm font-black text-slate-900">Enrolled Students</h3>
                <span class="rounded-xl bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">{{ $stats['totalStudents'] }} enrolled</span>
            </div>
            @if($program->students->isEmpty())
            <div class="py-16 text-center">
                <p class="text-sm text-slate-400">No students enrolled in this program yet.</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-50 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                            <th class="px-5 py-3 text-left">Student</th>
                            <th class="px-5 py-3 text-left">Student No.</th>
                            <th class="px-5 py-3 text-left">Semester</th>
                            <th class="px-5 py-3 text-left">Batch</th>
                            <th class="px-5 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($program->students->take(50) as $student)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br {{ $grad }} text-xs font-black text-white shrink-0">
                                        {{ strtoupper(substr($student->user?->name ?? 'S', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $student->user?->name ?? '—' }}</p>
                                        <p class="text-[11px] text-slate-400">{{ $student->user?->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $student->student_no ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-lg bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-700">Sem {{ $student->current_semester }}</span>
                            </td>
                            <td class="px-5 py-3 text-xs text-slate-500">{{ $student->batch ?? '—' }}</td>
                            <td class="px-5 py-3">
                                @php $sc = ['active'=>'bg-emerald-50 text-emerald-700','inactive'=>'bg-slate-100 text-slate-500','suspended'=>'bg-red-50 text-red-700','graduated'=>'bg-blue-50 text-blue-700']; @endphp
                                <span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ $sc[$student->status] ?? 'bg-slate-100 text-slate-500' }}">
                                    {{ ucfirst($student->status ?? 'unknown') }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($program->students->count() > 50)
            <div class="px-5 py-3 border-t border-slate-100 text-xs text-slate-400 text-center">
                Showing 50 of {{ $program->students->count() }}. <a href="{{ route('admin.students.index', ['program_id' => $program->id]) }}" class="text-[#8B0000] font-bold hover:underline">View all →</a>
            </div>
            @endif
            @endif
        </div>
    </div>

    {{-- ════════════════════════════════════════ --}}
    {{-- TAB: TEACHERS                           --}}
    {{-- ════════════════════════════════════════ --}}
    <div x-show="tab === 'teachers'" x-cloak>
        @if($stats['teachers']->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white py-16 text-center">
            <p class="text-sm text-slate-400">No teachers assigned to this program yet.</p>
        </div>
        @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($stats['teachers'] as $teacher)
            @php $tgrad = $gradients[$teacher->id % count($gradients)]; @endphp
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-4 mb-4">
                    @if($teacher->user?->avatar)
                    <img src="{{ asset('storage/'.$teacher->user->avatar) }}" class="h-12 w-12 rounded-xl object-cover ring-2 ring-slate-100"/>
                    @else
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br {{ $tgrad }} text-lg font-black text-white shadow-sm">
                        {{ strtoupper(substr($teacher->user?->name ?? 'T', 0, 1)) }}
                    </div>
                    @endif
                    <div class="min-w-0">
                        <p class="font-black text-slate-900 truncate">{{ $teacher->user?->name }}</p>
                        <p class="text-xs text-slate-500">{{ $teacher->designation ?? 'Teacher' }}</p>
                    </div>
                </div>
                {{-- Subjects in this program --}}
                <div class="space-y-1.5">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Subjects</p>
                    @foreach($teacher->subjects as $sub)
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-1.5">
                        <span class="text-xs font-semibold text-slate-700 truncate">{{ $sub->name }}</span>
                        <span class="shrink-0 rounded-md bg-slate-200 px-1.5 py-0.5 text-[10px] font-bold text-slate-500 ml-2">S{{ $sub->semester }}</span>
                    </div>
                    @endforeach
                </div>
                <a href="{{ route('admin.teachers.show', $teacher) }}" class="mt-3 flex items-center justify-center gap-1 rounded-xl bg-slate-100 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200 transition">
                    View Profile
                </a>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ════════════════════════════════════════ --}}
    {{-- TAB: SYLLABUS                           --}}
    {{-- ════════════════════════════════════════ --}}
    <div x-show="tab === 'syllabus'" x-cloak>
        <div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-black text-slate-900">Program Syllabus</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Official curriculum document</p>
                </div>
                <a href="{{ route('admin.programs.edit', $program) }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000]/10 px-3 py-2 text-xs font-bold text-[#8B0000] hover:bg-[#8B0000]/20 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Upload New
                </a>
            </div>
            @if($program->syllabus)
            <div class="p-6">
                <div class="flex items-center gap-4 rounded-2xl border border-red-100 bg-red-50 p-5">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-red-100">
                        <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-black text-slate-900">{{ basename($program->syllabus) }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">PDF Document &bull; {{ $program->name }}</p>
                    </div>
                    <a href="{{ asset('storage/'.$program->syllabus) }}" target="_blank"
                       class="shrink-0 inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#7a0000] transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        View PDF
                    </a>
                </div>
            </div>
            @else
            <div class="py-20 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <p class="text-base font-bold text-slate-700">No syllabus uploaded</p>
                <p class="mt-1 text-sm text-slate-400">Upload the curriculum PDF to make it available here.</p>
                <a href="{{ route('admin.programs.edit', $program) }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#7a0000] transition">Upload Syllabus</a>
            </div>
            @endif
        </div>
    </div>

    {{-- ════════════════════════════════════════ --}}
    {{-- TAB: ACTIVITY LOG                       --}}
    {{-- ════════════════════════════════════════ --}}
    <div x-show="tab === 'activity'" x-cloak>
        <div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-sm font-black text-slate-900">Activity Log</h3>
            </div>
            @if($auditLogs->isEmpty())
            <div class="py-16 text-center">
                <p class="text-sm text-slate-400">No activity recorded for this program.</p>
            </div>
            @else
            <div class="divide-y divide-slate-50">
                @foreach($auditLogs as $log)
                <div class="flex items-start gap-4 px-5 py-4">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-black text-slate-600">
                        {{ strtoupper(substr($log->user?->name ?? '?', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-slate-700">
                            <strong class="text-slate-900">{{ $log->user?->name ?? 'System' }}</strong>
                            performed <strong class="font-mono text-xs text-[#8B0000]">{{ $log->action }}</strong>
                        </p>
                        <p class="mt-0.5 text-[11px] text-slate-400">{{ bsDate($log->created_at, 'Y, F d h:i A') }} &bull; {{ $log->ip_address }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- ════════════════════════════════════════ --}}
    {{-- TAB: SETTINGS                           --}}
    {{-- ════════════════════════════════════════ --}}
    <div x-show="tab === 'settings'" x-cloak>
        <div class="max-w-2xl space-y-5">
            {{-- Quick toggle --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-slate-900">Program Status</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Enable or disable enrollment for this program.</p>
                    </div>
                    <form method="POST" action="{{ route('admin.programs.update', $program) }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="name" value="{{ $program->name }}">
                        <input type="hidden" name="code" value="{{ $program->code }}">
                        <input type="hidden" name="department_id" value="{{ $program->department_id }}">
                        <input type="hidden" name="duration_years" value="{{ $program->duration_years }}">
                        <input type="hidden" name="total_semesters" value="{{ $program->total_semesters }}">
                        <input type="hidden" name="is_active" value="{{ $program->is_active ? '0' : '1' }}">
                        <button type="submit"
                                class="relative inline-flex h-7 w-12 items-center rounded-full transition-colors {{ $program->is_active ? 'bg-emerald-500' : 'bg-slate-200' }}">
                            <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow-sm transition-transform {{ $program->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Edit button --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
                <h3 class="text-sm font-black text-slate-900 mb-1">Edit Program Details</h3>
                <p class="text-xs text-slate-500 mb-4">Update the program name, code, coordinator, syllabus, and other details.</p>
                <a href="{{ route('admin.programs.edit', $program) }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#7a0000] transition">
                    Open Edit Form →
                </a>
            </div>

            {{-- Danger zone --}}
            <div class="rounded-2xl border border-red-100 bg-white p-6 shadow-sm">
                <h3 class="text-sm font-black text-red-700 mb-1">Danger Zone</h3>
                <p class="text-xs text-slate-500 mb-4">Deleting this program will remove all associated subjects. Students and marks will be preserved but unlinked.</p>
                <button type="button" @click="confirmDelete = true"
                        class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete Program
                </button>
            </div>
        </div>
    </div>

    {{-- ── DELETE CONFIRM MODAL ── --}}
    <div x-show="confirmDelete" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4"
         @keydown.escape.window="confirmDelete = false">
        <div class="absolute inset-0 bg-black/50" @click="confirmDelete = false"
             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"></div>
        <div class="relative w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-xl"
             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 mb-4">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <h3 class="text-base font-black text-slate-900">Delete Program?</h3>
            <p class="mt-1 text-sm text-slate-500">This will permanently remove <strong>{{ $program->name }}</strong> and all its subjects.</p>
            <div class="mt-5 flex gap-3">
                <button type="button" @click="confirmDelete = false"
                        class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Cancel</button>
                <form method="POST" action="{{ route('admin.programs.destroy', $program) }}" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-700 transition">Yes, Delete</button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
