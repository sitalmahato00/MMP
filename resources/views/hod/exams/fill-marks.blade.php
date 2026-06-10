@extends('layouts.app')

@section('title', 'Fill Marks - ' . $subject->name)

@section('content')
<x-page-header 
    :title="'Fill Marks - ' . $subject->name" 
    :subtitle="$exam->name . ' • ' . $exam->category_label"
    back="{{ route('hod.exams.fill-marks', ['exam_id' => $exam->id]) }}"/>

{{-- Subject Marking Scheme Info --}}
<section class="rounded-xl border border-slate-200/80 bg-white shadow-sm p-4 mb-6">
    <div class="flex items-start gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600 flex-shrink-0">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="flex-1">
            <h3 class="text-sm font-semibold text-slate-900">Marking Scheme</h3>
            @if($exam->category === 'monthly_assessment')
                <p class="text-xs text-slate-600 mt-1">
                    <span class="font-medium">Full Marks:</span> {{ $exam->assessment_full_marks ?? 100 }} • 
                    <span class="font-medium">Pass Marks:</span> {{ $exam->assessment_pass_marks ?? 40 }}
                </p>
            @else
                <div class="grid grid-cols-2 gap-4 mt-2 text-xs">
                    <div>
                        <p class="font-medium text-slate-700">Theory</p>
                        <p class="text-slate-600">Internal: {{ $subject->full_marks_internal_theory ?? 0 }} (Pass: {{ $subject->pass_marks_internal_theory ?? 0 }})</p>
                        <p class="text-slate-600">External: {{ $subject->full_marks_external_theory ?? 0 }} (Pass: {{ $subject->pass_marks_external_theory ?? 0 }})</p>
                    </div>
                    <div>
                        <p class="font-medium text-slate-700">Practical</p>
                        <p class="text-slate-600">Internal: {{ $subject->full_marks_internal_practical ?? 0 }} (Pass: {{ $subject->pass_marks_internal_practical ?? 0 }})</p>
                        <p class="text-slate-600">External: {{ $subject->full_marks_external_practical ?? 0 }} (Pass: {{ $subject->pass_marks_external_practical ?? 0 }})</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>

{{-- Quick Actions --}}
<section class="rounded-xl border border-slate-200/80 bg-white shadow-sm p-4 mb-6" x-data="marksForm()">
    <div class="flex flex-wrap items-center gap-3">
        <button type="button" @click="markAllPresent()"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Mark All Present
        </button>
        <button type="button" @click="markAllAbsent()"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Mark All Absent
        </button>
    </div>
</section>

{{-- Marks Table Form --}}
<form method="POST" action="{{ route('hod.exams.save-marks') }}" x-data="marksForm()">
    @csrf
    <input type="hidden" name="exam_id" value="{{ $exam->id }}">
    <input type="hidden" name="subject_id" value="{{ $subject->id }}">
    <input type="hidden" name="program_id" value="{{ $programId }}">
    <input type="hidden" name="semester" value="{{ $semester }}">

    <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 text-xs font-medium text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left sticky left-0 bg-slate-50 z-10">Student</th>
                        @if($exam->category === 'monthly_assessment')
                            <th class="px-4 py-3 text-center">
                                <div>Attendance %</div>
                                <div class="text-[10px] text-slate-400 normal-case">(During Exam)</div>
                            </th>
                            <th class="px-4 py-3 text-center">
                                <div>Obtained Marks</div>
                                <div class="text-[10px] text-slate-400 normal-case">Full: {{ $exam->assessment_full_marks ?? 100 }} / Pass: {{ $exam->assessment_pass_marks ?? 40 }}</div>
                            </th>
                        @else
                            <th class="px-4 py-3 text-center">
                                <div>Internal Theory</div>
                                <div class="text-[10px] text-slate-400 normal-case">
                                    @php
                                        $scheme = DB::table('exam_subject_marking_schemes')
                                            ->where('exam_id', $exam->id)
                                            ->where('subject_id', $subject->id)
                                            ->first();
                                        $fullIntTheory = $scheme->full_marks_internal_theory ?? $subject->full_marks_internal_theory ?? 0;
                                        $passIntTheory = $scheme->pass_marks_internal_theory ?? $subject->pass_marks_internal_theory ?? 0;
                                    @endphp
                                    Full: {{ $fullIntTheory }} / Pass: {{ $passIntTheory }}
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center">
                                <div>External Theory</div>
                                <div class="text-[10px] text-slate-400 normal-case">
                                    @php
                                        $fullExtTheory = $scheme->full_marks_external_theory ?? $subject->full_marks_external_theory ?? 0;
                                        $passExtTheory = $scheme->pass_marks_external_theory ?? $subject->pass_marks_external_theory ?? 0;
                                    @endphp
                                    Full: {{ $fullExtTheory }} / Pass: {{ $passExtTheory }}
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center">
                                <div>Internal Practical</div>
                                <div class="text-[10px] text-slate-400 normal-case">
                                    @php
                                        $fullIntPractical = $scheme->full_marks_internal_practical ?? $subject->full_marks_internal_practical ?? 0;
                                        $passIntPractical = $scheme->pass_marks_internal_practical ?? $subject->pass_marks_internal_practical ?? 0;
                                    @endphp
                                    Full: {{ $fullIntPractical }} / Pass: {{ $passIntPractical }}
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center">
                                <div>External Practical</div>
                                <div class="text-[10px] text-slate-400 normal-case">
                                    @php
                                        $fullExtPractical = $scheme->full_marks_external_practical ?? $subject->full_marks_external_practical ?? 0;
                                        $passExtPractical = $scheme->pass_marks_external_practical ?? $subject->pass_marks_external_practical ?? 0;
                                    @endphp
                                    Full: {{ $fullExtPractical }} / Pass: {{ $passExtPractical }}
                                </div>
                            </th>
                            </th>
                        @endif
                        <th class="px-4 py-3 text-left">Remarks</th>
                        <th class="px-4 py-3 text-center">Absent</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($students as $index => $student)
                        @php
                            $existingMark = $existingMarks->get($student->id);
                        @endphp
                        <tr class="hover:bg-slate-50" :class="students[{{ $index }}].isAbsent ? 'bg-red-50/30' : ''">
                            <td class="px-4 py-3 sticky left-0 bg-white z-10" :class="students[{{ $index }}].isAbsent ? 'bg-red-50/30' : ''">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-600 flex-shrink-0">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-slate-900 truncate">{{ $student->user->name }}</p>
                                        <p class="text-xs text-slate-500">Roll: {{ $student->roll_number }}</p>
                                    </div>
                                </div>
                                <input type="hidden" name="marks[{{ $index }}][student_id]" value="{{ $student->id }}">
                            </td>

                            @if($exam->category === 'monthly_assessment')
                                {{-- Attendance Percentage --}}
                                <td class="px-4 py-3">
                                    <input type="number"
                                           name="marks[{{ $index }}][assessment_attendance_percent]"
                                           value="{{ $existingMark ? $existingMark->assessment_attendance_percent : '' }}"
                                           step="0.1" min="0" max="100"
                                           placeholder="0.0"
                                           :disabled="students[{{ $index }}].isAbsent"
                                           class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm text-center disabled:bg-slate-50 disabled:text-slate-400">
                                </td>
                                {{-- Obtained Marks --}}
                                <td class="px-4 py-3">
                                    <input type="number" 
                                           name="marks[{{ $index }}][assessment_obtained_marks]" 
                                           value="{{ $existingMark ? $existingMark->assessment_obtained_marks : '' }}"
                                           step="0.01" min="0" max="{{ $exam->assessment_full_marks ?? 100 }}"
                                           placeholder="0.00"
                                           :disabled="students[{{ $index }}].isAbsent"
                                           class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm text-center disabled:bg-slate-50 disabled:text-slate-400">
                                </td>
                            @else
                                {{-- CTEVT Marks --}}
                                <td class="px-4 py-3">
                                    <input type="number" 
                                           name="marks[{{ $index }}][internal_theory_marks]" 
                                           value="{{ $existingMark ? $existingMark->internal_theory_marks : '' }}"
                                           step="0.01" min="0" max="{{ $fullIntTheory }}"
                                           placeholder="0.00"
                                           :disabled="students[{{ $index }}].isAbsent"
                                           class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm text-center disabled:bg-slate-50 disabled:text-slate-400">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" 
                                           name="marks[{{ $index }}][external_theory_marks]" 
                                           value="{{ $existingMark ? $existingMark->external_theory_marks : '' }}"
                                           step="0.01" min="0" max="{{ $fullExtTheory }}"
                                           placeholder="0.00"
                                           :disabled="students[{{ $index }}].isAbsent"
                                           class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm text-center disabled:bg-slate-50 disabled:text-slate-400">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" 
                                           name="marks[{{ $index }}][internal_practical_marks]" 
                                           value="{{ $existingMark ? $existingMark->internal_practical_marks : '' }}"
                                           step="0.01" min="0" max="{{ $fullIntPractical }}"
                                           placeholder="0.00"
                                           :disabled="students[{{ $index }}].isAbsent"
                                           class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm text-center disabled:bg-slate-50 disabled:text-slate-400">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" 
                                           name="marks[{{ $index }}][external_practical_marks]" 
                                           value="{{ $existingMark ? $existingMark->external_practical_marks : '' }}"
                                           step="0.01" min="0" max="{{ $fullExtPractical }}"
                                           placeholder="0.00"
                                           :disabled="students[{{ $index }}].isAbsent"
                                           class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm text-center disabled:bg-slate-50 disabled:text-slate-400">
                                </td>
                            @endif

                            {{-- Remarks --}}
                            <td class="px-4 py-3">
                                <input type="text" 
                                       name="marks[{{ $index }}][remarks]" 
                                       value="{{ $existingMark ? $existingMark->remarks : '' }}"
                                       placeholder="Optional..."
                                       :disabled="students[{{ $index }}].isAbsent"
                                       class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm disabled:bg-slate-50 disabled:text-slate-400">
                            </td>

                            {{-- Absent Checkbox --}}
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" 
                                       name="marks[{{ $index }}][is_absent]" 
                                       value="1"
                                       x-model="students[{{ $index }}].isAbsent"
                                       {{ $existingMark && $existingMark->is_absent ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Submit Actions --}}
    <div class="flex items-center gap-3 pb-6">
        <x-btn type="submit">Save Marks</x-btn>
        <x-btn href="{{ route('hod.exams.fill-marks', ['exam_id' => $exam->id]) }}" variant="secondary">Back to Selection</x-btn>
    </div>
</form>

@push('scripts')
<script>
function marksForm() {
    return {
        students: @json($students->map(function($student, $index) use ($existingMarks) {
            $existingMark = $existingMarks->get($student->id);
            return [
                'isAbsent' => $existingMark ? (bool)$existingMark->is_absent : false
            ];
        })->values()),
        
        markAllPresent() {
            this.students.forEach((student, index) => {
                student.isAbsent = false;
            });
        },
        
        markAllAbsent() {
            if (confirm('Mark all students as absent?')) {
                this.students.forEach((student, index) => {
                    student.isAbsent = true;
                });
            }
        }
    }
}
</script>
@endpush
@endsection
