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
            'gradient' => 'linear-gradient(135deg,#2563EB,#3B82F6)',
        ],
        [
            'title' => 'Percentage Rate',
            'value' => number_format($kpiData['percentage_rate'], 1),
            'suffix' => '%',
            'note' => ($kpiData['published_assessments'] ?? 0) . ' published assessment' . (($kpiData['published_assessments'] ?? 0) === 1 ? '' : 's'),
            'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'gradient' => 'linear-gradient(135deg,#059669,#10B981)',
        ],
        [
            'title' => 'Pending Assignments',
            'value' => number_format($kpiData['pending_assignments']),
            'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
            'gradient' => 'linear-gradient(135deg,#7C3AED,#8B5CF6)',
        ],
        [
            'title' => 'Total Subjects',
            'value' => number_format($kpiData['total_subjects']),
            'note' => 'Enrolled subjects',
            'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
            'gradient' => 'linear-gradient(135deg,#D97706,#F59E0B)',
        ],
    ];
@endphp

<div class="space-y-4 sm:space-y-6">
    {{-- 1. KPI CARDS — gradient, light/dark independent --}}
    <section class="grid gap-3 sm:gap-4 grid-cols-2 lg:grid-cols-4">
        @foreach($kpiCards as $card)
        <div class="kpi-card relative overflow-hidden rounded-2xl p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
             style="background: {{ $card['gradient'] }};">
            <div class="pointer-events-none absolute -right-3 -top-3 h-16 w-16 rounded-full bg-white/10"></div>
            <div class="flex h-8 w-8 items-center justify-center rounded-lg mb-3 bg-white/20">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                </svg>
            </div>
            <div class="flex items-baseline gap-1">
                <span class="text-2xl font-black text-white">{{ $card['value'] }}</span>
                @if(!empty($card['suffix']))
                    <span class="text-sm font-semibold text-white/70">{{ $card['suffix'] }}</span>
                @endif
            </div>
            <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-wider text-white/75">{{ $card['title'] }}</p>
            @if(!empty($card['note']))
                <p class="mt-1 text-[10px] text-white/60 truncate">{{ $card['note'] }}</p>
            @endif
        </div>
        @endforeach
    </section>

    {{-- 3. CHARTS --}}
    <section class="grid gap-5 lg:grid-cols-2">
        {{-- Semester Performance --}}
        <div class="rounded-xl bg-white dark:bg-[#132044] border border-slate-200 dark:border-[#1e3a5f] shadow-sm">
            <div class="px-4 sm:px-5 py-4 border-b border-slate-100 dark:border-[#1e3a5f]">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Semester Performance</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">CTEVT Final vs Assessment marks percentage</p>
            </div>
            <div class="p-4 sm:p-5">
                <div style="position:relative; height:220px;">
                    <canvas id="gradeChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Attendance Trend --}}
        <div class="rounded-xl bg-white dark:bg-[#132044] border border-slate-200 dark:border-[#1e3a5f] shadow-sm">
            <div class="px-4 sm:px-5 py-4 border-b border-slate-100 dark:border-[#1e3a5f]">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Attendance Trend</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Daily attendance % (Last 7 days)</p>
                    </div>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300">
                        Current Week
                    </span>
                </div>
            </div>
            <div class="p-4 sm:p-5">
                <div style="position:relative; height:220px;">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>
    </section>

    {{-- 4. RECENT NOTICES --}}
    <section class="rounded-xl bg-white dark:bg-[#132044] border border-slate-200 dark:border-[#1e3a5f] shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-[#1e3a5f]">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Recent Notices</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Internal announcements</p>
        </div>
        <div class="divide-y divide-slate-100 dark:divide-[#1e3a5f]">
            @forelse($notices as $notice)
                <div class="flex gap-3 px-5 py-3.5 hover:bg-slate-50 dark:hover:bg-[#1e3a5f] transition-colors">
                    <div class="flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-lg text-center bg-slate-100 dark:bg-[#1e3a5f] text-slate-600 dark:text-slate-400">
                        <span class="text-[8px] font-semibold leading-none">{{ bsDate($notice->created_at, 'Y') }}</span>
                        <span class="text-sm font-bold leading-none">{{ bsDate($notice->created_at, 'd') }}</span>
                        <span class="text-[7px] font-semibold uppercase leading-none">{{ bsDate($notice->created_at, 'F') }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-slate-900 dark:text-slate-100">{{ $notice->title }}</p>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ bsDate($notice->created_at, 'F d, Y') }} · {{ $notice->author->name ?? 'System' }}</p>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">No notices available</p>
                </div>
            @endforelse
        </div>
    </section>

    {{-- 5. UPCOMING ASSIGNMENTS --}}
    <section class="rounded-xl bg-white dark:bg-[#132044] border border-slate-200 dark:border-[#1e3a5f] shadow-sm">
        <div class="px-4 sm:px-5 py-4 border-b border-slate-100 dark:border-[#1e3a5f]">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Upcoming Assignments</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Pending assignments and their due dates</p>
        </div>
        <div class="p-4 sm:p-5">
            @if($upcomingAssignments->count() > 0)
                <div class="space-y-3 max-h-64 sm:max-h-80 overflow-y-auto">
                    @foreach($upcomingAssignments as $assignment)
                        @php
                            $daysLeft = now()->diffInDays($assignment->due_date, false);
                            $isOverdue = $daysLeft < 0;
                            $isUrgent  = $daysLeft <= 2 && $daysLeft >= 0;
                        @endphp
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-[#1e3a5f] border border-slate-200 dark:border-[#2d4a70] transition-colors hover:bg-slate-100 dark:hover:bg-[#24456e]">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $assignment->title }}</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">({{ $assignment->subject->name ?? 'Unknown' }})</span>
                                </div>
                                @if($assignment->description)
                                    <p class="text-xs mt-0.5 line-clamp-1 text-slate-600 dark:text-slate-400">{{ Str::limit($assignment->description, 80) }}</p>
                                @endif
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Due: {{ bsDate($assignment->due_date, 'Y F d') }}</span>
                                    @if($isOverdue)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">Overdue</span>
                                    @elseif($isUrgent)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">Due Soon</span>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-2 sm:mt-0 sm:ml-3 flex-shrink-0">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                                    {{ bsDate($assignment->due_date, 'F d') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-10">
                    <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <p class="mt-2 text-sm text-slate-400 dark:text-slate-500">No pending assignments</p>
                </div>
            @endif
        </div>
    </section>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.classList.contains('dark');
    const tickColor   = isDark ? '#64748b' : '#94a3b8';
    const gridColor   = isDark ? '#1e3a5f' : '#f1f5f9';
    const legendColor = isDark ? '#94a3b8' : '#64748b';

    Chart.defaults.font.family = 'Inter, sans-serif';
    Chart.defaults.font.size   = 12;
    Chart.defaults.color       = legendColor;

    // Semester Performance Bar Chart
    const gradeCtx = document.getElementById('gradeChart');
    if (gradeCtx) {
        const semData = @json($chartData['semester_marks']);
        new Chart(gradeCtx, {
            type: 'bar',
            data: {
                labels: semData.labels,
                datasets: [
                    {
                        label: 'CTEVT Final %',
                        data: semData.ctevt,
                        backgroundColor: 'rgba(37,99,235,0.82)',
                        borderRadius: 5,
                        borderSkipped: false,
                    },
                    {
                        label: 'Assessment %',
                        data: semData.assessment,
                        backgroundColor: 'rgba(16,185,129,0.82)',
                        borderRadius: 5,
                        borderSkipped: false,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 14, font: { size: 11 }, color: legendColor } },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.dataset.label + ': ' + (ctx.parsed.y !== null ? ctx.parsed.y + '%' : 'N/A')
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 }, color: tickColor } },
                    y: { beginAtZero: true, max: 100, grid: { color: gridColor },
                         ticks: { callback: v => v + '%', font: { size: 11 }, color: tickColor } }
                }
            }
        });
    }

    // Attendance Trend Line Chart
    const attendanceCtx = document.getElementById('attendanceChart');
    if (attendanceCtx) {
        const attendanceData = @json($chartData['attendance']);
        new Chart(attendanceCtx, {
            type: 'line',
            data: {
                labels: attendanceData.map(i => i.date_bs_short),
                datasets: [{
                    label: 'Attendance %',
                    data: attendanceData.map(i => i.rate),
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
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: ctx => attendanceData[ctx[0].dataIndex].date_bs,
                            label: ctx => 'Attendance: ' + ctx.parsed.y + '%'
                        }
                    }
                },
                scales: {
                    y: { min: 0, max: 100, grid: { color: gridColor },
                         ticks: { callback: v => v + '%', font: { size: 11 }, color: tickColor } },
                    x: { grid: { display: false },
                         ticks: { font: { size: 11 }, color: tickColor, maxRotation: window.innerWidth < 640 ? 45 : 0 } }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
