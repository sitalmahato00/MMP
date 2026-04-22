@extends('layouts.app')

@section('title', $assignment->title)

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl lg:rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-cyan-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Assignment</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        {{ $assignment->title }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">{{ $assignment->subject->name }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('teacher.assignments.edit', $assignment) }}" class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </a>
                    <a href="{{ route('teacher.assignments.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Back
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Assignment Info --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase">Subject</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">{{ $assignment->subject->name }}</p>
            <p class="text-xs text-slate-500">{{ $assignment->subject->code }}</p>
        </div>
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase">Due Date</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">{{ bsDate($assignment->due_date, 'M d, Y') }}</p>
            @php
                $isOverdue = $assignment->due_date < now();
            @endphp
            <p class="text-xs {{ $isOverdue ? 'text-rose-600' : 'text-emerald-600' }}">
                {{ $isOverdue ? 'Overdue' : 'Upcoming' }}
            </p>
        </div>
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase">Submissions</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">{{ $assignment->submissions->count() }}</p>
            <p class="text-xs text-slate-500">students submitted</p>
        </div>
    </div>

    {{-- Description --}}
    @if($assignment->description)
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-900 mb-3">Description</h2>
            <p class="text-sm text-slate-600 whitespace-pre-wrap">{{ $assignment->description }}</p>
        </div>
    @endif

    {{-- Attachment --}}
    @if($assignment->attachment)
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-900 mb-3">Attachment</h2>
            <a href="{{ Storage::url($assignment->attachment) }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-600 transition hover:bg-blue-100">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download File
            </a>
        </div>
    @endif

    {{-- Submissions --}}
    <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3 sm:px-6">
            <h2 class="text-sm font-semibold text-slate-900">Student Submissions</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Student</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-700">Submitted</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Submission Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($assignment->submissions as $submission)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $submission->student->user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($submission->student->user->name) }}" 
                                        alt="{{ $submission->student->user->name }}" class="h-8 w-8 rounded-full">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $submission->student->user->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $submission->student->student_no }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                    Yes
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ bsDate($submission->created_at, 'M d, Y h:i A') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-12 text-center">
                                <p class="text-sm text-slate-500">No submissions yet</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
