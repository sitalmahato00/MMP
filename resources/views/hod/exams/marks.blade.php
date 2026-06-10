@extends('layouts.app')

@section('title', 'Exam Marks - ' . $exam->name)

@section('content')
<x-page-header 
    :title="$exam->name" 
    :subtitle="$exam->category_label . ' • ' . bsDate($exam->start_date, 'F d, Y')"
    back="{{ route('hod.exams.index') }}"/>

{{-- KPI Cards --}}
<section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50">
                <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
        <div class="mt-3">
            <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($totalMarks) }}</span>
            <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Total Marks</p>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50">
                <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="mt-3">
            <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($pendingMarks) }}</span>
            <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Pending</p>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-50">
                <svg class="h-4 w-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>
        <div class="mt-3">
            <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($submittedMarks) }}</span>
            <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Submitted</p>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50">
                <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="mt-3">
            <span class="text-2xl font-bold tracking-tight text-slate-900">{{ number_format($approvedMarks) }}</span>
            <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Approved</p>
        </div>
    </div>
</section>

{{-- Filters --}}
<section class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm mb-6">
    <form method="GET" action="{{ route('hod.exams.marks') }}" class="flex flex-col gap-3 lg:gap-4">
        <input type="hidden" name="exam_id" value="{{ $exam->id }}">

        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:flex-wrap">
            <div class="min-w-0 flex-1 lg:max-w-[28rem]">
                <label class="relative block w-full">
                    <span class="sr-only">Search marks</span>
                    <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400"><i class="fas fa-search"></i></span>
                    <x-input
                        name="search"
                        :value="request('search')"
                        placeholder="Search students, subjects, programs..."
                        class="w-full rounded-full border border-slate-200 bg-slate-50 py-2.5 pl-11 pr-4 text-sm text-slate-900 shadow-sm transition focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20"
                    />
                </label>
            </div>

            <div class="grid w-full gap-3 sm:grid-cols-2 lg:grid-cols-5 lg:flex-1">
                <label class="block text-sm font-semibold text-slate-700">
                    <span class="sr-only">Program</span>
                    <x-select name="program_id" class="w-full rounded-full border border-slate-200 bg-slate-50 py-2 px-3 text-sm text-slate-900 shadow-sm transition focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20">
                        <option value="">All Programs</option>
                        @foreach($programs as $prog)
                            <option value="{{ $prog->id }}" @selected(request('program_id') == $prog->id)>
                                {{ $prog->name }}
                            </option>
                        @endforeach
                    </x-select>
                </label>

                <label class="block text-sm font-semibold text-slate-700">
                    <span class="sr-only">Semester</span>
                    <x-select name="semester" class="w-full rounded-full border border-slate-200 bg-slate-50 py-2 px-3 text-sm text-slate-900 shadow-sm transition focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20">
                        <option value="">All Semesters</option>
                        @foreach($semesters as $sem)
                            <option value="{{ $sem }}" @selected(request('semester') == $sem)>
                                Semester {{ $sem }}
                            </option>
                        @endforeach
                    </x-select>
                </label>

                <label class="block text-sm font-semibold text-slate-700">
                    <span class="sr-only">Subject</span>
                    <x-select name="subject_id" class="w-full rounded-full border border-slate-200 bg-slate-50 py-2 px-3 text-sm text-slate-900 shadow-sm transition focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20">
                        <option value="">All Subjects</option>
                        @foreach($subjects as $subj)
                            <option value="{{ $subj->id }}" @selected(request('subject_id') == $subj->id)>
                                {{ $subj->name }} (Sem {{ $subj->semester }})
                            </option>
                        @endforeach
                    </x-select>
                </label>

                <label class="block text-sm font-semibold text-slate-700">
                    <span class="sr-only">Status</span>
                    <x-select name="status" class="w-full rounded-full border border-slate-200 bg-slate-50 py-2 px-3 text-sm text-slate-900 shadow-sm transition focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20">
                        <option value="">All Status</option>
                        <option value="draft" @selected(request('status') == 'draft')>Draft</option>
                        <option value="submitted" @selected(request('status') == 'submitted')>Submitted</option>
                        <option value="approved" @selected(request('status') == 'approved')>Approved</option>
                        <option value="published" @selected(request('status') == 'published')>Published</option>
                    </x-select>
                </label>

                <div class="flex items-center gap-3">
                    <x-btn type="submit" class="rounded-full px-4 py-2">Apply</x-btn>
                    @if(request()->hasAny(['search', 'program_id', 'subject_id', 'semester', 'status']))
                        <a href="{{ route('hod.exams.marks', ['exam_id' => $exam->id]) }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">Clear</a>
                    @endif
                </div>
            </div>
        </div>
    </form>
</section>

    {{-- Grouped Marks: Semester → Subject → Students --}}
    @if(request()->hasAny(['search', 'program_id', 'semester', 'subject_id', 'status']))
        <div class="mb-3 flex items-center gap-2 text-sm text-slate-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
            Filters active —
            <a href="{{ route('hod.exams.marks', ['exam_id' => $exam->id]) }}" class="text-blue-600 hover:underline">Clear all</a>
        </div>
    @endif

    @forelse($groupedMarks as $semester => $subjectGroups)
        @foreach($subjectGroups as $subjectId => $subjectMarks)
            @php $subject = $subjectMarks->first()->subject; @endphp
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-slate-50 border-b border-slate-100 px-5 py-4">
                    <div class="min-w-0">
                        <h2 class="text-sm font-bold text-slate-900 leading-tight">{{ $subject?->name ?? 'N/A' }} <span class="text-xs font-mono text-slate-400">{{ $subject?->code }}</span></h2>
                        <p class="mt-1 text-xs text-slate-500">Semester {{ $semester }} · {{ $subjectMarks->count() }} {{ Str::plural('student', $subjectMarks->count()) }}</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                        <span class="font-medium text-slate-700">{{ $subjectMarks->where('status', 'approved')->count() }} approved</span>
                        <span class="font-medium text-blue-600">{{ $subjectMarks->where('status', 'submitted')->count() }} submitted</span>
                        <span class="text-slate-400">{{ $subjectMarks->where('status', 'draft')->count() }} draft</span>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <a href="{{ route('hod.exams.subjects.marks', ['exam' => $exam, 'subject' => $subjectId]) }}"
                           title="View sheet — {{ $subject?->name }}"
                           class="inline-flex items-center gap-1.5 rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                            View sheet
                        </a>
                        <a href="{{ route('hod.exams.export-marks', array_filter(['exam_id' => $exam->id, 'subject_id' => $subjectId, 'semester' => $semester, 'format' => 'excel'])) }}"
                           title="Export Excel — {{ $subject?->name }}"
                           class="inline-flex items-center gap-1.5 rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 transition-colors">
                            Excel
                        </a>
                        <a href="{{ route('hod.exams.export-marks', array_filter(['exam_id' => $exam->id, 'subject_id' => $subjectId, 'semester' => $semester, 'format' => 'pdf'])) }}"
                           title="Export PDF — {{ $subject?->name }}"
                           class="inline-flex items-center gap-1.5 rounded-md bg-red-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-800 transition-colors">
                            PDF
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    @empty
        <div class="rounded-xl border border-slate-200 bg-white p-12 text-center shadow-sm">
            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="mt-3 text-sm font-medium text-slate-600">No marks found</p>
            <p class="mt-1 text-xs text-slate-400">Try adjusting your filters</p>
        </div>
    @endforelse

@endsection
