@extends('layouts.app')

@section('title', 'My Marks & Results')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Academic Performance</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        My Marks & Results
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">View your exam results and academic performance</p>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($totalExams) }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Total Exams</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($averageMarks, 1) }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Average Marks</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-50">
                    <svg class="h-4 w-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($totalSubjects) }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Subjects</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50">
                    <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline gap-1">
                    <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($passPercentage, 1) }}</span>
                    <span class="text-sm font-medium text-slate-400">%</span>
                </div>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Pass Rate</p>
            </div>
        </div>
    </section>

    {{-- Performance Chart --}}
    @if(count($chartData['labels']) > 0)
    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm p-6">
        <h2 class="text-sm font-semibold text-slate-900 mb-4">Performance Trend</h2>
        <canvas id="performanceChart" height="80"></canvas>
    </section>
    @endif

    {{-- Filters --}}
    <section class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[150px]">
                <label class="block text-xs font-medium text-slate-700 mb-1">Exam Type</label>
                <select name="exam_type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Types</option>
                    <option value="internal" {{ request('exam_type') == 'internal' ? 'selected' : '' }}>Internal</option>
                    <option value="external" {{ request('exam_type') == 'external' ? 'selected' : '' }}>External</option>
                </select>
            </div>

            <div class="min-w-[150px]">
                <label class="block text-xs font-medium text-slate-700 mb-1">Category</label>
                <select name="category" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Categories</option>
                    <option value="monthly_assessment" {{ request('category') == 'monthly_assessment' ? 'selected' : '' }}>Monthly Assessment</option>
                    <option value="midterm" {{ request('category') == 'midterm' ? 'selected' : '' }}>Midterm</option>
                    <option value="final" {{ request('category') == 'final' ? 'selected' : '' }}>Final</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Filter
                </button>
                <a href="{{ route('student.marks.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Clear
                </a>
            </div>
        </form>
    </section>

    {{-- Marks Table --}}
    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">Exam Results</h2>
            <p class="text-xs text-slate-500">Your published exam marks and grades</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 text-xs font-medium text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Exam</th>
                        <th class="px-5 py-3 text-left">Subject</th>
                        <th class="px-5 py-3 text-left">Date</th>
                        <th class="px-5 py-3 text-center">Obtained</th>
                        <th class="px-5 py-3 text-center">Total</th>
                        <th class="px-5 py-3 text-center">Percentage</th>
                        <th class="px-5 py-3 text-center">Grade</th>
                        <th class="px-5 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($marks as $mark)
                        @php
                            $percentage = $mark->display_percentage ?? 0;
                            $passed = $mark->display_passed ?? false;
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <div class="text-sm font-medium text-slate-900">{{ $mark->exam->name }}</div>
                                <div class="text-xs text-slate-500">{{ ucfirst($mark->exam->type) }} • {{ str_replace('_', ' ', ucwords($mark->exam->category)) }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm text-slate-900">{{ $mark->subject->name }}</div>
                                <div class="text-xs text-slate-500">{{ $mark->subject->code }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm text-slate-900">{{ bsDate($mark->exam->start_date, 'F d, Y') }}</div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="text-sm font-medium text-slate-900">{{ number_format($mark->display_obtained_marks ?? 0, 2) }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="text-sm text-slate-600">{{ number_format($mark->display_full_marks ?? 0, 2) }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="text-sm font-medium text-slate-900">{{ $percentage }}%</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if(!empty($mark->display_grade))
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-blue-50 text-blue-700">
                                        {{ $mark->display_grade }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($passed)
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-emerald-50 text-emerald-700">
                                        Pass
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-red-50 text-red-700">
                                        Fail
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="mt-2 text-sm text-slate-500">No exam results available yet</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($marks->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">
                {{ $marks->links() }}
            </div>
        @endif
    </section>
</div>

@if(count($chartData['labels']) > 0)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('performanceChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartData['labels']),
                datasets: [{
                    label: 'Average Percentage',
                    data: @json($chartData['data']),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Average: ' + context.parsed.y.toFixed(1) + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
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
    }
});
</script>
@endpush
@endif
@endsection
