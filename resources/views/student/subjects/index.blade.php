@extends('layouts.app')

@section('title', 'My Subjects')

@php
    $studiedSemesters = collect($subjectOverview['completed'] ?? [])
        ->concat(collect($subjectOverview['running'] ?? []))
        ->sortByDesc('semester')
        ->values();

    $studiedSubjectsCount = (int) data_get($subjectOverview, 'counts.completed', 0)
        + (int) data_get($subjectOverview, 'counts.running', 0);

    $program = $student->program;
@endphp

@section('content')
<div class="space-y-6">
    <section class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Student Subjects</p>
                    <h1 class="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl lg:text-3xl">My Subjects</h1>
                    <p class="mt-1 text-sm text-slate-600">
                        {{ $program?->name ?? 'No Program' }} - Semester {{ $student->current_semester ?? 'N/A' }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        {{ $session?->name ?? 'No active session' }}
                    </span>
                    <span class="text-xs text-slate-500">
                        {{ number_format($studiedSubjectsCount) }} subjects from Semester 1 to Semester {{ $student->current_semester ?? 'N/A' }}
                    </span>
                </div>
            </div>
        </div>
    </section>

    @include('partials.portal-subject-semesters', [
        'studiedSemesters' => $studiedSemesters,
        'currentSemester' => $student->current_semester,
        'program' => $program,
        'heading' => 'Studied Subjects',
        'subheading' => 'Use View Subjects on a semester, then View Details on a subject to open the full detail tree.',
    ])
</div>
@endsection
