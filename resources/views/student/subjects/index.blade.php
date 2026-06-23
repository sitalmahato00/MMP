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
    @include('partials.portal-subject-semesters', [
        'studiedSemesters' => $studiedSemesters,
        'currentSemester' => $student->current_semester,
        'program' => $program,
        'heading' => 'Studied Subjects',
        'subheading' => 'Use View Subjects on a semester, then View Details on a subject to open the full detail tree.',
    ])
</div>
@endsection
