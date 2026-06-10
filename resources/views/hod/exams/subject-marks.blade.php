@extends('layouts.app')

@section('title', 'Subject Marks - ' . $subject->name)

@section('content')
<x-page-header
    title="Subject Marks — {{ $subject->name }}"
    subtitle="{{ $exam->name }} • {{ $subject->code }} • Semester {{ $subject->semester }}"
    back="{{ route('hod.exams.marks', ['exam_id' => $exam->id, 'subject_id' => $subject->id]) }}" />

<div class="space-y-6">
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Total students</p>
            <p class="mt-3 text-3xl font-black text-slate-950">{{ number_format($summary['total_students']) }}</p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Marks entered</p>
            <p class="mt-3 text-3xl font-black text-slate-950">{{ number_format($summary['marks_entered']) }}</p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Passed</p>
            <p class="mt-3 text-3xl font-black text-slate-950">{{ number_format($summary['passed']) }}</p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Failed / missing</p>
            <p class="mt-3 text-3xl font-black text-slate-950">{{ number_format($summary['failed'] + $summary['missing_marks']) }}</p>
        </article>
    </section>

    <section class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900">Student marks for this subject</h2>
            <p class="mt-1 text-sm text-slate-500">All active students in {{ $subject->program?->name ?? 'the program' }} for semester {{ $subject->semester }}.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('hod.exams.export-marks', ['exam_id' => $exam->id, 'format' => 'excel']) }}?subject_id={{ $subject->id }}"
               class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                Excel
            </a>
            <a href="{{ route('hod.exams.export-marks', ['exam_id' => $exam->id, 'format' => 'pdf']) }}?subject_id={{ $subject->id }}"
               target="_blank"
               class="inline-flex items-center gap-2 rounded-xl bg-red-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-800">
                PDF
            </a>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Student ID</th>
                        <th class="px-4 py-3">Student</th>
                        <th class="px-4 py-3">Program</th>
                        @if($exam->category === 'monthly_assessment')
                            <th class="px-4 py-3 text-right">Attendance %</th>
                        @endif
                        <th class="px-4 py-3 text-right">Obtained</th>
                        <th class="px-4 py-3 text-center">Result</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($studentRows as $row)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-4 text-right text-slate-700">{{ $row['student']->roll_number ?? $row['student']->student_no ?? '—' }}</td>
                            <td class="px-4 py-4">
                                <div class="font-semibold text-slate-900">{{ $row['student']->user?->name ?? 'Unknown student' }}</div>
                                <div class="text-xs text-slate-500">{{ $row['student']->user?->email ?? '' }}</div>
                            </td>
                            <td class="px-4 py-4 text-slate-700">{{ $row['student']->program?->name ?? '' }}</td>
                            @if($exam->category === 'monthly_assessment')
                                <td class="px-4 py-4 text-right text-slate-700">{{ $row['attendance_percent_label'] ?? '—' }}</td>
                            @endif
                            <td class="px-4 py-4 text-right text-slate-700">{{ $row['obtained'] !== null ? number_format($row['obtained'], 1) : '—' }}</td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $row['result'] === 'Pass' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : ($row['result'] === 'Fail' ? 'bg-rose-50 text-[#8B0000] ring-rose-200' : 'bg-amber-50 text-amber-700 ring-amber-200') }} ring-1">
                                    {{ $row['result'] }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $row['status'] === 'Missing' ? 'bg-slate-100 text-slate-600' : ($row['status'] === 'Draft' ? 'bg-slate-100 text-slate-600' : ($row['status'] === 'Submitted' ? 'bg-blue-50 text-blue-700' : ($row['status'] === 'Approved' ? 'bg-emerald-50 text-emerald-700' : 'bg-green-50 text-emerald-700'))) }} ring-1">
                                    {{ $row['status'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-500">
                                No student marks available for this subject yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
