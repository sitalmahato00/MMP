@extends('layouts.app')
@section('title', 'Result Sheet')

@section('content')
@php
    $statusTone = match ($summary['result_status'] ?? 'Pending') {
        'Pass' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'Fail' => 'bg-rose-50 text-[#8B0000] ring-rose-200',
        default => 'bg-amber-50 text-amber-700 ring-amber-200',
    };
@endphp

<div class="space-y-6">
    <section class="relative overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_24px_70px_rgba(15,23,42,0.07)] print:shadow-none">
        <div class="absolute inset-0 bg-gradient-to-br from-rose-50 via-white to-sky-50/40"></div>
        <div class="relative px-6 py-6 sm:px-8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-3xl space-y-3">
                    <div class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-[#8B0000]">
                        <i class="fas fa-scroll"></i>
                        Official result sheet
                    </div>
                    <div>
                        <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">{{ $student->user?->name ?? 'Student' }}</h1>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
                            {{ $exam->name }} · {{ $exam->academicSession?->name_bs ?: $exam->academicSession?->name ?: '—' }} · {{ $exam->department?->name ?? 'Common' }}
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-700">Program: {{ $student->program?->code ? $student->program->code . ' - ' : '' }}{{ $student->program?->name ?? '—' }}</span>
                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-700">Department: {{ $student->department?->code ? $student->department->code . ' - ' : '' }}{{ $student->department?->name ?? '—' }}</span>
                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-700">Semester: {{ $student->current_semester }}</span>
                        <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-emerald-700">Verification {{ $verificationCode }}</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 print:hidden">
                    <a href="{{ route('admin.exams.show', $exam) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                        Back to exam
                    </a>
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#7a0000]">
                        Print sheet
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Obtained Marks</p>
            <p class="mt-2 text-3xl font-black tracking-tight text-slate-950">{{ number_format($summary['obtained'] ?? 0, 2) }}</p>
        </article>
        <article class="rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Total Marks</p>
            <p class="mt-2 text-3xl font-black tracking-tight text-slate-950">{{ number_format($summary['full_marks'] ?? 0, 2) }}</p>
        </article>
        <article class="rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Percentage</p>
            <p class="mt-2 text-3xl font-black tracking-tight text-slate-950">{{ number_format($summary['percentage'] ?? 0, 1) }}%</p>
        </article>
        <article class="rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Result Status</p>
            <div class="mt-2 flex items-center gap-2">
                <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $statusTone }}">{{ $summary['result_status'] ?? 'Pending' }}</span>
                <span class="text-3xl font-black tracking-tight text-slate-950">{{ number_format($summary['gpa'] ?? 0, 2) }}</span>
            </div>
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr] print:block">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm print:shadow-none">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Subject Breakdown</p>
                    <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Marks by subject</h2>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700">{{ count($subjectResults) }} subjects</span>
            </div>

            <div class="mt-5 overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50/95 backdrop-blur sticky top-0">
                            <tr class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">
                                <th class="px-4 py-3 text-left">Subject</th>
                                <th class="px-4 py-3 text-left">Full</th>
                                <th class="px-4 py-3 text-left">Obtained</th>
                                <th class="px-4 py-3 text-left">%</th>
                                <th class="px-4 py-3 text-left">Grade</th>
                                <th class="px-4 py-3 text-left">Remark</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($subjectResults as $row)
                                <tr>
                                    <td class="px-4 py-3.5">
                                        <p class="font-semibold text-slate-950">{{ $row['subject_code'] ? $row['subject_code'] . ' - ' : '' }}{{ $row['subject_name'] }}</p>
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-600">{{ number_format($row['full_marks'], 2) }}</td>
                                    <td class="px-4 py-3.5 text-slate-600">{{ number_format($row['obtained'], 2) }}</td>
                                    <td class="px-4 py-3.5 text-slate-600">{{ number_format($row['percentage'], 1) }}%</td>
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 bg-slate-50 text-slate-700 ring-slate-200">{{ $row['grade'] }}</span>
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-600">{{ $row['result_status'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6"><x-empty-state title="No subject result rows" message="Marks will appear here after the exam is verified and published."/></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </article>

        <article class="space-y-6 print:mt-6">
            <div class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm print:shadow-none">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Student Profile</p>
                <div class="mt-3 flex items-start gap-4">
                    @if($student->user?->avatar)
                        <img src="{{ asset('storage/' . $student->user->avatar) }}" alt="" class="h-16 w-16 rounded-2xl object-cover ring-2 ring-white">
                    @else
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-[#8B0000] to-rose-700 text-lg font-black text-white">{{ strtoupper(substr($student->user?->name ?? 'S', 0, 1)) }}</div>
                    @endif
                    <div class="min-w-0">
                        <p class="text-2xl font-black tracking-tight text-slate-950">{{ $student->user?->name ?? 'Student' }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $student->student_no ?? 'No student number' }} · {{ $student->registration_number ?? 'No registration number' }}</p>
                        <p class="mt-1 text-sm text-slate-500">Roll: {{ $student->roll_number ?? '—' }} · Symbol: {{ $student->symbol_no ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm print:shadow-none">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Review Summary</p>
                <div class="mt-3 space-y-3 text-sm text-slate-600">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="font-semibold text-slate-900">Performance summary</p>
                        <p class="mt-1 leading-6">{{ $summary['result_status'] ?? 'Pending' }} result with {{ $summary['gpa'] ?? 0 }} GPA and {{ number_format($summary['percentage'] ?? 0, 1) }}% aggregate score.</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="font-semibold text-slate-900">Verification code</p>
                        <p class="mt-1 leading-6">{{ $verificationCode }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="font-semibold text-slate-900">Academic context</p>
                        <p class="mt-1 leading-6">{{ $exam->academicSession?->name_bs ?: $exam->academicSession?->name ?: '—' }} · {{ $exam->department?->name ?? 'Common' }} · Sem {{ $student->current_semester }}</p>
                    </div>
                </div>
            </div>
        </article>
    </section>
</div>
@endsection