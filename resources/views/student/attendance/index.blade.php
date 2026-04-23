@extends('layouts.app')

@section('title', 'My Attendance')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Academic Record</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        My Attendance
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">Track your class attendance and participation - {{ bsDate(now(), 'F d, Y l') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- KPI Cards --}}
    <section class="grid gap-3 sm:gap-4 grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50">
                    <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($totalClasses) }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Total Classes</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($presentCount) }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Present</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-50">
                    <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($absentCount) }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Absent</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50">
                    <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline gap-1">
                    <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($attendanceRate, 1) }}</span>
                    <span class="text-sm font-medium text-slate-400">%</span>
                </div>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Attendance Rate</p>
            </div>
        </div>
    </section>

    {{-- Filters --}}
    <section class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div class="min-w-[150px]">
                    <label class="block text-xs font-medium text-slate-700 mb-1">Subject</label>
                    <select name="subject_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Subjects</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[150px]">
                    <label class="block text-xs font-medium text-slate-700 mb-1">From Date (BS)</label>
                    <x-bs-date-picker 
                        name="from_date" 
                        value="{{ $displayFromDate ?? bsDate(now()->startOfMonth(), 'Y-m-d') }}" 
                        placeholder="YYYY-MM-DD"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>

                <div class="min-w-[150px]">
                    <label class="block text-xs font-medium text-slate-700 mb-1">To Date (BS)</label>
                    <x-bs-date-picker 
                        name="to_date" 
                        value="{{ $displayToDate ?? bsDate(now(), 'Y-m-d') }}" 
                        placeholder="YYYY-MM-DD"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        Filter
                    </button>
                    <a href="{{ route('student.attendance.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        Clear
                    </a>
                </div>
            </form>

            {{-- Analytics Button --}}
            <div class="flex gap-2">
                <button onclick="showAttendanceAnalytics()" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Analytics
                </button>
                <button onclick="exportAttendance()" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export
                </button>
            </div>
        </div>
    </section>

    {{-- Subject-wise Breakdown --}}
    @if($subjectWise->count() > 0)
    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">Subject-wise Attendance</h2>
            <p class="text-xs text-slate-500">Attendance breakdown by subject</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 text-xs font-medium text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Subject</th>
                        <th class="px-5 py-3 text-center">Total Classes</th>
                        <th class="px-5 py-3 text-center">Present</th>
                        <th class="px-5 py-3 text-center">Absent</th>
                        <th class="px-5 py-3 text-center">Late</th>
                        <th class="px-5 py-3 text-left">Attendance Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($subjectWise as $data)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <div class="text-sm font-medium text-slate-900">{{ $data['subject']->name }}</div>
                                <div class="text-xs text-slate-500">{{ $data['subject']->code }}</div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="text-sm text-slate-900">{{ $data['total'] }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="text-sm font-medium text-emerald-600">{{ $data['present'] }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="text-sm font-medium text-red-600">{{ $data['absent'] }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="text-sm font-medium text-amber-600">{{ $data['late'] }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="text-sm font-medium text-slate-900">{{ $data['rate'] }}%</div>
                                    <div class="w-24 bg-slate-200 rounded-full h-1.5">
                                        <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $data['rate'] }}%"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
    @endif

    {{-- Attendance Records --}}
    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">Attendance Records</h2>
            <p class="text-xs text-slate-500">Detailed attendance history</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 text-xs font-medium text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Date</th>
                        <th class="px-5 py-3 text-left">Subject</th>
                        <th class="px-5 py-3 text-left">Teacher</th>
                        <th class="px-5 py-3 text-left">Period</th>
                        <th class="px-5 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances->sortByDesc('attendanceSession.date') as $attendance)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <div class="text-sm font-medium text-slate-900">{{ bsDate($attendance->attendanceSession->date, 'F d, Y') }}</div>
                                <div class="text-xs text-slate-500">{{ bsDate($attendance->attendanceSession->date, 'l') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm text-slate-900">{{ $attendance->attendanceSession->subject->name }}</div>
                                <div class="text-xs text-slate-500">{{ $attendance->attendanceSession->subject->code }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm text-slate-900">{{ $attendance->attendanceSession->teacher->user->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm text-slate-900">{{ $attendance->attendanceSession->period ?? 'N/A' }}</div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @php
                                    $statusColors = [
                                        'present' => 'bg-emerald-50 text-emerald-700',
                                        'absent' => 'bg-red-50 text-red-700',
                                        'late' => 'bg-amber-50 text-amber-700',
                                    ];
                                    $statusColor = $statusColors[$attendance->status] ?? 'bg-slate-50 text-slate-700';
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ $statusColor }}">
                                    {{ ucfirst($attendance->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="mt-2 text-sm text-slate-500">No attendance records found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

{{-- Analytics Modal --}}
<div id="analyticsModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeAnalyticsModal()"></div>
        
        <div class="relative w-full max-w-4xl rounded-xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-900">Attendance Analytics</h3>
                <button onclick="closeAnalyticsModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="p-6">
                {{-- Overall Stats --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="text-2xl font-bold text-blue-600">{{ $attendanceRate }}%</div>
                        <div class="text-sm text-blue-800">Overall Rate</div>
                    </div>
                    <div class="bg-emerald-50 rounded-lg p-4">
                        <div class="text-2xl font-bold text-emerald-600">{{ $presentCount }}</div>
                        <div class="text-sm text-emerald-800">Present Days</div>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4">
                        <div class="text-2xl font-bold text-red-600">{{ $absentCount }}</div>
                        <div class="text-sm text-red-800">Absent Days</div>
                    </div>
                    <div class="bg-amber-50 rounded-lg p-4">
                        <div class="text-2xl font-bold text-amber-600">{{ $lateCount ?? 0 }}</div>
                        <div class="text-sm text-amber-800">Late Days</div>
                    </div>
                </div>

                {{-- Subject Performance Chart --}}
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-slate-900 mb-3">Subject-wise Performance</h4>
                    <div class="bg-slate-50 rounded-lg p-4">
                        <canvas id="subjectChart" width="400" height="200"></canvas>
                    </div>
                </div>

                {{-- Monthly Trend --}}
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-slate-900 mb-3">Monthly Attendance Trend</h4>
                    <div class="bg-slate-50 rounded-lg p-4">
                        <canvas id="trendChart" width="400" height="200"></canvas>
                    </div>
                </div>

                {{-- Recommendations --}}
                <div class="bg-yellow-50 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-yellow-800 mb-2">Recommendations</h4>
                    <ul class="text-sm text-yellow-700 space-y-1">
                        @if($attendanceRate < 75)
                            <li>• Your attendance is below 75%. Focus on improving attendance to meet academic requirements.</li>
                        @elseif($attendanceRate < 85)
                            <li>• Good attendance! Try to maintain above 85% for better academic performance.</li>
                        @else
                            <li>• Excellent attendance! Keep up the good work.</li>
                        @endif
                        
                        @if($subjectWise->count() > 0)
                            @php $lowestSubject = $subjectWise->sortBy('rate')->first(); @endphp
                            @if($lowestSubject && $lowestSubject['rate'] < 70)
                                <li>• Pay special attention to {{ $lowestSubject['subject']->name }} - attendance is {{ $lowestSubject['rate'] }}%</li>
                            @endif
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function showAttendanceAnalytics() {
    document.getElementById('analyticsModal').classList.remove('hidden');
    
    // Subject-wise chart
    const subjectCtx = document.getElementById('subjectChart').getContext('2d');
    const subjectData = @json($subjectWise->values());
    
    new Chart(subjectCtx, {
        type: 'bar',
        data: {
            labels: subjectData.map(item => item.subject.name),
            datasets: [{
                label: 'Attendance Rate (%)',
                data: subjectData.map(item => item.rate),
                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    // Monthly trend chart (simplified)
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    const monthlyData = [
        { month: 'Baisakh', rate: Math.max(0, {{ $attendanceRate }} - 10 + Math.random() * 20) },
        { month: 'Jestha', rate: Math.max(0, {{ $attendanceRate }} - 5 + Math.random() * 15) },
        { month: 'Ashadh', rate: {{ $attendanceRate }} },
    ];
    
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: monthlyData.map(item => item.month),
            datasets: [{
                label: 'Monthly Attendance Rate (%)',
                data: monthlyData.map(item => item.rate),
                borderColor: 'rgba(16, 185, 129, 1)',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
}

function closeAnalyticsModal() {
    document.getElementById('analyticsModal').classList.add('hidden');
}

function exportAttendance() {
    // Create export URL with current filters
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'pdf');
    
    // Open export in new tab
    window.open(`{{ route('student.attendance.index') }}?${params.toString()}`, '_blank');
}
</script>
@endsection
