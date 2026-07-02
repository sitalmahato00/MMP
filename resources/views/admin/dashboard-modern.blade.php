@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $semesterStatusColors = [
        'running'   => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-500'],
        'delayed'   => ['bg' => 'bg-amber-100',   'text' => 'text-amber-700',   'dot' => 'bg-amber-500'],
        'completed' => ['bg' => 'bg-slate-100',   'text' => 'text-slate-600',   'dot' => 'bg-slate-400'],
    ];

    $sessionName = $selectedSession?->name ?? $activeSession?->name ?? 'Current session';
    $rangeLabel  = isset($rangeStart, $rangeEnd)
        ? bsDate($rangeStart, 'Y, F d') . ' – ' . bsDate($rangeEnd, 'Y, F d')
        : null;
    $semesters   = $runningSemesters ?? [];

    $totalActiveUsers  = ($currentStudents ?? 0) + ($totalTeachers ?? 0) + ($totalParents ?? 0);
    $attendanceRate    = $attendanceSummary['rate'] ?? 0;
    $passRate          = $passSummary['rate'] ?? 0;
    $attendancePresent = $attendanceSummary['present'] ?? 0;
    $attendanceTotal   = $attendanceSummary['total'] ?? 0;
    $passPassed        = $passSummary['passed'] ?? 0;
    $passTotal         = $passSummary['total'] ?? 0;

    $gd          = $gradeDistribution;
    $gradeColors = ['#22c55e','#3b82f6','#a855f7','#f97316','#eab308','#ef4444'];
    $gradeLabels = ['A+ (90-100)','A (80-89)','B+ (70-79)','B (60-69)','C (50-59)','F (<50)'];

    $alertDotColors = [
        'danger'  => 'bg-rose-500',
        'warning' => 'bg-amber-500',
        'success' => 'bg-emerald-500',
        'info'    => 'bg-sky-500',
    ];

    $dashboardStateJson    = json_encode($dashboardState, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    $dashboardStateEncoded = $dashboardStateJson ? base64_encode($dashboardStateJson) : '';

    $hasAttendance = !empty($attendanceChartData['7']['data']) && array_sum($attendanceChartData['7']['data']) > 0;
    $internalNotices = $recentNotices->whereIn('type', ['general','department','teachers','exam'])->take(4);
    $publicNotices   = $recentNotices->where('type', 'event')->take(4);
@endphp

<div id="principal-dashboard"
     class="mx-auto max-w-[1440px] space-y-4"
     data-principal-dashboard
     data-dashboard-endpoint="{{ route('admin.dashboard') }}"
     data-dashboard-state="{{ $dashboardStateEncoded }}">

    {{-- ══════════════════════════════════════════════
         ROW 1 · HEADER
    ══════════════════════════════════════════════ --}}
    <div class="rounded-lg border border-slate-200 bg-white px-6 py-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Principal Dashboard</p>
                <h1 class="mt-0.5 text-2xl font-bold tracking-tight text-slate-900">
                    {{ $greeting }}, {{ auth()->user()->name ?? 'Admin' }}
                </h1>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.students.create') }}"
                   class="inline-flex items-center gap-1.5 rounded border border-slate-900 bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-slate-800">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Student
                </a>
                <a href="{{ route('admin.notices.create') }}"
                   class="inline-flex items-center gap-1.5 rounded border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                    Create Notice
                </a>
                <a href="{{ route('admin.attendance.index') }}"
                   class="inline-flex items-center gap-1.5 rounded bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-blue-700">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Attendance Overview
                </a>
            </div>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3">
            <span class="inline-flex items-center gap-1.5 rounded bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                <span data-dashboard-session-display>{{ $sessionName }}</span>
            </span>
            @foreach($semesters as $sem)
                @php $sc = $semesterStatusColors[$sem['status']] ?? $semesterStatusColors['running']; @endphp
                <span class="inline-flex items-center gap-1 rounded {{ $sc['bg'] }} px-2.5 py-1 text-[11px] font-semibold {{ $sc['text'] }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $sc['dot'] }}"></span>
                    Sem {{ $sem['number'] }}
                </span>
            @endforeach
            <div class="ml-auto flex flex-wrap items-center gap-1.5 text-xs text-slate-400">
                @if($rangeLabel)
                    <span data-dashboard-range-display>{{ $rangeLabel }}</span>
                    <span class="text-slate-300">|</span>
                @endif
                <span data-dashboard-updated-display>Updated {{ bsDate($lastUpdated, 'Y, F d') }}, {{ $lastUpdated->format('h:i A') }}</span>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         ROW 2 · KPI CARDS (4 equal columns)
    ══════════════════════════════════════════════ --}}
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">

        {{-- Total Active Users — Blue --}}
        <div class="kpi-card relative overflow-hidden rounded-2xl p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
             style="background: linear-gradient(135deg, #2563EB, #3B82F6);">
            <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute -bottom-3 -left-3 h-14 w-14 rounded-full bg-white/5"></div>
            <div class="relative flex items-center gap-3">
                <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xl font-black leading-tight text-white">{{ number_format($totalActiveUsers) }}</p>
                    <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-wider text-white/80">Total Active Users</p>
                </div>
            </div>
            <p class="relative mt-2 text-[11px] text-white/60">Students + Teachers + Parents</p>
        </div>

        {{-- Attendance Rate — Green --}}
        <div class="kpi-card relative overflow-hidden rounded-2xl p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
             style="background: linear-gradient(135deg, #10B981, #22C55E);">
            <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute -bottom-3 -left-3 h-14 w-14 rounded-full bg-white/5"></div>
            <div class="relative flex items-center gap-3">
                <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xl font-black leading-tight text-white">{{ number_format($attendanceRate, 1) }}%</p>
                    <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-wider text-white/80">Attendance Rate</p>
                </div>
            </div>
            <p class="relative mt-2 text-[11px] text-white/60">{{ number_format($attendancePresent) }} / {{ number_format($attendanceTotal) }} present</p>
        </div>

        {{-- Pass Rate — Purple --}}
        <div class="kpi-card relative overflow-hidden rounded-2xl p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
             style="background: linear-gradient(135deg, #7C3AED, #A855F7);">
            <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute -bottom-3 -left-3 h-14 w-14 rounded-full bg-white/5"></div>
            <div class="relative flex items-center gap-3">
                <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xl font-black leading-tight text-white">{{ number_format($passRate, 1) }}%</p>
                    <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-wider text-white/80">Pass Rate</p>
                </div>
            </div>
            <p class="relative mt-2 text-[11px] text-white/60">{{ number_format($passPassed) }} / {{ number_format($passTotal) }} passed</p>
        </div>

        {{-- Total Departments — Orange --}}
        <a href="{{ route('admin.departments.index') }}"
           class="relative block overflow-hidden rounded-2xl p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
           style="background: linear-gradient(135deg, #F97316, #FB923C);">
            <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute -bottom-3 -left-3 h-14 w-14 rounded-full bg-white/5"></div>
            <div class="relative flex items-center gap-3">
                <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xl font-black leading-tight text-white">{{ number_format($departmentCount) }}</p>
                    <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-wider text-white/80">Total Departments</p>
                </div>
            </div>
            <p class="relative mt-2 text-[11px] text-white/60">Active departments</p>
        </a>
    </div>

    {{-- ══════════════════════════════════════════════
         ROW 3 · CHARTS — side by side, full width
    ══════════════════════════════════════════════ --}}
    <div class="grid gap-4 md:grid-cols-2">

        {{-- Attendance Trend --}}
        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                <div>
                    <p class="text-sm font-semibold text-slate-900">Attendance Trend</p>
                    <p class="mt-0.5 text-xs text-slate-400">Daily attendance percentage over time</p>
                </div>
                <div class="flex items-center gap-0.5 rounded border border-slate-200 p-0.5 text-xs font-medium">
                    <button type="button" data-att-period="7"
                            class="att-filter-btn rounded bg-blue-600 px-3 py-1.5 text-white transition">
                        7 Days
                    </button>
                    <button type="button" data-att-period="30"
                            class="att-filter-btn rounded px-3 py-1.5 text-slate-600 transition hover:bg-slate-50">
                        30 Days
                    </button>
                    <button type="button" data-att-period="session"
                            class="att-filter-btn rounded px-3 py-1.5 text-slate-600 transition hover:bg-slate-50">
                        Session
                    </button>
                </div>
            </div>
            <div class="px-5 py-5">
                @if(!$hasAttendance)
                    <div class="flex flex-col items-center justify-center gap-3" style="height: 200px;">
                        {{-- Placeholder bar icon --}}
                        <svg class="h-10 w-10 text-slate-200" fill="currentColor" viewBox="0 0 24 24">
                            <rect x="3" y="12" width="4" height="9" rx="1"/>
                            <rect x="10" y="7" width="4" height="14" rx="1"/>
                            <rect x="17" y="3" width="4" height="18" rx="1"/>
                        </svg>
                        <span class="text-sm text-slate-400">No attendance data available.</span>
                    </div>
                @else
                    <div style="height: 200px; position: relative;">
                        <canvas id="attendance-trend-chart"></canvas>
                    </div>
                @endif
            </div>
        </div>

        {{-- Grade Distribution --}}
        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <p class="text-sm font-semibold text-slate-900">Grade Distribution</p>
                <p class="mt-0.5 text-xs text-slate-400">Student performance breakdown by grade</p>
            </div>
            <div class="flex items-center gap-6 px-5 py-5">
                <div style="width: 160px; height: 160px; flex-shrink: 0; position: relative;">
                    <canvas id="grade-donut-chart"></canvas>
                </div>
                <div class="flex-1 min-w-0 space-y-2">
                    @foreach($gradeLabels as $i => $gl)
                        <div class="flex items-center gap-2 text-xs">
                            <span class="h-2 w-2 flex-shrink-0 rounded-full" style="background-color: {{ $gradeColors[$i] }};"></span>
                            <span class="text-slate-600">{{ $gl }}</span>
                            <span class="ml-auto flex-shrink-0 font-medium text-slate-700">({{ $gd['data'][$i] ?? 0 }}%)</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         ROW 4 · BOTTOM — 3 equal columns
    ══════════════════════════════════════════════ --}}
    <div class="grid gap-4 md:grid-cols-3">

        {{-- Notices & Updates --}}
        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <p class="text-sm font-semibold text-slate-900">Notices & Updates</p>
            </div>
            {{-- Tabs --}}
            <div class="border-b border-slate-100">
                <div class="flex">
                    <button type="button" id="tab-internal"
                            class="notice-tab border-b-2 border-blue-600 px-5 py-2.5 text-xs font-semibold text-blue-600"
                            data-tab="internal">
                        Internal
                    </button>
                    <button type="button" id="tab-public"
                            class="notice-tab border-b-2 border-transparent px-5 py-2.5 text-xs font-medium text-slate-500 hover:text-slate-700"
                            data-tab="public">
                        Public
                    </button>
                </div>
            </div>
            {{-- Internal --}}
            <div id="notices-internal">
                @if($internalNotices->isEmpty())
                    <div class="flex items-center justify-center py-8 text-xs text-slate-400">
                        No recent notices.
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($internalNotices as $notice)
                            <a href="{{ route('admin.notices.edit', $notice) }}"
                               class="block px-5 py-3 transition hover:bg-slate-50">
                                <p class="text-xs font-medium text-slate-800 line-clamp-1">{{ $notice->title }}</p>
                                <p class="mt-0.5 text-[11px] text-slate-400">
                                    {{ bsDate($notice->published_at ?? $notice->created_at, 'F d, Y') }}
                                    &middot; {{ ucfirst($notice->type) }}
                                </p>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
            {{-- Public --}}
            <div id="notices-public" class="hidden">
                @if($publicNotices->isEmpty())
                    <div class="flex items-center justify-center py-8 text-xs text-slate-400">
                        No public notices.
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($publicNotices as $notice)
                            <a href="{{ route('admin.notices.edit', $notice) }}"
                               class="block px-5 py-3 transition hover:bg-slate-50">
                                <p class="text-xs font-medium text-slate-800 line-clamp-1">{{ $notice->title }}</p>
                                <p class="mt-0.5 text-[11px] text-slate-400">
                                    {{ bsDate($notice->published_at ?? $notice->created_at, 'F d, Y') }}
                                </p>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Community --}}
        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <p class="text-sm font-semibold text-slate-900">Community</p>
            </div>
            <div class="grid grid-cols-3 divide-x divide-slate-100">
                <a href="{{ route('admin.teachers.index') }}"
                   class="flex flex-col items-center gap-1 py-6 text-center transition hover:bg-slate-50">
                    <span class="text-2xl font-bold text-slate-900">{{ number_format($totalTeachers ?? 0) }}</span>
                    <span class="text-xs text-slate-500">Teachers</span>
                </a>
                <a href="{{ route('admin.parents.index') }}"
                   class="flex flex-col items-center gap-1 py-6 text-center transition hover:bg-slate-50">
                    <span class="text-2xl font-bold text-slate-900">{{ number_format($totalParents ?? 0) }}</span>
                    <span class="text-xs text-slate-500">Parents</span>
                </a>
                <a href="{{ route('admin.alumni.index') }}"
                   class="flex flex-col items-center gap-1 py-6 text-center transition hover:bg-slate-50">
                    <span class="text-2xl font-bold text-slate-900">{{ number_format($totalAlumni ?? 0) }}</span>
                    <span class="text-xs text-slate-500">Alumni</span>
                </a>
            </div>
        </div>

        {{-- Insights & Alerts --}}
        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <p class="text-sm font-semibold text-slate-900">Insights & Alerts</p>
                <p class="mt-0.5 text-[11px] text-slate-400">Important notifications and observations</p>
            </div>
            <div class="divide-y divide-slate-100" data-dashboard-alert-list>
                @forelse($alerts as $alert)
                    <div class="px-5 py-3.5">
                        <div class="flex items-start gap-2.5">
                            <span class="mt-0.5 h-2 w-2 flex-shrink-0 rounded-full {{ $alertDotColors[$alert['tone']] ?? 'bg-slate-400' }}"></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold text-slate-900">{{ $alert['title'] }}</p>
                                <p class="mt-0.5 text-[11px] text-slate-500">{{ $alert['message'] }}</p>
                            </div>
                        </div>
                        @if(!empty($alert['actionHref']))
                            <div class="mt-2 flex justify-end">
                                <a href="{{ $alert['actionHref'] }}"
                                   class="text-[11px] font-semibold text-slate-600 transition hover:text-blue-600">
                                    {{ $alert['actionLabel'] ?? 'View' }}
                                </a>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="flex items-center justify-center py-8 text-xs text-slate-400">
                        No alerts at this time.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {

    // ── Attendance Trend ─────────────────────────────────────────────
    const attData = @json($attendanceChartData ?? []);
    const attSets = {
        '7':       { labels: attData?.['7']?.labels   ?? [], data: attData?.['7']?.data   ?? [] },
        '30':      { labels: attData?.['30']?.labels  ?? [], data: attData?.['30']?.data  ?? [] },
        'session': { labels: attData?.['30']?.labels  ?? [], data: attData?.['30']?.data  ?? [] },
    };

    const attCanvas = document.getElementById('attendance-trend-chart');
    let attChart = null;

    if (attCanvas) {
        const ctx = attCanvas.getContext('2d');
        const grad = ctx.createLinearGradient(0, 0, 0, 200);
        grad.addColorStop(0, 'rgba(59,130,246,0.22)');
        grad.addColorStop(1, 'rgba(59,130,246,0.02)');

        attChart = new Chart(attCanvas, {
            type: 'line',
            data: {
                labels: attSets['7'].labels,
                datasets: [{
                    label: 'Attendance %',
                    data: attSets['7'].data,
                    borderColor: '#3b82f6',
                    backgroundColor: grad,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8', maxRotation: 0, font: { size: 10 } },
                    },
                    y: {
                        suggestedMin: 0,
                        suggestedMax: 100,
                        grid: { color: 'rgba(148,163,184,0.12)' },
                        ticks: { color: '#94a3b8', font: { size: 10 }, callback: v => v + '%' },
                    },
                },
            },
        });

        document.querySelectorAll('.att-filter-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.att-filter-btn').forEach(b => {
                    b.classList.remove('bg-blue-600', 'text-white');
                    b.classList.add('text-slate-600');
                });
                this.classList.remove('text-slate-600');
                this.classList.add('bg-blue-600', 'text-white');

                const set = attSets[this.dataset.attPeriod];
                attChart.data.labels = set.labels;
                attChart.data.datasets[0].data = set.data;
                attChart.update();
            });
        });
    }

    // ── Grade Donut ──────────────────────────────────────────────────
    const gradeCanvas = document.getElementById('grade-donut-chart');
    if (gradeCanvas) {
        const gd = @json($gradeDistribution);
        const colors = ['#22c55e','#3b82f6','#a855f7','#f97316','#eab308','#ef4444'];
        const chartData = gd.hasData
            ? gd.data
            : [0, 0, 0, 0, 0, 100]; // red fallback when no data

        new Chart(gradeCanvas, {
            type: 'doughnut',
            data: {
                labels: gd.labels,
                datasets: [{
                    data: chartData,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: gd.hasData,
                        callbacks: {
                            label: ctx => {
                                const count = gd.counts?.[ctx.dataIndex];
                                return ctx.label + ': ' + ctx.parsed + '%' + (count ? ' (' + count + ' students)' : '');
                            },
                        },
                    },
                },
            },
        });
    }

    // ── Notice Tabs ──────────────────────────────────────────────────
    document.querySelectorAll('.notice-tab').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.notice-tab').forEach(t => {
                t.classList.remove('border-blue-600', 'text-blue-600');
                t.classList.add('border-transparent', 'text-slate-500');
            });
            this.classList.add('border-blue-600', 'text-blue-600');
            this.classList.remove('border-transparent', 'text-slate-500');

            const tab = this.dataset.tab;
            document.getElementById('notices-internal').classList.toggle('hidden', tab !== 'internal');
            document.getElementById('notices-public').classList.toggle('hidden', tab !== 'public');
        });
    });

})();
</script>
@endpush
