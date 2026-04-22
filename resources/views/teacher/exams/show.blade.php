@extends('layouts.app')

@section('title', $exam->name)

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl lg:rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-rose-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Exam Details</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        {{ $exam->name }}
                    </h1>
                </div>
                <a href="{{ route('teacher.exams.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back
                </a>
            </div>
        </div>
    </section>

    {{-- Stats --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat-card 
            title="Total Marks" 
            value="{{ $totalMarks }}" 
            icon="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            tone="blue"
        />
        <x-stat-card 
            title="Submitted" 
            value="{{ $submittedMarks }}" 
            icon="M5 13l4 4L19 7"
            tone="amber"
        />
        <x-stat-card 
            title="Published" 
            value="{{ $publishedMarks }}" 
            icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            tone="emerald"
        />
    </div>

    {{-- Exam Info --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase">Category</p>
            <p class="mt-2 text-lg font-semibold text-slate-900 capitalize">{{ str_replace('_', ' ', $exam->category) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase">Status</p>
            <p class="mt-2 text-lg font-semibold text-slate-900 capitalize">{{ $exam->status }}</p>
        </div>
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase">Start Date</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">{{ bsDate($exam->start_date, 'M d, Y') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase">End Date</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">{{ bsDate($exam->end_date, 'M d, Y') }}</p>
        </div>
    </div>

    {{-- Marks Table --}}
    <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 px-4 py-3 sm:px-6">
            <h2 class="text-sm font-semibold text-slate-900">Student Marks</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Student</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-700">Subject</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-700">Theory</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-700">Practical</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-700">Total</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-700">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($marks as $mark)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $mark->student->user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($mark->student->user->name) }}" 
                                        alt="{{ $mark->student->user->name }}" class="h-8 w-8 rounded-full">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $mark->student->user->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $mark->student->student_no }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <p class="font-semibold text-slate-900">{{ $mark->subject->name }}</p>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-sm font-semibold text-slate-900">
                                    {{ $mark->internal_theory_marks ?? 0 }} + {{ $mark->external_theory_marks ?? 0 }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-sm font-semibold text-slate-900">
                                    {{ $mark->internal_practical_marks ?? 0 }} + {{ $mark->external_practical_marks ?? 0 }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700">
                                    {{ $mark->total_marks }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $statusColors = [
                                        'draft' => 'bg-slate-50 text-slate-700',
                                        'submitted' => 'bg-amber-50 text-amber-700',
                                        'approved' => 'bg-blue-50 text-blue-700',
                                        'published' => 'bg-emerald-50 text-emerald-700',
                                    ];
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusColors[$mark->status] ?? 'bg-slate-50 text-slate-700' }} capitalize">
                                    {{ $mark->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <p class="text-sm text-slate-500">No marks recorded</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($marks->hasPages())
        <div class="flex justify-center">
            {{ $marks->links() }}
        </div>
    @endif
</div>
@endsection
