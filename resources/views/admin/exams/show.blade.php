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
    $programLabels = $exam->programs->map(function ($program) {
        $semester = $program->pivot?->semester;
        return trim(($program->code ? $program->code . ' - ' : '') . $program->name) . ($semester ? ' · Sem ' . $semester : '');
    })->implode(' · ') ?: 'No programs assigned';

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

    <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Performance</p>
                    <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Result analytics</h2>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700">Subject, grade, and trend charts</span>
            </div>
            <div class="mt-5 grid gap-5 xl:grid-cols-3">
                <div class="h-[260px] rounded-[1.5rem] bg-slate-50/80 p-4 ring-1 ring-slate-200/80">
                    <canvas id="examSubjectPerformance"></canvas>
                </div>
                <div class="h-[260px] rounded-[1.5rem] bg-slate-50/80 p-4 ring-1 ring-slate-200/80">
                    <canvas id="examGradeDistribution"></canvas>
                </div>
                <div class="h-[260px] rounded-[1.5rem] bg-slate-50/80 p-4 ring-1 ring-slate-200/80">
                    <canvas id="examYearTrend"></canvas>
                </div>
            </div>
        </article>

        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
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
                        <p class="mt-1 text-sm leading-6 text-slate-600">Published at {{ bsDate($exam->published_at, 'Y, F d h:i A') ?: '—' }}</p>
                    @endif
                </div>
            </div>
        </article>
    </section>

    <section class="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="tab = 'overview'"
                        :class="tab === 'overview' ? 'bg-[#8B0000] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="rounded-xl px-4 py-2.5 text-sm font-bold transition">Overview</button>
                <button type="button" @click="tab = 'subjects'"
                        :class="tab === 'subjects' ? 'bg-[#8B0000] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="rounded-xl px-4 py-2.5 text-sm font-bold transition">Subjects</button>
                <button type="button" @click="tab = 'students'"
                        :class="tab === 'students' ? 'bg-[#8B0000] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="rounded-xl px-4 py-2.5 text-sm font-bold transition">Students</button>
                <button type="button" @click="tab = 'verification'"
                        :class="tab === 'verification' ? 'bg-[#8B0000] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="rounded-xl px-4 py-2.5 text-sm font-bold transition">Verification</button>
                <button type="button" @click="tab = 'publish'"
                        :class="tab === 'publish' ? 'bg-[#8B0000] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="rounded-xl px-4 py-2.5 text-sm font-bold transition">Publish</button>
            </div>
            <div class="text-xs font-semibold text-slate-400">{{ $studentRows->count() }} student results · {{ $subjectRows->count() }} subject rows</div>
        </div>

        <div x-show="tab === 'overview'" x-cloak class="p-5">
            <div class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
                <article class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Exam Snapshot</p>
                            <h3 class="mt-1 text-xl font-black tracking-tight text-slate-950">Configuration summary</h3>
                        </div>
                        <span class="rounded-full bg-rose-50 px-3 py-1.5 text-xs font-bold text-[#8B0000]">{{ $exam->programs->count() }} programs</span>
                    </div>

                    <div class="mt-4 grid gap-3 md:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Academic Session</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $exam->academicSession?->name_bs ?: $exam->academicSession?->name ?: '—' }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Department</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $exam->department?->code ? $exam->department->code . ' - ' . $exam->department->name : ($exam->department?->name ?? 'Common') }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Programs</p>
                            <p class="mt-1 text-sm leading-6 text-slate-600">{{ $programLabels }}</p>
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
                                <th class="px-4 py-3 text-left">Program / Subject</th>
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
                                        <p class="font-semibold text-slate-950">{{ $row['program_code'] ? $row['program_code'] . ' - ' : '' }}{{ $row['program_name'] }}</p>
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
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $rowTone }}">{{ $row['status_label'] }}</span>
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
            </div>
        </div>

        <div x-show="tab === 'students'" x-cloak class="p-5">
            <div class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50/95 backdrop-blur sticky top-0">
                            <tr class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">
                                <th class="px-4 py-3 text-left">Student</th>
                                <th class="px-4 py-3 text-left">Program</th>
                                <th class="px-4 py-3 text-left">Result</th>
                                <th class="px-4 py-3 text-left">Score</th>
                                <th class="px-4 py-3 text-left">GPA</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-right">Sheet</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($studentRows as $row)
                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-3">
                                            @if($row['avatar'])
                                                <img src="{{ asset('storage/' . $row['avatar']) }}" alt="" class="h-10 w-10 rounded-xl object-cover ring-2 ring-white">
                                            @else
                                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#8B0000] to-rose-700 text-xs font-black text-white">{{ strtoupper(substr($row['name'], 0, 1)) }}</div>
                                            @endif
                                            <div>
                                                <p class="font-semibold text-slate-950">{{ $row['name'] }}</p>
                                                <p class="mt-1 text-[11px] text-slate-400">{{ $row['roll_number'] ?? $row['symbol_no'] ?? 'No roll number' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <p class="font-semibold text-slate-900">{{ $row['program'] ?? '—' }}</p>
                                        <p class="mt-1 text-[11px] text-slate-400">{{ $row['department'] ?? '—' }} · Sem {{ $row['semester'] }}</p>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <p class="font-semibold text-slate-900">{{ $row['subject_count'] }} subjects</p>
                                        <p class="mt-1 text-[11px] text-slate-400">{{ $row['obtained'] }} / {{ $row['full_marks'] }}</p>
                                    </td>
                                    <td class="px-4 py-3.5 font-bold text-slate-950">{{ number_format($row['percentage'], 1) }}%</td>
                                    <td class="px-4 py-3.5 font-bold text-slate-950">{{ number_format($row['gpa'], 2) }}</td>
                                    <td class="px-4 py-3.5">
                                        @php $gradeTone = $gradeColors[$row['grade_band']] ?? $gradeColors['Fail']; @endphp
                                        <div class="space-y-1">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $gradeTone }}">{{ $row['result_status'] }}</span>
                                            <p class="text-[11px] text-slate-400">{{ $row['grade_band'] }} band · Absent {{ $row['absent_count'] }} · Withheld {{ $row['withheld_count'] }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 text-right">
                                        <a href="{{ route('admin.exams.result-sheet', [$exam, $row['student']]) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-[#8B0000]/30 hover:text-[#8B0000]">
                                            Open sheet
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <x-empty-state title="No student results" message="Publish marks to generate student result sheets and comparative analytics."/>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
                            <p class="mt-1 text-sm leading-6 text-slate-600">Individual result sheets open from the student tab. Use them to review subject totals and grades.</p>
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
                                <span class="mt-0.5 h-5 w-5 rounded-full {{ $verificationRows->count() > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-500' }} flex items-center justify-center text-[10px] font-black">2</span>
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
                            <p class="mt-1 font-semibold text-slate-900">{{ $published ? 'Published at ' . (bsDate($exam->published_at, 'Y, F d h:i A') ?: '—') : 'Awaiting release' }}</p>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.Chart) {
        return;
    }

    const subjectData = @json($charts['subjectPerformance'] ?? ['labels' => [], 'values' => []]);
    const gradeData = @json($charts['gradeDistribution'] ?? ['labels' => [], 'values' => []]);
    const trendData = @json($charts['yearTrend'] ?? ['labels' => [], 'values' => []]);

    const subjectCanvas = document.getElementById('examSubjectPerformance');
    if (subjectCanvas) {
        new Chart(subjectCanvas, {
            type: 'bar',
            data: {
                labels: subjectData.labels,
                datasets: [{
                    label: 'Subject score %',
                    data: subjectData.values,
                    borderRadius: 10,
                    borderSkipped: false,
                    backgroundColor: 'rgba(139, 0, 0, 0.85)',
                    maxBarThickness: 24,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, suggestedMax: 100, ticks: { callback: value => value + '%' } },
                },
            },
        });
    }

    const gradeCanvas = document.getElementById('examGradeDistribution');
    if (gradeCanvas) {
        new Chart(gradeCanvas, {
            type: 'doughnut',
            data: {
                labels: gradeData.labels,
                datasets: [{
                    data: gradeData.values,
                    backgroundColor: ['rgba(16, 185, 129, 0.85)', 'rgba(59, 130, 246, 0.85)', 'rgba(245, 158, 11, 0.85)', 'rgba(139, 0, 0, 0.85)'],
                    borderWidth: 0,
                    hoverOffset: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } },
                },
            },
        });
    }

    const trendCanvas = document.getElementById('examYearTrend');
    if (trendCanvas) {
        const ctx = trendCanvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 260);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.22)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.03)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: trendData.labels,
                datasets: [{
                    label: 'Pass rate',
                    data: trendData.values,
                    borderColor: '#8B0000',
                    backgroundColor: gradient,
                    fill: true,
                    borderWidth: 3,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#8B0000',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    tension: 0.38,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, suggestedMax: 100, ticks: { callback: value => value + '%' } },
                },
            },
        });
    }
});
</script>
@endpush