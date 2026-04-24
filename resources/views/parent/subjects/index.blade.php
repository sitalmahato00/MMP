@extends('layouts.app')

@section('title', 'Children Subjects')

@section('content')
<div class="space-y-6">
    <section class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Parent Portal</p>
                    <h1 class="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl lg:text-3xl">Children Subjects</h1>
                    <p class="mt-1 text-sm text-slate-600">Review subject details, syllabus files, and assigned teachers for each child.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        {{ $session?->name ?? 'No active session' }}
                    </span>
                    <span class="text-xs text-slate-500">
                        {{ $childrenSubjects->count() }} child{{ $childrenSubjects->count() === 1 ? '' : 'ren' }}
                    </span>
                </div>
            </div>
        </div>
    </section>

    @if($childrenSubjects->isEmpty())
        <section class="rounded-xl border border-slate-200/80 bg-white p-10 text-center shadow-sm">
            <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="mt-3 text-sm text-slate-500">No children are linked to this parent account.</p>
        </section>
    @else
        @foreach($childrenSubjects as $childData)
            @php
                $student = $childData['student'];
                $subjectOverview = $childData['subjectOverview'];
                $studiedSemesters = collect($subjectOverview['completed'] ?? [])
                    ->concat(collect($subjectOverview['running'] ?? []))
                    ->sortByDesc('semester')
                    ->values();
                $studiedSubjectsCount = (int) data_get($subjectOverview, 'counts.completed', 0)
                    + (int) data_get($subjectOverview, 'counts.running', 0);
                $program = $student->program;
            @endphp

            <div class="space-y-4">
                <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 text-lg font-bold text-blue-700">
                                    {{ strtoupper(substr($student->user?->name ?? 'S', 0, 1)) }}
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-slate-900">{{ $student->user?->name ?? 'Student' }}</h2>
                                    <p class="mt-1 text-sm text-slate-600">
                                        {{ $program?->name ?? 'No Program' }} - Semester {{ $student->current_semester ?? 'N/A' }}
                                        @if($student->section)
                                            - Section {{ $student->section }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600">
                                    {{ number_format($studiedSubjectsCount) }} subjects studied
                                </span>
                                <span class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700">
                                    Semester {{ $student->current_semester ?? 'N/A' }}
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
                    'subheading' => 'Open a semester, then a subject, to review its details, subject syllabus, and assigned teachers.',
                ])
            </div>
        @endforeach
    @endif
</div>
@endsection
