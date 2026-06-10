@extends('layouts.app')

@section('title', 'Fill Marks - Select Subject')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <x-page-header 
        title="Fill Marks - {{ $exam->name }}" 
        subtitle="Select program and subject to fill marks"
        back="{{ route('teacher.exams.index') }}" />

    <form method="GET" action="{{ route('teacher.exams.fill-marks') }}" class="max-w-4xl space-y-6">
        <input type="hidden" name="exam_id" value="{{ $exam->id }}">

        {{-- ── 1. PROGRAM & SEMESTER SELECTION ──────────────── --}}
        <x-form-section title="Program & Semester" subtitle="Select the program and semester to fill marks for.">
            <x-form-row>
                <x-form-field label="Program" name="program_id" :required="true">
                    <x-select name="program_id" :required="true" onchange="this.form.submit()">
                        <option value="">Select Program</option>
                        @foreach($programs as $prog)
                            <option value="{{ $prog->id }}" @selected($programId == $prog->id)>
                                {{ $prog->name }} (Semester {{ $prog->pivot->semester }})
                            </option>
                        @endforeach
                    </x-select>
                </x-form-field>

                <x-form-field label="Semester" name="semester" :required="true">
                    <x-select name="semester" :required="true" onchange="this.form.submit()">
                        @foreach($semesters as $sem)
                            <option value="{{ $sem }}" @selected($semester == $sem)>
                                Semester {{ $sem }}
                            </option>
                        @endforeach
                    </x-select>
                </x-form-field>
            </x-form-row>
        </x-form-section>

        @if($programId && $subjects->isNotEmpty())
            {{-- ── 2. SUBJECT SELECTION ─────────────────────────── --}}
            <x-form-section title="Select Subject" subtitle="Choose the subject to fill marks for (only your assigned subjects are shown).">
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($subjects as $subject)
                        <button type="submit" name="subject_id" value="{{ $subject->id }}"
                                class="flex items-start gap-3 rounded-lg border border-slate-200 p-4 text-left transition hover:border-blue-300 hover:bg-blue-50/50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600 flex-shrink-0">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-900">{{ $subject->name }}</p>
                                <p class="text-xs text-slate-500">{{ $subject->code }} • {{ ucfirst($subject->type) }}</p>
                            </div>
                        </button>
                    @endforeach
                </div>
            </x-form-section>
        @elseif($programId)
            <x-alert type="warning" title="No subjects found">
                No subjects are assigned to you for the selected program and semester.
            </x-alert>
        @endif
    </form>
</div>
@endsection