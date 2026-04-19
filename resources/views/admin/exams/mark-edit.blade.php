@extends('layouts.app')
@section('title', 'Edit Mark')

@section('content')
@php
    $subject = $mark->subject;
    $student = $mark->student;
    $isMonthlyAssessment = ($exam->category ?? 'ctevt_final') === 'monthly_assessment';
    $hasPractical = ! $isMonthlyAssessment && (bool) ($subject?->hasPractical());
    $resultState = old('result_state', $mark->is_absent ? 'absent' : ($mark->is_withheld ? 'withheld' : 'normal'));
    $resultTone = match ($mark->result_remark ?? 'Pending') {
        'Pass' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'Fail' => 'bg-rose-50 text-[#8B0000] ring-rose-200',
        'Absent' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'Withheld' => 'bg-slate-100 text-slate-700 ring-slate-200',
        default => 'bg-slate-100 text-slate-700 ring-slate-200',
    };
@endphp

<div class="space-y-6">
    <section class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-white px-6 py-8 shadow-[0_24px_70px_rgba(15,23,42,0.08)] sm:px-8">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(139,0,0,0.14),_transparent_38%),radial-gradient(circle_at_bottom_left,_rgba(59,130,246,0.10),_transparent_30%)]"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl space-y-3">
                <div class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-[#8B0000]">
                    <i class="fas fa-pen-to-square"></i>
                    Mark correction
                </div>
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Edit mark record</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
                        Adjust the student score components for this subject only. Exam details, session, and subject assignment remain locked.
                    </p>
                </div>
            </div>
            <div class="grid gap-3 sm:grid-cols-3 lg:w-[32rem]">
                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                    <div class="text-xs uppercase tracking-[0.2em] text-slate-500">Student</div>
                    <div class="mt-2 text-sm font-semibold text-slate-900">{{ $student?->user?->name ?? 'Student' }}</div>
                </div>
                <div class="rounded-2xl border border-sky-200 bg-sky-50/80 p-4">
                    <div class="text-xs uppercase tracking-[0.2em] text-sky-600">Subject</div>
                    <div class="mt-2 text-sm font-semibold text-sky-900">{{ $subject?->code ? $subject->code . ' - ' : '' }}{{ $subject?->name ?? 'Subject' }}</div>
                </div>
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4">
                    <div class="text-xs uppercase tracking-[0.2em] text-emerald-600">Current result</div>
                    <div class="mt-2 text-sm font-semibold text-emerald-900">{{ $mark->result_remark }}</div>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.08fr_0.92fr]">
        <form method="POST" action="{{ route('admin.exams.marks.update', [$exam, $mark]) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.24em] text-slate-400">Score components</p>
                        <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">Update this mark only</h2>
                        <p class="mt-1 text-sm text-slate-500">The exam header and workflow stay unchanged.</p>
                    </div>
                    <span class="rounded-full {{ $resultTone }} px-3 py-1.5 text-xs font-bold">{{ $mark->result_remark }}</span>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    @if($isMonthlyAssessment)
                        <div class="md:col-span-2 rounded-2xl border border-sky-200 bg-sky-50/70 p-4 text-sm text-sky-900">
                            This exam uses the Monthly Assessment format. Fill attendance, pass marks, full marks, and obtained marks below.
                        </div>
                    @else
                        <label class="space-y-2">
                            <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Internal theory marks</span>
                            <input type="number" name="internal_theory_marks" step="0.01" min="0" value="{{ old('internal_theory_marks', $mark->internal_theory_marks) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                            @error('internal_theory_marks')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </label>

                        <label class="space-y-2">
                            <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">External theory marks</span>
                            <input type="number" name="external_theory_marks" step="0.01" min="0" value="{{ old('external_theory_marks', $mark->external_theory_marks) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                            @error('external_theory_marks')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </label>

                        @if($hasPractical)
                            <label class="space-y-2">
                                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Internal practical marks</span>
                                <input type="number" name="internal_practical_marks" step="0.01" min="0" value="{{ old('internal_practical_marks', $mark->internal_practical_marks) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                                @error('internal_practical_marks')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                            </label>

                            <label class="space-y-2">
                                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">External practical marks</span>
                                <input type="number" name="external_practical_marks" step="0.01" min="0" value="{{ old('external_practical_marks', $mark->external_practical_marks) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                                @error('external_practical_marks')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                            </label>
                        @else
                            <div class="md:col-span-2 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                                This subject has no practical component.
                            </div>
                        @endif
                    @endif

                    <label class="md:col-span-2 flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                        <input type="checkbox" name="is_delayed" value="1" @checked(old('is_delayed', $mark->is_delayed)) class="mt-1 h-4 w-4 rounded border-slate-300 text-[#8B0000] focus:ring-rose-200">
                        <div>
                            <p class="font-semibold text-slate-900">Mark entry is delayed</p>
                            <p class="mt-0.5 text-sm text-slate-500">Enable when external/practical marks are delayed and not yet final.</p>
                        </div>
                    </label>

                    <label class="space-y-2 md:col-span-2">
                        <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Delay reason</span>
                        <textarea name="delay_reason" rows="2" placeholder="Optional reason for delayed marks" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">{{ old('delay_reason', $mark->delay_reason) }}</textarea>
                        @error('delay_reason')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </label>

                    @if($isMonthlyAssessment)
                        <label class="space-y-2">
                            <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Attendance %</span>
                            <input type="number" name="assessment_attendance_percent" step="0.01" min="0" max="100" value="{{ old('assessment_attendance_percent', $mark->assessment_attendance_percent) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                            @error('assessment_attendance_percent')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </label>
                        <label class="space-y-2">
                            <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Assessment full marks</span>
                            <input type="number" name="assessment_full_marks" step="0.01" min="0" value="{{ old('assessment_full_marks', $mark->assessment_full_marks) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                            @error('assessment_full_marks')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </label>
                        <label class="space-y-2">
                            <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Assessment pass marks</span>
                            <input type="number" name="assessment_pass_marks" step="0.01" min="0" value="{{ old('assessment_pass_marks', $mark->assessment_pass_marks) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                            @error('assessment_pass_marks')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </label>
                        <label class="space-y-2">
                            <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Assessment obtained marks</span>
                            <input type="number" name="assessment_obtained_marks" step="0.01" min="0" value="{{ old('assessment_obtained_marks', $mark->assessment_obtained_marks) }}" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                            @error('assessment_obtained_marks')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </label>
                    @endif

                    <label class="space-y-2 md:col-span-2">
                        <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Result state</span>
                        <select name="result_state" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">
                            <option value="normal" @selected($resultState === 'normal')>Normal</option>
                            <option value="absent" @selected($resultState === 'absent')>Absent</option>
                            <option value="withheld" @selected($resultState === 'withheld')>Withheld</option>
                        </select>
                        @error('result_state')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </label>

                    <label class="space-y-2 md:col-span-2">
                        <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Remarks</span>
                        <textarea name="remarks" rows="4" placeholder="Optional correction note" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-[#8B0000] focus:bg-white focus:ring-2 focus:ring-rose-100">{{ old('remarks', $mark->remarks) }}</textarea>
                        @error('remarks')<p class="text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    </label>
                </div>
            </article>

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-[#8B0000] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#7a0000]">
                    Save Mark
                </button>
                <a href="{{ route('admin.exams.show', ['exam' => $exam, 'tab' => 'marks']) }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                    Back to marks
                </a>
            </div>
        </form>

        <aside class="space-y-6">
            <article class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.24em] text-slate-400">Record context</p>
                <div class="mt-4 space-y-3 text-sm text-slate-600">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Exam</p>
                        <p class="mt-1 font-semibold text-slate-900">{{ $exam->name }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Student</p>
                        <p class="mt-1 font-semibold text-slate-900">{{ $student?->user?->name ?? 'Student' }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $student?->student_no ?? '—' }} · Roll {{ $student?->roll_number ?? '—' }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Subject</p>
                        <p class="mt-1 font-semibold text-slate-900">{{ $subject?->code ? $subject->code . ' - ' : '' }}{{ $subject?->name ?? 'Subject' }}</p>
                        <p class="mt-1 text-sm text-slate-500">Sem {{ $subject?->semester ?? '—' }} · {{ $subject?->hasPractical() ? 'Theory + practical' : 'Theory only' }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Current score</p>
                        <p class="mt-1 font-semibold text-slate-900">{{ number_format((float) $mark->total_marks, 2) }} / {{ number_format((float) ($isMonthlyAssessment ? ($mark->assessment_full_marks ?? 0) : ($subject?->totalFullMarks ?? 0)), 2) }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ isset($mark->percentage) ? number_format((float) $mark->percentage, 1) . '%' : 'No percentage' }} · {{ $mark->result_remark }}</p>
                    </div>
                </div>
            </article>

            <article class="rounded-[2rem] border border-slate-200 bg-gradient-to-br from-[#8B0000] to-rose-700 p-6 text-white shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.24em] text-white/70">Correction note</p>
                <h3 class="mt-2 text-2xl font-black tracking-tight">Only the mark record changes here.</h3>
                <p class="mt-2 text-sm leading-6 text-white/75">Exam header data, programs, and publish settings stay untouched.</p>
            </article>
        </aside>
    </section>
</div>
@endsection