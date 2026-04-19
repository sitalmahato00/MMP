@extends('layouts.app')
@section('title', 'Exams & Results')

@section('content')
@php
    $selectedSession = $sessions->firstWhere('id', $filters['sessionId'] ?? null);
    $selectedSessionLabel = $selectedSession?->name_bs ?: $selectedSession?->name ?: 'Current session';
    $selectedDepartment = $departments->firstWhere('id', $filters['departmentId'] ?? null);
    $selectedProgram = $programs->firstWhere('id', $filters['programId'] ?? null);

    $kpiStyles = [
        'blue' => [
            'card' => 'bg-gradient-to-br from-sky-50 to-white border-sky-100',
            'badge' => 'bg-sky-100 text-sky-700',
            'spark' => 'text-sky-600',
        ],
        'amber' => [
            'card' => 'bg-gradient-to-br from-amber-50 to-white border-amber-100',
            'badge' => 'bg-amber-100 text-amber-700',
            'spark' => 'text-amber-600',
        ],
        'violet' => [
            'card' => 'bg-gradient-to-br from-violet-50 to-white border-violet-100',
            'badge' => 'bg-violet-100 text-violet-700',
            'spark' => 'text-violet-600',
        ],
        'emerald' => [
            'card' => 'bg-gradient-to-br from-emerald-50 to-white border-emerald-100',
            'badge' => 'bg-emerald-100 text-emerald-700',
            'spark' => 'text-emerald-600',
        ],
        'rose' => [
            'card' => 'bg-gradient-to-br from-rose-50 to-white border-rose-100',
            'badge' => 'bg-rose-100 text-[#8B0000]',
            'spark' => 'text-[#8B0000]',
        ],
        'slate' => [
            'card' => 'bg-gradient-to-br from-slate-50 to-white border-slate-200',
            'badge' => 'bg-slate-100 text-slate-700',
            'spark' => 'text-slate-700',
        ],
    ];

    $statusClasses = [
        'green' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'orange' => 'bg-orange-50 text-orange-700 ring-orange-200',
        'yellow' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'purple' => 'bg-violet-50 text-violet-700 ring-violet-200',
        'blue' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'slate' => 'bg-slate-100 text-slate-700 ring-slate-200',
    ];

    $filterStatusOptions = [
        '' => 'All statuses',
        'upcoming' => 'Upcoming',
        'ongoing' => 'Ongoing',
        'marks_pending' => 'Marks Pending',
        'verifying' => 'Verifying',
        'published' => 'Published',
    ];
@endphp

<div class="space-y-6">
    <section class="relative overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_24px_70px_rgba(15,23,42,0.07)]">
        <div class="absolute inset-0 bg-gradient-to-br from-rose-50 via-white to-sky-50/60"></div>
        <div class="absolute -right-16 -top-16 h-44 w-44 rounded-full bg-rose-200/30 blur-3xl"></div>
        <div class="absolute -left-16 bottom-0 h-44 w-44 rounded-full bg-sky-200/30 blur-3xl"></div>

        <div class="relative px-6 py-6 sm:px-8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-3xl space-y-3">
                    <div class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-[11px] font-black uppercase tracking-[0.24em] text-[#8B0000]">
                        <i class="fas fa-file-alt"></i>
                        Examinations & Results
                    </div>
                    <div>
                        <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Exams dashboard</h1>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
                            Track the full exam lifecycle from setup and mark entry to verification, publishing, and result sheets.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-700">Session: {{ $selectedSessionLabel }}</span>
                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-700">Department: {{ $selectedDepartment?->name ?? 'All departments' }}</span>
                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-700">Program: {{ $selectedProgram?->name ?? 'All programs' }}</span>
                        <span class="rounded-full bg-rose-50 px-3 py-1.5 text-[#8B0000]">{{ $exams->total() }} records</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.exams.analytics', array_merge(request()->except('page'), [])) }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-sky-200 hover:bg-sky-50/70 hover:text-sky-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3a1 1 0 011 1v3m0 0a1 1 0 01-1 1H7a1 1 0 00-1 1v8a1 1 0 001 1h4a1 1 0 011 1v3m0-17h4a1 1 0 011 1v3m0 0a1 1 0 001 1h4a1 1 0 011 1v8a1 1 0 01-1 1h-4a1 1 0 00-1 1v3"/></svg>
                        Analytics
                    </a>
                    <a href="{{ route('admin.exams.create') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#7a0000]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/></svg>
                        Create Exam
                    </a>
                    <a href="{{ route('admin.exams.export', array_merge(request()->except('page'), ['format' => 'pdf'])) }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-rose-200 hover:bg-rose-50/70 hover:text-[#8B0000]">
                        PDF Export
                    </a>
                    <a href="{{ route('admin.exams.export', array_merge(request()->except('page'), ['format' => 'excel'])) }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50/70 hover:text-emerald-700">
                        Excel Export
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.exams.index') }}" class="mt-6 rounded-[1.5rem] border border-slate-200 bg-white/90 p-4 shadow-sm backdrop-blur">
                <div class="grid gap-3 xl:grid-cols-7">
                    <div class="xl:col-span-2">
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Search</label>
                        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Exam, program, subject, teacher..."
                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-rose-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Session</label>
                        <select name="year" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-rose-100">
                            <option value="">Current session</option>
                            @foreach($sessions as $session)
                                <option value="{{ $session->id }}" @selected(($filters['sessionId'] ?? null) === $session->id)>
                                    {{ $session->name_bs ?: $session->name }}{{ $session->id === ($currentSession?->id ?? null) ? ' · Current' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Department</label>
                        <select name="department_id" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-rose-100">
                            <option value="">All departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" @selected(($filters['departmentId'] ?? null) === $department->id)>
                                    {{ $department->code ? $department->code . ' - ' : '' }}{{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Program</label>
                        <select name="program_id" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-rose-100">
                            <option value="">All programs</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" @selected(($filters['programId'] ?? null) === $program->id)>
                                    {{ $program->code ? $program->code . ' - ' : '' }}{{ $program->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Semester</label>
                        <select name="semester" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-rose-100">
                            <option value="">All semesters</option>
                            @foreach($semesterOptions as $semester)
                                <option value="{{ $semester }}" @selected(($filters['semester'] ?? null) === $semester)>Semester {{ $semester }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Exam Type</label>
                        <select name="category" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-rose-100">
                            <option value="">All exam types</option>
                            @foreach($categoryOptions as $categoryKey => $categoryLabel)
                                <option value="{{ $categoryKey }}" @selected(($filters['category'] ?? null) === $categoryKey)>{{ $categoryLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Status</label>
                        <select name="status" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-rose-100">
                            @foreach($filterStatusOptions as $statusKey => $statusLabel)
                                <option value="{{ $statusKey }}" @selected(($filters['status'] ?? '') === $statusKey)>{{ $statusLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#7a0000]">
                        Apply Filters
                    </button>
                    <a href="{{ route('admin.exams.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                        Clear
                    </a>
                    <span class="text-xs font-semibold text-slate-400">Results adapt to the selected academic session and result workflow state.</span>
                </div>
            </form>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        @foreach($kpis as $kpi)
            @php $tone = $kpiStyles[$kpi['tone']] ?? $kpiStyles['slate']; @endphp
            <article class="rounded-[1.5rem] border {{ $tone['card'] }} p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">{{ $kpi['label'] }}</p>
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ $kpi['value'] }}</p>
                    </div>
                    <div class="rounded-full {{ $tone['badge'] }} px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.18em] whitespace-nowrap">{{ $kpi['note'] }}</div>
                </div>
            </article>
        @endforeach
    </section>

    <section class="rounded-2xl border border-slate-200 bg-slate-50/60 px-6 py-4 flex items-center justify-between gap-4">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Detailed Analytics</p>
            <p class="mt-0.5 text-sm text-slate-600">Department performance, grade distribution, yearly trends and more.</p>
        </div>
        <a href="{{ route('admin.exams.analytics') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.5l6-6 4 4 8-8"/></svg>
            View Charts
        </a>
    </section>

    <section class="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Exam List</p>
                <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Managed exams and result workflow</h2>
            </div>
            <div class="text-xs font-semibold text-slate-400">Open any row to continue scheduling, marks, verification, or publishing.</div>
        </div>

        <div class="overflow-hidden">
            <div class="mmp-table-wrap">
                <table class="mmp-table divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50/95 backdrop-blur sticky top-0">
                        <tr class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">
                            <th class="px-4 py-3 text-left">Exam</th>
                            <th class="px-4 py-3 text-left">Session / Program</th>
                            <th class="px-4 py-3 text-left">Schedule</th>
                            <th class="px-4 py-3 text-left">Completion</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($exams as $exam)
                            @php
                                $statusTone = $statusClasses[$exam['status_tone']] ?? $statusClasses['slate'];
                            @endphp
                            <tr class="group transition hover:bg-slate-50/70">
                                <td class="px-4 py-3.5">
                                    <a href="{{ route('admin.exams.show', $exam['exam']) }}" class="block">
                                        <p class="font-semibold text-slate-950 group-hover:text-[#8B0000]">{{ $exam['name'] }}</p>
                                        <p class="mt-1 text-[11px] text-slate-400">{{ $exam['type_label'] }}</p>
                                    </a>
                                </td>
                                <td class="px-4 py-3.5">
                                    <p class="font-semibold text-slate-900">{{ $exam['exam']->academicSession?->name_bs ?: $exam['exam']->academicSession?->name ?: '—' }}</p>
                                    <p class="mt-1 text-[11px] text-slate-400">{{ $exam['department_label'] }}</p>
                                    <p class="mt-1 text-[11px] text-slate-400">{{ $exam['programs_label'] }}</p>
                                </td>
                                <td class="px-4 py-3.5">
                                    <p class="font-semibold text-slate-900">{{ $exam['start_date_label'] }} - {{ $exam['end_date_label'] }}</p>
                                    <p class="mt-1 text-[11px] text-slate-400">{{ $exam['semester_label'] }}</p>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="space-y-1.5">
                                        <div class="h-2.5 w-32 rounded-full bg-slate-100">
                                            <div class="h-2.5 rounded-full bg-gradient-to-r from-[#8B0000] to-rose-600" style="width: {{ min(100, max(0, $exam['marks_completion'])) }}%"></div>
                                        </div>
                                        <p class="text-xs font-semibold text-slate-600">{{ number_format($exam['marks_completion'], 1) }}% complete · {{ $exam['marks_count'] }} marks</p>
                                        <p class="text-[11px] text-slate-400">Published marks: {{ $exam['published_marks_count'] }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="space-y-1">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $statusTone }}">{{ $exam['status_label'] }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <x-table-actions
                                            :show="route('admin.exams.show', $exam['exam'])"
                                            :edit="route('admin.exams.edit', $exam['exam'])"
                                            :destroy="route('admin.exams.destroy', $exam['exam'])"
                                            name="{{ $exam['name'] }}"
                                        />
                                        @if(! $exam['exam']->isPublishedState)
                                            <form method="POST" action="{{ route('admin.exams.publish', $exam['exam']) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 transition hover:bg-emerald-100">Publish</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-empty-state
                                        title="No exams found"
                                        message="Create the first exam to start the scheduling, mark entry, and publishing workflow."
                                        action="{{ route('admin.exams.create') }}"
                                        actionLabel="Create Exam"/>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="border-t border-slate-100 px-5 py-4">
            {{ $exams->links() }}
        </div>
    </section>
</div>
@endsection