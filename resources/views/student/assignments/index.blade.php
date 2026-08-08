@extends('layouts.app')

@section('title', 'My Assignments')

@section('content')
<div class="space-y-6">
    {{-- KPI Cards --}}
    <section class="grid gap-3 sm:gap-4 grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                    <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ number_format($totalAssignments) }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Assignments</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30">
                    <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ number_format($pendingCount) }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Pending</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/30">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ number_format($submittedCount) }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Submitted</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-50 dark:bg-violet-900/30">
                    <svg class="h-4 w-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ number_format($gradedCount) }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Graded</p>
            </div>
        </div>
    </section>

    {{-- Filters --}}
    <section class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] p-4 shadow-sm">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <input type="hidden" name="semester" value="{{ $selectedSemester }}">

            {{-- Semester dropdown inline --}}
            <div class="min-w-[130px]">
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Semester</label>
                <select name="semester" onchange="this.form.submit()"
                    class="w-full rounded-lg border border-slate-300 dark:border-[#2d4a70] bg-white dark:bg-[#1a2f50] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500">
                    @foreach($semesterOptions as $sem)
                        <option value="{{ $sem }}" {{ $selectedSemester == $sem ? 'selected' : '' }}>
                            Semester {{ $sem }}{{ $sem == $currentSemester ? ' (Current)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-[150px]">
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Subject</label>
                <select name="subject_id" class="w-full rounded-lg border border-slate-300 dark:border-[#2d4a70] bg-white dark:bg-[#1a2f50] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-[150px]">
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border border-slate-300 dark:border-[#2d4a70] bg-white dark:bg-[#1a2f50] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="graded" {{ request('status') == 'graded' ? 'selected' : '' }}>Graded</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Filter
                </button>
                <a href="{{ route('student.assignments.index') }}" class="rounded-lg border border-slate-300 dark:border-[#2d4a70] px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#1e3a5f]">
                    Clear
                </a>
            </div>
        </form>
    </section>

    {{-- Assignments List --}}
    <section class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] shadow-sm">
        <div class="border-b border-slate-100 dark:border-[#1e3a5f] px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Assignments</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Your course assignments and submissions</p>
        </div>
        
        <div class="divide-y divide-slate-100 dark:divide-[#1e3a5f]">
            @forelse($assignments as $assignment)
                <div class="px-5 py-4 hover:bg-slate-50 dark:hover:bg-[#1e3a5f]">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start gap-3">
                                <div class="flex-1">
                                    <a href="{{ route('student.assignments.show', $assignment->id) }}" 
                                       class="text-sm font-medium text-slate-900 dark:text-slate-100 hover:text-blue-600">
                                        {{ $assignment->title }}
                                    </a>
                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                        <span>{{ $assignment->subject->name }}</span>
                                        <span>•</span>
                                        <span>{{ $assignment->teacher->user->name ?? 'N/A' }}</span>
                                        <span>•</span>
                                        <span>Due: {{ bsDate($assignment->due_date, 'F d, Y') }}</span>
                                    </div>
                                    @if($assignment->description)
                                        <p class="mt-1 text-xs text-slate-600 dark:text-slate-400 line-clamp-2">{{ $assignment->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            @php
                                $statusConfig = [
                                    'pending' => ['color' => 'bg-amber-50 text-amber-700', 'label' => 'Pending'],
                                    'submitted' => ['color' => 'bg-blue-50 text-blue-700', 'label' => 'Submitted'],
                                    'graded' => ['color' => 'bg-emerald-50 text-emerald-700', 'label' => 'Graded'],
                                    'overdue' => ['color' => 'bg-red-50 text-red-700', 'label' => 'Overdue'],
                                ];
                                $config = $statusConfig[$assignment->submission_status] ?? ['color' => 'bg-slate-50 text-slate-700', 'label' => 'Unknown'];
                            @endphp
                            
                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ $config['color'] }}">
                                {{ $config['label'] }}
                            </span>
                            
                            @if($assignment->my_submission && $assignment->my_submission->marks_obtained !== null)
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-violet-50 text-violet-700">
                                    {{ $assignment->my_submission->marks_obtained }} marks
                                </span>
                            @endif
                            
                            <a href="{{ route('student.assignments.show', $assignment->id) }}" 
                               class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 hover:bg-blue-100">
                                View
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-5 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No assignments found</p>
                </div>
            @endforelse
        </div>

        @if($assignments->hasPages())
            <div class="border-t border-slate-100 dark:border-[#1e3a5f] px-5 py-4">
                {{ $assignments->appends(['semester' => $selectedSemester, 'subject_id' => request('subject_id'), 'status' => request('status')])->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
