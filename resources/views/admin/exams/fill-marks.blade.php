@extends('layouts.app')

@section('title', 'Fill Marks - ' . ($subject?->name ?? $exam->name))

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.exams.show', $exam) }}" class="text-sm font-semibold text-slate-400 hover:text-slate-600">
                    &larr; {{ $exam->name }}
                </a>
            </div>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                Fill Marks: {{ $subject?->name ?? 'Select Subject' }}
            </h1>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                Academic Session: {{ $exam->academicSession?->name ?? 'Current' }} &bull; Type: {{ ucfirst($exam->exam_type ?? $exam->type ?? 'Exam') }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($subject)
                <a href="{{ route('admin.exams.subjects.marks', ['exam' => $exam->id, 'subject' => $subject->id]) }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                    View Entered Marks
                </a>
            @endif
            <a href="{{ route('admin.exams.show', $exam) }}"
               class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300">
                Back to Exam
            </a>
        </div>
    </div>

    {{-- Exam / Program / Semester / Subject Selector --}}
    <form method="GET" action="{{ route('admin.exams.fill-marks') }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Exam</label>
                <select name="exam_id" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    @foreach($allExams as $ex)
                        <option value="{{ $ex->id }}" {{ $exam->id == $ex->id ? 'selected' : '' }}>
                            {{ $ex->name }} ({{ $ex->academicSession?->name_bs ?: $ex->academicSession?->name ?: 'Session' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Program</label>
                <select name="program_id" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    @foreach($programs as $prog)
                        <option value="{{ $prog->id }}" {{ $programId == $prog->id ? 'selected' : '' }}>
                            {{ $prog->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Semester</label>
                <select name="semester" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    @for($s = 1; $s <= 6; $s++)
                        <option value="{{ $s }}" {{ $semester == $s ? 'selected' : '' }}>
                            Semester {{ $s }}
                        </option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Subject</label>
                <select name="subject_id" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    @forelse($subjects as $sub)
                        <option value="{{ $sub->id }}" {{ $subjectId == $sub->id ? 'selected' : '' }}>
                            {{ $sub->code ? '[' . $sub->code . '] ' : '' }}{{ $sub->name }}
                        </option>
                    @empty
                        <option value="">No subjects found for semester {{ $semester }}</option>
                    @endforelse
                </select>
            </div>
        </div>
    </form>

    @if($subject)
        @php
            $isMonthlyAssessment = ($exam->category ?? 'ctevt_final') === 'monthly_assessment';
            $scheme = \Illuminate\Support\Facades\DB::table('exam_subject_marking_schemes')
                ->where('exam_id', $exam->id)
                ->where('subject_id', $subject->id)
                ->first();

            $fullIntTheory = $scheme->full_marks_internal_theory ?? $subject->full_marks_internal_theory ?? 0;
            $passIntTheory = $scheme->pass_marks_internal_theory ?? $subject->pass_marks_internal_theory ?? 0;
            $fullExtTheory = $scheme->full_marks_external_theory ?? $subject->full_marks_external_theory ?? 0;
            $passExtTheory = $scheme->pass_marks_external_theory ?? $subject->pass_marks_external_theory ?? 0;

            $fullIntPractical = $scheme->full_marks_internal_practical ?? $subject->full_marks_internal_practical ?? 0;
            $passIntPractical = $scheme->pass_marks_internal_practical ?? $subject->pass_marks_internal_practical ?? 0;
            $fullExtPractical = $scheme->full_marks_external_practical ?? $subject->full_marks_external_practical ?? 0;
            $passExtPractical = $scheme->pass_marks_external_practical ?? $subject->pass_marks_external_practical ?? 0;

            $assessmentFull = $exam->assessment_full_marks ?? 100;
            $assessmentPass = $exam->assessment_pass_marks ?? 40;
            $hasExistingCount = $existingMarks->count();
        @endphp

        {{-- Scheme Details --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-xs font-bold text-[#8B0000] dark:bg-red-950/40">
                        {{ $subject->code ?? 'SUB' }}
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ $subject->name }}</h3>
                        <p class="text-xs text-slate-400">
                            @if($isMonthlyAssessment)
                                Monthly Assessment &bull; Assessment #{{ $exam->assessment_number ?? '1' }}
                            @else
                                {{ ucfirst($subject->type ?? 'Theory') }} &bull; Marking Scheme Limits
                            @endif
                        </p>
                    </div>
                </div>
                @if($hasExistingCount > 0)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        Editing Mode ({{ $hasExistingCount }} of {{ $students->count() }} marks recorded)
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 ring-1 ring-amber-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        New Entry Mode
                    </span>
                @endif
            </div>

            @if($isMonthlyAssessment)
                <div class="mt-3 grid grid-cols-2 gap-4 text-xs sm:grid-cols-3">
                    <div class="rounded-xl bg-slate-50 p-2.5 dark:bg-slate-800/60">
                        <p class="font-bold text-slate-500">Assessment Full Marks</p>
                        <p class="mt-1 text-sm font-black text-slate-900 dark:text-white">{{ $assessmentFull }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-2.5 dark:bg-slate-800/60">
                        <p class="font-bold text-slate-500">Assessment Pass Marks</p>
                        <p class="mt-1 text-sm font-black text-slate-900 dark:text-white">{{ $assessmentPass }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-2.5 dark:bg-slate-800/60">
                        <p class="font-bold text-slate-500">Attendance Weightage</p>
                        <p class="mt-1 text-sm font-black text-slate-900 dark:text-white">Tracked (0 - 100%)</p>
                    </div>
                </div>
            @else
                <div class="mt-3 grid grid-cols-2 gap-4 text-xs sm:grid-cols-4">
                    <div class="rounded-xl bg-slate-50 p-2.5 dark:bg-slate-800/60">
                        <p class="font-bold text-slate-500">Internal Theory</p>
                        <p class="mt-1 text-sm font-black text-slate-900 dark:text-white">Full: {{ $fullIntTheory }} &bull; Pass: {{ $passIntTheory }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-2.5 dark:bg-slate-800/60">
                        <p class="font-bold text-slate-500">External Theory</p>
                        <p class="mt-1 text-sm font-black text-slate-900 dark:text-white">Full: {{ $fullExtTheory }} &bull; Pass: {{ $passExtTheory }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-2.5 dark:bg-slate-800/60">
                        <p class="font-bold text-slate-500">Internal Practical</p>
                        <p class="mt-1 text-sm font-black text-slate-900 dark:text-white">Full: {{ $fullIntPractical }} &bull; Pass: {{ $passIntPractical }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-2.5 dark:bg-slate-800/60">
                        <p class="font-bold text-slate-500">External Practical</p>
                        <p class="mt-1 text-sm font-black text-slate-900 dark:text-white">Full: {{ $fullExtPractical }} &bull; Pass: {{ $passExtPractical }}</p>
                    </div>
                </div>
            @endif
        </div>

        @if($students->isNotEmpty())
            <form method="POST" action="{{ route('admin.exams.save-marks') }}" x-data="adminMarksForm()">
                @csrf
                <input type="hidden" name="exam_id" value="{{ $exam->id }}">
                <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                <input type="hidden" name="program_id" value="{{ $programId }}">
                <input type="hidden" name="semester" value="{{ $semester }}">

                {{-- Action Bar --}}
                <div class="flex items-center justify-between rounded-xl bg-slate-100 p-3 dark:bg-slate-800">
                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">
                        {{ $students->count() }} enrolled student(s)
                    </span>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="markAllPresent()"
                                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                            Mark All Present
                        </button>
                        <button type="button" @click="markAllAbsent()"
                                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200">
                            Mark All Absent
                        </button>
                    </div>
                </div>

                {{-- Table --}}
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                            <thead class="border-b border-slate-100 bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:border-slate-800 dark:bg-slate-800/70">
                                <tr>
                                    <th class="px-4 py-3 sticky left-0 bg-slate-50 z-10 dark:bg-slate-800">Student</th>
                                    @if($isMonthlyAssessment)
                                        <th class="px-3 py-3 text-center">Obtained Marks<br><span class="text-[10px] lowercase text-slate-400">(max {{ $assessmentFull }})</span></th>
                                        <th class="px-3 py-3 text-center">Attendance %<br><span class="text-[10px] lowercase text-slate-400">(optional)</span></th>
                                    @else
                                        <th class="px-3 py-3 text-center">Int Theory<br><span class="text-[10px] lowercase text-slate-400">(max {{ $fullIntTheory }})</span></th>
                                        <th class="px-3 py-3 text-center">Ext Theory<br><span class="text-[10px] lowercase text-slate-400">(max {{ $fullExtTheory }})</span></th>
                                        <th class="px-3 py-3 text-center">Int Practical<br><span class="text-[10px] lowercase text-slate-400">(max {{ $fullIntPractical }})</span></th>
                                        <th class="px-3 py-3 text-center">Ext Practical<br><span class="text-[10px] lowercase text-slate-400">(max {{ $fullExtPractical }})</span></th>
                                    @endif
                                    <th class="px-3 py-3">Remarks</th>
                                    <th class="px-3 py-3 text-center">Absent?</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach($students as $index => $student)
                                    @php
                                        $existingMark = $existingMarks->get($student->id);
                                    @endphp
                                    <tr id="student-{{ $student->id }}" class="hover:bg-slate-50 transition dark:hover:bg-slate-800/50" :class="students[{{ $index }}].isAbsent ? 'bg-red-50/40 dark:bg-red-950/20' : ''">
                                        <td class="px-4 py-3 sticky left-0 bg-white z-10 dark:bg-slate-900" :class="students[{{ $index }}].isAbsent ? 'bg-red-50/40 dark:bg-red-950/20' : ''">
                                            <input type="hidden" name="marks[{{ $index }}][student_id]" value="{{ $student->id }}">
                                            <div class="font-bold text-slate-900 dark:text-white">{{ $student->user?->name ?? 'N/A' }}</div>
                                            <div class="text-xs text-slate-400">Roll: {{ $student->roll_number ?? '-' }} &bull; Reg: {{ $student->registration_number ?? '-' }}</div>
                                        </td>
                                        @if($isMonthlyAssessment)
                                            <td class="px-3 py-3 text-center">
                                                <input type="number" step="0.01" min="0" max="{{ $assessmentFull }}"
                                                       name="marks[{{ $index }}][assessment_obtained_marks]"
                                                       value="{{ $existingMark ? $existingMark->assessment_obtained_marks : '' }}"
                                                       :disabled="students[{{ $index }}].isAbsent"
                                                       placeholder="0.00"
                                                       class="w-28 rounded-lg border border-slate-200 px-2 py-1.5 text-center text-sm font-semibold focus:border-red-500 focus:outline-none disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:disabled:bg-slate-800/40">
                                            </td>
                                            <td class="px-3 py-3 text-center">
                                                <input type="number" step="0.01" min="0" max="100"
                                                       name="marks[{{ $index }}][assessment_attendance_percent]"
                                                       value="{{ $existingMark ? $existingMark->assessment_attendance_percent : '' }}"
                                                       :disabled="students[{{ $index }}].isAbsent"
                                                       placeholder="%"
                                                       class="w-24 rounded-lg border border-slate-200 px-2 py-1.5 text-center text-sm font-semibold focus:border-red-500 focus:outline-none disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:disabled:bg-slate-800/40">
                                            </td>
                                        @else
                                            <td class="px-3 py-3">
                                                <input type="number" step="0.01" min="0" max="{{ $fullIntTheory }}"
                                                       name="marks[{{ $index }}][internal_theory_marks]"
                                                       value="{{ $existingMark ? $existingMark->internal_theory_marks : '' }}"
                                                       :disabled="students[{{ $index }}].isAbsent"
                                                       placeholder="0.00"
                                                       class="w-24 rounded-lg border border-slate-200 px-2 py-1.5 text-center text-sm font-semibold focus:border-red-500 focus:outline-none disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:disabled:bg-slate-800/40">
                                            </td>
                                            <td class="px-3 py-3">
                                                <input type="number" step="0.01" min="0" max="{{ $fullExtTheory }}"
                                                       name="marks[{{ $index }}][external_theory_marks]"
                                                       value="{{ $existingMark ? $existingMark->external_theory_marks : '' }}"
                                                       :disabled="students[{{ $index }}].isAbsent"
                                                       placeholder="0.00"
                                                       class="w-24 rounded-lg border border-slate-200 px-2 py-1.5 text-center text-sm font-semibold focus:border-red-500 focus:outline-none disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:disabled:bg-slate-800/40">
                                            </td>
                                            <td class="px-3 py-3">
                                                <input type="number" step="0.01" min="0" max="{{ $fullIntPractical }}"
                                                       name="marks[{{ $index }}][internal_practical_marks]"
                                                       value="{{ $existingMark ? $existingMark->internal_practical_marks : '' }}"
                                                       :disabled="students[{{ $index }}].isAbsent"
                                                       placeholder="0.00"
                                                       class="w-24 rounded-lg border border-slate-200 px-2 py-1.5 text-center text-sm font-semibold focus:border-red-500 focus:outline-none disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:disabled:bg-slate-800/40">
                                            </td>
                                            <td class="px-3 py-3">
                                                <input type="number" step="0.01" min="0" max="{{ $fullExtPractical }}"
                                                       name="marks[{{ $index }}][external_practical_marks]"
                                                       value="{{ $existingMark ? $existingMark->external_practical_marks : '' }}"
                                                       :disabled="students[{{ $index }}].isAbsent"
                                                       placeholder="0.00"
                                                       class="w-24 rounded-lg border border-slate-200 px-2 py-1.5 text-center text-sm font-semibold focus:border-red-500 focus:outline-none disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:disabled:bg-slate-800/40">
                                            </td>
                                        @endif
                                        <td class="px-3 py-3">
                                            <input type="text"
                                                   name="marks[{{ $index }}][remarks]"
                                                   value="{{ $existingMark ? $existingMark->remarks : '' }}"
                                                   :disabled="students[{{ $index }}].isAbsent"
                                                   placeholder="Optional remarks"
                                                   class="w-full min-w-[140px] rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs focus:border-red-500 focus:outline-none disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:disabled:bg-slate-800/40">
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <input type="checkbox"
                                                   name="marks[{{ $index }}][is_absent]"
                                                   value="1"
                                                   x-model="students[{{ $index }}].isAbsent"
                                                   {{ $existingMark && $existingMark->is_absent ? 'checked' : '' }}
                                                   class="h-4 w-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Submit Buttons --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="rounded-xl bg-[#8B0000] px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#6b0000] transition">
                        Save Marks
                    </button>
                    <a href="{{ route('admin.exams.show', $exam) }}"
                       class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                        Cancel
                    </a>
                </div>
            </form>
        @else
            <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-base font-bold text-slate-800 dark:text-slate-200">No students enrolled</p>
                <p class="mt-1 text-sm text-slate-400">There are no students enrolled in Semester {{ $semester }} for this program.</p>
            </div>
        @endif
    @else
        <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-base font-bold text-slate-800 dark:text-slate-200">Please choose a subject</p>
            <p class="mt-1 text-sm text-slate-400">Select a program, semester, and subject from above to start entering marks.</p>
        </div>
    @endif
</div>

@push('scripts')
<script>
function adminMarksForm() {
    return {
        students: @json($students->map(function($student) use ($existingMarks) {
            $existingMark = $existingMarks->get($student->id);
            return [
                'isAbsent' => $existingMark ? (bool)$existingMark->is_absent : false
            ];
        })->values()),

        markAllPresent() {
            this.students.forEach(s => s.isAbsent = false);
        },

        markAllAbsent() {
            if (confirm('Mark all students as absent?')) {
                this.students.forEach(s => s.isAbsent = true);
            }
        }
    };
}
</script>
@endpush
@endsection
