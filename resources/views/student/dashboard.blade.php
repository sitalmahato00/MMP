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
            'tone' => 'blue',
        ],
        [
            'title' => 'Percentage Rate',
            'value' => number_format($kpiData['percentage_rate'], 1),
            'suffix' => '%',
            'note' => ($kpiData['published_assessments'] ?? 0) . ' published assessment' . (($kpiData['published_assessments'] ?? 0) === 1 ? '' : 's')
                . (($kpiData['distinction_assessments'] ?? 0) > 0
                    ? ' · ' . $kpiData['distinction_assessments'] . ' distinction'
                        . (($kpiData['distinction_assessments'] ?? 0) === 1 ? '' : 's')
                    : ''),
            'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'tone' => 'emerald',
        ],
        [
            'title' => 'Pending Assignments',
            'value' => number_format($kpiData['pending_assignments']),
            'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
            'tone' => 'violet',
        ],
        [
            'title' => 'Total Subjects',
            'value' => number_format($kpiData['total_subjects']),
            'note' => 'Enrolled subjects',
            'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
            'tone' => 'amber',
        ],
    ];

    $toneMap = [
        'blue'    => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'ring' => 'ring-blue-100', 'bar' => 'bg-blue-500'],
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'ring' => 'ring-emerald-100', 'bar' => 'bg-emerald-500'],
        'violet'  => ['bg' => 'bg-violet-50', 'text' => 'text-violet-600', 'ring' => 'ring-violet-100', 'bar' => 'bg-violet-500'],
        'amber'   => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'ring' => 'ring-amber-100', 'bar' => 'bg-amber-500'],
    ];
@endphp

<div class="space-y-4 sm:space-y-6">
    {{-- ═══════════════════════════════════════════════════════════
         1. TOP HEADER – Student Overview
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
                        <p class="mt-1 text-sm text-slate-600">{{ $student->program->name ?? 'No Program' }} - Semester {{ $student->current_semester ?? 'N/A' }}</p>
                    @else
                        <p class="mt-1 text-sm text-amber-600">
                            <svg class="inline h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            Student profile not found
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
         2. KPI METRICS – 4 Cards
    ═══════════════════════════════════════════════════════════ --}}
    <section class="grid gap-3 sm:gap-4 grid-cols-2 lg:grid-cols-4">
        @foreach($kpiCards as $card)
            @php $t = $toneMap[$card['tone']] ?? $toneMap['blue']; @endphp
            <div class="group relative overflow-hidden rounded-xl border border-slate-200/80 bg-white p-3 sm:p-4 shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                <div class="flex items-start justify-between">
                    <div class="flex h-8 w-8 sm:h-9 sm:w-9 items-center justify-center rounded-lg {{ $t['bg'] }}">
                        <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4 {{ $t['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-2 sm:mt-3">
                    <div class="flex items-baseline gap-1">
                        <span class="text-lg sm:text-2xl font-bold tracking-tight text-slate-900">{{ $card['value'] }}</span>
                        @if(!empty($card['suffix']))
                            <span class="text-xs sm:text-sm font-medium text-slate-400">{{ $card['suffix'] }}</span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-[10px] sm:text-[11px] font-medium uppercase tracking-wider text-slate-400">{{ $card['title'] }}</p>
                </div>
                @if(!empty($card['note']))
                    <p class="mt-1 sm:mt-2 text-[10px] sm:text-[11px] text-slate-500">{{ $card['note'] }}</p>
                @endif
                <div class="absolute bottom-0 left-0 right-0 h-0.5 {{ $t['bar'] }} opacity-40"></div>
            </div>
        @endforeach
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         3. ANALYTICS CHARTS – Student Performance
    ═══════════════════════════════════════════════════════════ --}}
    <section class="grid gap-5 lg:grid-cols-2">
        {{-- Grade Distribution Donut Chart --}}
        <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 sm:px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Grade Distribution</h2>
                <p class="text-xs text-slate-500">Your exam performance breakdown</p>
            </div>
            <div class="p-4 sm:p-5">
                <canvas class="h-48 sm:h-64 w-full" id="gradeChart"></canvas>
            </div>
        </div>

        {{-- Attendance Trend Chart --}}
        <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 sm:px-5 py-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">Attendance Trend</h2>
                        <p class="text-xs text-slate-500">Daily attendance percentage (Last 7 days)</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                            Current Week
                        </span>
                    </div>
                </div>
            </div>
            <div class="p-4 sm:p-5">
                <canvas class="h-48 sm:h-64 w-full" id="attendanceChart"></canvas>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         3. NOTICES SECTION – Recent Notices with Tabs
    ═══════════════════════════════════════════════════════════ --}}
    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm" x-data="{ activeTab: 'internal' }">
        <div class="border-b border-slate-100 px-5 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Recent Notices</h2>
                    <p class="text-xs text-slate-500">Internal and CTEVT announcements</p>
                </div>
            </div>
            
            {{-- Notice Tabs --}}
            <div class="mt-3 flex space-x-1 rounded-lg bg-slate-100 p-1">
                <button @click="activeTab = 'internal'" 
                        :class="activeTab === 'internal' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                        class="flex-1 rounded-md px-3 py-1.5 text-xs font-medium transition-all">
                    Internal
                </button>
                <button @click="activeTab = 'ctevt'" 
                        :class="activeTab === 'ctevt' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                        class="flex-1 rounded-md px-3 py-1.5 text-xs font-medium transition-all">
                    CTEVT
                </button>
            </div>
        </div>
        
        {{-- Internal Notices --}}
        <div x-show="activeTab === 'internal'" class="divide-y divide-slate-100">
            @forelse($notices as $notice)
                <div class="flex gap-3 px-5 py-3.5 transition hover:bg-slate-50">
                    <div class="flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                        <span class="text-[8px] font-semibold leading-none">{{ bsDate($notice->created_at, 'Y') }}</span>
                        <span class="text-sm font-bold leading-none">{{ bsDate($notice->created_at, 'd') }}</span>
                        <span class="text-[7px] font-semibold uppercase leading-none">{{ bsDate($notice->created_at, 'F') }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-slate-900">{{ $notice->title }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">{{ bsDate($notice->created_at, 'F d, Y') }} · {{ $notice->author->name ?? 'System' }}</p>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-2 text-xs text-slate-400">No notices available</p>
                </div>
            @endforelse
        </div>
        
        {{-- CTEVT Notices Tab --}}
        <div x-show="activeTab === 'ctevt'" class="divide-y divide-slate-100" x-cloak x-data="{ ctevtSubTab: 'general' }">
            <div class="flex gap-1 bg-slate-50 p-2">
                <button @click="ctevtSubTab = 'general'" :class="ctevtSubTab === 'general' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-600 hover:bg-white/50'" class="flex-1 rounded-md px-2.5 py-1.5 text-[10px] font-semibold transition">
                    General
                </button>
                <button @click="ctevtSubTab = 'result'" :class="ctevtSubTab === 'result' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-600 hover:bg-white/50'" class="flex-1 rounded-md px-2.5 py-1.5 text-[10px] font-semibold transition">
                    Results
                </button>
            </div>

            <div x-show="ctevtSubTab === 'general'" class="divide-y divide-slate-100 max-h-[350px] sm:max-h-[450px] overflow-y-auto">
                @if(isset($ctevtGeneralNotices['items']) && count($ctevtGeneralNotices['items']) > 0)
                    @foreach($ctevtGeneralNotices['items'] as $notice)
                        <a href="{{ $notice['url'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="block px-4 py-2.5 transition hover:bg-slate-50">
                            <div class="flex items-center gap-2">
                                <span class="shrink-0 rounded bg-red-50 px-1.5 py-0.5 text-[8px] font-bold text-red-600">CTEVT</span>
                                <span class="min-w-0 flex-1 truncate text-[11px] font-medium text-slate-700">{{ $notice['title'] ?? 'Notice' }}</span>
                            </div>
                            @if(!empty($notice['updated_date']))
                                <p class="mt-1 text-[9px] text-slate-400">{{ $notice['updated_date'] }}</p>
                            @endif
                        </a>
                    @endforeach
                @else
                    <p class="py-8 text-center text-xs text-slate-400">No general notices available.</p>
                @endif
            </div>

            <div x-show="ctevtSubTab === 'result'" x-cloak class="divide-y divide-slate-100 max-h-[350px] sm:max-h-[450px] overflow-y-auto">
                @if(isset($ctevtResultNotices['items']) && count($ctevtResultNotices['items']) > 0)
                    @foreach($ctevtResultNotices['items'] as $notice)
                        <a href="{{ $notice['url'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="block px-4 py-2.5 transition hover:bg-slate-50">
                            <div class="flex items-center gap-2">
                                <span class="shrink-0 rounded bg-emerald-50 px-1.5 py-0.5 text-[8px] font-bold text-emerald-600">CTEVT</span>
                                <span class="min-w-0 flex-1 truncate text-[11px] font-medium text-slate-700">{{ $notice['title'] ?? 'Result' }}</span>
                            </div>
                            @if(!empty($notice['updated_date']))
                                <p class="mt-1 text-[9px] text-slate-400">{{ $notice['updated_date'] }}</p>
                            @endif
                        </a>
                    @endforeach
                @else
                    <p class="py-8 text-center text-xs text-slate-400">No result notices available.</p>
                @endif
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         4. UPCOMING ASSIGNMENTS – Pending Tasks
    ═══════════════════════════════════════════════════════════ --}}
    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 sm:px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">Upcoming Assignments</h2>
            <p class="text-xs text-slate-500">Pending assignments and their due dates</p>
        </div>
        <div class="p-4 sm:p-5">
            @if($upcomingAssignments->count() > 0)
                <div class="space-y-3 max-h-64 sm:max-h-80 overflow-y-auto">
                    @foreach($upcomingAssignments as $assignment)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 rounded-lg bg-slate-50 hover:bg-slate-100 transition-colors">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                                    <span class="text-sm font-semibold text-slate-900">{{ $assignment->title }}</span>
                                    <span class="text-xs text-slate-500">({{ $assignment->subject->name ?? 'Unknown Subject' }})</span>
                                </div>
                                @if($assignment->description)
                                    <p class="text-xs text-slate-600 mt-0.5 line-clamp-2">{{ Str::limit($assignment->description, 100) }}</p>
                                @endif
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs text-slate-500">Due: {{ bsDate($assignment->due_date, 'Y F d, l') }}</span>
                                    @php
                                        $daysLeft = now()->diffInDays($assignment->due_date, false);
                                        $isOverdue = $daysLeft < 0;
                                        $isUrgent = $daysLeft <= 2 && $daysLeft >= 0;
                                    @endphp
                                    @if($isOverdue)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                            Overdue
                                        </span>
                                    @elseif($isUrgent)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                                            Due Soon
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-2 sm:mt-0 sm:ml-3 flex-shrink-0">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ bsDate($assignment->due_date, 'F d') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 sm:py-12">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <p class="mt-2 text-sm text-slate-400">No pending assignments</p>
                </div>
            @endif
        </div>
    </section>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart.js default configuration
    Chart.defaults.font.family = 'Inter, sans-serif';
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#64748b';
    
    // Grade Distribution Donut Chart
    const gradeCtx = document.getElementById('gradeChart');
    if (gradeCtx) {
        const gradeData = @json($chartData['grades']);
        console.log('Grade Data:', gradeData);
        
        new Chart(gradeCtx, {
            type: 'doughnut',
            data: {
                labels: gradeData.labels,
                datasets: [{
                    data: gradeData.data,
                    backgroundColor: gradeData.colors,
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: window.innerWidth < 640 ? 'bottom' : 'right',
                        labels: {
                            padding: 15,
                            font: {
                                size: window.innerWidth < 640 ? 10 : 11
                            },
                            generateLabels: function(chart) {
                                const data = chart.data;
                                return data.labels.map((label, i) => ({
                                    text: label + ' (' + data.datasets[0].data[i] + ')',
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
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Attendance Trend Chart
    const attendanceCtx = document.getElementById('attendanceChart');
    if (attendanceCtx) {
        const attendanceData = @json($chartData['attendance']);
        console.log('Attendance Data:', attendanceData);
        
        new Chart(attendanceCtx, {
            type: 'line',
            data: {
                labels: attendanceData.map(item => item.date_bs_short), // Use BS short dates for labels
                datasets: [{
                    label: 'Attendance %',
                    data: attendanceData.map(item => item.rate),
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37,99,235,0.08)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.38,
                    pointRadius: 4,
                    pointBackgroundColor: '#2563eb',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
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
                            title: function(context) {
                                const index = context[0].dataIndex;
                                return attendanceData[index].date_bs; // Use full BS date for tooltip
                            },
                            label: function(context) {
                                return 'Attendance: ' + context.parsed.y + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 0,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            },
                            font: {
                                size: window.innerWidth < 640 ? 10 : 12
                            }
                        },
                        grid: {
                            color: '#f1f5f9'
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: window.innerWidth < 640 ? 9 : 11
                            },
                            maxRotation: window.innerWidth < 640 ? 45 : 0
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
    
    // Handle responsive chart updates
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            Chart.helpers.each(Chart.instances, function(instance) {
                if (instance.chart.canvas.id === 'gradeChart') {
                    // Update legend position for grade chart
                    instance.options.plugins.legend.position = window.innerWidth < 640 ? 'bottom' : 'right';
                    instance.options.plugins.legend.labels.font.size = window.innerWidth < 640 ? 10 : 11;
                }
                if (instance.chart.canvas.id === 'attendanceChart') {
                    // Update font sizes for attendance chart
                    instance.options.scales.y.ticks.font.size = window.innerWidth < 640 ? 10 : 12;
                    instance.options.scales.x.ticks.font.size = window.innerWidth < 640 ? 9 : 11;
                    instance.options.scales.x.ticks.maxRotation = window.innerWidth < 640 ? 45 : 0;
                }
                instance.update();
            });
        }, 250);
    });
});
</script>
@endpush
@endsection
