@extends('layouts.app')
@section('title', 'Attendance Session')

@section('content')
@php
    $gradients = ['from-[#8B0000] to-rose-700', 'from-sky-600 to-indigo-700', 'from-emerald-600 to-teal-700', 'from-violet-600 to-purple-700'];
    $heroGrad = $gradients[$attendanceSession->id % count($gradients)];

    $statusTone = [
        'present' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'absent' => 'bg-rose-50 text-[#8B0000] ring-rose-200',
        'late' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'excused' => 'bg-slate-100 text-slate-700 ring-slate-200',
    ];

    $summaryTone = [
        'present' => 'text-emerald-600',
        'absent' => 'text-[#8B0000]',
        'late' => 'text-amber-600',
        'excused' => 'text-slate-600',
    ];
@endphp

<div x-data="{ query: '' }" class="space-y-6">
    {{-- HERO --}}
    <section class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br {{ $heroGrad }} shadow-sm">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.18),transparent_30%)]"></div>
        <div class="relative px-6 py-6 sm:px-8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div class="max-w-4xl text-white">
                    <div class="flex flex-wrap items-center gap-2 text-[11px] font-bold uppercase tracking-[0.22em] text-white/70">
                        <span class="rounded-full bg-white/15 px-3 py-1.5">Attendance Session</span>
                        <span class="rounded-full bg-white/15 px-3 py-1.5">{{ $attendanceSession->academicSession?->name ?? 'Current session' }}</span>
                        <span class="rounded-full bg-white/15 px-3 py-1.5">Sem {{ $attendanceSession->semester }}</span>
                    </div>
                    <h1 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">{{ $attendanceSession->subject?->name ?? 'Class attendance' }}</h1>
                    <p class="mt-2 text-sm leading-6 text-white/75">Detailed class attendance review for {{ $attendanceSession->teacher?->user?->name ?? 'the teacher' }} on {{ bsDate($attendanceSession->date, 'Y, F d') ?: '—' }}.</p>
                    <div class="mt-4 flex flex-wrap items-center gap-2 text-xs font-semibold text-white/80">
                        <span class="rounded-full bg-white/15 px-3 py-1.5">{{ $attendanceSession->program?->department?->name ?? 'Department' }}</span>
                        <span class="rounded-full bg-white/15 px-3 py-1.5">{{ $attendanceSession->program?->name ?? 'Program' }}</span>
                        <span class="rounded-full bg-white/15 px-3 py-1.5">{{ $attendanceSession->period ?? 'Period not set' }}</span>
                        <span class="rounded-full bg-white/15 px-3 py-1.5">{{ $attendanceSession->section ? 'Section ' . $attendanceSession->section : 'All sections' }}</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.attendance.index', request()->query()) }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-white/20">
                        ← Back
                    </a>
                    @if($attendanceSession->teacher_id)
                        <a href="{{ route('admin.teachers.show', ['teacher' => $attendanceSession->teacher_id, 'tab' => 'attendance']) }}"
                           class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-white/20">
                            Teacher Profile
                        </a>
                    @endif
                </div>
            </div>

            <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @php
                    $summaryCards = [
                        ['label' => 'Present', 'value' => $summary['present'], 'tone' => 'present'],
                        ['label' => 'Absent', 'value' => $summary['absent'], 'tone' => 'absent'],
                        ['label' => 'Late / Excused', 'value' => $summary['late'] + $summary['excused'], 'tone' => 'late'],
                        ['label' => 'Completion', 'value' => $summary['completion'] . '%', 'tone' => 'excused'],
                    ];
                @endphp
                @foreach($summaryCards as $card)
                    <div class="rounded-[1.5rem] bg-white/15 p-4 text-center text-white backdrop-blur-sm">
                        <p class="text-2xl font-black">{{ $card['value'] }}</p>
                        <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.18em] text-white/65">{{ $card['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <div class="space-y-6">
            {{-- Student list --}}
            <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Student List</p>
                        <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Attendance status by student</h2>
                        <p class="mt-1 text-sm text-slate-500">Search and review the marking state for this class session.</p>
                    </div>
                    <div class="w-full md:w-72">
                        <input x-model="query" type="text" placeholder="Search student..."
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-rose-100">
                    </div>
                </div>

                <div class="mt-5 overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm">
                    <div class="max-h-[620px] overflow-auto">
                        <table class="min-w-full divide-y divide-slate-100 text-sm">
                            <thead class="sticky top-0 bg-slate-50/95 backdrop-blur">
                                <tr class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">
                                    <th class="px-4 py-3 text-left">Student</th>
                                    <th class="px-4 py-3 text-left">Semester</th>
                                    <th class="px-4 py-3 text-left">Status toggle</th>
                                    <th class="px-4 py-3 text-left">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($records as $record)
                                    @php
                                        $student = $record->student;
                                        $studentName = $student?->user?->name ?? 'Student';
                                        $status = $record->status ?? 'present';
                                    @endphp
                                    <tr data-search="{{ strtolower($studentName . ' ' . ($student->student_no ?? '') . ' ' . ($student->roll_number ?? '')) }}" x-show="query === '' || $el.dataset.search.includes(query.toLowerCase())" class="group transition hover:bg-slate-50/70">
                                        <td class="px-4 py-3.5">
                                            <div class="flex items-center gap-3">
                                                @if($student?->user?->avatar)
                                                    <img src="{{ asset('storage/' . $student->user->avatar) }}" alt="" class="h-10 w-10 rounded-xl object-cover ring-2 ring-slate-100">
                                                @else
                                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#8B0000] to-rose-700 text-xs font-black text-white">{{ strtoupper(substr($studentName, 0, 1)) }}</div>
                                                @endif
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-slate-900">{{ $studentName }}</p>
                                                    <p class="truncate text-[11px] text-slate-400">{{ $student?->student_no ?? $student?->roll_number ?? 'No ID' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <span class="rounded-full bg-sky-50 px-2.5 py-1 text-[11px] font-bold text-sky-700 ring-1 ring-sky-100">Sem {{ $student?->current_semester ?? $attendanceSession->semester }}</span>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div class="inline-flex rounded-full bg-slate-100 p-1 text-[11px] font-bold text-slate-500">
                                                @foreach(['present' => 'P', 'absent' => 'A', 'late' => 'L', 'excused' => 'E'] as $key => $abbr)
                                                    <span class="rounded-full px-2.5 py-1 {{ $status === $key ? 'bg-[#8B0000] text-white shadow-sm' : 'text-slate-500' }}">{{ $abbr }}</span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <p class="max-w-md text-sm text-slate-600">{{ $record->remarks ?? 'No remarks' }}</p>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-16 text-center">
                                            <div class="mx-auto max-w-md">
                                                <p class="text-base font-bold text-slate-700">No students loaded</p>
                                                <p class="mt-1 text-sm text-slate-400">This attendance session does not have student records yet.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($records->hasPages())
                        <div class="border-t border-slate-100 px-4 py-3">
                            {{ $records->onEachSide(1)->links() }}
                        </div>
                    @endif
                </div>
            </article>

        </div>

        <aside class="space-y-6 xl:sticky xl:top-6 xl:self-start">
            <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Session Summary</p>
                <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Quick overview</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-slate-500">Teacher</dt>
                        <dd class="font-semibold text-slate-900">{{ $attendanceSession->teacher?->user?->name ?? 'Unassigned' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-slate-500">Subject</dt>
                        <dd class="font-semibold text-slate-900">{{ $attendanceSession->subject?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-slate-500">Date & Time</dt>
                        <dd class="font-semibold text-slate-900">{{ bsDate($attendanceSession->date, 'Y, F d') ?: '—' }} · {{ $attendanceSession->period ?? 'N/A' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-slate-500">Semester</dt>
                        <dd class="font-semibold text-slate-900">Sem {{ $attendanceSession->semester }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-slate-500">Department</dt>
                        <dd class="font-semibold text-slate-900">{{ $attendanceSession->program?->department?->name ?? '—' }}</dd>
                    </div>
                </dl>
            </article>

            <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Teacher Notes</p>
                <h3 class="mt-1 text-xl font-black text-slate-950">Remarks captured</h3>
                <div class="mt-4 space-y-2">
                    @forelse($notes as $note)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-600">{{ $note }}</div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-4 text-sm text-slate-400">No teacher remarks were attached to this class session.</div>
                    @endforelse
                </div>
            </article>

            <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">History</p>
                        <h3 class="mt-1 text-xl font-black text-slate-950">Previous sessions</h3>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700">{{ $historySessions->count() }}</span>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse($historySessions as $history)
                        @php
                            $historyTotal = $history->records_count ?? 0;
                            $historyPresent = $history->present_records_count ?? 0;
                            $historyRate = $historyTotal > 0 ? round(($historyPresent / $historyTotal) * 100, 1) : 0;
                        @endphp
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-bold text-slate-900">{{ bsDate($history->date, 'Y, F d') ?: '—' }}</p>
                                    <p class="text-xs text-slate-400">{{ $history->teacher?->user?->name ?? 'Teacher' }} · Sem {{ $history->semester }}</p>
                                </div>
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-slate-700 shadow-sm">{{ $historyRate }}%</span>
                            </div>
                            <div class="mt-3 h-2 rounded-full bg-white overflow-hidden">
                                <div class="h-2 rounded-full bg-[#8B0000]" style="width: {{ $historyRate }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-4 text-sm text-slate-400">No previous sessions for this subject.</div>
                    @endforelse
                </div>
            </article>
        </aside>
    </section>
</div>
@endsection
