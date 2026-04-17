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
    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_24px_70px_rgba(15,23,42,0.07)] sm:p-8">
        <div class="flex flex-col gap-5 border-b border-slate-100 pb-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-950">Analytics</h1>
                    <p class="mt-2 text-sm text-slate-600">Deep analysis made simple. Pick one metric, then read the data.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2" role="tablist" aria-label="Metric selector">
                    @foreach($metricOptions as $metricOption)
                        @continue(!in_array($metricOption['key'], ['attendance', 'academic', 'admissions'], true))
                        @php
                            $metricTone = $metricStyles[$metricOption['tone']] ?? $metricStyles['slate'];
                        @endphp
                        <button
                            type="button"
                            data-analytics-metric-button
                            data-metric="{{ $metricOption['key'] }}"
                            data-metric-tone="{{ $metricOption['tone'] }}"
                            class="rounded-2xl border px-4 py-2.5 text-sm font-bold transition-all duration-200 {{ !empty($metricOption['selected']) ? $metricTone['active'] : $metricTone['idle'] }}">
                            {{ $metricOption['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-3">
                <div>
                    <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Academic Session</label>
                    <select data-analytics-session-select class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                        @foreach($state['sessionOptions'] ?? [] as $sessionOption)
                            <option value="{{ $sessionOption['id'] }}" @selected(!empty($sessionOption['selected']))>
                                {{ $sessionOption['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Department</label>
                    <select data-analytics-department-select class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                        <option value="">All departments</option>
                        @foreach($state['departmentOptions'] ?? [] as $departmentOption)
                            <option value="{{ $departmentOption['id'] }}" @selected(!empty($departmentOption['selected']))>
                                {{ $departmentOption['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Program</label>
                    <select data-analytics-program-select class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                        <option value="">All programs</option>
                        @foreach($state['programOptions'] ?? [] as $programOption)
                            <option value="{{ $programOption['id'] }}" @selected(!empty($programOption['selected']))>
                                {{ $programOption['label'] }}@if(!empty($programOption['department'])) / {{ $programOption['department'] }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="mt-7 rounded-[1.5rem] border border-slate-200 bg-white p-5 sm:p-6">
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Main Chart</p>
            <h2 data-analytics-main-title class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ $mainChart['title'] ?? $selectedMetricLabel }}</h2>
            <p data-analytics-main-subtitle class="mt-1 text-sm text-slate-600">{{ $mainChart['subtitle'] ?? $selectedMetricDescription }}</p>
            <div class="mt-5 h-[430px] rounded-[1.25rem] bg-slate-50 p-4 ring-1 ring-slate-200/80">
                <canvas data-analytics-main-chart></canvas>
            </div>
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <div class="rounded-[1.5rem] border border-slate-200 bg-white p-5 sm:p-6">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Comparison</p>
                <h3 data-analytics-comparison-title class="mt-2 text-xl font-black tracking-tight text-slate-950">{{ $comparisonChart['title'] ?? 'Comparison' }}</h3>
                <p data-analytics-comparison-subtitle class="mt-1 text-sm text-slate-600">{{ $comparisonChart['subtitle'] ?? '' }}</p>
                <div class="mt-4 h-[230px] rounded-[1rem] bg-slate-50 p-3 ring-1 ring-slate-200/80">
                    <canvas data-analytics-comparison-chart></canvas>
                </div>
            </div>

            <div class="rounded-[1.5rem] border border-slate-200 bg-white p-5 sm:p-6">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Insights</p>
                <h3 class="mt-2 text-xl font-black tracking-tight text-slate-950">Quick observations</h3>
                <ul data-analytics-insight-panel class="mt-4 space-y-2 text-sm leading-6 text-slate-700">
                    @foreach(array_slice($state['insights'] ?? [], 0, 3) as $insight)
                        <li>• {{ $insight['message'] }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>
</div>
@endsection
