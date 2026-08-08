@extends('layouts.app')

@section('title', 'My Marks & Results')

@section('content')
<div class="space-y-6">
    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] p-4 shadow-sm">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ number_format($totalAssessments) }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Published Assessments</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] p-4 shadow-sm">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/30">
                <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline gap-1">
                    <span class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ number_format($averagePercentage, 1) }}</span>
                    <span class="text-sm font-medium text-slate-400 dark:text-slate-500">%</span>
                </div>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Percentage Rate</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] p-4 shadow-sm">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-50 dark:bg-violet-900/30">
                <svg class="h-4 w-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ number_format($totalSubjects) }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Subjects</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] p-4 shadow-sm">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30">
                <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline gap-1">
                    <span class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ number_format($passPercentage, 1) }}</span>
                    <span class="text-sm font-medium text-slate-400 dark:text-slate-500">%</span>
                </div>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Pass Rate</p>
            </div>
        </div>
    </section>

    <section class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] p-4 shadow-sm">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <input type="hidden" name="semester" value="{{ $selectedSemester ?? $currentSemester }}">

            {{-- Semester dropdown inline --}}
            <div class="min-w-[130px]">
                <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">Semester</label>
                <select name="semester" onchange="this.form.submit()"
                    class="w-full rounded-lg border border-slate-300 dark:border-[#2d4a70] bg-white dark:bg-[#1a2f50] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500">
                    @foreach($semesterOptions as $sem)
                        <option value="{{ $sem }}" {{ ($selectedSemester ?? $currentSemester) == $sem ? 'selected' : '' }}>
                            Semester {{ $sem }}{{ $sem == $currentSemester ? ' (Current)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-[150px]">
                <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">Exam Type</label>
                <select name="exam_type" class="w-full rounded-lg border border-slate-300 dark:border-[#2d4a70] bg-white dark:bg-[#1a2f50] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Types</option>
                    <option value="regular" {{ request('exam_type') == 'regular' ? 'selected' : '' }}>Regular Semester Exam</option>
                    <option value="back" {{ request('exam_type') == 'back' ? 'selected' : '' }}>Back / Partial Exam</option>
                    <option value="internal" {{ request('exam_type') == 'internal' ? 'selected' : '' }}>Internal / Monthly Test</option>
                    <option value="practical" {{ request('exam_type') == 'practical' ? 'selected' : '' }}>Practical Exam</option>
                </select>
            </div>

            <div class="min-w-[150px]">
                <label class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">Category</label>
                <select name="category" class="w-full rounded-lg border border-slate-300 dark:border-[#2d4a70] bg-white dark:bg-[#1a2f50] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Categories</option>
                    <option value="monthly_assessment" {{ request('category') == 'monthly_assessment' ? 'selected' : '' }}>Monthly Test / Assessment</option>
                    <option value="ctevt_final" {{ request('category') == 'ctevt_final' ? 'selected' : '' }}>CTEVT Final</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Filter
                </button>
                <a href="{{ route('student.marks.index') }}" class="rounded-lg border border-slate-300 dark:border-[#2d4a70] px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#1e3a5f]">
                    Clear
                </a>
            </div>
        </form>
    </section>

    <section class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] shadow-sm">
        <div class="border-b border-slate-100 dark:border-[#1e3a5f] px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Exam Results</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Your published assessments with marksheet access</p>
        </div>

        <div class="divide-y divide-slate-200 dark:divide-[#1e3a5f]">
            @forelse($assessmentResults as $result)
                <div class="p-6 transition-colors hover:bg-slate-50 dark:hover:bg-[#1e3a5f]">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-3">
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $result['exam']->name }}</h3>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $result['passed'] ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $result['passed'] ? 'Passed' : 'Failed' }}
                                </span>
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-slate-600 dark:text-slate-400">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar text-slate-400"></i>
                                    <span>{{ bsDate($result['exam']->published_at ?? $result['exam']->created_at) }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-tag text-slate-400"></i>
                                    <span>{{ $result['exam']->category_label }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-book text-slate-400"></i>
                                    <span>{{ $result['marks_count'] }} Subject{{ $result['marks_count'] === 1 ? '' : 's' }}</span>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap items-center gap-6">
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Total Marks</p>
                                    <p class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ number_format($result['total_obtained'], 2) }} / {{ number_format($result['total_full'], 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Percentage Rate</p>
                                    <p class="text-lg font-bold text-blue-600">{{ number_format($result['percentage'], 1) }}%</p>
                                </div>
                            </div>
                        </div>

                        <div class="lg:ml-6">
                            <a href="{{ route('student.marks.show', $result['exam']->id) }}"
                               class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                                <i class="fas fa-eye mr-2"></i>
                                View Marksheet
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-5 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No exam results available yet</p>
                </div>
            @endforelse
        </div>

        @if($assessmentResults->isNotEmpty())
            <div class="border-t border-slate-100 dark:border-[#1e3a5f] px-5 py-4 text-xs text-slate-500 dark:text-slate-400">
                Showing {{ $assessmentResults->count() }} published assessment{{ $assessmentResults->count() === 1 ? '' : 's' }}.
            </div>
        @endif
    </section>
</div>
@endsection
