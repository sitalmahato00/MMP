@extends('layouts.app')

@section('title', 'Analytics')

@section('content')
@php
    $metricIconPaths = [
        'students' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
        'attendance' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>',
        'results' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
        'marks' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m-6-8h6m2 12H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
        'program' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10"/>',
        'department' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 21V7a2 2 0 012-2h12a2 2 0 012 2v14M8 21v-4m8 4v-8m-4 8V9"/>',
    ];

    $toneStyles = [
        'red' => [
            'iconWrap' => 'bg-red-50 text-[#8B0000]',
            'trend' => 'bg-red-50 text-[#8B0000] ring-1 ring-red-100',
        ],
        'amber' => [
            'iconWrap' => 'bg-amber-50 text-amber-600',
            'trend' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-100',
        ],
        'green' => [
            'iconWrap' => 'bg-emerald-50 text-emerald-600',
            'trend' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100',
        ],
        'slate' => [
            'iconWrap' => 'bg-slate-50 text-slate-600',
            'trend' => 'bg-slate-50 text-slate-700 ring-1 ring-slate-200',
        ],
    ];

    $alertToneStyles = [
        'danger' => [
            'shell' => 'border-rose-200 bg-rose-50/70',
            'text' => 'text-rose-700',
        ],
        'warning' => [
            'shell' => 'border-amber-200 bg-amber-50/70',
            'text' => 'text-amber-700',
        ],
        'success' => [
            'shell' => 'border-emerald-200 bg-emerald-50/70',
            'text' => 'text-emerald-700',
        ],
        'info' => [
            'shell' => 'border-sky-200 bg-sky-50/70',
            'text' => 'text-sky-700',
        ],
    ];

    $state = $analyticsState;
    $selectedSessionLabel = $state['selectedSessionLabel'] ?? 'Current session';
    $selectedDepartmentLabel = $state['selectedDepartmentLabel'] ?? null;
    $selectedProgramLabel = $state['selectedProgramLabel'] ?? null;
    $examStats = $state['examStats'] ?? ['total' => 0, 'completed' => 0];
    $topDepartment = collect($state['topDepartments'] ?? [])->first();
    $topProgram = collect($state['topPrograms'] ?? [])->first();
@endphp

<div id="analytics-page"
    class="space-y-8"
    data-analytics-page
    data-analytics-endpoint="{{ route('admin.analytics') }}"
    data-analytics-state='@js($state)'>
    <x-page-header title="Analytics" subtitle="Simple academic insights from students, attendance, and results. Use the filters to narrow the view, then open a report only when you need the details.">
        <x-slot name="actions">
            <x-btn href="{{ route('admin.dashboard') }}" variant="secondary">Back to Dashboard</x-btn>
            <x-btn href="{{ route('admin.students.index') }}" variant="secondary">Students</x-btn>
            <x-btn href="{{ route('admin.exams.index') }}">Exams</x-btn>
        </x-slot>
    </x-page-header>

    <section class="relative overflow-hidden rounded-[2rem] border border-slate-200 bg-white px-6 py-6 shadow-[0_28px_80px_rgba(15,23,42,0.08)] sm:px-8 lg:px-10">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(139,0,0,0.08),transparent_34%),radial-gradient(circle_at_bottom_left,rgba(148,163,184,0.12),transparent_30%)]"></div>
        <div class="absolute right-0 top-0 h-40 w-40 translate-x-1/2 -translate-y-1/2 rounded-full bg-red-100/50 blur-3xl"></div>

        <div class="relative grid gap-6 xl:grid-cols-[1.2fr_0.9fr] xl:items-start">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-red-200 bg-red-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.24em] text-[#8B0000]">
                    Academic analytics
                </span>
                <h1 class="mt-4 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl lg:text-5xl">
                    One clear view of attendance, results, and program health.
                </h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
                    Summary data comes first. Comparisons come second. Drill-down stays hidden until you choose a department or program and open a report.
                </p>

                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="#analytics-drilldown" class="inline-flex items-center gap-2 rounded-2xl bg-[#8B0000] px-4 py-3 text-sm font-bold text-white shadow-[0_12px_30px_rgba(139,0,0,0.22)] transition-all duration-200 hover:-translate-y-0.5 hover:bg-[#6e0000]">
                        View Details
                    </a>
                    <a href="{{ $reportHref }}" data-analytics-report-link class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-red-200 hover:text-[#8B0000]">
                        Open Student Report
                    </a>
                    <a href="{{ route('admin.exams.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-950 px-4 py-3 text-sm font-bold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-slate-800">
                        Open Exams
                    </a>
                </div>

                <div class="mt-5 flex flex-wrap gap-3 text-xs font-semibold text-slate-500">
                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Session: <span data-analytics-session-pill>{{ $selectedSessionLabel }}</span>
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5">
                        <span class="h-2 w-2 rounded-full bg-[#8B0000]"></span>
                        Department: <span data-analytics-department-pill>{{ $selectedDepartmentLabel ?? 'All departments' }}</span>
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5">
                        <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                        Program: <span data-analytics-program-pill>{{ $selectedProgramLabel ?? 'All programs' }}</span>
                    </span>
                </div>
            </div>

            <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50/90 p-4 shadow-sm backdrop-blur">
                <div class="flex flex-col gap-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-slate-500">Filters</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">Change the academic session, department, or program.</p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Academic Session</label>
                            <select data-analytics-session-select class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                                @foreach($sessionOptions as $sessionOption)
                                    <option value="{{ $sessionOption['id'] }}" @selected(($selectedSession?->id ?? null) === $sessionOption['id'])>
                                        {{ $sessionOption['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Department</label>
                            <select data-analytics-department-select class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                                <option value="">All departments</option>
                                @foreach($departmentOptions as $departmentOption)
                                    <option value="{{ $departmentOption['id'] }}" @selected(($selectedDepartment?->id ?? null) === $departmentOption['id'])>
                                        {{ $departmentOption['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Program</label>
                            <select data-analytics-program-select class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                                <option value="">All programs</option>
                                @foreach($programOptions as $programOption)
                                    <option value="{{ $programOption['id'] }}" @selected(($selectedProgram?->id ?? null) === $programOption['id'])>
                                        {{ $programOption['label'] }}@if(!empty($programOption['department'])) / {{ $programOption['department'] }}@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" data-analytics-apply class="inline-flex items-center rounded-2xl bg-[#8B0000] px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#6e0000]">
                            Apply Filters
                        </button>
                        <button type="button" data-analytics-open-details class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-red-200 hover:text-[#8B0000]">
                            View Details
                        </button>
                        <span data-analytics-loading class="hidden inline-flex items-center rounded-full bg-red-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-[#8B0000] ring-1 ring-red-100">
                            Updating
                        </span>
                    </div>

                    <p class="text-xs leading-6 text-slate-500">
                        Summary metrics update with the selected filters. Detailed student tables load only when you click View Details.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($summaryCards as $card)
            @php $styles = $toneStyles[$card['tone']] ?? $toneStyles['slate']; @endphp
            <a href="{{ $card['href'] }}" data-analytics-card="{{ $card['key'] }}" class="group rounded-[1.5rem] border border-slate-200 bg-white p-5 text-left shadow-[0_16px_45px_rgba(15,23,42,0.06)] transition-all duration-200 hover:-translate-y-1 hover:shadow-[0_22px_60px_rgba(15,23,42,0.10)]">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">{{ $card['title'] }}</p>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span data-analytics-value class="text-3xl font-black tracking-tight text-slate-950">{{ $card['value'] }}</span>
                            @if(!empty($card['suffix']))
                                <span class="text-sm font-semibold text-slate-500">{{ $card['suffix'] }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $styles['iconWrap'] }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            {!! $metricIconPaths[$card['key']] ?? $metricIconPaths['results'] !!}
                        </svg>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-between gap-3">
                    <span data-analytics-trend class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold {{ $styles['trend'] }}">
                        @if(($card['trendDirection'] ?? 'flat') === 'down')
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13l-7 7-7-7m14-6l-7 7-7-7"/>
                            </svg>
                        @elseif(($card['trendDirection'] ?? 'flat') === 'flat')
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"/>
                            </svg>
                        @else
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11l7-7 7 7M12 4v16"/>
                            </svg>
                        @endif
                        {{ $card['trend'] }}
                    </span>
                    <p data-analytics-note class="text-xs leading-5 text-slate-500">{{ $card['note'] }}</p>
                </div>
            </a>
        @endforeach
    </section>

    <section id="attendance-analytics" class="grid gap-6 xl:grid-cols-[1.35fr_0.95fr]">
        <x-card class="overflow-hidden border border-slate-200 shadow-[0_18px_50px_rgba(15,23,42,0.06)]">
            <x-slot name="header">
                <div>
                    <h2 class="text-lg font-black text-slate-950">Attendance Trend</h2>
                    <p class="text-sm text-slate-500">Simple attendance movement across the selected session.</p>
                </div>
                <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-[#8B0000]">Trend</span>
            </x-slot>

            <div class="h-[320px]">
                <canvas data-analytics-chart="attendance-trend"></canvas>
            </div>
        </x-card>

        <x-card class="overflow-hidden border border-slate-200 shadow-[0_18px_50px_rgba(15,23,42,0.06)]">
            <x-slot name="header">
                <div>
                    <h2 class="text-lg font-black text-slate-950">Department Attendance</h2>
                    <p class="text-sm text-slate-500">Which departments are keeping attendance steady?</p>
                </div>
            </x-slot>

            <div class="h-[260px]">
                <canvas data-analytics-chart="attendance-comparison"></canvas>
            </div>

            <div class="mt-5 space-y-3" data-analytics-alert-list>
                @forelse($state['alerts'] as $alert)
                    <div class="flex items-start gap-4 rounded-3xl border px-4 py-4 {{ ($alertToneStyles[$alert['tone']] ?? $alertToneStyles['info'])['shell'] }}">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-slate-950">{{ $alert['title'] }}</p>
                            <p class="mt-1 text-sm leading-6 text-slate-600">{{ $alert['message'] }}</p>
                        </div>
                        <a href="{{ $alert['actionHref'] }}" class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-bold text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:text-[#8B0000]">
                            {{ $alert['actionLabel'] ?? 'Open' }}
                        </a>
                    </div>
                @empty
                    <x-empty-state title="No alerts" message="Attendance is stable in the selected view.">
                        <x-slot name="icon">
                            <svg class="mx-auto h-12 w-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                            </svg>
                        </x-slot>
                    </x-empty-state>
                @endforelse
            </div>
        </x-card>
    </section>

    <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <x-card class="border border-slate-200 shadow-[0_18px_50px_rgba(15,23,42,0.06)]">
            <x-slot name="header">
                <div>
                    <h2 class="text-lg font-black text-slate-950">Results / Marks</h2>
                    <p class="text-sm text-slate-500">Pass rate, average marks, and the strongest academic units.</p>
                </div>
            </x-slot>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-[1.5rem] bg-slate-950 p-5 text-white shadow-[0_16px_45px_rgba(15,23,42,0.10)]" data-analytics-top-department-card>
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-white/60">Top Department</p>
                    @if($topDepartment)
                        <h3 class="mt-3 text-2xl font-black tracking-tight">{{ $topDepartment['name'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-white/75">Attendance and results combine to create a simple academic score.</p>
                        <div class="mt-5 flex items-end justify-between gap-4">
                            <div>
                                <span class="text-4xl font-black">{{ number_format($topDepartment['score'], 1) }}%</span>
                                <p class="text-xs uppercase tracking-[0.18em] text-white/60">Performance score</p>
                            </div>
                            <a href="{{ route('admin.students.index', ['department_id' => $topDepartment['department_id']]) }}" class="rounded-2xl bg-white/10 px-4 py-3 text-xs font-bold uppercase tracking-[0.18em] text-white transition hover:bg-white/15">
                                Open Report
                            </a>
                        </div>
                        <div class="mt-5 grid grid-cols-3 gap-2 text-center text-xs text-white/80">
                            <div class="rounded-2xl bg-white/10 px-3 py-3">
                                <p class="font-bold">Attendance</p>
                                <p class="mt-1 text-sm font-black">{{ number_format($topDepartment['attendance_rate'] ?? 0, 1) }}%</p>
                            </div>
                            <div class="rounded-2xl bg-white/10 px-3 py-3">
                                <p class="font-bold">Pass rate</p>
                                <p class="mt-1 text-sm font-black">{{ number_format($topDepartment['pass_rate'] ?? 0, 1) }}%</p>
                            </div>
                            <div class="rounded-2xl bg-white/10 px-3 py-3">
                                <p class="font-bold">Students</p>
                                <p class="mt-1 text-sm font-black">{{ number_format($topDepartment['students'] ?? 0) }}</p>
                            </div>
                        </div>
                    @else
                        <x-empty-state title="No department data" message="Department insights will appear once attendance and marks are available.">
                            <x-slot name="icon">
                                <svg class="mx-auto h-12 w-12 text-white/20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 21V7a2 2 0 012-2h12a2 2 0 012 2v14M8 21v-4m8 4v-8m-4 8V9" />
                                </svg>
                            </x-slot>
                        </x-empty-state>
                    @endif
                </div>

                <div class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm" data-analytics-top-program-card>
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Top Program</p>
                    @if($topProgram)
                        <h3 class="mt-3 text-2xl font-black tracking-tight text-slate-950">{{ $topProgram['name'] }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-500">A quick view of the strongest course in the selected session.</p>
                        <div class="mt-5 flex items-end justify-between gap-4">
                            <div>
                                <span class="text-4xl font-black text-slate-950">{{ number_format($topProgram['score'], 1) }}%</span>
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Performance score</p>
                            </div>
                            <a href="{{ route('admin.students.index', ['program_id' => $topProgram['program_id']]) }}" class="rounded-2xl bg-slate-950 px-4 py-3 text-xs font-bold uppercase tracking-[0.18em] text-white transition hover:bg-slate-800">
                                Open Report
                            </a>
                        </div>
                        <div class="mt-5 grid grid-cols-3 gap-2 text-center text-xs text-slate-500">
                            <div class="rounded-2xl bg-slate-50 px-3 py-3">
                                <p class="font-bold">Attendance</p>
                                <p class="mt-1 text-sm font-black text-slate-950">{{ number_format($topProgram['attendance_rate'] ?? 0, 1) }}%</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-3 py-3">
                                <p class="font-bold">Pass rate</p>
                                <p class="mt-1 text-sm font-black text-slate-950">{{ number_format($topProgram['pass_rate'] ?? 0, 1) }}%</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-3 py-3">
                                <p class="font-bold">Students</p>
                                <p class="mt-1 text-sm font-black text-slate-950">{{ number_format($topProgram['students'] ?? 0) }}</p>
                            </div>
                        </div>
                    @else
                        <x-empty-state title="No program data" message="Program insights will appear once results are published.">
                            <x-slot name="icon">
                                <svg class="mx-auto h-12 w-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h10" />
                                </svg>
                            </x-slot>
                        </x-empty-state>
                    @endif
                </div>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <div class="rounded-2xl bg-slate-50 px-4 py-4 ring-1 ring-slate-200">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Published exams</p>
                    <p class="mt-1 text-2xl font-black text-slate-950" data-analytics-exam-total>{{ number_format($examStats['total'] ?? 0) }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-4 ring-1 ring-slate-200">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Completed exams</p>
                    <p class="mt-1 text-2xl font-black text-slate-950" data-analytics-exam-completed>{{ number_format($examStats['completed'] ?? 0) }}</p>
                </div>
            </div>
        </x-card>

        <x-card id="department-comparison-card" class="border border-slate-200 shadow-[0_18px_50px_rgba(15,23,42,0.06)]">
            <x-slot name="header">
                <div>
                    <h2 class="text-lg font-black text-slate-950">Department Comparison</h2>
                    <p class="text-sm text-slate-500">Compare attendance and pass rate side by side.</p>
                </div>
            </x-slot>

            <div class="h-[330px]">
                <canvas data-analytics-chart="department-comparison"></canvas>
            </div>
        </x-card>
    </section>

    <section id="analytics-drilldown" class="space-y-6">
        <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
            <x-card class="border border-slate-200 shadow-[0_18px_50px_rgba(15,23,42,0.06)]">
            <x-slot name="header">
                <div>
                    <h2 class="text-lg font-black text-slate-950">Department and Program Comparison</h2>
                    <p class="text-sm text-slate-500">A short ranked view of the strongest academic units.</p>
                </div>
            </x-slot>

            <div class="space-y-4">
                <div>
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="text-sm font-bold text-slate-900">Top Departments</h3>
                        <a href="{{ route('admin.students.index') }}" class="text-xs font-bold text-[#8B0000] transition hover:text-[#640000]">Open report</a>
                    </div>
                    <div class="space-y-3" data-analytics-top-department-list>
                        @forelse(collect($state['topDepartments'] ?? [])->take(3) as $row)
                            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-bold text-slate-950">{{ $row['name'] }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ number_format($row['students'] ?? 0) }} students</p>
                                    </div>
                                    <span class="rounded-full bg-red-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-[#8B0000]">{{ number_format($row['score'] ?? 0, 1) }}%</span>
                                </div>
                                <div class="mt-3 grid grid-cols-3 gap-2 text-[11px] text-slate-500">
                                    <span class="rounded-full bg-slate-50 px-2 py-1">Attendance {{ number_format($row['attendance_rate'] ?? 0, 1) }}%</span>
                                    <span class="rounded-full bg-slate-50 px-2 py-1">Pass {{ number_format($row['pass_rate'] ?? 0, 1) }}%</span>
                                    <span class="rounded-full bg-slate-50 px-2 py-1">Marks {{ number_format($row['average_marks'] ?? 0, 1) }}%</span>
                                </div>
                            </div>
                        @empty
                            <x-empty-state title="No department comparison" message="Department rankings will appear once marks and attendance exist.">
                                <x-slot name="icon">
                                    <svg class="mx-auto h-12 w-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 21V7a2 2 0 012-2h12a2 2 0 012 2v14M8 21v-4m8 4v-8m-4 8V9" />
                                    </svg>
                                </x-slot>
                            </x-empty-state>
                        @endforelse
                    </div>
                </div>

                <div>
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="text-sm font-bold text-slate-900">Top Programs</h3>
                        <a href="{{ route('admin.students.index') }}" class="text-xs font-bold text-[#8B0000] transition hover:text-[#640000]">Open report</a>
                    </div>
                    <div class="space-y-3" data-analytics-top-program-list>
                        @forelse(collect($state['topPrograms'] ?? [])->take(3) as $row)
                            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-bold text-slate-950">{{ $row['name'] }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $row['department'] ?? 'Program' }}</p>
                                    </div>
                                    <span class="rounded-full bg-slate-950 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-white">{{ number_format($row['score'] ?? 0, 1) }}%</span>
                                </div>
                                <div class="mt-3 grid grid-cols-3 gap-2 text-[11px] text-slate-500">
                                    <span class="rounded-full bg-slate-50 px-2 py-1">Attendance {{ number_format($row['attendance_rate'] ?? 0, 1) }}%</span>
                                    <span class="rounded-full bg-slate-50 px-2 py-1">Pass {{ number_format($row['pass_rate'] ?? 0, 1) }}%</span>
                                    <span class="rounded-full bg-slate-50 px-2 py-1">Marks {{ number_format($row['average_marks'] ?? 0, 1) }}%</span>
                                </div>
                            </div>
                        @empty
                            <x-empty-state title="No program comparison" message="Program rankings will appear once marks are published.">
                                <x-slot name="icon">
                                    <svg class="mx-auto h-12 w-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h10" />
                                    </svg>
                                </x-slot>
                            </x-empty-state>
                        @endforelse
                    </div>
                </div>
            </div>
        </x-card>
    </div>

        <x-card id="analytics-detail-card" class="border border-slate-200 shadow-[0_18px_50px_rgba(15,23,42,0.06)]">
            <x-slot name="header">
                <div>
                    <h2 class="text-lg font-black text-slate-950">Drill-down</h2>
                    <p class="text-sm text-slate-500">Student list, attendance, and marks per exam appear here when you open a report.</p>
                </div>
                <a href="{{ $reportHref }}" data-analytics-detail-report-link class="text-sm font-bold text-[#8B0000] transition hover:text-[#640000]">Open Report</a>
            </x-slot>

            <div data-analytics-detail-panel>
                <x-empty-state title="Choose a department or program" message="Use the filters above, then click View Details. The report will load student rows, attendance, and marks per exam without reloading the page.">
                    <x-slot name="icon">
                        <svg class="mx-auto h-12 w-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m-6-8h6m2 12H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </x-slot>
                </x-empty-state>
            </div>
        </x-card>
    </section>
</div>
@endsection