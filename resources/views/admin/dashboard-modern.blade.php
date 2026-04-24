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
$rangeLabel = isset($rangeStart, $rangeEnd) ? bsDate($rangeStart, 'Y, F d') . ' �" ' . bsDate($rangeEnd, 'Y, F d') : null;
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
    <section class="relative overflow-hidden rounded-xl lg:rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            {{-- Row 1: Greeting + Quick Actions --}}
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-\[10px\] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Principal Dashboard</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        {{ $greeting }}, {{ auth()->user()->name ?? 'Principal' }}
                    </h1>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.students.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3 sm:px-3.5 py-1.5 sm:py-2 text-\[11px\] sm:text-xs font-semibold text-white shadow-sm transition hover:bg-slate-800">
                        <svg class="h-3 w-3 sm:h-3.5 sm:w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Student
                    </a>
                    <a href="{{ route('admin.notices.create') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 sm:px-3.5 py-1.5 sm:py-2 text-\[11px\] sm:text-xs font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                        Create Notice
                    </a>
                    {{-- Manage Admissions button removed --}}
                    <a href="{{ route('admin.attendance.index') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 sm:px-3.5 py-1.5 sm:py-2 text-\[11px\] sm:text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700">
                        <svg class="h-3 w-3 sm:h-3.5 sm:w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Attendance Overview
                    </a>
                </div>
            </div>

            {{-- Row 2: Session + Semester Chips + Date Range --}}
            <div class="mt-3 sm:mt-4 flex flex-wrap items-center gap-2 sm:gap-3 border-t border-slate-100 pt-4">
                <div class="flex items-center gap-2 rounded-lg bg-slate-100 px-2.5 sm:px-3 py-1 sm:py-1.5 text-\[11px\] sm:text-xs font-medium text-slate-600">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    <span data-dashboard-session-display>{{ $sessionName }}</span>
                </div>

                @foreach($semesters as $sem)
                    @php $semColor = $semesterStatusColors[$sem['status']] ?? $semesterStatusColors['running']; @endphp
                    <span class="inline-flex items-center gap-1.5 rounded-lg {{ $semColor['bg'] }} px-2 sm:px-2.5 py-0.5 sm:py-1 text-\[10px\] sm:text-\[11px\] font-semibold {{ $semColor['text'] }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $semColor['dot'] }}"></span>
                        Sem {{ $sem['number'] }}
                    </span>
                @endforeach

                <div class="w-full sm:w-auto sm:ml-auto flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 text-\[11px\] sm:text-xs text-slate-500">
                    @if($rangeLabel)
                        <span data-dashboard-range-display>{{ $rangeLabel }}</span>
                        <span class="text-slate-300">|</span>
                    @endif
                    <span data-dashboard-updated-display>Updated {{ bsDate($lastUpdated, 'Y, F d') }}, {{ $lastUpdated->format('h:i A') }}</span>
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
         3. MAIN ANALYTICS – Charts + Notices Panel
    ═══════════════════════════════════════════════════════════ --}}
    <section id="main-insights" class="grid gap-5 grid-cols-1 lg:grid-cols-[1fr_320px]">
        {{-- LEFT: Charts --}}
        <div class="space-y-5">
            {{-- Attendance Curve Chart --}}
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">Attendance Trend</h2>
                        <p class="text-xs text-slate-500">Daily attendance percentage over time</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="attendance-filter-btn px-3 py-1 text-xs font-semibold rounded-md bg-blue-50 text-blue-600" data-period="7">7 Days</button>
                        <button class="attendance-filter-btn px-3 py-1 text-xs font-semibold rounded-md text-slate-600 hover:bg-slate-50" data-period="30">30 Days</button>
                        <button class="attendance-filter-btn px-3 py-1 text-xs font-semibold rounded-md text-slate-600 hover:bg-slate-50" data-period="session">Session</button>
                    </div>
                </div>
                <div class="h-[200px] sm:h-[250px]">
                    <canvas id="attendance-curve-chart"></canvas>
                </div>
            </div>

            {{-- Grade Distribution Donut Chart --}}
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">Grade Distribution</h2>
                        <p class="text-xs text-slate-500">Student performance breakdown by grade</p>
                    </div>
                </div>
                <div class="h-[200px] sm:h-[250px] flex items-center justify-center">
                    <canvas id="grade-donut-chart"></canvas>
                </div>
            </div>
        </div>

        {{-- RIGHT: Notices Panel --}}
        <div class="space-y-5">
            {{-- Notices & CTEVT Notices Tabbed Card --}}
            <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm" x-data="{ activeNoticeTab: 'internal' }">
                <div class="border-b border-slate-100 px-4 py-3">
                    <h2 class="text-sm font-semibold text-slate-900">Notices & Updates</h2>
                </div>

                {{-- Tabs --}}
                <div class="flex border-b border-slate-100 bg-slate-50">
                    <button @click="activeNoticeTab = 'internal'" :class="activeNoticeTab === 'internal' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-slate-600 hover:text-slate-900'" class="flex-1 px-3 py-2.5 text-xs font-semibold transition">
                        Internal
                    </button>
                    <button @click="activeNoticeTab = 'ctevt'" :class="activeNoticeTab === 'ctevt' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-slate-600 hover:text-slate-900'" class="flex-1 px-3 py-2.5 text-xs font-semibold transition">
                        CTEVT
                    </button>
                </div>

                {{-- Internal Notices Tab --}}
                <div x-show="activeNoticeTab === 'internal'" class="divide-y divide-slate-100 max-h-[400px] sm:max-h-[500px] overflow-y-auto">
                    @forelse($recentNotices as $notice)
                        <a href="{{ route('admin.notices.edit', $notice) }}" class="flex items-start gap-2.5 px-4 py-3 transition hover:bg-slate-50">
                            <div class="flex h-9 w-9 shrink-0 flex-col items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                                <span class="text-[7px] font-semibold leading-none">{{ bsDate($notice->created_at, 'Y') }}</span>
                                <span class="text-xs font-bold leading-none">{{ bsDate($notice->created_at, 'd') }}</span>
                                <span class="text-[6px] font-semibold uppercase leading-none">{{ bsDate($notice->created_at, 'F') }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="line-clamp-2 text-xs font-medium text-slate-900">{{ $notice->title }}</p>
                                <p class="mt-0.5 text-[10px] text-slate-500">{{ bsDate($notice->created_at, 'F d, Y') }}</p>
                            </div>
                        </a>
                    @empty
                        <div class="py-8 text-center">
                            <p class="text-xs text-slate-400">No recent notices.</p>
                        </div>
                    @endforelse
                    @if($recentNotices->isNotEmpty())
                        <div class="px-4 py-2.5 bg-slate-50">
                            <a href="{{ route('admin.notices.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">View all →</a>
                        </div>
                    @endif
                </div>

                {{-- CTEVT Notices Tab --}}
                <div x-show="activeNoticeTab === 'ctevt'" x-cloak x-data="{ ctevtSubTab: 'general' }">
                    <div class="flex gap-1 bg-slate-50 p-2">
                        <button @click="ctevtSubTab = 'general'" :class="ctevtSubTab === 'general' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-600 hover:bg-white/50'" class="flex-1 rounded-md px-2.5 py-1.5 text-[10px] font-semibold transition">
                            General ({{ $ctevtGeneralItems->count() }})
                        </button>
                        <button @click="ctevtSubTab = 'result'" :class="ctevtSubTab === 'result' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-600 hover:bg-white/50'" class="flex-1 rounded-md px-2.5 py-1.5 text-[10px] font-semibold transition">
                            Results ({{ $ctevtResultItems->count() }})
                        </button>
                    </div>

                    <div x-show="ctevtSubTab === 'general'" class="divide-y divide-slate-100 max-h-[350px] sm:max-h-[450px] overflow-y-auto">
                        @forelse($ctevtGeneralItems as $notice)
                            <a href="{{ $notice['url'] ?? $ctevtGeneralPageUrl }}" target="_blank" rel="noopener noreferrer" class="block px-4 py-2.5 transition hover:bg-slate-50">
                                <div class="flex items-center gap-2">
                                    <span class="shrink-0 rounded bg-red-50 px-1.5 py-0.5 text-[8px] font-bold text-red-600">CTEVT</span>
                                    <span class="min-w-0 flex-1 truncate text-[11px] font-medium text-slate-700">{{ $notice['title'] ?? 'Notice' }}</span>
                                </div>
                                @if(!empty($notice['updated_date']))
                                    <p class="mt-1 text-[9px] text-slate-400">{{ $notice['updated_date'] }}</p>
                                @endif
                            </a>
                        @empty
                            <p class="py-8 text-center text-xs text-slate-400">No general notices available.</p>
                        @endforelse
                    </div>

                    <div x-show="ctevtSubTab === 'result'" x-cloak class="divide-y divide-slate-100 max-h-[350px] sm:max-h-[450px] overflow-y-auto">
                        @forelse($ctevtResultItems as $notice)
                            <a href="{{ $notice['url'] ?? $ctevtResultPageUrl }}" target="_blank" rel="noopener noreferrer" class="block px-4 py-2.5 transition hover:bg-slate-50">
                                <div class="flex items-center gap-2">
                                    <span class="shrink-0 rounded bg-emerald-50 px-1.5 py-0.5 text-[8px] font-bold text-emerald-600">CTEVT</span>
                                    <span class="min-w-0 flex-1 truncate text-[11px] font-medium text-slate-700">{{ $notice['title'] ?? 'Result' }}</span>
                                </div>
                                @if(!empty($notice['updated_date']))
                                    <p class="mt-1 text-[9px] text-slate-400">{{ $notice['updated_date'] }}</p>
                                @endif
                            </a>
                        @empty
                            <p class="py-8 text-center text-xs text-slate-400">No result notices available.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Active Academic Flow Card --}}
            <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-4 py-3">
                    <div class="flex items-center gap-2">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50">
                            <svg class="h-3.5 w-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-xs font-semibold text-slate-900">Active Academic Flow</h2>
                            <p class="text-[10px] text-slate-500">Session: {{ $sessionName }}</p>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-slate-100 px-4">
                    @forelse($semesters as $sem)
                        @php $sc = $semesterStatusColors[$sem['status']] ?? $semesterStatusColors['running']; @endphp
                        <div class="py-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $sc['dot'] }}"></span>
                                    <span class="text-xs font-semibold text-slate-900">{{ $sem['label'] }}</span>
                                </div>
                                <span class="rounded-md {{ $sc['bg'] }} px-1.5 py-0.5 text-[9px] font-semibold {{ $sc['text'] }}">
                                    {{ $sem['statusLabel'] }}
                                </span>
                            </div>
                            @if($sem['delayReason'])
                                <p class="mt-1 text-[10px] text-amber-600">{{ $sem['delayReason'] }}</p>
                            @endif
                            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full {{ $sc['bar'] }} transition-all duration-500" style="width: {{ $sem['progress'] }}%"></div>
                            </div>
                            <div class="mt-1 flex justify-between text-[9px] text-slate-400">
                                <span>{{ $sem['startDate'] ?? '—' }}</span>
                                <span>{{ $sem['progress'] }}%</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center">
                            <p class="text-[11px] text-slate-400">No semesters configured.</p>
                        </div>
                    @endforelse
                </div>

                @if(count($semesters) > 0)
                    <div class="border-t border-slate-100 px-4 py-2.5">
                        <a href="{{ route('admin.academic-sessions.index') }}" class="text-[11px] font-semibold text-blue-600 transition hover:text-blue-700">
                            View Details →
                        </a>
                    </div>
                @endif
            </div>

            {{-- Quick Stats --}}
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Community</p>
                <div class="mt-3 grid grid-cols-3 gap-2">
                    <div class="text-center">
                        <p class="text-base font-bold text-slate-900">{{ number_format($totalTeachers ?? 0) }}</p>
                        <p class="text-[9px] text-slate-500">Teachers</p>
                    </div>
                    <div class="text-center">
                        <p class="text-base font-bold text-slate-900">{{ number_format($totalParents ?? 0) }}</p>
                        <p class="text-[9px] text-slate-500">Parents</p>
                    </div>
                    <div class="text-center">
                        <p class="text-base font-bold text-slate-900">{{ number_format($totalAlumni ?? 0) }}</p>
                        <p class="text-[9px] text-slate-500">Alumni</p>
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
                            <a href="{{ $alert['actionHref'] }}" class="shrink-0 rounded-md border border-slate-200 px-2 sm:px-2.5 py-0.5 sm:py-1 text-\[10px\] sm:text-\[11px\] font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">
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


</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get real Nepali dates from backend
    const attendanceChartData = @json($attendanceChartData ?? []);
    
    // Sample data - replace with actual data from backend
    const attendanceData = {
        '7': {
            labels: attendanceChartData['7']?.labels || ['बै 1', 'बै 2', 'बै 3', 'बै 4', 'बै 5', 'बै 6', 'बै 7'],
            data: attendanceChartData['7']?.data || [85, 88, 82, 90, 87, 89, 91]
        },
        '30': {
            labels: attendanceChartData['30']?.labels || Array.from({length: 30}, (_, i) => `बै ${i + 1}`),
            data: attendanceChartData['30']?.data || Array.from({length: 30}, () => Math.floor(Math.random() * 20) + 75)
        },
        'session': {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6', 'Week 7', 'Week 8'],
            data: [82, 85, 87, 86, 88, 90, 89, 91]
        }
    };

    // Attendance Curve Chart
    const attendanceCtx = document.getElementById('attendance-curve-chart');
    let attendanceChart = new Chart(attendanceCtx, {
        type: 'line',
        data: {
            labels: attendanceData['7'].labels,
            datasets: [{
                label: 'Attendance %',
                data: attendanceData['7'].data,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Attendance: ' + context.parsed.y + '%';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    min: 70,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });

    // Filter buttons for attendance chart
    document.querySelectorAll('.attendance-filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const period = this.dataset.period;
            
            // Update button styles
            document.querySelectorAll('.attendance-filter-btn').forEach(b => {
                b.classList.remove('bg-blue-50', 'text-blue-600');
                b.classList.add('text-slate-600', 'hover:bg-slate-50');
            });
            this.classList.add('bg-blue-50', 'text-blue-600');
            this.classList.remove('text-slate-600', 'hover:bg-slate-50');
            
            // Update chart data
            attendanceChart.data.labels = attendanceData[period].labels;
            attendanceChart.data.datasets[0].data = attendanceData[period].data;
            attendanceChart.update();
        });
    });

    // Grade Distribution Donut Chart
    const gradeCtx = document.getElementById('grade-donut-chart');
    const gradeData = @json($gradeDistribution);
    
    if (gradeData.hasData) {
        new Chart(gradeCtx, {
            type: 'doughnut',
            data: {
                labels: gradeData.labels,
                datasets: [{
                    data: gradeData.data,
                    backgroundColor: [
                        'rgb(34, 197, 94)',   // Green for A+
                        'rgb(59, 130, 246)',  // Blue for A
                        'rgb(168, 85, 247)',  // Purple for B+
                        'rgb(251, 146, 60)',  // Orange for B
                        'rgb(251, 191, 36)',  // Yellow for C
                        'rgb(239, 68, 68)'    // Red for F
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 15,
                            font: {
                                size: 11
                            },
                            generateLabels: function(chart) {
                                const data = chart.data;
                                return data.labels.map((label, i) => ({
                                    text: label + ' (' + data.datasets[0].data[i] + '%)',
                                    fillStyle: data.datasets[0].backgroundColor[i],
                                    hidden: false,
                                    index: i
                                }));
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const count = gradeData.counts[context.dataIndex];
                                return context.label + ': ' + context.parsed + '% (' + count + ' students)';
                            }
                        }
                    }
                }
            }
        });
    } else {
        // Show "No data" message
        gradeCtx.parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-slate-400 text-sm">No grade data available for this period</div>';
    }
});
</script>
@endpush



