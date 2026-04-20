@extends('layouts.app')

@section('title', 'Exams & Marks')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">{{ $department->name }} Department</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        Exams & Marks Management
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">Monitor exam schedules and student performance</p>
                </div>
            </div>
        </div>
    </section>

    {{-- KPI Cards --}}
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
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
            <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-500 opacity-40"></div>
        </div>

        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50">
                    <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($upcomingExams) }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Upcoming</p>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-amber-500 opacity-40"></div>
        </div>

        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-orange-50">
                    <svg class="h-4 w-4 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($ongoingExams) }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Ongoing</p>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-orange-500 opacity-40"></div>
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
                <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($completedExams) }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Completed</p>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-emerald-500 opacity-40"></div>
        </div>
    </section>

    {{-- Exams Table --}}
    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">Department Exams</h2>
            <p class="text-xs text-slate-500">Exam schedules and status for your department</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 text-xs font-medium text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Exam Name</th>
                        <th class="px-5 py-3 text-left">Type</th>
                        <th class="px-5 py-3 text-left">Schedule</th>
                        <th class="px-5 py-3 text-left">Programs</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($exams as $exam)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <div class="text-sm font-medium text-slate-900">{{ $exam->name }}</div>
                                <div class="text-xs text-slate-500">{{ $exam->academicSession->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm text-slate-900">{{ ucfirst($exam->type) }}</div>
                                <div class="text-xs text-slate-500">{{ $exam->category_label }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm text-slate-900">{{ bsDate($exam->start_date, 'M d, Y') }}</div>
                                @if($exam->end_date && $exam->end_date != $exam->start_date)
                                    <div class="text-xs text-slate-500">to {{ bsDate($exam->end_date, 'M d, Y') }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm text-slate-900">{{ $exam->programs->count() }} programs</div>
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $statusColors = [
                                        'upcoming' => 'bg-blue-50 text-blue-700',
                                        'ongoing' => 'bg-orange-50 text-orange-700',
                                        'completed' => 'bg-emerald-50 text-emerald-700',
                                        'published' => 'bg-green-50 text-green-700',
                                    ];
                                    $statusColor = $statusColors[$exam->status] ?? 'bg-slate-50 text-slate-700';
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ $statusColor }}">
                                    {{ $exam->status_label }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    @if($exam->status === 'completed' || $exam->is_published)
                                        <a href="{{ route('hod.exams.marks', ['exam_id' => $exam->id]) }}" 
                                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            View Marks
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="mt-2 text-sm text-slate-500">No exams found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($exams->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">
                {{ $exams->links() }}
            </div>
        @endif
    </section>

    {{-- Quick Actions --}}
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('hod.exams.results') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-blue-300 hover:bg-blue-50/50">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-900">Student Results</p>
                <p class="text-xs text-slate-500">View student performance</p>
            </div>
        </a>

        <a href="{{ route('hod.exams.analytics') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-emerald-300 hover:bg-emerald-50/50">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-900">Analytics</p>
                <p class="text-xs text-slate-500">Performance insights</p>
            </div>
        </a>

        <a href="{{ route('hod.reports.performance') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-violet-300 hover:bg-violet-50/50">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-900">Export Reports</p>
                <p class="text-xs text-slate-500">Download performance data</p>
            </div>
        </a>

        <a href="{{ route('hod.dashboard') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-slate-300 hover:bg-slate-50">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-50 text-slate-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-900">Back to Dashboard</p>
                <p class="text-xs text-slate-500">Return to main dashboard</p>
            </div>
        </a>
    </section>
</div>
@endsection