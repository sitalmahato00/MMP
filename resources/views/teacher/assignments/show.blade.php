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
                <div class="flex gap-2 flex-wrap">
                    <a href="{{ route('teacher.assignments.edit', $assignment) }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </a>
                    <a href="{{ route('teacher.assignments.index') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Back
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Flash --}}
    @if(session('success'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700 font-medium">
            ✓ {{ session('success') }}
        </div>
    @endif

    {{-- KPI Cards --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase">Subject</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">{{ $assignment->subject->name }}</p>
            <p class="text-xs text-slate-500">{{ $assignment->subject->code }}</p>
        </div>
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase">Due Date</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">{{ bsDate($assignment->due_date, 'Y-m-d') }}</p>
            @php $isOverdue = $assignment->due_date < now(); @endphp
            <p class="text-xs {{ $isOverdue ? 'text-rose-600' : 'text-emerald-600' }}">
                {{ $isOverdue ? 'Overdue' : 'Upcoming' }}
            </p>
        </div>
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase">Submissions</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">{{ $assignment->submissions->count() }}</p>
            <div class="flex gap-3 mt-1">
                @php
                    $graded   = $assignment->submissions->where('status', 'graded')->count();
                    $pending  = $assignment->submissions->where('status', '!=', 'graded')->count();
                @endphp
                <span class="text-xs text-emerald-600">{{ $graded }} graded</span>
                @if($pending > 0)
                    <span class="text-xs text-amber-600">{{ $pending }} pending</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Description --}}
    @if($assignment->description)
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-900 mb-3">Description</h2>
            <p class="text-sm text-slate-600 whitespace-pre-wrap">{{ $assignment->description }}</p>
        </div>
    @endif

    {{-- Assignment Attachment --}}
    @if($assignment->attachment)
        @php
            $ext = strtolower(pathinfo($assignment->attachment, PATHINFO_EXTENSION));
            $imageExts = ['jpg','jpeg','png','gif','webp'];
            $fileUrl = asset('storage/' . ltrim($assignment->attachment, '/'));
        @endphp
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-900 mb-3">Assignment Attachment</h2>
            @if(in_array($ext, $imageExts))
                <div class="mb-3">
                    <img src="{{ $fileUrl }}" alt="Assignment attachment"
                        class="max-h-64 rounded-lg border border-slate-200 object-contain cursor-pointer"
                        onclick="window.open('{{ $fileUrl }}', '_blank')">
                </div>
            @endif
            <a href="{{ $fileUrl }}" target="_blank"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-600 transition hover:bg-blue-100">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ in_array($ext, $imageExts) ? 'Open Full Image' : 'Download File (.' . strtoupper($ext) . ')' }}
            </a>
        </div>
    @endif

    {{-- Student Submissions --}}
    <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 sm:px-6">
            <h2 class="text-sm font-semibold text-slate-900">
                Student Submissions
                <span class="ml-2 inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700">
                    {{ $assignment->submissions->count() }}
                </span>
            </h2>
        </div>

        @forelse($assignment->submissions as $submission)
            @php
                $subExt      = $submission->attachment ? strtolower(pathinfo($submission->attachment, PATHINFO_EXTENSION)) : null;
                $isImage     = $subExt && in_array($subExt, ['jpg','jpeg','png','gif','webp']);
                $subFileUrl  = $submission->attachment ? asset('storage/' . ltrim($submission->attachment, '/')) : null;
                $statusColor = match($submission->status) {
                    'graded'    => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'late'      => 'bg-orange-50 text-orange-700 border-orange-200',
                    default     => 'bg-blue-50 text-blue-700 border-blue-200',
                };
                $statusLabel = match($submission->status) {
                    'graded'    => 'Graded',
                    'late'      => 'Late',
                    'submitted' => 'Submitted',
                    default     => ucfirst($submission->status),
                };
            @endphp

            <div class="border-b border-slate-100 last:border-0 p-4 sm:p-6 {{ $loop->even ? 'bg-slate-50/40' : '' }}">
                {{-- Student Header Row --}}
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
                    <div class="flex items-center gap-3">
                        <img src="{{ $submission->student->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($submission->student->user->name) . '&background=6366f1&color=fff' }}"
                            alt="{{ $submission->student->user->name }}"
                            class="h-10 w-10 rounded-full border border-slate-200 object-cover flex-shrink-0">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $submission->student->user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $submission->student->student_no }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $statusColor }}">
                            {{ $statusLabel }}
                        </span>
                        <span class="text-xs text-slate-400">
                            {{ bsDate($submission->created_at, 'M d, Y h:i A') }}
                        </span>
                    </div>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    {{-- Left: Student note + attachment --}}
                    <div class="space-y-3">

                        {{-- Student Note --}}
                        @if($submission->student_note)
                            <div class="rounded-lg bg-slate-50 border border-slate-200 p-3">
                                <p class="text-xs font-semibold text-slate-500 uppercase mb-1.5">Student's Note</p>
                                <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ $submission->student_note }}</p>
                            </div>
                        @else
                            <p class="text-xs text-slate-400 italic">No note submitted</p>
                        @endif

                        {{-- Student Attachment --}}
                        @if($subFileUrl)
                            <div class="rounded-lg bg-slate-50 border border-slate-200 p-3">
                                <p class="text-xs font-semibold text-slate-500 uppercase mb-2">Submitted File</p>
                                @if($isImage)
                                    <img src="{{ $subFileUrl }}" alt="Student submission"
                                        class="max-h-48 w-full rounded-lg border border-slate-200 object-contain mb-2 cursor-pointer"
                                        onclick="window.open('{{ $subFileUrl }}', '_blank')">
                                @endif
                                <a href="{{ $subFileUrl }}" target="_blank"
                                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-600 transition hover:bg-indigo-100">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    {{ $isImage ? 'View Image' : 'Download ' . strtoupper($subExt) . ' File' }}
                                </a>
                            </div>
                        @else
                            <p class="text-xs text-slate-400 italic">No file attached</p>
                        @endif
                    </div>

                    {{-- Right: Marks & Grade Form --}}
                    <div>
                        @if($submission->status === 'graded')
                            {{-- Already Graded --}}
                            <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-3 mb-3">
                                <p class="text-xs font-semibold text-emerald-700 uppercase mb-1.5">Grade</p>
                                <p class="text-2xl font-bold text-emerald-700">
                                    {{ $submission->marks_obtained }}
                                    @if($assignment->max_marks)
                                        <span class="text-sm font-normal text-emerald-600">/ {{ $assignment->max_marks }}</span>
                                    @endif
                                </p>
                            </div>
                            @if($submission->teacher_feedback)
                                <div class="rounded-lg bg-amber-50 border border-amber-200 p-3 mb-3">
                                    <p class="text-xs font-semibold text-amber-700 uppercase mb-1.5">Your Feedback</p>
                                    <p class="text-sm text-amber-800 whitespace-pre-wrap">{{ $submission->teacher_feedback }}</p>
                                </div>
                            @endif
                            {{-- Re-grade button --}}
                            <button onclick="document.getElementById('grade-form-{{ $submission->id }}').classList.toggle('hidden')"
                                class="text-xs text-slate-500 underline hover:text-slate-700">
                                Edit Grade
                            </button>
                        @endif

                        {{-- Grade Form (hidden if already graded, visible if not) --}}
                        <form id="grade-form-{{ $submission->id }}"
                            action="{{ route('teacher.assignments.submissions.grade', [$assignment, $submission]) }}"
                            method="POST"
                            class="{{ $submission->status === 'graded' ? 'hidden' : '' }} mt-3">
                            @csrf
                            <div class="rounded-lg border border-slate-200 bg-white p-3 space-y-3">
                                <p class="text-xs font-semibold text-slate-700 uppercase">
                                    {{ $submission->status === 'graded' ? 'Edit Grade' : 'Grade This Submission' }}
                                </p>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">
                                        Marks Obtained
                                        @if($assignment->max_marks)
                                            <span class="text-slate-400">(out of {{ $assignment->max_marks }})</span>
                                        @endif
                                    </label>
                                    <input type="number"
                                        name="marks_obtained"
                                        value="{{ old('marks_obtained', $submission->marks_obtained) }}"
                                        min="0"
                                        max="{{ $assignment->max_marks ?? '' }}"
                                        step="0.5"
                                        required
                                        placeholder="e.g. 18"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Feedback (optional)</label>
                                    <textarea name="teacher_feedback"
                                        rows="2"
                                        placeholder="Feedback for the student..."
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">{{ old('teacher_feedback', $submission->teacher_feedback) }}</textarea>
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit"
                                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        {{ $submission->status === 'graded' ? 'Update Grade' : 'Save Grade' }}
                                    </button>
                                    @if($submission->status === 'graded')
                                        <button type="button"
                                            onclick="document.getElementById('grade-form-{{ $submission->id }}').classList.add('hidden')"
                                            class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                            Cancel
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        @empty
            <div class="px-4 py-16 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="mt-3 text-sm text-slate-500">No submissions yet</p>
                <p class="text-xs text-slate-400 mt-1">Students haven't submitted this assignment yet</p>
            </div>
        @endforelse
    </div>

</div>
@endsection
