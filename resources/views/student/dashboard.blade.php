@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')
@php
    $kpiCards = [
        [
            'title' => 'Attendance Rate',
            'value' => number_format($kpiData['attendance_rate'], 1),
            'suffix' => '%',
            'note' => 'Overall attendance',
            'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
            'tone' => 'emerald',
        ],
        [
            'title' => 'Pending Assignments',
            'value' => number_format($kpiData['pending_assignments']),
            'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'tone' => 'amber',
        ],
        [
            'title' => 'Average Grade',
            'value' => number_format($kpiData['average_grade'], 1),
            'suffix' => '%',
            'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
            'tone' => 'violet',
        ],
        [
            'title' => 'Today\'s Classes',
            'value' => number_format($todaySlots->count()),
            'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
            'tone' => 'blue',
        ],
    ];

    $toneMap = [
        'blue'    => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'ring' => 'ring-blue-100', 'bar' => 'bg-blue-500'],
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'ring' => 'ring-emerald-100', 'bar' => 'bg-emerald-500'],
        'violet'  => ['bg' => 'bg-violet-50', 'text' => 'text-violet-600', 'ring' => 'ring-violet-100', 'bar' => 'bg-violet-500'],
        'amber'   => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'ring' => 'ring-amber-100', 'bar' => 'bg-amber-500'],
    ];
@endphp

<div class="space-y-6">
    {{-- ═══════════════════════════════════════════════════════════
         1. TOP HEADER
    ═══════════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden rounded-xl lg:rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Student Dashboard</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        {{ $greeting }}, {{ auth()->user()->name }}
                    </h1>
                    @if($student)
                        <p class="mt-1 text-sm text-slate-600">
                            {{ $student->program->name ?? 'N/A' }} · Semester {{ $student->current_semester ?? 'N/A' }}
                        </p>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        {{ $session?->name ?? 'No active session' }}
                    </span>
                    <span class="text-xs text-slate-500">
                        Updated {{ bsDate($lastUpdated, 'F d, Y') }}, {{ bsDateTime($lastUpdated, '', 'h:i A') }}
                    </span>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         2. KPI METRICS
    ═══════════════════════════════════════════════════════════ --}}
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($kpiCards as $card)
            @php $t = $toneMap[$card['tone']] ?? $toneMap['blue']; @endphp
            <div class="group relative overflow-hidden rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-start justify-between">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg {{ $t['bg'] }}">
                        <svg class="h-4 w-4 {{ $t['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-bold tracking-tight text-slate-900">{{ $card['value'] }}</span>
                        @if(!empty($card['suffix']))
                            <span class="text-sm font-medium text-slate-400">{{ $card['suffix'] }}</span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">{{ $card['title'] }}</p>
                </div>
                @if(!empty($card['note']))
                    <p class="mt-2 text-[11px] text-slate-500">{{ $card['note'] }}</p>
                @endif
                <div class="absolute bottom-0 left-0 right-0 h-0.5 {{ $t['bar'] }} opacity-40"></div>
            </div>
        @endforeach
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         3. CHARTS SECTION
    ═══════════════════════════════════════════════════════════ --}}
    <section class="grid gap-6 lg:grid-cols-2">
        {{-- Attendance Chart --}}
        <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-base font-semibold text-slate-900">Attendance Trend</h2>
                <p class="mt-1 text-sm text-slate-500">Last 7 days attendance percentage</p>
            </div>
            <div class="p-6">
                <div class="h-48" id="attendanceChart">
                    <canvas id="attendanceCanvas"></canvas>
                </div>
            </div>
        </div>

        {{-- Grade Distribution Chart --}}
        <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-base font-semibold text-slate-900">Grade Distribution</h2>
                <p class="mt-1 text-sm text-slate-500">Performance breakdown by grade</p>
            </div>
            <div class="p-6">
                <div class="h-48 flex items-center justify-center" id="gradeChart">
                    <canvas id="gradeCanvas"></canvas>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         4. TODAY'S CLASSES & NOTICES
    ═══════════════════════════════════════════════════════════ --}}
    <section class="grid gap-6 lg:grid-cols-2">
        {{-- Today's Classes --}}
        <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-base font-semibold text-slate-900">Today's Classes</h2>
                <p class="mt-1 text-sm text-slate-500">{{ bsDate(now(), 'l, F d, Y') }}</p>
            </div>
            <div class="p-6">
                @if($todaySlots->count() > 0)
                    <div class="space-y-3 max-h-[400px] overflow-y-auto">
                        @foreach($todaySlots as $slot)
                            <div class="flex items-center gap-4 rounded-lg border border-slate-200 p-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.832 18.477 19.246 18 17.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-slate-900">{{ $slot->subject->name ?? 'N/A' }}</h3>
                                    <p class="text-sm text-slate-600">{{ $slot->teacher->user->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - 
                                        {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="mt-2 text-sm text-slate-500">No classes scheduled for today</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Notices with CTEVT Tabs --}}
        <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm" x-data="{ activeTab: 'general' }">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-base font-semibold text-slate-900">Notices & Announcements</h2>
                <div class="mt-3 flex space-x-1 rounded-lg bg-slate-100 p-1">
                    <button @click="activeTab = 'general'" 
                            :class="activeTab === 'general' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                            class="flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition-all">
                        General
                    </button>
                    <button @click="activeTab = 'ctevt'" 
                            :class="activeTab === 'ctevt' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                            class="flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition-all">
                        CTEVT
                    </button>
                </div>
            </div>
            
            {{-- General Notices --}}
            <div x-show="activeTab === 'general'" class="p-6">
                @if($notices->count() > 0)
                    <div class="space-y-3 max-h-[400px] overflow-y-auto">
                        @foreach($notices->take(5) as $notice)
                            <div class="flex items-start gap-3 rounded-lg border border-slate-200 p-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100">
                                    <svg class="h-5 w-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-slate-900 line-clamp-2">{{ $notice->title }}</h3>
                                    <p class="text-xs text-slate-500 mt-1">
                                        {{ bsDate($notice->created_at, 'F d, Y') }} · {{ $notice->author->name ?? 'System' }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="mt-2 text-sm text-slate-500">No general notices available</p>
                    </div>
                @endif
            </div>

            {{-- CTEVT Notices Tab --}}
            <div x-show="activeTab === 'ctevt'" class="p-6" x-cloak x-data="{ ctevtSubTab: 'general' }">
                <div class="flex gap-1 bg-slate-50 p-2 rounded-lg mb-4">
                    <button @click="ctevtSubTab = 'general'" 
                            :class="ctevtSubTab === 'general' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-600 hover:bg-white/50'" 
                            class="flex-1 rounded-md px-2.5 py-1.5 text-xs font-semibold transition">
                        General ({{ collect($ctevtGeneralNotices['items'] ?? [])->count() }})
                    </button>
                    <button @click="ctevtSubTab = 'result'" 
                            :class="ctevtSubTab === 'result' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-600 hover:bg-white/50'" 
                            class="flex-1 rounded-md px-2.5 py-1.5 text-xs font-semibold transition">
                        Results ({{ collect($ctevtResultNotices['items'] ?? [])->count() }})
                    </button>
                </div>

                {{-- CTEVT General Notices --}}
                <div x-show="ctevtSubTab === 'general'" class="space-y-3 max-h-[350px] overflow-y-auto">
                    @forelse(collect($ctevtGeneralNotices['items'] ?? []) as $notice)
                        <a href="{{ $notice['url'] ?? ($ctevtGeneralNotices['page_url'] ?? '#') }}" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="flex items-start gap-3 rounded-lg border border-slate-200 p-4 hover:border-blue-300 hover:bg-blue-50/50 transition-all">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-50">
                                <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="shrink-0 rounded bg-red-50 px-1.5 py-0.5 text-[8px] font-bold text-red-600">CTEVT</span>
                                    <h3 class="font-semibold text-slate-900 line-clamp-2 text-sm">{{ $notice['title'] ?? 'Notice' }}</h3>
                                </div>
                                @if(!empty($notice['updated_date']))
                                    <p class="text-xs text-slate-500">{{ $notice['updated_date'] }}</p>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2 text-sm text-slate-500">No general notices available</p>
                        </div>
                    @endforelse
                </div>

                {{-- CTEVT Result Notices --}}
                <div x-show="ctevtSubTab === 'result'" x-cloak class="space-y-3 max-h-[350px] overflow-y-auto">
                    @forelse(collect($ctevtResultNotices['items'] ?? []) as $notice)
                        <a href="{{ $notice['url'] ?? ($ctevtResultNotices['page_url'] ?? '#') }}" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="flex items-start gap-3 rounded-lg border border-slate-200 p-4 hover:border-emerald-300 hover:bg-emerald-50/50 transition-all">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50">
                                <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="shrink-0 rounded bg-emerald-50 px-1.5 py-0.5 text-[8px] font-bold text-emerald-600">CTEVT</span>
                                    <h3 class="font-semibold text-slate-900 line-clamp-2 text-sm">{{ $notice['title'] ?? 'Result' }}</h3>
                                </div>
                                @if(!empty($notice['updated_date']))
                                    <p class="text-xs text-slate-500">{{ $notice['updated_date'] }}</p>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2 text-sm text-slate-500">No result notices available</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Attendance Chart
    const attendanceCtx = document.getElementById('attendanceCanvas');
    if (attendanceCtx) {
        const attendanceData = {!! json_encode($attendanceChartData) !!};
        
        new Chart(attendanceCtx, {
            type: 'line',
            data: {
                labels: attendanceData.labels,
                datasets: [{
                    label: 'Attendance %',
                    data: attendanceData.data,
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: 'rgba(148, 163, 184, 0.1)',
                        },
                        ticks: {
                            color: '#64748b',
                            font: { size: 11 },
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            color: '#64748b',
                            font: { size: 11 }
                        }
                    }
                }
            }
        });
    }

    // Grade Distribution Chart
    const gradeCtx = document.getElementById('gradeCanvas');
    if (gradeCtx) {
        const gradeData = {!! json_encode($gradeDistribution) !!};
        
        new Chart(gradeCtx, {
            type: 'doughnut',
            data: {
                labels: gradeData.labels,
                datasets: [{
                    data: gradeData.data,
                    backgroundColor: gradeData.colors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 11 },
                            color: '#64748b'
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
