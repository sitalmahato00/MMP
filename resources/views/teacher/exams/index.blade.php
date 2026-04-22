@extends('layouts.app')

@section('title', 'Exams')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl lg:rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-rose-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Teacher</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        Exams
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">View exam schedules and results</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card 
            title="Total Exams" 
            value="{{ $totalExams }}" 
            icon="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            tone="blue"
        />
        <x-stat-card 
            title="Upcoming" 
            value="{{ $upcomingExams }}" 
            icon="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
            tone="amber"
        />
        <x-stat-card 
            title="Ongoing" 
            value="{{ $ongoingExams }}" 
            icon="M13 10V3L4 14h7v7l9-11h-7z"
            tone="rose"
        />
        <x-stat-card 
            title="Completed" 
            value="{{ $completedExams }}" 
            icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            tone="emerald"
        />
    </div>

    {{-- Filters --}}
    <x-filter-bar action="{{ route('teacher.exams.index') }}">
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Exam name..." 
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Status</label>
            <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Category</label>
            <select name="category" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                <option value="">All Categories</option>
                <option value="monthly_assessment" {{ request('category') == 'monthly_assessment' ? 'selected' : '' }}>Monthly Assessment</option>
                <option value="midterm" {{ request('category') == 'midterm' ? 'selected' : '' }}>Midterm</option>
                <option value="final" {{ request('category') == 'final' ? 'selected' : '' }}>Final</option>
            </select>
        </div>
    </x-filter-bar>

    {{-- Exams Table --}}
    <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Exam Name</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Category</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-700">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Date</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($exams as $exam)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $exam->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $exam->subjects->pluck('name')->join(', ') }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700 capitalize">
                                    {{ str_replace('_', ' ', $exam->category) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $statusColors = [
                                        'upcoming' => 'bg-amber-50 text-amber-700',
                                        'ongoing' => 'bg-rose-50 text-rose-700',
                                        'completed' => 'bg-emerald-50 text-emerald-700',
                                    ];
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusColors[$exam->status] ?? 'bg-slate-50 text-slate-700' }} capitalize">
                                    {{ $exam->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ bsDate($exam->start_date, 'M d, Y') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('teacher.exams.show', $exam) }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-100">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="mt-2 text-sm text-slate-500">No exams available</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($exams->hasPages())
        <div class="flex justify-center">
            {{ $exams->links() }}
        </div>
    @endif
</div>
@endsection
