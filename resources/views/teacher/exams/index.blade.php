@extends('layouts.app')

@section('title', 'Exams & Marks')

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
                        Exams & Marks
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">View exam schedules and manage marks</p>
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

    {{-- Exams Table (HOD-style, restricted actions) --}}
    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">Assigned Exams</h2>
            <p class="text-xs text-slate-500">Exam schedules and mark entry for your assigned subjects</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 text-xs font-medium text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Exam Name</th>
                        <th class="px-5 py-3 text-left">Type</th>
                        <th class="px-5 py-3 text-left">Schedule</th>
                        <!-- Removed Subjects column -->
                        <th class="px-5 py-3 text-center">Semester</th>
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
                                <div class="text-xs text-slate-500">{{ $exam->category_label ?? str_replace('_', ' ', $exam->category) }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm text-slate-900">{{ bsDate($exam->start_date, 'F d, Y') }}</div>
                                @if($exam->end_date && $exam->end_date != $exam->start_date)
                                    <div class="text-xs text-slate-500">to {{ bsDate($exam->end_date, 'F d, Y') }}</div>
                                @endif
                            </td>
                            <!-- Removed Subjects column cell -->
                            <td class="px-5 py-4 text-center">
                                @php
                                    $semesters = $exam->subjects->pluck('pivot.semester')->filter()->unique()->sort()->values();
                                @endphp
                                @if($semesters->count() > 0)
                                    <span class="text-sm font-medium text-slate-900">{{ $semesters->implode(', ') }}</span>
                                @else
                                    <span class="text-sm font-medium text-slate-500">All</span>
                                @endif
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
                                    {{ $exam->status_label ?? ucfirst($exam->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $assignedSubject = $exam->subjects->first(function($subject) { return $subject->is_assigned_to_teacher; });
                                @endphp
                                @if($assignedSubject && !in_array($exam->status, ['published', 'completed']))
                                    <a href="{{ route('teacher.exams.fill-marks', ['exam' => $exam->id, 'subject_id' => $assignedSubject->id]) }}"
                                       class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700 hover:bg-emerald-100 transition-colors"
                                       title="Fill Marks">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                        Fill
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
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

    {{-- Pagination --}}
    @if($exams->hasPages())
        <div class="flex justify-center">
            {{ $exams->links() }}
        </div>
    @endif
</div>
@endsection
