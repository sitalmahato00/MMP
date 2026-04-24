@php
    $heading = $heading ?? 'Studied Subjects';
    $subheading = $subheading ?? 'Use View Subjects on a semester, then View Details on a subject to open the full detail tree.';
@endphp

<section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-5 py-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">{{ $heading }}</h2>
                <p class="mt-0.5 text-xs text-slate-500">{{ $subheading }}</p>
            </div>
            <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-700">
                Semester {{ $currentSemester ?? 'N/A' }}
            </span>
        </div>
    </div>

    <div class="p-4 sm:p-5">
        @if($studiedSemesters->isNotEmpty())
            <div class="space-y-4">
                @foreach($studiedSemesters as $semesterGroup)
                    @php
                        $isCurrentSemester = (int) $semesterGroup['semester'] === (int) ($currentSemester ?? 0);
                        $subjects = collect($semesterGroup['subjects'] ?? []);
                    @endphp

                    <details class="overflow-hidden rounded-xl border {{ $isCurrentSemester ? 'border-blue-200 bg-blue-50/40' : 'border-slate-200 bg-slate-50/70' }}">
                        <summary class="cursor-pointer px-4 py-4" style="list-style: none;">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h3 class="text-sm font-semibold text-slate-900">{{ $semesterGroup['title'] }}</h3>
                                    <p class="mt-0.5 text-xs {{ $isCurrentSemester ? 'text-blue-600' : 'text-slate-500' }}">
                                        {{ $isCurrentSemester ? 'Currently running subjects' : 'Subjects already studied' }}
                                    </p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-md bg-white px-2 py-0.5 text-[11px] font-medium text-slate-600">
                                        {{ $semesterGroup['subject_count'] }} {{ $semesterGroup['subject_count'] === 1 ? 'subject' : 'subjects' }}
                                    </span>
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $isCurrentSemester ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700' }}">
                                        {{ $isCurrentSemester ? 'Current' : 'Completed' }}
                                    </span>
                                    <span class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-1 text-xs font-semibold text-white">
                                        View Subjects
                                    </span>
                                </div>
                            </div>
                        </summary>

                        <div class="border-t border-slate-200 px-4 py-4 sm:px-5">
                            @if($subjects->isNotEmpty())
                                <div class="space-y-3">
                                    @foreach($subjects as $subject)
                                        <details class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                                            <summary class="cursor-pointer px-4 py-3" style="list-style: none;">
                                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                                    <div class="min-w-0 flex-1">
                                                        <p class="truncate text-sm font-semibold text-slate-900">{{ $subject['name'] }}</p>
                                                        <p class="mt-1 text-xs text-slate-500">
                                                            {{ $subject['code'] }} - {{ $subject['type_label'] }} - {{ $subject['credit_hours'] ?? 'N/A' }} credit{{ (int) ($subject['credit_hours'] ?? 0) === 1 ? '' : 's' }}
                                                        </p>
                                                        <p class="mt-1 text-xs text-slate-500">
                                                            Teacher: {{ $subject['teacher_summary'] }}
                                                        </p>
                                                    </div>
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600">
                                                            Sem {{ $subject['semester'] }}
                                                        </span>
                                                        @if(!empty($subject['syllabus_url']))
                                                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">
                                                                Syllabus
                                                            </span>
                                                        @endif
                                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $subject['is_active'] ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                                            {{ $subject['status_label'] }}
                                                        </span>
                                                        <span class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1 text-xs font-semibold text-white">
                                                            View Details
                                                        </span>
                                                    </div>
                                                </div>
                                            </summary>

                                            <div class="border-t border-slate-100 px-4 py-4">
                                                <div class="space-y-5 border-l-2 border-slate-200 pl-4">
                                                    <div class="flex gap-3">
                                                        <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-blue-500"></span>
                                                        <div class="min-w-0 flex-1">
                                                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Subject Overview</p>
                                                            <div class="mt-3 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                                                                <div class="rounded-lg bg-slate-50 px-3 py-2">
                                                                    <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Subject Code</p>
                                                                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $subject['code'] }}</p>
                                                                </div>
                                                                <div class="rounded-lg bg-slate-50 px-3 py-2">
                                                                    <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Type</p>
                                                                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $subject['type_label'] }}</p>
                                                                </div>
                                                                <div class="rounded-lg bg-slate-50 px-3 py-2">
                                                                    <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Credit Hours</p>
                                                                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $subject['credit_hours'] ?? 'N/A' }}</p>
                                                                </div>
                                                                <div class="rounded-lg bg-slate-50 px-3 py-2">
                                                                    <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Status</p>
                                                                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $subject['status_label'] }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="flex gap-3">
                                                        <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-500"></span>
                                                        <div class="min-w-0 flex-1">
                                                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Marking Scheme</p>
                                                            <div class="mt-3 grid gap-3 lg:grid-cols-3">
                                                                <div class="rounded-lg border border-slate-200 bg-slate-50/80 p-3">
                                                                    <div class="flex items-center justify-between">
                                                                        <p class="text-sm font-semibold text-slate-900">Theory</p>
                                                                        @if($subject['has_theory'])
                                                                            <span class="text-xs font-medium text-slate-500">{{ $subject['total_theory_pass_marks'] }}/{{ $subject['total_theory_marks'] }}</span>
                                                                        @endif
                                                                    </div>
                                                                    @if($subject['has_theory'])
                                                                        <div class="mt-3 space-y-2 text-xs text-slate-600">
                                                                            <div class="flex items-center justify-between gap-3">
                                                                                <span>Internal Theory</span>
                                                                                <span class="font-semibold text-slate-900">{{ $subject['pass_marks_internal_theory'] }}/{{ $subject['full_marks_internal_theory'] }}</span>
                                                                            </div>
                                                                            <div class="flex items-center justify-between gap-3">
                                                                                <span>External Theory</span>
                                                                                <span class="font-semibold text-slate-900">{{ $subject['pass_marks_external_theory'] }}/{{ $subject['full_marks_external_theory'] }}</span>
                                                                            </div>
                                                                            <div class="flex items-center justify-between gap-3 border-t border-slate-200 pt-2 text-sm">
                                                                                <span class="font-semibold text-slate-900">Total Theory</span>
                                                                                <span class="font-semibold text-slate-900">{{ $subject['total_theory_pass_marks'] }}/{{ $subject['total_theory_marks'] }}</span>
                                                                            </div>
                                                                        </div>
                                                                    @else
                                                                        <p class="mt-3 text-xs text-slate-500">No theory component assigned for this subject.</p>
                                                                    @endif
                                                                </div>

                                                                <div class="rounded-lg border border-slate-200 bg-slate-50/80 p-3">
                                                                    <div class="flex items-center justify-between">
                                                                        <p class="text-sm font-semibold text-slate-900">Practical</p>
                                                                        @if($subject['has_practical'])
                                                                            <span class="text-xs font-medium text-slate-500">{{ $subject['total_practical_pass_marks'] }}/{{ $subject['total_practical_marks'] }}</span>
                                                                        @endif
                                                                    </div>
                                                                    @if($subject['has_practical'])
                                                                        <div class="mt-3 space-y-2 text-xs text-slate-600">
                                                                            <div class="flex items-center justify-between gap-3">
                                                                                <span>Internal Practical</span>
                                                                                <span class="font-semibold text-slate-900">{{ $subject['pass_marks_internal_practical'] }}/{{ $subject['full_marks_internal_practical'] }}</span>
                                                                            </div>
                                                                            <div class="flex items-center justify-between gap-3">
                                                                                <span>External Practical</span>
                                                                                <span class="font-semibold text-slate-900">{{ $subject['pass_marks_external_practical'] }}/{{ $subject['full_marks_external_practical'] }}</span>
                                                                            </div>
                                                                            <div class="flex items-center justify-between gap-3 border-t border-slate-200 pt-2 text-sm">
                                                                                <span class="font-semibold text-slate-900">Total Practical</span>
                                                                                <span class="font-semibold text-slate-900">{{ $subject['total_practical_pass_marks'] }}/{{ $subject['total_practical_marks'] }}</span>
                                                                            </div>
                                                                        </div>
                                                                    @else
                                                                        <p class="mt-3 text-xs text-slate-500">No practical component assigned for this subject.</p>
                                                                    @endif
                                                                </div>

                                                                <div class="rounded-lg bg-blue-50 px-3 py-3">
                                                                    <p class="text-[11px] font-medium uppercase tracking-wide text-blue-700">Grand Total</p>
                                                                    <p class="mt-1 text-base font-bold text-blue-900">{{ $subject['total_pass_marks'] }}/{{ $subject['total_full_marks'] }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="flex gap-3">
                                                        <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-amber-500"></span>
                                                        <div class="min-w-0 flex-1">
                                                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Program, Subject Details, and Syllabus</p>
                                                            <div class="mt-3 grid gap-3 xl:grid-cols-3">
                                                                <div class="rounded-lg bg-slate-50 px-3 py-3">
                                                                    <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Program Details</p>
                                                                    <div class="mt-2 space-y-1 text-sm text-slate-600">
                                                                        <p><span class="font-medium text-slate-900">Program:</span> {{ $program?->name ?? 'N/A' }}</p>
                                                                        <p><span class="font-medium text-slate-900">Program Code:</span> {{ $program?->code ?? 'N/A' }}</p>
                                                                        <p><span class="font-medium text-slate-900">CTEVT Code:</span> {{ $program?->ctevt_code ?? 'N/A' }}</p>
                                                                        <p><span class="font-medium text-slate-900">Affiliation:</span> {{ $program?->affiliation_type ?? 'N/A' }}</p>
                                                                        <p><span class="font-medium text-slate-900">Duration:</span> {{ $program?->duration_years ?? $program?->duration ?? 'N/A' }} year(s)</p>
                                                                        <p><span class="font-medium text-slate-900">Total Semesters:</span> {{ $program?->total_semesters ?? 'N/A' }}</p>
                                                                    </div>
                                                                </div>

                                                                <div class="rounded-lg bg-slate-50 px-3 py-3">
                                                                    <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Subject Details</p>
                                                                    <div class="mt-2 space-y-3 text-sm text-slate-600">
                                                                        <div>
                                                                            <p class="font-medium text-slate-900">Details</p>
                                                                            <p class="mt-1 whitespace-pre-line text-xs text-slate-500">{{ $subject['details'] ?: 'No subject details added.' }}</p>
                                                                        </div>
                                                                        <div>
                                                                            <p class="font-medium text-slate-900">Teachers</p>
                                                                            <p class="mt-1 text-xs text-slate-500">{{ $subject['teacher_summary'] }}</p>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="rounded-lg bg-slate-50 px-3 py-3">
                                                                    <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Syllabus and Notes</p>
                                                                    <div class="mt-2 space-y-3 text-sm text-slate-600">
                                                                        <div>
                                                                            <p class="font-medium text-slate-900">Subject Syllabus</p>
                                                                            @if(!empty($subject['syllabus_url']))
                                                                                <a href="{{ $subject['syllabus_url'] }}" target="_blank" rel="noopener" class="mt-1 inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                                                                                    View Subject Syllabus
                                                                                </a>
                                                                                @if(!empty($subject['syllabus_name']))
                                                                                    <p class="mt-1 break-all text-[11px] text-slate-500">{{ $subject['syllabus_name'] }}</p>
                                                                                @endif
                                                                            @else
                                                                                <p class="mt-1 text-xs text-slate-500">No subject syllabus uploaded.</p>
                                                                            @endif
                                                                        </div>
                                                                        <div>
                                                                            <p class="font-medium text-slate-900">Program Syllabus</p>
                                                                            @if($program?->syllabus_url)
                                                                                <a href="{{ $program->syllabus_url }}" target="_blank" rel="noopener" class="mt-1 inline-flex items-center rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800">
                                                                                    View Program Syllabus
                                                                                </a>
                                                                            @else
                                                                                <p class="mt-1 text-xs text-slate-500">No program syllabus uploaded.</p>
                                                                            @endif
                                                                        </div>
                                                                        <div>
                                                                            <p class="font-medium text-slate-900">Program Description</p>
                                                                            <p class="mt-1 whitespace-pre-line text-xs text-slate-500">{{ $program?->description ?: 'No description added.' }}</p>
                                                                        </div>
                                                                        <div>
                                                                            <p class="font-medium text-slate-900">Eligibility</p>
                                                                            <p class="mt-1 whitespace-pre-line text-xs text-slate-500">{{ $program?->eligibility ?: 'No eligibility details added.' }}</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="flex gap-3">
                                                        <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-violet-500"></span>
                                                        <div class="min-w-0 flex-1">
                                                            <div class="flex items-center justify-between gap-3">
                                                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Assigned Teachers</p>
                                                                <p class="text-xs text-slate-500">{{ count($subject['teachers'] ?? []) }} assigned</p>
                                                            </div>

                                                            @if(!empty($subject['teachers']))
                                                                <div class="mt-3 space-y-2">
                                                                    @foreach($subject['teachers'] as $teacher)
                                                                        <div class="rounded-lg bg-slate-50 px-3 py-2">
                                                                            <p class="text-sm font-medium text-slate-900">{{ $teacher['name'] }}</p>
                                                                            <p class="mt-0.5 text-xs text-slate-500">
                                                                                {{ $teacher['role'] ?? 'Teacher' }}@if(!empty($teacher['section'])) - Section {{ $teacher['section'] }}@endif
                                                                            </p>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @else
                                                                <p class="mt-3 text-xs text-slate-500">Teacher not assigned yet.</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </details>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-slate-500">No subjects found in this semester.</p>
                            @endif
                        </div>
                    </details>
                @endforeach
            </div>
        @else
            <div class="py-12 text-center">
                <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="mt-2 text-sm text-slate-500">No studied subjects found yet.</p>
            </div>
        @endif
    </div>
</section>
