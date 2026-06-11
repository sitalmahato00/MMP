@extends('layouts.app')
@section('title', 'Exams & Results')

@section('content')
@php
    $selectedSession = $sessions->firstWhere('id', $filters['sessionId'] ?? null);
    $selectedSessionLabel = $selectedSession?->name_bs ?: $selectedSession?->name ?: 'Current session';

    $statusClasses = [
        'green' => 'bg-emerald-50 text-emerald-700',
        'orange' => 'bg-orange-50 text-orange-700',
        'yellow' => 'bg-amber-50 text-amber-700',
        'purple' => 'bg-violet-50 text-violet-700',
        'blue' => 'bg-sky-50 text-sky-700',
        'slate' => 'bg-slate-100 text-slate-700',
    ];
@endphp

<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Exams & Results</h1>
            <p class="mt-0.5 text-sm text-slate-500">Track exam lifecycle from setup to mark entry, verification, and publishing.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.exams.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-[#7a0000] transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/></svg>
                Create Exam
            </a>
            <a href="{{ route('admin.exams.export', array_merge(request()->except('page'), ['format' => 'pdf'])) }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition">
                PDF Export
            </a>
            <a href="{{ route('admin.exams.export', array_merge(request()->except('page'), ['format' => 'excel'])) }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition">
                Excel Export
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    @php
    $examGrads = ['135deg,#7C3AED,#A855F7','135deg,#10B981,#22C55E','135deg,#DC2626,#EF4444','135deg,#F97316,#FB923C','135deg,#2563EB,#3B82F6','135deg,#0F766E,#14B8A6'];
    $examIcons = ['M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4','M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z','M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636','M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z','M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z','M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'];
    @endphp
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-6">
        @foreach($kpis as $i => $kpi)
            <div class="relative overflow-hidden rounded-2xl p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                 style="background: linear-gradient({{ $examGrads[$i % count($examGrads)] }});">
                <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
                <div class="pointer-events-none absolute -bottom-3 -left-3 h-14 w-14 rounded-full bg-white/5"></div>
                <div class="relative">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-white/20 backdrop-blur-sm">
                            <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $examIcons[$i % count($examIcons)] }}"/>
                            </svg>
                        </div>
                        <p class="text-lg font-black leading-tight text-white">{{ $kpi['value'] }}</p>
                    </div>
                    <p class="mt-1.5 text-[11px] font-semibold uppercase tracking-wider text-white/80 truncate">{{ $kpi['label'] }}</p>
                    @if(!empty($kpi['note']))
                        <p class="mt-0.5 text-[10px] text-white/60 truncate">{{ $kpi['note'] }}</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.exams.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            <div class="relative xl:col-span-2">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search exam, program, subject..." 
                       class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
            </div>

            <select name="year" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                <option value="">Current Session</option>
                @foreach($sessions as $session)
                    <option value="{{ $session->id }}" @selected(($filters['sessionId'] ?? null) === $session->id)>
                        {{ $session->name_bs ?: $session->name }}{{ $session->id === ($currentSession?->id ?? null) ? ' · Current' : '' }}
                    </option>
                @endforeach
            </select>

            <select name="department_id" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                <option value="">All Departments</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" @selected(($filters['departmentId'] ?? null) === $department->id)>
                        {{ $department->code ? $department->code . ' - ' : '' }}{{ $department->name }}
                    </option>
                @endforeach
            </select>

            <select name="program_id" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                <option value="">All Programs</option>
                @foreach($programs as $program)
                    <option value="{{ $program->id }}" @selected(($filters['programId'] ?? null) === $program->id)>
                        {{ $program->code ? $program->code . ' - ' : '' }}{{ $program->name }}
                    </option>
                @endforeach
            </select>

            <select name="semester" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                <option value="">All Semesters</option>
                @foreach($semesterOptions as $semester)
                    <option value="{{ $semester }}" @selected(($filters['semester'] ?? null) === $semester)>Semester {{ $semester }}</option>
                @endforeach
            </select>

            <select name="category" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                <option value="">All Exam Types</option>
                @foreach($categoryOptions as $categoryKey => $categoryLabel)
                    <option value="{{ $categoryKey }}" @selected(($filters['category'] ?? null) === $categoryKey)>{{ $categoryLabel }}</option>
                @endforeach
            </select>

            <select name="status" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                <option value="">All Statuses</option>
                <option value="upcoming" @selected(($filters['status'] ?? '') === 'upcoming')>Upcoming</option>
                <option value="ongoing" @selected(($filters['status'] ?? '') === 'ongoing')>Ongoing</option>
                <option value="marks_pending" @selected(($filters['status'] ?? '') === 'marks_pending')>Marks Pending</option>
                <option value="verifying" @selected(($filters['status'] ?? '') === 'verifying')>Verifying</option>
                <option value="published" @selected(($filters['status'] ?? '') === 'published')>Published</option>
            </select>
        </div>

        <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-500">
                Session: <span class="font-semibold text-slate-700">{{ $selectedSessionLabel }}</span>
            </p>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="rounded-xl bg-[#8B0000] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#7a0000] shadow-sm">Apply Filters</button>
                <a href="{{ route('admin.exams.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-500 transition hover:bg-slate-50">Reset</a>
            </div>
        </div>
    </form>

    {{-- Exams Table --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Exam List</h2>
                <p class="text-sm text-slate-500">Manage exams and result workflow</p>
            </div>
            <div class="text-sm text-slate-500">{{ $exams->total() }} exams</div>
        </div>

        <div class="mmp-table-wrap">
            <table class="mmp-table divide-y divide-slate-200 text-left">
                <thead class="bg-slate-50/80">
                    <tr class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">
                        <th class="px-6 py-4">Exam</th>
                        <th class="px-6 py-4">Session</th>
                        <th class="px-6 py-4">Program</th>
                        <th class="px-6 py-4">Schedule</th>
                        <th class="px-6 py-4">Completion</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($exams as $exam)
                        @php
                            $statusTone = $statusClasses[$exam['status_tone']] ?? $statusClasses['slate'];
                        @endphp
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="px-6 py-5">
                                <div class="font-semibold text-slate-900">{{ $exam['name'] }}</div>
                                <div class="text-sm text-slate-500">{{ $exam['type_label'] }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="font-semibold text-slate-900">{{ $exam['exam']->academicSession?->name_bs ?: $exam['exam']->academicSession?->name ?: '—' }}</div>
                                <div class="text-sm text-slate-500">{{ $exam['semester_label'] }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="text-slate-600">{{ $exam['department_label'] }}</div>
                                <div class="text-sm text-slate-500">{{ $exam['programs_label'] }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="font-semibold text-slate-900">{{ $exam['start_date_label'] }}</div>
                                <div class="text-sm text-slate-500">to {{ $exam['end_date_label'] }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="h-2 w-24 rounded-full bg-slate-100">
                                    <div class="h-2 rounded-full bg-[#8B0000]" style="width: {{ min(100, max(0, $exam['marks_completion'])) }}%"></div>
                                </div>
                                <div class="mt-1 text-xs text-slate-600">{{ number_format($exam['marks_completion'], 1) }}% · {{ $exam['marks_count'] }} marks</div>
                            </td>
                            <td class="px-6 py-5">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusTone }}">{{ $exam['status_label'] }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.exams.show', $exam['exam']) }}"
                                       class="rounded-full border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">
                                        View
                                    </a>
                                    <a href="{{ route('admin.exams.edit', $exam['exam']) }}"
                                       class="rounded-full border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">
                                        Edit
                                    </a>
                                    @if(! $exam['exam']->isPublishedState)
                                        <form method="POST" action="{{ route('admin.exams.publish', $exam['exam']) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-full border border-emerald-300 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                                Publish
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-sm text-slate-500">
                                No exams found. Create the first exam to start the workflow.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 px-6 py-4">
            {{ $exams->links() }}
        </div>
    </div>
</div>
@endsection
