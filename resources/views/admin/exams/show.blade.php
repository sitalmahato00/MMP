@extends('layouts.app')
@section('title', $exam->name)

@section('content')
@php
    $typeLabel = match ($exam->type) {
        'regular' => 'Regular Semester Exam',
        'back' => 'Back / Partial Exam',
        'internal' => 'Internal / Monthly Test',
        'practical' => 'Practical Exam',
        default => ucfirst($exam->type),
    };

    $statusClasses = [
        'green' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'orange' => 'bg-orange-50 text-orange-700 ring-orange-200',
        'yellow' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'purple' => 'bg-violet-50 text-violet-700 ring-violet-200',
        'blue' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'slate' => 'bg-slate-100 text-slate-700 ring-slate-200',
    ];

    $gradeColors = [
        'A' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'B' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'C' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'Fail' => 'bg-rose-50 text-[#8B0000] ring-rose-200',
    ];

    $semesterLabels = $exam->programs->pluck('pivot.semester')->filter()->unique()->sort()->map(fn ($semester) => 'Sem ' . $semester)->implode(' · ') ?: '—';
    $programSummaries = $exam->programs
        ->groupBy('id')
        ->map(function ($programAssignments) {
            $program = $programAssignments->first();
            $semesters = $programAssignments
                ->pluck('pivot.semester')
                ->filter()
                ->map(fn ($semester) => 'Sem ' . $semester)
                ->unique()
                ->sort()
                ->implode(', ');

            return [
                'name' => trim(($program->code ? $program->code . ' - ' : '') . $program->name),
                'semesters' => $semesters,
            ];
        })
        ->values();
    $departmentLabel = $exam->department?->code ? $exam->department->code . ' - ' . $exam->department->name : ($exam->department?->name ?? 'Common');
    $uploadedMarksTotal = $uploadedMarkGroups->sum('marks_count');

    $statusTone = $statusClasses[$exam->status_tone] ?? $statusClasses['slate'];
@endphp

<div class="space-y-6">
    <section class="relative overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_24px_70px_rgba(15,23,42,0.07)]">
        <div class="absolute inset-0 bg-gradient-to-br from-rose-50 via-white to-sky-50/50"></div>
        <div class="absolute -right-20 -top-20 h-52 w-52 rounded-full bg-rose-200/30 blur-3xl"></div>
        <div class="absolute -left-20 bottom-0 h-52 w-52 rounded-full bg-sky-200/30 blur-3xl"></div>

        <div class="relative px-6 py-6 sm:px-8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-4xl space-y-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-[#8B0000]">
                            <i class="fas fa-book-open"></i>
                            Exam workspace
                        </span>
                        <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $statusTone }}">{{ $exam->status_label }}</span>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-700">{{ $typeLabel }}</span>
                    </div>

                    <div>
                        <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">{{ $exam->name }}</h1>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 sm:text-base">
                            Manage scheduling, marks, verification, publishing, and result sheets from one exam detail workspace.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-700">Session: {{ $exam->academicSession?->name_bs ?: $exam->academicSession?->name ?: '—' }}</span>
                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-700">Department: {{ $exam->department?->code ? $exam->department->code . ' - ' . $exam->department->name : ($exam->department?->name ?? 'Common') }}</span>
                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-700">Programs: {{ $exam->programs->count() }}</span>
                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-700">Semesters: {{ $semesterLabels }}</span>
                        <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-emerald-700">Start {{ bsDate($exam->start_date, 'Y, F d') ?: '—' }}</span>
                        <span class="rounded-full bg-sky-50 px-3 py-1.5 text-sky-700">End {{ bsDate($exam->end_date, 'Y, F d') ?: '—' }}</span>
                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-600">Created {{ bsDate($exam->created_at, 'Y, F d') }}</span>
                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-600">Updated {{ bsDate($exam->updated_at, 'Y, F d') }}</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.exams.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                        Back to list
                    </a>
                    <a href="{{ route('admin.exams.analytics') }}" class="inline-flex items-center gap-2 rounded-xl border border-sky-200 bg-sky-50 px-4 py-2.5 text-sm font-bold text-sky-700 shadow-sm transition hover:bg-sky-100">
                        Analytics
                    </a>
                    <a href="{{ route('admin.exams.edit', $exam) }}" class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#7a0000]">
                        Edit Exam
                    </a>
                    @if(! $published)
                        <form method="POST" action="{{ route('admin.exams.publish', $exam) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-bold text-emerald-700 shadow-sm transition hover:bg-emerald-100">
                                Publish Results
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($summary['cards'] as $card)
            <article class="rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">{{ $card['label'] }}</p>
                <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ $card['value'] }}</p>
                <div class="mt-2 h-1.5 rounded-full bg-slate-100">
                    <div class="h-1.5 rounded-full bg-gradient-to-r from-[#8B0000] to-rose-500" style="width: 72%"></div>
                </div>
            </article>
        @endforeach
    </section>

    <section class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Workflow</p>
                    <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Status and routing</h2>
                </div>
                <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">CTEVT flow</span>
            </div>

            <div class="mt-4 space-y-3">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm font-bold text-slate-900">Setup</p>
                    <p class="mt-1 text-sm leading-6 text-slate-600">Session, department, programs, semester, and schedule window are locked into the exam header.</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm font-bold text-slate-900">Mark entry</p>
                    <p class="mt-1 text-sm leading-6 text-slate-600">Teachers submit marks against the selected subjects and student records, then HOD verifies them.</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm font-bold text-slate-900">Publish</p>
                    <p class="mt-1 text-sm leading-6 text-slate-600">Once verified, the principal can publish the result sheet and make individual student sheets available.
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm font-bold text-slate-900">State</p>
                    <p class="mt-1 text-sm leading-6 text-slate-600">
                        Marks open: <span class="font-bold text-slate-900">{{ $exam->marks_open ? 'Yes' : 'No' }}</span> · Published: <span class="font-bold text-slate-900">{{ $published ? 'Yes' : 'No' }}</span>
                    </p>
                    @if($exam->published_at)
                        <p class="mt-1 text-sm leading-6 text-slate-600">Published at {{ bsDateTime($exam->published_at, 'Y, F d', 'h:i A') ?: '—' }}</p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-[2rem] border border-slate-200 bg-white shadow-sm" x-data="{ tab: @js(request('tab', 'overview')) }">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="tab = 'overview'"
                        :class="tab === 'overview' ? 'bg-[#8B0000] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="rounded-xl px-4 py-2.5 text-sm font-bold transition">Overview</button>
                <button type="button" @click="tab = 'subjects'"
                        :class="tab === 'subjects' ? 'bg-[#8B0000] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="rounded-xl px-4 py-2.5 text-sm font-bold transition">Subjects</button>
                <button type="button" @click="tab = 'marks'"
                    :class="tab === 'marks' ? 'bg-[#8B0000] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="rounded-xl px-4 py-2.5 text-sm font-bold transition">Uploaded Marks</button>
                <button type="button" @click="tab = 'verification'"
                        :class="tab === 'verification' ? 'bg-[#8B0000] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="rounded-xl px-4 py-2.5 text-sm font-bold transition">Verification</button>
                <button type="button" @click="tab = 'publish'"
                        :class="tab === 'publish' ? 'bg-[#8B0000] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="rounded-xl px-4 py-2.5 text-sm font-bold transition">Publish</button>
            </div>
            <div class="text-xs font-semibold text-slate-400">{{ $subjectRows->total() }} subject rows · {{ $uploadedMarksTotal }} uploaded marks</div>
        </div>

        <div x-show="tab === 'overview'" x-cloak class="p-5">
            <div class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
                <article class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Exam Snapshot</p>
                            <h3 class="mt-1 text-xl font-black tracking-tight text-slate-950">Configuration summary</h3>
                        </div>
                        <span class="rounded-full bg-rose-50 px-3 py-1.5 text-xs font-bold text-[#8B0000]">{{ $exam->programs->unique('id')->count() }} programs</span>
                    </div>

                    <div class="mt-4 grid gap-3 md:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Academic Session</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $exam->academicSession?->name_bs ?: $exam->academicSession?->name ?: '—' }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Department</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $departmentLabel }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4 md:col-span-2">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Programs</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @forelse($programSummaries as $programSummary)
                                    <span class="inline-flex items-center rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                                        {{ $programSummary['name'] }}@if($programSummary['semesters'])<span class="ml-1 text-slate-400">· {{ $programSummary['semesters'] }}</span>@endif
                                    </span>
                                @empty
                                    <span class="text-sm text-slate-400">No programs assigned</span>
                                @endforelse
                            </div>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Schedule</p>
                            <p class="mt-1 text-sm leading-6 text-slate-600">{{ bsDate($exam->start_date, 'Y, F d') ?: '—' }} to {{ bsDate($exam->end_date, 'Y, F d') ?: '—' }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4 md:col-span-2">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Remarks</p>
                            <p class="mt-1 text-sm leading-6 text-slate-600">
                                Marks open: {{ $exam->marks_open ? 'Yes' : 'No' }} · Published: {{ $published ? 'Yes' : 'No' }} ·
                                {{ $summary['passCount'] }} pass / {{ $summary['failCount'] }} fail records in current student result set.
                            </p>
                        </div>
                    </div>
                </article>

                <article class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Top Performers</p>
                            <h3 class="mt-1 text-xl font-black tracking-tight text-slate-950">Best student result sheets</h3>
                        </div>
                        <span class="rounded-full bg-sky-50 px-3 py-1.5 text-xs font-bold text-sky-700">Top 5</span>
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse($topPerformers as $row)
                            <a href="{{ route('admin.exams.result-sheet', [$exam, $row['student']]) }}" class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-[#8B0000]/30 hover:bg-rose-50/70">
                                <div class="flex items-center gap-3">
                                    @if($row['avatar'])
                                        <img src="{{ asset('storage/' . $row['avatar']) }}" alt="" class="h-10 w-10 rounded-xl object-cover ring-2 ring-white">
                                    @else
                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#8B0000] to-rose-700 text-xs font-black text-white">{{ strtoupper(substr($row['name'], 0, 1)) }}</div>
                                    @endif
                                    <div>
                                        <p class="font-semibold text-slate-950">{{ $row['name'] }}</p>
                                        <p class="text-[11px] text-slate-400">{{ $row['program'] ?? '—' }} · Sem {{ $row['semester'] ?? '—' }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-slate-950">{{ number_format($row['percentage'], 1) }}%</p>
                                    <p class="text-[11px] text-slate-400">{{ $row['grade_band'] }} · {{ $row['result_status'] }}</p>
                                </div>
                            </a>
                        @empty
                            <x-empty-state title="No result data" message="Student results will appear after marks are entered and verified."/>
                        @endforelse
                    </div>
                </article>
            </div>
        </div>

        <div x-show="tab === 'subjects'" x-cloak class="p-5">
            <div class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50/95 backdrop-blur sticky top-0">
                            <tr class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">
                                <th class="px-4 py-3 text-left">Department / Program / Subject</th>
                                <th class="px-4 py-3 text-left">Teacher</th>
                                <th class="px-4 py-3 text-left">Students</th>
                                <th class="px-4 py-3 text-left">Entered</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Last Updated</th>
                                <th class="px-4 py-3 text-left">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($subjectRows as $row)
                                @php $rowTone = $statusClasses[$row['status_tone']] ?? $statusClasses['slate']; @endphp
                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="px-4 py-3.5">
                                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">{{ $row['department_code'] ? $row['department_code'] . ' - ' : '' }}{{ $row['department_name'] ?? 'All departments' }}</p>
                                        <p class="mt-1 font-semibold text-slate-950">{{ $row['program_code'] ? $row['program_code'] . ' - ' : '' }}{{ $row['program_name'] }}</p>
                                        <p class="mt-1 text-[11px] text-slate-400">Sem {{ $row['semester'] }} · {{ $row['subject_code'] ? $row['subject_code'] . ' - ' : '' }}{{ $row['subject_name'] }} · {{ $row['subject_type'] }}</p>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <p class="font-semibold text-slate-900">{{ $row['teacher_name'] }}</p>
                                        <p class="mt-1 text-[11px] text-slate-400">{{ $row['invigilator'] }}</p>
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-600">{{ $row['student_count'] }}</td>
                                    <td class="px-4 py-3.5">
                                        <div class="space-y-1.5">
                                            <div class="h-2.5 w-32 rounded-full bg-slate-100">
                                                <div class="h-2.5 rounded-full bg-gradient-to-r from-[#8B0000] to-rose-600" style="width: {{ min(100, max(0, $row['entered_pct'])) }}%"></div>
                                            </div>
                                            <p class="text-xs font-semibold text-slate-600">{{ number_format($row['entered_pct'], 1) }}% · {{ $row['marks_count'] }} marks</p>
                                            <p class="text-[11px] text-slate-400">Submitted {{ $row['submitted_count'] }} · Approved {{ $row['approved_count'] }} · Published {{ $row['published_count'] }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $row['upload_state_tone'] ?? $rowTone }}">{{ $row['upload_state_label'] ?? $row['status_label'] }}</span>
                                        <p class="mt-1 text-[11px] text-slate-400">{{ (int) ($row['missing_count'] ?? 0) === 0 ? 'All uploaded' : ($row['marks_count'] . ' uploaded · ' . $row['missing_count'] . ' pending') }}</p>
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-600">{{ $row['last_updated'] }}</td>
                                    <td class="px-4 py-3.5 text-slate-600">{{ $row['remarks'] ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <x-empty-state title="No subject rows" message="Assign programs and generate marks to populate subject-level tracking."/>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($subjectRows->hasPages())
                    <div class="border-t border-slate-100 px-4 py-3">
                        {{ $subjectRows->onEachSide(1)->links() }}
                    </div>
                @endif
            </div>
        </div>

        <div x-show="tab === 'marks'" x-cloak class="p-5 space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Uploaded marks</p>
                    <h3 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Department -> semester -> subject</h3>
                    <p class="mt-1 text-sm text-slate-500">Expand a department to drill down into semesters and subjects, similar to the sidebar tree.</p>
                </div>
                <div class="flex flex-wrap gap-2 text-xs font-bold text-slate-700">
                    <span class="rounded-full bg-slate-100 px-3 py-1.5">All {{ $allMarksCount ?? 0 }}</span>
                    <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-emerald-700">Filled {{ $filledMarksCount ?? 0 }}</span>
                    <span class="rounded-full bg-amber-50 px-3 py-1.5 text-amber-700">Unfilled {{ $unfilledMarksCount ?? 0 }}</span>
                    <span class="rounded-full bg-violet-50 px-3 py-1.5 text-violet-700">Delayed {{ $delayedMarksCount ?? 0 }}</span>
                    <span class="rounded-full bg-slate-100 px-3 py-1.5">{{ $uploadedMarkGroups->count() }} departments</span>
                    <span class="rounded-full bg-slate-100 px-3 py-1.5">{{ $uploadedMarksTotal }} mark records</span>
                </div>
            </div>

            <div class="space-y-4">
                @forelse($uploadedMarkGroups as $departmentGroup)
                    <details name="uploaded-marks-departments" class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
                        <summary class="cursor-pointer list-none px-5 py-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="mt-1 flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-rose-50 text-[#8B0000] ring-1 ring-rose-200">
                                        <i class="fas fa-layer-group text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Department</p>
                                        <h4 class="mt-1 text-xl font-black tracking-tight text-slate-950">
                                            {{ $departmentGroup['department_code'] ? $departmentGroup['department_code'] . ' - ' : '' }}{{ $departmentGroup['department_name'] }}
                                        </h4>
                                        <p class="mt-1 text-sm text-slate-500">{{ $departmentGroup['marks_count'] }} uploaded marks · {{ $departmentGroup['subjects_count'] }} unique subjects</p>
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-slate-700">
                                    <span class="rounded-full bg-slate-100 px-3 py-1.5">{{ $departmentGroup['semesters']->count() }} semesters</span>
                                    <span class="rounded-full bg-rose-50 px-3 py-1.5 text-[#8B0000]">{{ $departmentGroup['marks_count'] }} records</span>
                                </div>
                            </div>
                        </summary>

                        <div class="border-t border-slate-100 bg-slate-50/60 px-4 py-4 sm:px-5">
                            <div class="space-y-3 border-l-2 border-slate-200 pl-4 sm:pl-5">
                                @foreach($departmentGroup['semesters'] as $semesterGroup)
                                    <details name="department-{{ $loop->parent->index }}-semesters" class="overflow-hidden rounded-[1.4rem] border border-slate-200 bg-white shadow-sm">
                                        <summary class="cursor-pointer list-none px-4 py-3.5">
                                            <div class="flex flex-wrap items-start justify-between gap-3">
                                                <div class="flex items-start gap-3">
                                                    <div class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-700 ring-1 ring-sky-200">
                                                        <i class="fas fa-graduation-cap text-xs"></i>
                                                    </div>
                                                    <div>
                                                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Semester</p>
                                                        <h5 class="mt-1 text-lg font-black tracking-tight text-slate-950">Semester {{ $semesterGroup['semester'] }}</h5>
                                                        <p class="mt-1 text-sm text-slate-500">{{ $semesterGroup['marks_count'] }} marks · {{ $semesterGroup['subjects_count'] }} subjects</p>
                                                    </div>
                                                </div>
                                                <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-slate-700">
                                                    <span class="rounded-full bg-slate-100 px-3 py-1.5">{{ $semesterGroup['subjects_count'] }} subjects</span>
                                                    <span class="rounded-full bg-white px-3 py-1.5 shadow-sm ring-1 ring-slate-200">{{ $semesterGroup['marks_count'] }} records</span>
                                                </div>
                                            </div>
                                        </summary>

                                        <div class="border-t border-slate-100 bg-slate-50/60 px-4 py-4">
                                            <div class="space-y-3 border-l-2 border-slate-200 pl-4">
                                                @foreach($semesterGroup['subjects'] as $subjectGroup)
                                                    <details name="department-{{ $loop->parent->parent->index }}-semester-{{ $loop->parent->index }}-subjects" class="overflow-hidden rounded-[1.25rem] border border-slate-200 bg-white shadow-sm">
                                                        <summary class="cursor-pointer list-none px-4 py-3">
                                                            <div class="flex flex-wrap items-start justify-between gap-3">
                                                                <div class="flex min-w-0 items-start gap-3">
                                                                    <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-[10px] font-black text-[#8B0000] ring-1 ring-rose-200">
                                                                        {{ strtoupper(substr($subjectGroup['subject_code'] ?: $subjectGroup['subject_name'], 0, 2)) }}
                                                                    </div>
                                                                    <div class="min-w-0">
                                                                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Subject</p>
                                                                        <h6 class="mt-1 text-base font-bold text-slate-950">{{ $subjectGroup['subject_code'] ? $subjectGroup['subject_code'] . ' - ' : '' }}{{ $subjectGroup['subject_name'] }}</h6>
                                                                        <p class="mt-1 text-xs text-slate-400">{{ $subjectGroup['marks_count'] }} uploaded marks · Avg {{ number_format($subjectGroup['average_score'], 1) }}%</p>
                                                                    </div>
                                                                </div>
                                                                <div class="flex flex-wrap items-center gap-2">
                                                                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700">{{ $subjectGroup['subject_type'] }}</span>
                                                                    <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">{{ $subjectGroup['passed_count'] }} passed</span>
                                                                </div>
                                                            </div>
                                                        </summary>

                                                        <div class="border-t border-slate-100 bg-white">
                                                            @php
                                                                $criteria = $subjectGroup['criteria'] ?? [
                                                                    'full_internal_theory' => 0,
                                                                    'pass_internal_theory' => 0,
                                                                    'full_external_theory' => 0,
                                                                    'pass_external_theory' => 0,
                                                                    'full_internal_practical' => 0,
                                                                    'pass_internal_practical' => 0,
                                                                    'full_external_practical' => 0,
                                                                    'pass_external_practical' => 0,
                                                                ];
                                                            @endphp
                                                            <div class="flex flex-wrap items-center justify-end gap-2 border-b border-slate-100 px-4 py-3 text-xs font-bold text-slate-700">
                                                                <a href="{{ route('admin.exams.marks.export', [$exam, 'pdf']) }}?subject_id={{ $subjectGroup['subject_id'] }}" class="rounded-full bg-white px-3 py-1.5 text-[#8B0000] ring-1 ring-rose-200 transition hover:bg-rose-50">Export PDF</a>
                                                                <a href="{{ route('admin.exams.marks.export', [$exam, 'excel']) }}?subject_id={{ $subjectGroup['subject_id'] }}" class="rounded-full bg-white px-3 py-1.5 text-sky-700 ring-1 ring-sky-200 transition hover:bg-sky-50">Export Excel</a>
                                                            </div>
                                                            @if(($exam->category ?? 'ctevt_final') !== 'monthly_assessment')
                                                                <form method="POST" action="{{ route('admin.exams.subjects.marking-scheme.update', [$exam, $subjectGroup['subject_id']]) }}" class="border-b border-slate-100 bg-slate-50/70 px-4 py-3">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <div class="grid gap-2 text-[11px] text-slate-600 sm:grid-cols-2 lg:grid-cols-4">
                                                                        <label class="space-y-1">
                                                                            <span class="font-bold uppercase tracking-[0.12em] text-slate-500">Int theory (pass/full)</span>
                                                                            <div class="flex items-center gap-2">
                                                                                <input type="number" name="pass_marks_internal_theory" min="0" step="0.01" value="{{ old('pass_marks_internal_theory', $criteria['pass_internal_theory']) }}" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-900">
                                                                                <input type="number" name="full_marks_internal_theory" min="0" step="0.01" value="{{ old('full_marks_internal_theory', $criteria['full_internal_theory']) }}" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-900">
                                                                            </div>
                                                                        </label>
                                                                        <label class="space-y-1">
                                                                            <span class="font-bold uppercase tracking-[0.12em] text-slate-500">Ext theory (pass/full)</span>
                                                                            <div class="flex items-center gap-2">
                                                                                <input type="number" name="pass_marks_external_theory" min="0" step="0.01" value="{{ old('pass_marks_external_theory', $criteria['pass_external_theory']) }}" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-900">
                                                                                <input type="number" name="full_marks_external_theory" min="0" step="0.01" value="{{ old('full_marks_external_theory', $criteria['full_external_theory']) }}" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-900">
                                                                            </div>
                                                                        </label>
                                                                        <label class="space-y-1">
                                                                            <span class="font-bold uppercase tracking-[0.12em] text-slate-500">Int practical (pass/full)</span>
                                                                            <div class="flex items-center gap-2">
                                                                                <input type="number" name="pass_marks_internal_practical" min="0" step="0.01" value="{{ old('pass_marks_internal_practical', $criteria['pass_internal_practical']) }}" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-900">
                                                                                <input type="number" name="full_marks_internal_practical" min="0" step="0.01" value="{{ old('full_marks_internal_practical', $criteria['full_internal_practical']) }}" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-900">
                                                                            </div>
                                                                        </label>
                                                                        <label class="space-y-1">
                                                                            <span class="font-bold uppercase tracking-[0.12em] text-slate-500">Ext practical (pass/full)</span>
                                                                            <div class="flex items-center gap-2">
                                                                                <input type="number" name="pass_marks_external_practical" min="0" step="0.01" value="{{ old('pass_marks_external_practical', $criteria['pass_external_practical']) }}" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-900">
                                                                                <input type="number" name="full_marks_external_practical" min="0" step="0.01" value="{{ old('full_marks_external_practical', $criteria['full_external_practical']) }}" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-900">
                                                                            </div>
                                                                        </label>
                                                                    </div>
                                                                    <div class="mt-3 flex items-center justify-between gap-3">
                                                                        <p class="text-[11px] font-medium text-slate-500">Set pass and full marks for this subject, then save.</p>
                                                                        <button type="submit" class="rounded-full bg-[#8B0000] px-3.5 py-1.5 text-xs font-bold text-white transition hover:bg-[#760000]">Save Criteria</button>
                                                                    </div>
                                                                </form>
                                                            @endif
                                                            <div class="overflow-x-auto">
                                                                <table class="min-w-full divide-y divide-slate-100 text-sm">
                                                                    <thead class="bg-slate-50/95 backdrop-blur sticky top-0">
                                                                        <tr class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">
                                                                            <th class="px-4 py-3 text-left">Student</th>
                                                                            <th class="px-4 py-3 text-left">
                                                                                <div class="space-y-1">
                                                                                    <p>Mark breakdown</p>
                                                                                    @if(($exam->category ?? 'ctevt_final') === 'monthly_assessment')
                                                                                        <p class="normal-case tracking-normal text-[10px] font-semibold text-slate-500">Attendance, pass marks, full marks, and obtained marks for this assessment</p>
                                                                                    @else
                                                                                        <p class="normal-case tracking-normal text-[10px] font-semibold text-slate-500">IT {{ $criteria['pass_internal_theory'] }}/{{ $criteria['full_internal_theory'] }} · ET {{ $criteria['pass_external_theory'] }}/{{ $criteria['full_external_theory'] }} · IP {{ $criteria['pass_internal_practical'] }}/{{ $criteria['full_internal_practical'] }} · EP {{ $criteria['pass_external_practical'] }}/{{ $criteria['full_external_practical'] }}</p>
                                                                                    @endif
                                                                                </div>
                                                                            </th>
                                                                            <th class="px-4 py-3 text-left">Total</th>
                                                                            <th class="px-4 py-3 text-left">Result</th>
                                                                            <th class="px-4 py-3 text-left">Teacher</th>
                                                                            <th class="px-4 py-3 text-left">Updated</th>
                                                                            <th class="px-4 py-3 text-right">Actions</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody class="divide-y divide-slate-50">
                                                                        @foreach($subjectGroup['marks'] as $mark)
                                                                            @php
                                                                                $resultLabel = $mark['result_remark'] ?? 'Pending';
                                                                                $resultTone = match ($resultLabel) {
                                                                                    'Pass' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                                                                    'Absent' => 'bg-amber-50 text-amber-700 ring-amber-200',
                                                                                    'Delayed' => 'bg-violet-50 text-violet-700 ring-violet-200',
                                                                                    'Withheld' => 'bg-slate-100 text-slate-700 ring-slate-200',
                                                                                    'Fail' => 'bg-rose-50 text-[#8B0000] ring-rose-200',
                                                                                    default => 'bg-slate-100 text-slate-700 ring-slate-200',
                                                                                };
                                                                            @endphp
                                                                            <tr class="transition hover:bg-slate-50/70">
                                                                                <td class="px-4 py-3.5">
                                                                                    <p class="font-semibold text-slate-950">{{ $mark['student_name'] }}</p>
                                                                                    <p class="mt-1 text-[11px] text-slate-400">{{ $mark['program_name'] }} · Roll {{ $mark['roll_number'] }} · Student {{ $mark['student_no'] }}</p>
                                                                                </td>
                                                                                <td class="px-4 py-3.5 text-slate-600">
                                                                                    <div class="grid gap-1 text-[11px] sm:grid-cols-2 xl:grid-cols-4">
                                                                                        @if(($exam->category ?? 'ctevt_final') === 'monthly_assessment')
                                                                                            <span>Attendance: <strong class="text-slate-900">{{ $mark['assessment_attendance_percent'] !== null ? number_format((float) $mark['assessment_attendance_percent'], 1) . '%' : '—' }}</strong></span>
                                                                                            <span>Pass / Full: <strong class="text-slate-900">{{ $mark['assessment_pass_marks'] ?? '—' }} / {{ $mark['assessment_full_marks'] ?? '—' }}</strong></span>
                                                                                            <span>Obtained: <strong class="text-slate-900">{{ $mark['assessment_obtained_marks'] ?? '—' }}</strong></span>
                                                                                        @else
                                                                                            <span>Internal theory: <strong class="text-slate-900">{{ $mark['internal_theory'] ?? '—' }}</strong></span>
                                                                                            <span>External theory: <strong class="text-slate-900">{{ $mark['external_theory'] ?? '—' }}</strong></span>
                                                                                            <span>Internal practical: <strong class="text-slate-900">{{ $mark['internal_practical'] ?? '—' }}</strong></span>
                                                                                            <span>External practical: <strong class="text-slate-900">{{ $mark['external_practical'] ?? '—' }}</strong></span>
                                                                                        @endif
                                                                                    </div>
                                                                                </td>
                                                                                <td class="px-4 py-3.5">
                                                                                    <p class="font-bold text-slate-950">{{ number_format((float) $mark['total_marks'], 2) }}</p>
                                                                                    <p class="mt-1 text-[11px] text-slate-400">{{ isset($mark['percentage']) && $mark['percentage'] !== null ? number_format((float) $mark['percentage'], 1) . '%' : 'No percentage' }}</p>
                                                                                </td>
                                                                                <td class="px-4 py-3.5">
                                                                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $resultTone }}">{{ $resultLabel }}</span>
                                                                                    <p class="mt-1 text-[11px] text-slate-400">{{ $mark['is_absent'] ? 'Absent' : ($mark['is_withheld'] ? 'Withheld' : ($mark['is_delayed'] ? ('Delayed' . (!empty($mark['delay_reason']) ? ': ' . $mark['delay_reason'] : '')) : ($mark['status'] ?? '—'))) }}</p>
                                                                                </td>
                                                                                <td class="px-4 py-3.5 text-slate-600">
                                                                                    <p class="font-semibold text-slate-900">{{ $mark['teacher_name'] }}</p>
                                                                                    <p class="mt-1 text-[11px] text-slate-400">{{ $mark['remarks'] ?: 'No remarks' }}</p>
                                                                                </td>
                                                                                <td class="px-4 py-3.5 text-slate-600">{{ $mark['updated_at_label'] }}</td>
                                                                                <td class="px-4 py-3.5 text-right">
                                                                                    <a href="{{ route('admin.exams.marks.edit', [$exam, $mark['mark_id']]) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 shadow-sm transition hover:border-[#8B0000] hover:text-[#8B0000]">
                                                                                        Edit
                                                                                    </a>
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </details>
                                                @endforeach
                                            </div>
                                        </div>
                                    </details>
                                @endforeach
                            </div>
                        </div>
                    </details>
                @empty
                    <x-empty-state title="No uploaded marks" message="Marks entered for this exam will appear here, grouped by department, semester, and subject."/>
                @endforelse
            </div>
        </div>

        <div x-show="tab === 'verification'" x-cloak class="p-5">
            <div class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
                <article class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Approval Queue</p>
                            <h3 class="mt-1 text-xl font-black tracking-tight text-slate-950">Verification progress</h3>
                        </div>
                        <span class="rounded-full bg-violet-50 px-3 py-1.5 text-xs font-bold text-violet-700">HOD review</span>
                    </div>

                    <div class="mt-4 overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100 text-sm">
                                <thead class="bg-slate-50/95 backdrop-blur sticky top-0">
                                    <tr class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">
                                        <th class="px-4 py-3 text-left">Subject</th>
                                        <th class="px-4 py-3 text-left">Teacher</th>
                                        <th class="px-4 py-3 text-left">Status</th>
                                        <th class="px-4 py-3 text-left">Last Updated</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @forelse($verificationRows as $row)
                                        @php $rowTone = $statusClasses[$row['status_tone']] ?? $statusClasses['slate']; @endphp
                                        <tr class="transition hover:bg-slate-50/70">
                                            <td class="px-4 py-3.5">
                                                <p class="font-semibold text-slate-950">{{ $row['subject_name'] }}</p>
                                                <p class="mt-1 text-[11px] text-slate-400">{{ $row['program_name'] }} · Sem {{ $row['semester'] }}</p>
                                            </td>
                                            <td class="px-4 py-3.5 text-slate-600">{{ $row['teacher_name'] }}</td>
                                            <td class="px-4 py-3.5"><span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $rowTone }}">{{ $row['status_label'] }}</span></td>
                                            <td class="px-4 py-3.5 text-slate-600">{{ $row['last_updated'] }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4"><x-empty-state title="No verification data" message="Once marks are submitted, verification progress appears here."/></td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($verificationRows->hasPages())
                            <div class="border-t border-slate-100 px-4 py-3">
                                {{ $verificationRows->onEachSide(1)->links() }}
                            </div>
                        @endif
                    </div>
                </article>

                <article class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Publish Readiness</p>
                            <h3 class="mt-1 text-xl font-black tracking-tight text-slate-950">Release controls</h3>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">{{ $published ? 'Locked' : 'Open' }}</span>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-bold text-slate-900">Publishing state</p>
                            <p class="mt-1 text-sm leading-6 text-slate-600">{{ $published ? 'This exam has already been published and result sheets are ready.' : 'This exam can be published once marks are fully verified.' }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-bold text-slate-900">Student access</p>
                               <p class="mt-1 text-sm leading-6 text-slate-600">Individual result sheets open from the overview cards and top performer links. Use them to review subject totals and grades.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-bold text-slate-900">Audit trail</p>
                            <p class="mt-1 text-sm leading-6 text-slate-600">Published results should remain immutable except through a controlled admin correction flow.</p>
                        </div>
                    </div>
                </article>
            </div>
        </div>

        <div x-show="tab === 'publish'" x-cloak class="p-5">
            <div class="grid gap-6 xl:grid-cols-[1fr_0.9fr]">
                <article class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Publishing</p>
                            <h3 class="mt-1 text-xl font-black tracking-tight text-slate-950">Finalize result release</h3>
                        </div>
                        <span class="rounded-full {{ $published ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }} px-3 py-1.5 text-xs font-bold">{{ $published ? 'Published' : 'Pending' }}</span>
                    </div>

                    <div class="mt-4 rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5">
                        <p class="text-sm font-bold text-slate-900">Workflow checklist</p>
                        <div class="mt-3 space-y-2 text-sm text-slate-600">
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 h-5 w-5 rounded-full {{ $exam->marks_open ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-500' }} flex items-center justify-center text-[10px] font-black">1</span>
                                <p>Marks entry opened for teachers.</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 h-5 w-5 rounded-full {{ $verificationRows->total() > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-500' }} flex items-center justify-center text-[10px] font-black">2</span>
                                <p>HOD verifies submitted marks and resolves exceptions.</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 h-5 w-5 rounded-full {{ $published ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-500' }} flex items-center justify-center text-[10px] font-black">3</span>
                                <p>Principal publishes the result sheet to close the cycle.</p>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Summary</p>
                            <h3 class="mt-1 text-xl font-black tracking-tight text-slate-950">Outcome snapshot</h3>
                        </div>
                        <span class="rounded-full bg-rose-50 px-3 py-1.5 text-xs font-bold text-[#8B0000]">{{ $summary['departmentAverage'] }}% avg</span>
                    </div>
                    <div class="mt-4 grid gap-3">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Top subject</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $summary['topSubject'] }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Top performer</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $summary['topPerformer'] }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Semester average</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $summary['semesterAverage'] }}%</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Publish state</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $published ? 'Published at ' . (bsDateTime($exam->published_at, 'Y, F d', 'h:i A') ?: '—') : 'Awaiting release' }}</p>
                        </div>
                    </div>

                    @if(! $published)
                        <form method="POST" action="{{ route('admin.exams.publish', $exam) }}" class="mt-5">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#7a0000]">
                                Publish Result Sheet
                            </button>
                        </form>
                    @endif
                </article>
            </div>
        </div>
    </section>
</div>
@endsection

