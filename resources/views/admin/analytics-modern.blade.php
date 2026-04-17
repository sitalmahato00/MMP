@extends('layouts.app')

@section('title', 'Analytics')

@section('content')
@php
    $state = $analyticsState;
    $metricOptions = $state['metricOptions'] ?? [];
    $selectedMetric = $state['selectedMetric'] ?? 'attendance';
    $selectedMetricLabel = $state['selectedMetricLabel'] ?? 'Attendance';
    $selectedMetricDescription = $state['selectedMetricDescription'] ?? '';
    $selectedSessionLabel = $state['selectedSessionLabel'] ?? 'Current session';
    $selectedDepartmentLabel = $state['selectedDepartmentLabel'] ?? 'All departments';
    $selectedProgramLabel = $state['selectedProgramLabel'] ?? 'All programs';
    $reportHref = $state['reportHref'] ?? route('admin.students.index');
    $mainChart = $state['mainChart'] ?? [];
    $comparisonChart = $state['comparisonChart'] ?? [];

    $metricStyles = [
        'red' => [
            'idle' => 'border-slate-200 bg-white text-slate-700 hover:border-red-200 hover:bg-red-50/60 hover:text-[#8B0000]',
            'active' => 'border-red-200 bg-red-50 text-[#8B0000] shadow-[0_12px_30px_rgba(139,0,0,0.10)]',
            'badge' => 'bg-red-50 text-[#8B0000]',
        ],
        'amber' => [
            'idle' => 'border-slate-200 bg-white text-slate-700 hover:border-amber-200 hover:bg-amber-50/60 hover:text-amber-700',
            'active' => 'border-amber-200 bg-amber-50 text-amber-700 shadow-[0_12px_30px_rgba(180,83,9,0.10)]',
            'badge' => 'bg-amber-50 text-amber-700',
        ],
        'slate' => [
            'idle' => 'border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-950',
            'active' => 'border-slate-300 bg-slate-950 text-white shadow-[0_12px_30px_rgba(15,23,42,0.12)]',
            'badge' => 'bg-slate-950 text-white',
        ],
    ];

    $insightStyles = [
        'info' => 'border-sky-200 bg-sky-50/70 text-sky-800',
        'success' => 'border-emerald-200 bg-emerald-50/70 text-emerald-800',
        'warning' => 'border-amber-200 bg-amber-50/70 text-amber-800',
    ];
@endphp

<div id="analytics-page"
    class="w-full space-y-8"
    data-analytics-page
    data-analytics-endpoint="{{ route('admin.analytics') }}"
    data-analytics-state='@js($state)'>
    <x-page-header title="Analytics" subtitle="Explore attendance, results, and admissions with one active lens at a time. Summary lives on the dashboard; this page is for digging deeper.">
        <x-slot name="actions">
            <x-btn href="{{ route('admin.students.index') }}" variant="secondary">Students</x-btn>
            <x-btn href="{{ route('admin.exams.index') }}" variant="secondary">Exams</x-btn>
        </x-slot>
    </x-page-header>

    <section class="relative overflow-hidden rounded-[2rem] border border-slate-200 bg-white px-5 py-5 shadow-[0_28px_80px_rgba(15,23,42,0.08)] sm:px-6 sm:py-6 lg:px-8">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(139,0,0,0.08),transparent_35%),radial-gradient(circle_at_bottom_left,rgba(148,163,184,0.14),transparent_28%)]"></div>
        <div class="relative grid gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">
            <aside class="xl:sticky xl:top-6 xl:self-start">
                <div class="rounded-[1.75rem] border border-slate-200 bg-slate-950 p-5 text-white shadow-[0_18px_50px_rgba(15,23,42,0.10)]">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-white/55">Control rail</p>
                        <h2 class="mt-3 text-2xl font-black tracking-tight">Filters and metric lens</h2>
                        <p class="mt-2 text-sm leading-6 text-white/72">Change the scope on the left, then let the charts on the right reshape around the active metric.</p>
                    </div>

                    <div class="mt-6 space-y-4">
                        <div>
                            <label class="mb-2 block text-[11px] font-bold uppercase tracking-[0.18em] text-white/55">Academic session</label>
                            <select data-analytics-session-select class="w-full rounded-2xl border border-white/10 bg-white/95 px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                                @foreach($state['sessionOptions'] ?? [] as $sessionOption)
                                    <option value="{{ $sessionOption['id'] }}" @selected(!empty($sessionOption['selected']))>
                                        {{ $sessionOption['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-[11px] font-bold uppercase tracking-[0.18em] text-white/55">Department</label>
                            <select data-analytics-department-select class="w-full rounded-2xl border border-white/10 bg-white/95 px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                                <option value="">All departments</option>
                                @foreach($state['departmentOptions'] ?? [] as $departmentOption)
                                    <option value="{{ $departmentOption['id'] }}" @selected(!empty($departmentOption['selected']))>
                                        {{ $departmentOption['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-[11px] font-bold uppercase tracking-[0.18em] text-white/55">Program</label>
                            <select data-analytics-program-select class="w-full rounded-2xl border border-white/10 bg-white/95 px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                                <option value="">All programs</option>
                                @foreach($state['programOptions'] ?? [] as $programOption)
                                    <option value="{{ $programOption['id'] }}" @selected(!empty($programOption['selected']))>
                                        {{ $programOption['label'] }}@if(!empty($programOption['department'])) / {{ $programOption['department'] }}@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-6">
                        <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-white/55">Metric lens</p>
                        <div class="mt-3 space-y-3">
                            @foreach($metricOptions as $metricOption)
                                @php
                                    $metricTone = $metricStyles[$metricOption['tone']] ?? $metricStyles['slate'];
                                @endphp
                                <button type="button"
                                    data-analytics-metric-button
                                    data-metric="{{ $metricOption['key'] }}"
                                    data-metric-tone="{{ $metricOption['tone'] }}"
                                    class="group w-full rounded-[1.35rem] border px-4 py-4 text-left transition-all duration-200 {{ !empty($metricOption['selected']) ? $metricTone['active'] : $metricTone['idle'] }}">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-sm font-black tracking-tight">{{ $metricOption['label'] }}</p>
                                            <p class="mt-1 text-xs leading-5 opacity-80">{{ $metricOption['description'] }}</p>
                                        </div>
                                        <span data-metric-badge class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] {{ $metricTone['badge'] }}">
                                            {{ $metricOption['key'] === $selectedMetric ? 'Active' : 'Lens' }}
                                        </span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-6 grid gap-2 text-xs font-semibold text-white/70">
                        <span class="inline-flex items-center gap-2 rounded-2xl bg-white/6 px-3 py-2">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                            Session: <span data-analytics-session-pill>{{ $selectedSessionLabel }}</span>
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-2xl bg-white/6 px-3 py-2">
                            <span class="h-2 w-2 rounded-full bg-[#8B0000]"></span>
                            Department: <span data-analytics-department-pill>{{ $selectedDepartmentLabel }}</span>
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-2xl bg-white/6 px-3 py-2">
                            <span class="h-2 w-2 rounded-full bg-sky-400"></span>
                            Program: <span data-analytics-program-pill>{{ $selectedProgramLabel }}</span>
                        </span>
                    </div>

                    <div class="mt-6 flex flex-col gap-3">
                        <button type="button" data-analytics-open-details class="inline-flex items-center justify-center rounded-2xl bg-white px-4 py-3 text-sm font-bold text-slate-950 transition hover:bg-slate-100">
                            View Details
                        </button>
                        <a href="{{ $reportHref }}" data-analytics-report-link class="inline-flex items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/10">
                            Open Filtered Report
                        </a>
                        <span data-analytics-loading class="hidden inline-flex items-center justify-center rounded-2xl bg-amber-50 px-4 py-3 text-xs font-bold uppercase tracking-[0.18em] text-amber-700 ring-1 ring-amber-100">
                            Updating charts
                        </span>
                    </div>

                    <p class="mt-4 text-xs leading-6 text-white/55">
                        Only the active metric is loaded. The drill-down table stays hidden until you request it.
                    </p>
                </div>
            </aside>

            <div class="space-y-6 min-w-0">
                <div class="rounded-[1.85rem] border border-slate-200 bg-white p-6 shadow-[0_18px_50px_rgba(15,23,42,0.06)]">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Main chart</p>
                            <h2 data-analytics-main-title class="mt-2 text-3xl font-black tracking-tight text-slate-950">{{ $mainChart['title'] ?? $selectedMetricLabel }}</h2>
                            <p data-analytics-main-subtitle class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">{{ $mainChart['subtitle'] ?? $selectedMetricDescription }}</p>
                        </div>
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.18em] text-slate-600">
                            {{ $selectedMetricLabel }}
                        </span>
                    </div>

                    <div class="mt-6 h-[400px] rounded-[1.5rem] bg-gradient-to-br from-slate-50 to-white p-4 ring-1 ring-slate-200/80">
                        <canvas data-analytics-main-chart></canvas>
                    </div>
                </div>

                <div class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
                    <div class="rounded-[1.85rem] border border-slate-200 bg-white p-6 shadow-[0_18px_50px_rgba(15,23,42,0.06)]">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Comparison chart</p>
                                <h3 data-analytics-comparison-title class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ $comparisonChart['title'] ?? 'Comparison' }}</h3>
                                <p data-analytics-comparison-subtitle class="mt-2 text-sm leading-6 text-slate-600">{{ $comparisonChart['subtitle'] ?? 'A focused comparison that changes with the active metric.' }}</p>
                            </div>
                            <span class="rounded-full bg-slate-950 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.18em] text-white">
                                Compare
                            </span>
                        </div>

                        <div class="mt-6 h-[320px] rounded-[1.5rem] bg-slate-50 p-4 ring-1 ring-slate-200/80">
                            <canvas data-analytics-comparison-chart></canvas>
                        </div>
                    </div>

                    <div class="rounded-[1.85rem] border border-slate-200 bg-white p-6 shadow-[0_18px_50px_rgba(15,23,42,0.06)]">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Insights</p>
                                <h3 class="mt-2 text-2xl font-black tracking-tight text-slate-950">Simple observations</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Short, readable notes from the active metric. No dashboard-style KPI grid here.</p>
                            </div>
                            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.18em] text-slate-600">
                                Text only
                            </span>
                        </div>

                        <div data-analytics-insight-panel class="mt-5 space-y-3">
                            @forelse($state['insights'] ?? [] as $insight)
                                @php $insightTone = $insightStyles[$insight['tone']] ?? $insightStyles['info']; @endphp
                                <div class="rounded-2xl border px-4 py-4 {{ $insightTone }}">
                                    <p class="text-sm font-black tracking-tight text-slate-950">{{ $insight['title'] }}</p>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">{{ $insight['message'] }}</p>
                                </div>
                            @empty
                                <x-empty-state title="No insights" message="Select a metric to generate analysis.">
                                    <x-slot name="icon">
                                        <svg class="mx-auto h-12 w-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m-6-8h6m2 12H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </x-slot>
                                </x-empty-state>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="analytics-drilldown" hidden data-analytics-detail-section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_18px_50px_rgba(15,23,42,0.06)]">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-5">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">Drill-down</p>
                <h3 class="mt-2 text-2xl font-black tracking-tight text-slate-950">Student-level data</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">This section stays hidden until you click View Details. It loads only the currently selected scope.</p>
            </div>
            <a href="{{ $reportHref }}" class="inline-flex items-center rounded-2xl bg-slate-950 px-4 py-3 text-sm font-bold text-white transition hover:bg-slate-800">
                Open Filtered Report
            </a>
        </div>

        <div data-analytics-detail-panel class="mt-6">
            <x-empty-state title="Open the drill-down" message="Click View Details to load student rows with marks and attendance for the current filters.">
                <x-slot name="icon">
                    <svg class="mx-auto h-12 w-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m-6-8h6m2 12H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </x-slot>
            </x-empty-state>
        </div>
    </section>
</div>
@endsection
