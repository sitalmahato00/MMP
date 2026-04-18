@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $dashboardUrl = function (string $periodValue) use ($selectedSession) {
        $params = ['period' => $periodValue];
        if ($selectedSession?->id) $params['session_id'] = $selectedSession->id;
        return route('admin.dashboard', $params);
    };

    $toneMap = [
        'blue'    => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'ring' => 'ring-blue-100', 'bar' => 'bg-blue-500'],
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'ring' => 'ring-emerald-100', 'bar' => 'bg-emerald-500'],
        'violet'  => ['bg' => 'bg-violet-50', 'text' => 'text-violet-600', 'ring' => 'ring-violet-100', 'bar' => 'bg-violet-500'],
        'amber'   => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'ring' => 'ring-amber-100', 'bar' => 'bg-amber-500'],
        'indigo'  => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'ring' => 'ring-indigo-100', 'bar' => 'bg-indigo-500'],
        'rose'    => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'ring' => 'ring-rose-100', 'bar' => 'bg-rose-500'],
    ];

    $iconPaths = [
        'students'     => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a4 4 0 11-8 0 4 4 0 018 0z',
        'attendance'   => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
        'results'      => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        'applications' => 'M7 8h10M7 12h10M7 16h6M9 3h6a2 2 0 012 2v14l-5-3-5 3V5a2 2 0 012-2z',
        'semesters'    => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'departments'  => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    ];

    $alertTones = [
        'danger'  => ['bg' => 'bg-rose-50', 'border' => 'border-rose-200', 'icon' => 'text-rose-500', 'dot' => 'bg-rose-500'],
        'warning' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'icon' => 'text-amber-500', 'dot' => 'bg-amber-500'],
        'success' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'icon' => 'text-emerald-500', 'dot' => 'bg-emerald-500'],
        'info'    => ['bg' => 'bg-sky-50', 'border' => 'border-sky-200', 'icon' => 'text-sky-500', 'dot' => 'bg-sky-500'],
    ];

    $statusColors = [
        'pending'   => 'bg-amber-100 text-amber-700',
        'reviewed'  => 'bg-sky-100 text-sky-700',
        'contacted' => 'bg-violet-100 text-violet-700',
        'accepted'  => 'bg-emerald-100 text-emerald-700',
        'rejected'  => 'bg-rose-100 text-rose-700',
    ];

    $semesterStatusColors = [
        'running'   => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-500', 'bar' => 'bg-emerald-500'],
        'delayed'   => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'dot' => 'bg-amber-500', 'bar' => 'bg-amber-500'],
        'completed' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'dot' => 'bg-slate-400', 'bar' => 'bg-slate-400'],
    ];

    $sessionName = $selectedSession?->name ?? $activeSession?->name ?? 'Current session';
    $rangeLabel = isset($rangeStart, $rangeEnd) ? bsDate($rangeStart, 'd M Y') . ' – ' . bsDate($rangeEnd, 'd M Y') : null;
    $semesters = $runningSemesters ?? [];
    $ctevtGeneralItems = collect($ctevtGeneralNotices['items'] ?? []);
    $ctevtResultItems = collect($ctevtResultNotices['items'] ?? []);
    $ctevtGeneralPageUrl = $ctevtGeneralNotices['page_url'] ?? route('public.notices', ['type' => 'ctevt-general']);
    $ctevtResultPageUrl = $ctevtResultNotices['page_url'] ?? route('public.notices', ['type' => 'ctevt-result']);
    $dashboardStateJson = json_encode($dashboardState, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    $dashboardStateEncoded = $dashboardStateJson ? base64_encode($dashboardStateJson) : '';
@endphp

<div id="principal-dashboard"
    class="space-y-6"
    data-principal-dashboard
    data-dashboard-endpoint="{{ route('admin.dashboard') }}"
    data-dashboard-state="{{ $dashboardStateEncoded }}">

    {{-- ═══════════════════════════════════════════════════════════
         1. TOP HEADER – Smart Control Bar
    ═══════════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50/40"></div>
        <div class="relative px-6 py-5 sm:px-8">
            {{-- Row 1: Greeting + Quick Actions --}}
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Principal Dashboard</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                        {{ $greeting }}, {{ auth()->user()->name ?? 'Principal' }}
                    </h1>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.students.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-800">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Student
                    </a>
                    <a href="{{ route('admin.notices.create') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                        Create Notice
                    </a>
                    <a href="{{ route('admin.applications.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                        Manage Admissions
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                        Generate Report
                    </a>
                    <a href="{{ route('admin.analytics') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6m6 0h6m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0h6"/></svg>
                        Open Analytics
                    </a>
                </div>
            </div>

            {{-- Row 2: Session + Semester Chips + Date Range --}}
            <div class="mt-4 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-4">
                <div class="flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    <span data-dashboard-session-display>{{ $sessionName }}</span>
                </div>

                @foreach($semesters as $sem)
                    @php $semColor = $semesterStatusColors[$sem['status']] ?? $semesterStatusColors['running']; @endphp
                    <span class="inline-flex items-center gap-1.5 rounded-lg {{ $semColor['bg'] }} px-2.5 py-1 text-[11px] font-semibold {{ $semColor['text'] }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $semColor['dot'] }}"></span>
                        Sem {{ $sem['number'] }}
                    </span>
                @endforeach

                <div class="ml-auto flex items-center gap-2 text-xs text-slate-500">
                    @if($rangeLabel)
                        <span data-dashboard-range-display>{{ $rangeLabel }}</span>
                        <span class="text-slate-300">|</span>
                    @endif
                    <span data-dashboard-updated-display>Updated {{ $lastUpdated->format('h:i A') }}</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         2. KPI METRICS – 6 Smart Cards
    ═══════════════════════════════════════════════════════════ --}}
    <section id="dashboard-kpis" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        @foreach($kpiCards as $card)
            @php $t = $toneMap[$card['tone']] ?? $toneMap['blue']; @endphp
            <div data-kpi-card="{{ $card['key'] }}" class="group relative overflow-hidden rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-start justify-between">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg {{ $t['bg'] }}">
                        <svg class="h-4 w-4 {{ $t['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths[$card['icon']] ?? $iconPaths['students'] }}"/>
                        </svg>
                    </div>
                    <span data-kpi-trend class="inline-flex items-center gap-1 rounded-md {{ $t['bg'] }} px-1.5 py-0.5 text-[10px] font-semibold {{ $t['text'] }}">
                        @if($card['trendDirection'] === 'up')
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17l5-5 5 5M7 7l5 5 5-5"/></svg>
                        @elseif($card['trendDirection'] === 'down')
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 7l-5 5-5-5m0 10l5-5 5 5"/></svg>
                        @endif
                        {{ $card['trend'] }}
                    </span>
                </div>
                <div class="mt-3">
                    <div class="flex items-baseline gap-1">
                        <span data-kpi-value class="text-2xl font-bold tracking-tight text-slate-900">{{ $card['value'] }}</span>
                        @if($card['suffix'])
                            <span class="text-sm font-medium text-slate-400">{{ $card['suffix'] }}</span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">{{ $card['title'] }}</p>
                </div>
                <p data-kpi-note class="mt-2 text-[11px] text-slate-500">{{ $card['note'] }}</p>
                {{-- Sparkline placeholder bar --}}
                <div class="absolute bottom-0 left-0 right-0 h-0.5 {{ $t['bar'] }} opacity-40"></div>
            </div>
        @endforeach
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         3. MAIN ANALYTICS – Charts + Semester Status Panel
    ═══════════════════════════════════════════════════════════ --}}
    <section id="main-insights" class="grid gap-5 xl:grid-cols-[1fr_340px]">
        {{-- LEFT: Charts --}}
        <div class="space-y-5">
            {{-- Enrollment Trend --}}
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">Enrollment & Admissions Trend</h2>
                        <p class="text-xs text-slate-500">Monthly admissions momentum</p>
                    </div>
                    <span class="rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-600">{{ $periodLabel }}</span>
                </div>
                <div class="mt-4 h-[200px]">
                    <canvas id="principal-enrollment-chart" data-principal-chart="enrollment"></canvas>
                </div>
            </div>

            {{-- Department Performance (Horizontal Bar) --}}
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">Department Performance</h2>
                        <p class="text-xs text-slate-500">Composite score: 45% attendance + 55% pass rate</p>
                    </div>
                    @if($highlight)
                        <span class="rounded-md bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-600">Top: {{ $highlight['label'] }}</span>
                    @endif
                </div>
                <div class="mt-4 h-[200px]">
                    <canvas id="principal-department-chart" data-principal-chart="department"></canvas>
                </div>
            </div>
        </div>

        {{-- RIGHT: Semester Status Panel --}}
        <div class="space-y-5">
            {{-- Active Academic Flow Card --}}
            <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50">
                            <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900">Active Academic Flow</h2>
                            <p class="text-[11px] text-slate-500">Session: {{ $sessionName }}</p>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-slate-100 px-5">
                    @forelse($semesters as $sem)
                        @php $sc = $semesterStatusColors[$sem['status']] ?? $semesterStatusColors['running']; @endphp
                        <div class="py-3.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full {{ $sc['dot'] }}"></span>
                                    <span class="text-sm font-semibold text-slate-900">{{ $sem['label'] }}</span>
                                </div>
                                <span class="rounded-md {{ $sc['bg'] }} px-2 py-0.5 text-[10px] font-semibold {{ $sc['text'] }}">
                                    {{ $sem['statusLabel'] }}
                                </span>
                            </div>
                            @if($sem['delayReason'])
                                <p class="mt-1 text-[11px] text-amber-600">{{ $sem['delayReason'] }}</p>
                            @endif
                            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full {{ $sc['bar'] }} transition-all duration-500" style="width: {{ $sem['progress'] }}%"></div>
                            </div>
                            <div class="mt-1 flex justify-between text-[10px] text-slate-400">
                                <span>{{ $sem['startDate'] ?? '—' }}</span>
                                <span>{{ $sem['progress'] }}%</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center">
                            <p class="text-xs text-slate-400">No semesters configured for this session.</p>
                        </div>
                    @endforelse
                </div>

                @if(count($semesters) > 0)
                    <div class="border-t border-slate-100 px-5 py-3">
                        <a href="{{ route('admin.academic-sessions.index') }}" class="text-xs font-semibold text-blue-600 transition hover:text-blue-700">
                            View Details →
                        </a>
                    </div>
                @endif
            </div>

            {{-- Quick Stats --}}
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Community</p>
                <div class="mt-3 grid grid-cols-3 gap-3">
                    <div class="text-center">
                        <p class="text-lg font-bold text-slate-900">{{ number_format($totalTeachers ?? 0) }}</p>
                        <p class="text-[10px] text-slate-500">Teachers</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold text-slate-900">{{ number_format($totalParents ?? 0) }}</p>
                        <p class="text-[10px] text-slate-500">Parents</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold text-slate-900">{{ number_format($totalAlumni ?? 0) }}</p>
                        <p class="text-[10px] text-slate-500">Alumni</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         4. ALERTS & INSIGHTS + HIGHLIGHTS
    ═══════════════════════════════════════════════════════════ --}}
    <section class="grid gap-5 xl:grid-cols-[1fr_340px]">
        {{-- Alerts --}}
        <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Insights & Alerts</h2>
                    <p class="text-xs text-slate-500">AI-style intelligent observations</p>
                </div>
            </div>
            <div data-dashboard-alert-list class="divide-y divide-slate-100 px-5">
                @forelse($alerts as $alert)
                    @php $at = $alertTones[$alert['tone']] ?? $alertTones['info']; @endphp
                    <div class="flex items-start gap-3 py-3.5">
                        <div class="mt-0.5 h-2 w-2 shrink-0 rounded-full {{ $at['dot'] }}"></div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-slate-900">{{ $alert['title'] }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $alert['message'] }}</p>
                        </div>
                        @if(!empty($alert['actionHref']))
                            <a href="{{ $alert['actionHref'] }}" class="shrink-0 rounded-md border border-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
                                {{ $alert['actionLabel'] ?? 'View' }}
                            </a>
                        @endif
                    </div>
                @empty
                    <div class="py-8 text-center">
                        <p class="text-xs text-slate-400">No alerts for this period.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Highlight: Top Department --}}
        <div data-dashboard-highlight>
            @if($highlight)
                <div class="overflow-hidden rounded-xl border border-slate-200/80 shadow-sm">
                    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-blue-900 px-5 py-5 text-white">
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-white/50">Top Department</p>
                        <h3 class="mt-2 text-xl font-bold tracking-tight">{{ $highlight['name'] }}</h3>
                        <p class="mt-1 text-xs text-white/70">{{ $highlight['summary'] }}</p>

                        <div class="mt-4 flex items-end justify-between">
                            <div>
                                <span class="text-3xl font-bold">{{ number_format($highlight['score'], 1) }}%</span>
                                <p class="text-[10px] uppercase tracking-wider text-white/50">Performance</p>
                            </div>
                            <div class="rounded-lg bg-white/10 px-3 py-2 text-right">
                                <p class="text-[10px] text-white/50">Students</p>
                                <p class="text-sm font-bold">{{ number_format($highlight['students']) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 divide-x divide-slate-100 bg-white">
                        <div class="px-3 py-3 text-center">
                            <p class="text-sm font-bold text-slate-900">{{ number_format($highlight['attendance_rate'] ?? 0, 1) }}%</p>
                            <p class="text-[10px] text-slate-500">Attendance</p>
                        </div>
                        <div class="px-3 py-3 text-center">
                            <p class="text-sm font-bold text-slate-900">{{ number_format($highlight['pass_rate'] ?? 0, 1) }}%</p>
                            <p class="text-[10px] text-slate-500">Pass Rate</p>
                        </div>
                        <div class="px-3 py-3 text-center">
                            <p class="text-sm font-bold text-slate-900">{{ number_format($highlight['score'], 1) }}%</p>
                            <p class="text-[10px] text-slate-500">Score</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="flex h-full items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-8">
                    <div class="text-center">
                        <svg class="mx-auto h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/></svg>
                        <p class="mt-2 text-xs font-medium text-slate-500">No highlight data yet</p>
                        <p class="text-[11px] text-slate-400">Add attendance and results to see top department.</p>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         5. BOTTOM – Notices + Applications
    ═══════════════════════════════════════════════════════════ --}}
    <section class="grid gap-5 xl:grid-cols-2">
        {{-- Recent Notices --}}
        <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Recent Notices</h2>
                <a href="{{ route('admin.notices.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">View all</a>
            </div>
            <div data-dashboard-notice-list class="divide-y divide-slate-100">
                @forelse($recentNotices as $notice)
                    <a href="{{ route('admin.notices.edit', $notice) }}" class="flex gap-3 px-5 py-3.5 transition hover:bg-slate-50">
                        <div class="flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                            <span class="text-[9px] font-semibold uppercase leading-none">{{ bsDate($notice->created_at, 'M') }}</span>
                            <span class="text-sm font-bold leading-none">{{ bsDate($notice->created_at, 'd') }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-900">{{ $notice->title }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ bsDate($notice->created_at, 'd M Y') }} · {{ $notice->author->name ?? 'System' }}</p>
                        </div>
                        <span class="shrink-0 self-center rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600">{{ $notice->type }}</span>
                    </a>
                @empty
                    <div class="py-8 text-center">
                        <p class="text-xs text-slate-400">No recent notices.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Applications --}}
        <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Recent Applications</h2>
                <a href="{{ route('admin.applications.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">View all</a>
            </div>
            <div data-dashboard-application-list class="divide-y divide-slate-100">
                @forelse($recentApplications as $application)
                    @php $appStatus = $application->status ?? 'pending'; @endphp
                    <a href="{{ route('admin.applications.show', $application) }}" class="flex items-center gap-3 px-5 py-3.5 transition hover:bg-slate-50">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-900">{{ $application->full_name }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $application->department->name ?? 'General' }} · {{ bsDate($application->created_at, 'd M Y') }}</p>
                        </div>
                        <span class="shrink-0 rounded-md px-2 py-0.5 text-[10px] font-semibold {{ $statusColors[$appStatus] ?? $statusColors['pending'] }}">
                            {{ ucfirst($appStatus) }}
                        </span>
                    </a>
                @empty
                    <div class="py-8 text-center">
                        <p class="text-xs text-slate-400">No applications yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         6. CTEVT Notices – Collapsible Tabbed
    ═══════════════════════════════════════════════════════════ --}}
    <section x-data="{ ctevtOpen: false, activeTab: 'general' }" class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <button type="button" @click="ctevtOpen = !ctevtOpen" class="flex w-full items-center justify-between px-5 py-4 text-left transition hover:bg-slate-50">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">CTEVT Notices</h2>
                <p class="text-xs text-slate-500">Live notices and published results from CTEVT</p>
            </div>
            <svg :class="ctevtOpen && 'rotate-180'" class="h-4 w-4 text-slate-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>

        <div x-show="ctevtOpen" x-cloak x-transition class="border-t border-slate-100 px-5 pb-4 pt-3">
            {{-- Tabs --}}
            <div class="flex gap-1 rounded-lg bg-slate-100 p-0.5">
                <button @click="activeTab = 'general'" :class="activeTab === 'general' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500 hover:text-slate-700'" class="flex-1 rounded-md px-3 py-1.5 text-xs font-semibold transition">
                    General <span class="ml-1 text-[10px] text-slate-400">{{ $ctevtGeneralItems->count() }}</span>
                </button>
                <button @click="activeTab = 'result'" :class="activeTab === 'result' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500 hover:text-slate-700'" class="flex-1 rounded-md px-3 py-1.5 text-xs font-semibold transition">
                    Results <span class="ml-1 text-[10px] text-slate-400">{{ $ctevtResultItems->count() }}</span>
                </button>
            </div>

            {{-- General notices --}}
            <div x-show="activeTab === 'general'" class="mt-3 space-y-2">
                @forelse($ctevtGeneralItems as $notice)
                    <a href="{{ $notice['url'] ?? $ctevtGeneralPageUrl }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 rounded-lg border border-slate-100 px-3 py-2.5 transition hover:border-slate-200 hover:bg-slate-50">
                        <span class="shrink-0 rounded bg-red-50 px-1.5 py-0.5 text-[9px] font-bold text-red-600">CTEVT</span>
                        <span class="min-w-0 flex-1 truncate text-xs font-medium text-slate-700">{{ $notice['title'] ?? 'Notice' }}</span>
                        @if(!empty($notice['updated_date']))
                            <span class="shrink-0 text-[10px] text-slate-400">{{ $notice['updated_date'] }}</span>
                        @endif
                    </a>
                @empty
                    <p class="py-4 text-center text-xs text-slate-400">No general notices available.</p>
                @endforelse
            </div>

            {{-- Result notices --}}
            <div x-show="activeTab === 'result'" x-cloak class="mt-3 space-y-2">
                @forelse($ctevtResultItems as $notice)
                    <a href="{{ $notice['url'] ?? $ctevtResultPageUrl }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 rounded-lg border border-slate-100 px-3 py-2.5 transition hover:border-slate-200 hover:bg-slate-50">
                        <span class="shrink-0 rounded bg-emerald-50 px-1.5 py-0.5 text-[9px] font-bold text-emerald-600">CTEVT</span>
                        <span class="min-w-0 flex-1 truncate text-xs font-medium text-slate-700">{{ $notice['title'] ?? 'Result' }}</span>
                        @if(!empty($notice['updated_date']))
                            <span class="shrink-0 text-[10px] text-slate-400">{{ $notice['updated_date'] }}</span>
                        @endif
                    </a>
                @empty
                    <p class="py-4 text-center text-xs text-slate-400">No result notices available.</p>
                @endforelse
            </div>

            <div class="mt-3 flex justify-end gap-3 text-[11px] font-semibold">
                <a href="{{ route('public.notices', ['type' => 'ctevt-general']) }}" class="text-blue-600 hover:text-blue-700">View General</a>
                <a href="{{ route('public.notices', ['type' => 'ctevt-result']) }}" class="text-blue-600 hover:text-blue-700">View Results</a>
            </div>
        </div>
    </section>
</div>
@endsection
@extends('layouts.app')

@section('title', 'Principal Dashboard')

@section('content')
@php
    $dashboardUrl = function (string $periodValue) use ($selectedSession) {
        $params = ['period' => $periodValue];

        if ($selectedSession?->id) {
            $params['session_id'] = $selectedSession->id;
        }

        return route('admin.dashboard', $params);
    };

    $metricIconPaths = [
        'students' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
        'attendance' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>',
        'results' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
        'applications' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h10M7 16h6M9 3h6a2 2 0 012 2v14l-5-3-5 3V5a2 2 0 012-2z"/>',
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

    $alertStyles = [
        'danger' => [
            'shell' => 'border-rose-200 bg-rose-50/70',
            'iconWrap' => 'bg-rose-100 text-rose-700',
            'pill' => 'bg-rose-100 text-rose-700 ring-1 ring-rose-200',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.72 3h16.92a2 2 0 001.72-3L13.71 3.86a2 2 0 00-3.42 0z"/>',
        ],
        'warning' => [
            'shell' => 'border-amber-200 bg-amber-50/70',
            'iconWrap' => 'bg-amber-100 text-amber-700',
            'pill' => 'bg-amber-100 text-amber-700 ring-1 ring-amber-200',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.72 3h16.92a2 2 0 001.72-3L13.71 3.86a2 2 0 00-3.42 0z"/>',
        ],
        'info' => [
            'shell' => 'border-sky-200 bg-sky-50/70',
            'iconWrap' => 'bg-sky-100 text-sky-700',
            'pill' => 'bg-sky-100 text-sky-700 ring-1 ring-sky-200',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 21a9 9 0 100-18 9 9 0 000 18z"/>',
        ],
        'success' => [
            'shell' => 'border-emerald-200 bg-emerald-50/70',
            'iconWrap' => 'bg-emerald-100 text-emerald-700',
            'pill' => 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
        ],
    ];

    $statusStyles = [
        'pending' => 'bg-amber-100 text-amber-800 ring-1 ring-amber-200',
        'reviewed' => 'bg-sky-100 text-sky-800 ring-1 ring-sky-200',
        'contacted' => 'bg-violet-100 text-violet-800 ring-1 ring-violet-200',
        'accepted' => 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200',
        'rejected' => 'bg-rose-100 text-rose-800 ring-1 ring-rose-200',
    ];

    $statusLabels = [
        'pending' => 'Pending',
        'reviewed' => 'Reviewed',
        'contacted' => 'Contacted',
        'accepted' => 'Accepted',
        'rejected' => 'Rejected',
    ];

    $periodLabel = $periodLabel ?? 'Last 30 days';
    $sessionName = $selectedSession?->name ?? $activeSession?->name ?? 'Current session';
    $rangeLabel = isset($rangeStart, $rangeEnd)
        ? bsDate($rangeStart, 'd M Y') . ' - ' . bsDate($rangeEnd, 'd M Y')
        : null;
    $ctevtGeneralItems = collect($ctevtGeneralNotices['items'] ?? []);
    $ctevtResultItems = collect($ctevtResultNotices['items'] ?? []);
    $ctevtGeneralState = $ctevtGeneralNotices['source_state'] ?? 'unavailable';
    $ctevtResultState = $ctevtResultNotices['source_state'] ?? 'unavailable';
    $ctevtGeneralPageUrl = $ctevtGeneralNotices['page_url'] ?? route('public.notices', ['type' => 'ctevt-general']);
    $ctevtResultPageUrl = $ctevtResultNotices['page_url'] ?? route('public.notices', ['type' => 'ctevt-result']);
    $dashboardStateJson = json_encode($dashboardState, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    $dashboardStateEncoded = $dashboardStateJson ? base64_encode($dashboardStateJson) : '';
@endphp

<div id="principal-dashboard"
    class="space-y-8"
    data-principal-dashboard
    data-dashboard-endpoint="{{ route('admin.dashboard') }}"
    data-dashboard-state="{{ $dashboardStateEncoded }}">
    <section class="relative overflow-hidden rounded-[2rem] border border-slate-200 bg-white px-6 py-6 shadow-[0_28px_80px_rgba(15,23,42,0.08)] sm:px-8 lg:px-10">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(139,0,0,0.10),transparent_34%),radial-gradient(circle_at_bottom_left,rgba(248,113,113,0.08),transparent_32%)]"></div>
        <div class="absolute right-0 top-0 h-40 w-40 translate-x-1/2 -translate-y-1/2 rounded-full bg-red-100/60 blur-3xl"></div>
        <div class="relative flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-4xl">
                <span class="inline-flex items-center gap-2 rounded-full border border-red-200 bg-red-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.24em] text-[#8B0000]">
                    Principal view
                </span>
                <h1 class="mt-4 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl lg:text-5xl">
                    {{ $greeting }}, {{ auth()->user()->name ?? 'Principal' }}
                </h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
                    Quick executive snapshot for <span data-dashboard-hero-session>{{ $sessionName }}</span>. See what is happening now, spot risks, and jump to high-impact actions fast.
                </p>

                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ route('admin.students.create') }}" class="inline-flex items-center gap-2 rounded-2xl bg-[#8B0000] px-4 py-3 text-sm font-bold text-white shadow-[0_12px_30px_rgba(139,0,0,0.22)] transition-all duration-200 hover:-translate-y-0.5 hover:bg-[#6e0000]">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Student
                    </a>
                    <a href="{{ route('admin.notices.create') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-red-200 hover:text-[#8B0000]">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2m-1 0v14m0-14l4 4m-4-4-4 4"/>
                        </svg>
                        Create Notice
                    </a>
                    <a href="{{ route('admin.applications.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-[#8B0000] shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-red-100">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h10M7 16h6M9 3h6a2 2 0 012 2v14l-5-3-5 3V5a2 2 0 012-2z"/>
                        </svg>
                        Manage Admissions
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-950 px-4 py-3 text-sm font-bold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-slate-800">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6h13M9 11V3m0 0l4 4m-4-4-4 4M5 21h14"/>
                        </svg>
                        Generate Report
                    </a>
                </div>

                <div class="mt-5 flex flex-wrap gap-3 text-xs font-semibold text-slate-500">
                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Session: <span data-dashboard-session-display>{{ $sessionName }}</span>
                    </span>
                    @if($rangeLabel)
                        <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5">
                            <span class="h-2 w-2 rounded-full bg-[#8B0000]"></span>
                            Range: <span data-dashboard-range-display>{{ $rangeLabel }}</span>
                        </span>
                    @endif
                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5">
                        <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                        Updated <span data-dashboard-updated-display>{{ $lastUpdated->format('d M, h:i A') }}</span>
                    </span>
                </div>
            </div>

            <div class="w-full max-w-2xl rounded-[1.75rem] border border-slate-200 bg-slate-50/90 p-4 shadow-sm backdrop-blur">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-slate-500">Executive Snapshot</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">Focused on current status. Use Analytics for root-cause exploration.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span data-dashboard-loading class="hidden inline-flex items-center rounded-full bg-red-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-[#8B0000] ring-1 ring-red-100">
                            Updating
                        </span>
                        <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-bold text-slate-600 shadow-sm ring-1 ring-slate-200">
                            Session: {{ $sessionName }}
                        </span>
                        <a href="{{ route('admin.analytics') }}" class="inline-flex items-center rounded-full bg-slate-950 px-3 py-1 text-xs font-bold text-white transition hover:bg-slate-800">Open Analytics</a>
                    </div>
                </div>
                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <a href="{{ route('admin.analytics', ['metric' => 'attendance']) }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left transition hover:border-red-200 hover:bg-red-50/40">
                        <p class="text-sm font-semibold text-slate-900">Attendance analysis</p>
                        <p class="text-xs text-slate-500">Investigate causes behind attendance shifts.</p>
                    </a>
                    <a href="{{ route('admin.analytics', ['metric' => 'academic']) }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left transition hover:border-red-200 hover:bg-red-50/40">
                        <p class="text-sm font-semibold text-slate-900">Academic analysis</p>
                        <p class="text-xs text-slate-500">Explore marks, pass rate, and assignment delivery.</p>
                    </a>
                    <a href="{{ route('admin.analytics', ['metric' => 'departments']) }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left transition hover:border-red-200 hover:bg-red-50/40">
                        <p class="text-sm font-semibold text-slate-900">Department analysis</p>
                        <p class="text-xs text-slate-500">Compare departments and identify support priorities.</p>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section id="dashboard-kpis" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($kpiCards as $card)
            @php $styles = $toneStyles[$card['tone']] ?? $toneStyles['slate']; @endphp
            <a href="{{ $card['href'] ?? '#' }}" data-kpi-card="{{ $card['key'] }}" class="group rounded-[1.5rem] border border-slate-200 bg-white p-5 text-left shadow-[0_16px_45px_rgba(15,23,42,0.06)] transition-all duration-200 hover:-translate-y-1 hover:shadow-[0_22px_60px_rgba(15,23,42,0.10)]">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-slate-500">{{ $card['title'] }}</p>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span data-kpi-value class="text-3xl font-black tracking-tight text-slate-950">{{ $card['value'] }}</span>
                            @if(!empty($card['suffix']))
                                <span class="text-sm font-semibold text-slate-500">{{ $card['suffix'] }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $styles['iconWrap'] }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            {!! $metricIconPaths[$card['icon']] ?? $metricIconPaths['students'] !!}
                        </svg>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-between gap-3">
                    <span data-kpi-trend class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold {{ $styles['trend'] }}">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            @if($card['trendDirection'] === 'down')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13l-7 7-7-7m14-6l-7 7-7-7"/>
                            @elseif($card['trendDirection'] === 'flat')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11l7-7 7 7M12 4v16"/>
                            @endif
                        </svg>
                        {{ $card['trend'] }}
                    </span>
                    <p data-kpi-note class="text-xs leading-5 text-slate-500">{{ $card['note'] }}</p>
                </div>
            </a>
        @endforeach
    </section>

    <section id="main-insights" class="grid gap-6 xl:grid-cols-[1.45fr_0.95fr]">
        <x-card id="enrollment-trend" class="overflow-hidden border border-slate-200 shadow-[0_18px_50px_rgba(15,23,42,0.06)]">
            <x-slot name="header">
                <div>
                    <h2 class="text-lg font-black text-slate-950">Enrollment Pulse</h2>
                    <p class="text-sm text-slate-500">Mini trend showing admissions momentum at a glance.</p>
                </div>
                <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-[#8B0000]">Mini Visual</span>
            </x-slot>
            <div class="h-[190px]">
                <canvas id="principal-enrollment-chart" data-principal-chart="enrollment"></canvas>
            </div>
        </x-card>

        <x-card id="department-performance" class="overflow-hidden border border-slate-200 shadow-[0_18px_50px_rgba(15,23,42,0.06)]">
            <x-slot name="header">
                <div>
                    <h2 class="text-lg font-black text-slate-950">Department Pulse</h2>
                    <p class="text-sm text-slate-500">Compact performance snapshot across departments.</p>
                </div>
                @if($highlight)
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">Top: {{ $highlight['label'] }}</span>
                @endif
            </x-slot>
            <div class="h-[190px]">
                <canvas id="principal-department-chart" data-principal-chart="department"></canvas>
            </div>
        </x-card>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <x-card id="dashboard-alerts" class="border border-slate-200 shadow-[0_18px_50px_rgba(15,23,42,0.06)]">
            <x-slot name="header">
                <div>
                    <h2 class="text-lg font-black text-slate-950">Alerts</h2>
                    <p class="text-sm text-slate-500">Issues that need the principal desk today.</p>
                </div>
            </x-slot>

            <div data-dashboard-alert-list class="space-y-3">
                @forelse($alerts as $alert)
                    @php $alertStyle = $alertStyles[$alert['tone']] ?? $alertStyles['info']; @endphp
                    <div class="flex items-start gap-4 rounded-3xl border px-4 py-4 {{ $alertStyle['shell'] }}">
                        <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl {{ $alertStyle['iconWrap'] }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                {!! $alertStyle['icon'] !!}
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-slate-950">{{ $alert['title'] }}</p>
                            <p class="mt-1 text-sm leading-6 text-slate-600">{{ $alert['message'] }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] {{ $alertStyle['pill'] }}">{{ ucfirst($alert['tone']) }}</span>
                            @if(!empty($alert['actionHref']))
                                <a href="{{ $alert['actionHref'] }}" class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-bold text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:border-[#8B0000] hover:text-[#8B0000]">
                                    {{ $alert['actionLabel'] ?? 'Open' }}
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <x-empty-state title="No alerts" message="The campus looks stable for the selected period.">
                        <x-slot name="icon">
                            <svg class="mx-auto h-12 w-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                            </svg>
                        </x-slot>
                    </x-empty-state>
                @endforelse
            </div>
        </x-card>

        <x-card id="dashboard-highlight" class="border border-slate-200 shadow-[0_18px_50px_rgba(15,23,42,0.06)]">
            <x-slot name="header">
                <div>
                    <h2 class="text-lg font-black text-slate-950">Highlights</h2>
                    <p class="text-sm text-slate-500">Best performing department in the current view.</p>
                </div>
            </x-slot>

            <div data-dashboard-highlight>
            @if($highlight)
                <div class="rounded-[1.75rem] bg-gradient-to-br from-slate-950 via-slate-900 to-[#8B0000] p-5 text-white shadow-[0_24px_60px_rgba(15,23,42,0.24)]">
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-white/70">Top Department</p>
                    <h3 class="mt-3 text-2xl font-black tracking-tight">{{ $highlight['name'] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-white/80">{{ $highlight['summary'] }}</p>

                    <div class="mt-5 flex items-end justify-between gap-4">
                        <div>
                            <span class="text-4xl font-black">{{ $highlight['score'] }}%</span>
                            <p class="text-xs uppercase tracking-[0.18em] text-white/60">Performance score</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-4 py-3 text-right">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-white/60">Students</p>
                            <p class="mt-1 text-lg font-bold">{{ number_format($highlight['students']) }}</p>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-3 gap-3 text-center">
                        <div class="rounded-2xl bg-white/10 px-3 py-3">
                            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-white/60">Attendance</p>
                            <p class="mt-1 text-sm font-bold">{{ number_format($highlight['attendance_rate'] ?? 0, 1) }}%</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-3 py-3">
                            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-white/60">Pass rate</p>
                            <p class="mt-1 text-sm font-bold">{{ number_format($highlight['pass_rate'] ?? 0, 1) }}%</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-3 py-3">
                            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-white/60">Score</p>
                            <p class="mt-1 text-sm font-bold">{{ number_format($highlight['score'], 1) }}%</p>
                        </div>
                    </div>
                </div>
            @else
                <x-empty-state title="No highlight yet" message="Add attendance and result data to reveal the strongest department.">
                    <x-slot name="icon">
                        <svg class="mx-auto h-12 w-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </x-slot>
                </x-empty-state>
            @endif
            </div>
        </x-card>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <x-card id="dashboard-notices" class="border border-slate-200 shadow-[0_18px_50px_rgba(15,23,42,0.06)]">
            <x-slot name="header">
                <div>
                    <h2 class="text-lg font-black text-slate-950">Recent Notices</h2>
                    <p class="text-sm text-slate-500">The latest institutional updates visible to the principal office.</p>
                </div>
                <a href="{{ route('admin.notices.index') }}" class="text-sm font-bold text-[#8B0000] transition hover:text-[#640000]">View all</a>
            </x-slot>

            <div data-dashboard-notice-list class="space-y-3">
                @forelse($recentNotices as $notice)
                    <a href="{{ route('admin.notices.edit', $notice) }}" class="group flex gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-red-200 hover:shadow-md">
                        <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-2xl bg-red-50 text-[#8B0000]">
                            <span class="text-[10px] font-bold uppercase leading-none">{{ bsDate($notice->created_at, 'M') }}</span>
                            <span class="mt-1 text-lg font-black leading-none">{{ bsDate($notice->created_at, 'd') }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="truncate text-sm font-bold text-slate-950">{{ $notice->title }}</p>
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-600">{{ $notice->type }}</span>
                            </div>
                            <p class="mt-1 line-clamp-2 text-sm leading-6 text-slate-600">{{ $notice->content }}</p>
                            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                <span>{{ bsDate($notice->created_at, 'd M Y') }}</span>
                                <span>•</span>
                                <span>{{ $notice->author->name ?? 'System' }}</span>
                                @if($notice->department)
                                    <span>•</span>
                                    <span>{{ $notice->department->name }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <x-empty-state title="No notices" message="No notices have been published recently." action="{{ route('admin.notices.create') }}" actionLabel="Post Notice">
                        <x-slot name="icon">
                            <svg class="mx-auto h-12 w-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                            </svg>
                        </x-slot>
                    </x-empty-state>
                @endforelse
            </div>
        </x-card>

        <x-card id="dashboard-applications" class="border border-slate-200 shadow-[0_18px_50px_rgba(15,23,42,0.06)]">
            <x-slot name="header">
                <div>
                    <h2 class="text-lg font-black text-slate-950">Recent Applications</h2>
                    <p class="text-sm text-slate-500">A live admissions queue for the principal to review.</p>
                </div>
                <a href="{{ route('admin.applications.index') }}" class="text-sm font-bold text-[#8B0000] transition hover:text-[#640000]">View all</a>
            </x-slot>

            <div data-dashboard-application-list class="space-y-3">
                @forelse($recentApplications as $application)
                    @php
                        $applicationStatus = $application->status ?? 'pending';
                        $applicationStatusClass = $statusStyles[$applicationStatus] ?? $statusStyles['pending'];
                        $applicationStatusLabel = $statusLabels[$applicationStatus] ?? ucfirst($applicationStatus);
                    @endphp
                    <a href="{{ route('admin.applications.show', $application) }}" class="group flex gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-red-200 hover:shadow-md">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-slate-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h10M7 16h6M9 3h6a2 2 0 012 2v14l-5-3-5 3V5a2 2 0 012-2z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="truncate text-sm font-bold text-slate-950">{{ $application->full_name }}</p>
                                <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.18em] {{ $applicationStatusClass }}">{{ $applicationStatusLabel }}</span>
                            </div>
                            <p class="mt-1 text-sm leading-6 text-slate-600">{{ $application->department->name ?? 'General intake' }} · {{ $application->phone }}</p>
                            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                <span>{{ bsDate($application->created_at, 'd M Y') }}</span>
                                <span>•</span>
                                <span>{{ $application->email }}</span>
                            </div>
                        </div>
                    </a>
                @empty
                    <x-empty-state title="No applications" message="Applications will appear here as soon as students start applying." action="{{ route('public.apply') }}" actionLabel="Open Apply Page">
                        <x-slot name="icon">
                            <svg class="mx-auto h-12 w-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 8h10M7 12h10M7 16h6M9 3h6a2 2 0 012 2v14l-5-3-5 3V5a2 2 0 012-2z" />
                            </svg>
                        </x-slot>
                    </x-empty-state>
                @endforelse
            </div>
        </x-card>
    </section>

    <section>
        <x-card id="dashboard-ctevt" class="border border-slate-200 shadow-[0_18px_50px_rgba(15,23,42,0.06)]">
            <x-slot name="header">
                <div>
                    <h2 class="text-lg font-black text-slate-950">CTEVT Notices</h2>
                    <p class="text-sm text-slate-500">Live general notices and published result feeds from CTEVT.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 ring-1 ring-amber-100">General: {{ ucfirst($ctevtGeneralState) }}</span>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-100">Results: {{ ucfirst($ctevtResultState) }}</span>
                </div>
            </x-slot>

            <div x-data="{ activeCtevtTab: 'general' }" class="space-y-4">
                <div class="grid grid-cols-2 gap-2 rounded-3xl bg-slate-50 p-1 ring-1 ring-slate-200">
                    <button type="button"
                        @click="activeCtevtTab = 'general'"
                        :class="activeCtevtTab === 'general' ? 'bg-[#8B0000] text-white shadow-sm' : 'bg-transparent text-slate-600 hover:bg-white hover:text-[#8B0000]'"
                        class="rounded-2xl px-4 py-3 text-sm font-bold transition-all duration-200">
                        General Notices
                        <span class="ml-2 text-[10px] font-black uppercase tracking-[0.18em] text-current/70">{{ $ctevtGeneralItems->count() }}</span>
                    </button>
                    <button type="button"
                        @click="activeCtevtTab = 'result'"
                        :class="activeCtevtTab === 'result' ? 'bg-[#8B0000] text-white shadow-sm' : 'bg-transparent text-slate-600 hover:bg-white hover:text-[#8B0000]'"
                        class="rounded-2xl px-4 py-3 text-sm font-bold transition-all duration-200">
                        Published Results
                        <span class="ml-2 text-[10px] font-black uppercase tracking-[0.18em] text-current/70">{{ $ctevtResultItems->count() }}</span>
                    </button>
                </div>

                <div x-show="activeCtevtTab === 'general'" x-cloak class="space-y-3">
                    @forelse($ctevtGeneralItems as $notice)
                        @php
                            $noticeTitle = $notice['title'] ?? 'Notice';
                            $noticeUrl = $notice['url'] ?? $ctevtGeneralPageUrl;
                            $noticeDate = $notice['updated_date'] ?? '';
                            $noticePublisher = $notice['publisher'] ?? '';
                            $noticeFilesCount = (int) ($notice['files_count'] ?? 0);
                        @endphp
                        <a href="{{ $noticeUrl }}" target="_blank" rel="noopener noreferrer" class="group flex items-start gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-red-200 hover:shadow-md">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-red-50 text-[#8B0000] ring-1 ring-red-100">
                                <span class="text-[9px] font-black uppercase leading-none tracking-[0.18em]">CTEVT</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-950 group-hover:text-[#8B0000]">{{ $noticeTitle }}</p>
                                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                    @if(!empty($noticeDate))
                                        <span>{{ $noticeDate }}</span>
                                    @endif
                                    @if(!empty($noticePublisher))
                                        <span>• {{ $noticePublisher }}</span>
                                    @endif
                                    @if($noticeFilesCount > 0)
                                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">{{ $noticeFilesCount }} file{{ $noticeFilesCount > 1 ? 's' : '' }}</span>
                                    @endif
                                </div>
                            </div>
                            <svg class="mt-1 h-4 w-4 shrink-0 text-slate-300 transition-colors group-hover:text-[#8B0000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @empty
                        <x-empty-state title="No general notices" message="No live CTEVT general notices are available right now." action="{{ route('public.notices', ['type' => 'ctevt-general']) }}" actionLabel="Open CTEVT General">
                            <x-slot name="icon">
                                <svg class="mx-auto h-12 w-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                                </svg>
                            </x-slot>
                        </x-empty-state>
                    @endforelse
                </div>

                <div x-show="activeCtevtTab === 'result'" x-cloak class="space-y-3">
                    @forelse($ctevtResultItems as $notice)
                        @php
                            $noticeTitle = $notice['title'] ?? 'Result Notice';
                            $noticeUrl = $notice['url'] ?? $ctevtResultPageUrl;
                            $noticeDate = $notice['updated_date'] ?? '';
                            $noticePublisher = $notice['publisher'] ?? '';
                            $noticeFilesCount = (int) ($notice['files_count'] ?? 0);
                        @endphp
                        <a href="{{ $noticeUrl }}" target="_blank" rel="noopener noreferrer" class="group flex items-start gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-md">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                                <span class="text-[9px] font-black uppercase leading-none tracking-[0.18em]">CTEVT</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-950 group-hover:text-emerald-700">{{ $noticeTitle }}</p>
                                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                    @if(!empty($noticeDate))
                                        <span>{{ $noticeDate }}</span>
                                    @endif
                                    @if(!empty($noticePublisher))
                                        <span>• {{ $noticePublisher }}</span>
                                    @endif
                                    @if($noticeFilesCount > 0)
                                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">{{ $noticeFilesCount }} file{{ $noticeFilesCount > 1 ? 's' : '' }}</span>
                                    @endif
                                </div>
                            </div>
                            <svg class="mt-1 h-4 w-4 shrink-0 text-slate-300 transition-colors group-hover:text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @empty
                        <x-empty-state title="No result notices" message="No live CTEVT result notices are available right now." action="{{ route('public.notices', ['type' => 'ctevt-result']) }}" actionLabel="Open CTEVT Results">
                            <x-slot name="icon">
                                <svg class="mx-auto h-12 w-12 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                                </svg>
                            </x-slot>
                        </x-empty-state>
                    @endforelse
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-slate-50 px-4 py-3 text-xs text-slate-500 ring-1 ring-slate-200">
                    <p>CTEVT feeds are cached and linked to the public notice board for staff review.</p>
                    <div class="flex flex-wrap items-center gap-3 font-bold">
                        <a href="{{ route('public.notices', ['type' => 'ctevt-general']) }}" class="text-[#8B0000] transition hover:text-[#640000]">View General</a>
                        <a href="{{ route('public.notices', ['type' => 'ctevt-result']) }}" class="text-[#8B0000] transition hover:text-[#640000]">View Results</a>
                    </div>
                </div>
            </div>
        </x-card>
    </section>
</div>

@endsection
