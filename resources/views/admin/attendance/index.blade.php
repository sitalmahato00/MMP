@extends('layouts.app')
@section('title', 'Attendance')

@section('content')
@php
    $sessionLabel = $selectedSession?->name ?? 'Current session';
    $runningSemestersLabel = $runningSemesters->isNotEmpty() ? $runningSemesters->implode(' · ') : 'No running semesters';

    $toneStyles = [
        'rose' => [
            'card' => 'bg-gradient-to-br from-rose-50 to-white border-rose-100',
            'badge' => 'bg-rose-100 text-[#8B0000]',
            'line' => 'text-[#8B0000] stroke-[#8B0000]',
            'spark' => 'text-[#8B0000]',
        ],
        'blue' => [
            'card' => 'bg-gradient-to-br from-sky-50 to-white border-sky-100',
            'badge' => 'bg-sky-100 text-sky-700',
            'line' => 'text-sky-600 stroke-sky-600',
            'spark' => 'text-sky-600',
        ],
        'emerald' => [
            'card' => 'bg-gradient-to-br from-emerald-50 to-white border-emerald-100',
            'badge' => 'bg-emerald-100 text-emerald-700',
            'line' => 'text-emerald-600 stroke-emerald-600',
            'spark' => 'text-emerald-600',
        ],
        'violet' => [
            'card' => 'bg-gradient-to-br from-violet-50 to-white border-violet-100',
            'badge' => 'bg-violet-100 text-violet-700',
            'line' => 'text-violet-600 stroke-violet-600',
            'spark' => 'text-violet-600',
        ],
        'amber' => [
            'card' => 'bg-gradient-to-br from-amber-50 to-white border-amber-100',
            'badge' => 'bg-amber-100 text-amber-700',
            'line' => 'text-amber-600 stroke-amber-600',
            'spark' => 'text-amber-600',
        ],
        'slate' => [
            'card' => 'bg-gradient-to-br from-slate-50 to-white border-slate-200',
            'badge' => 'bg-slate-100 text-slate-700',
            'line' => 'text-slate-700 stroke-slate-700',
            'spark' => 'text-slate-700',
        ],
    ];

    $statusTone = [
        'done' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'pending' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'late' => 'bg-rose-50 text-[#8B0000] ring-rose-200',
    ];

    $riskTone = [
        'Low' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'Medium' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'High' => 'bg-rose-50 text-[#8B0000] ring-rose-200',
    ];

    $sortUrl = function (string $sortKey) {
        $direction = request('sort') === $sortKey && request('direction', 'desc') === 'desc' ? 'asc' : 'desc';
        return request()->fullUrlWithQuery([
            'sort' => $sortKey,
            'direction' => $direction,
            'tab' => request('tab', 'sessions'),
        ]);
    };
@endphp

<div x-data="{ tab: '{{ request('tab', 'sessions') }}', range: '{{ $filters['dateRange'] }}' }" class="space-y-6">
    {{-- HERO + FILTERS --}}
    <section class="relative overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-rose-50 via-white to-sky-50/50"></div>
        <div class="absolute -right-16 -top-16 h-44 w-44 rounded-full bg-rose-200/30 blur-3xl"></div>
        <div class="absolute -left-16 bottom-0 h-44 w-44 rounded-full bg-sky-200/30 blur-3xl"></div>

        <div class="relative px-6 py-6 sm:px-8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-3xl">
                    <p class="text-[11px] font-black uppercase tracking-[0.28em] text-slate-400">Admin / Principal Portal</p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Attendance</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Monitor department, semester, subject, and teacher attendance in one clean operational view.</p>
                    <div class="mt-4 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-700">Session: {{ $sessionLabel }}</span>
                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-700">Running Semesters: {{ $runningSemestersLabel }}</span>
                        <span class="rounded-full bg-sky-50 px-3 py-1.5 text-sky-700">{{ $window['label'] }}</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="#attendance-rules"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-rose-200 hover:bg-rose-50/70">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L8 21l-1.75-4m0 0L2 14l4-1.75M6.25 14L8 10l1.75 4m0 0L14 15.75 10 17.5m4-1.75L16 21l1.75-4M18.25 14L22 15.75l-4 1.75M14 15.75L12.25 12l1.75-4M12.25 12L16 10.25l4 1.75"/></svg>
                        Configure Rules
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.attendance.index') }}" class="mt-6 rounded-[1.5rem] border border-slate-200 bg-white/85 p-4 shadow-sm backdrop-blur">
                <input type="hidden" name="session_id" value="{{ $selectedSession?->id }}">
                <input type="hidden" name="tab" :value="tab">
                <div class="grid gap-3 xl:grid-cols-7">
                    <div class="xl:col-span-2">
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Search student / teacher</label>
                        <input name="search" value="{{ $filters['search'] }}" placeholder="Search by name, ID, subject..."
                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-rose-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Department</label>
                        <select name="department_id" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-rose-100">
                            <option value="">All departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" @selected($filters['departmentId'] === $department->id)>
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
                                <option value="{{ $program->id }}" @selected($filters['programId'] === $program->id)>
                                    {{ $program->code ? $program->code . ' - ' : '' }}{{ $program->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Semester</label>
                        <select name="semester" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-rose-100">
                            <option value="">All semesters</option>
                            @for($semester = 1; $semester <= 8; $semester++)
                                <option value="{{ $semester }}" @selected($filters['semester'] === $semester)>Semester {{ $semester }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Subject</label>
                        <select name="subject_id" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-rose-100">
                            <option value="">All subjects</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected($filters['subjectId'] === $subject->id)>
                                    S{{ $subject->semester }} · {{ $subject->code ? $subject->code . ' - ' : '' }}{{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Teacher</label>
                        <select name="teacher_id" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-rose-100">
                            <option value="">All teachers</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected($filters['teacherId'] === $teacher->id)>
                                    {{ $teacher->user?->name }}{{ $teacher->designation ? ' · ' . $teacher->designation : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Date range</label>
                        <select name="date_range" x-model="range" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-rose-100">
                            @foreach($rangeOptions as $option)
                                <option value="{{ $option['value'] }}" @selected($filters['dateRange'] === $option['value'])>{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div x-show="range === 'custom'" x-cloak class="mt-3 grid gap-3 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">From</label>
                        <x-bs-date-picker name="date_from_bs" :value="$filters['dateFromBs']" placeholder="YYYY-MM-DD"/>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">To</label>
                        <x-bs-date-picker name="date_to_bs" :value="$filters['dateToBs']" placeholder="YYYY-MM-DD"/>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#7a0000]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v4l-7 4v8l-4-2v-6L3 8V4z"/></svg>
                        Apply Filters
                    </button>
                    <a href="{{ route('admin.attendance.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                        Clear
                    </a>
                    <span class="text-xs font-semibold text-slate-400">Showing attendance through {{ $window['label'] }}</span>
                </div>
            </form>
        </div>
    </section>

    {{-- KPI STRIP --}}
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        @foreach($kpis as $kpi)
            @php $tone = $toneStyles[$kpi['tone']] ?? $toneStyles['slate']; @endphp
            <article class="rounded-[1.5rem] border {{ $tone['card'] }} p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">{{ $kpi['label'] }}</p>
                        <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ $kpi['value'] }}</p>
                        <div class="mt-1 flex items-center gap-2 text-xs font-semibold {{ $kpi['direction'] === 'up' ? 'text-emerald-600' : ($kpi['direction'] === 'down' ? 'text-rose-600' : 'text-slate-400') }}">
                            <span>{{ $kpi['direction'] === 'down' ? '↓' : '↑' }}</span>
                            <span>{{ $kpi['trend'] }}</span>
                            <span class="text-slate-400">{{ $kpi['note'] }}</span>
                        </div>
                    </div>
                    <div class="rounded-full {{ $tone['badge'] }} px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.18em]">{{ $kpi['note'] }}</div>
                </div>
            </article>
        @endforeach
    </section>

    <section class="space-y-6">
        <article class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-end justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Teacher-wise Completion</p>
                        <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Teacher reliability</h2>
                    </div>
                    <span class="rounded-full bg-sky-50 px-3 py-1.5 text-xs font-bold text-sky-700">{{ $teacherRows->count() }} teachers</span>
                </div>
                <div class="mt-4 overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm">
                    <div class="max-h-[320px] overflow-auto">
                        <table class="min-w-full divide-y divide-slate-100 text-sm">
                            <thead class="sticky top-0 bg-slate-50/95 backdrop-blur">
                                <tr class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">
                                    <th class="px-4 py-3 text-left">Teacher</th>
                                    <th class="px-4 py-3 text-left">Sessions</th>
                                    <th class="px-4 py-3 text-left">Completed</th>
                                    <th class="px-4 py-3 text-left">Pending</th>
                                    <th class="px-4 py-3 text-left">Reliability</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($teacherRows as $row)
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                @if($row['avatar'])
                                                    <img src="{{ asset('storage/' . $row['avatar']) }}" alt="" class="h-10 w-10 rounded-xl object-cover ring-2 ring-slate-100">
                                                @else
                                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-slate-700 to-slate-900 text-xs font-black text-white">
                                                        {{ strtoupper(substr($row['name'], 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div class="min-w-0">
                                                    <p class="font-bold text-slate-900">{{ $row['name'] }}</p>
                                                    <p class="truncate text-[11px] text-slate-400">{{ $row['department'] ?? 'Unassigned department' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">{{ $row['total_sessions'] }}</td>
                                        <td class="px-4 py-3 text-emerald-600">{{ $row['completed_sessions'] }}</td>
                                        <td class="px-4 py-3 text-rose-600">{{ $row['pending_sessions'] }}</td>
                                        <td class="px-4 py-3 font-bold text-slate-900">{{ number_format($row['reliability'], 1) }}%</td>
                                        <td class="px-4 py-3">
                                            @if($row['id'])
                                                <a href="{{ route('admin.teachers.show', ['teacher' => $row['id'], 'tab' => 'attendance']) }}"
                                                   class="rounded-full px-3 py-1 text-[11px] font-bold ring-1 {{ $row['reliability'] >= 90 ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : ($row['reliability'] >= 75 ? 'bg-amber-50 text-amber-700 ring-amber-200' : 'bg-rose-50 text-[#8B0000] ring-rose-200') }}">
                                                    {{ $row['status'] }}
                                                </a>
                                            @else
                                                <span class="rounded-full px-3 py-1 text-[11px] font-bold ring-1 {{ $row['reliability'] >= 90 ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : ($row['reliability'] >= 75 ? 'bg-amber-50 text-amber-700 ring-amber-200' : 'bg-rose-50 text-[#8B0000] ring-rose-200') }}">
                                                    {{ $row['status'] }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-400">No teacher attendance data yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
        </article>
    </section>

    {{-- TABS / TABLES --}}
    <section class="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
            <div class="flex items-center gap-2">
                <button type="button" @click="tab = 'sessions'"
                        :class="tab === 'sessions' ? 'bg-[#8B0000] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="rounded-xl px-4 py-2.5 text-sm font-bold transition">Attendance Sessions</button>
                <button type="button" @click="tab = 'students'"
                        :class="tab === 'students' ? 'bg-[#8B0000] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="rounded-xl px-4 py-2.5 text-sm font-bold transition">Student Monitoring</button>
                <button type="button" @click="tab = 'rules'"
                        :class="tab === 'rules' ? 'bg-[#8B0000] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="rounded-xl px-4 py-2.5 text-sm font-bold transition">Rules & Alerts</button>
            </div>
            <div class="text-xs font-semibold text-slate-400">Bulk export and quick filtering built in</div>
        </div>

        {{-- Sessions table --}}
        <div x-show="tab === 'sessions'" x-cloak class="p-5">
            <div class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="sticky top-0 bg-slate-50/95 backdrop-blur">
                            <tr class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">
                                <th class="px-4 py-3 text-left"><a href="{{ $sortUrl('date') }}" class="inline-flex items-center gap-1 hover:text-slate-700">Date @if(request('sort') === 'date')<span>{{ request('direction', 'desc') === 'asc' ? '↑' : '↓' }}</span>@endif</a></th>
                                <th class="px-4 py-3 text-left"><a href="{{ $sortUrl('teacher') }}" class="inline-flex items-center gap-1 hover:text-slate-700">Teacher @if(request('sort') === 'teacher')<span>{{ request('direction', 'desc') === 'asc' ? '↑' : '↓' }}</span>@endif</a></th>
                                <th class="px-4 py-3 text-left">Department</th>
                                <th class="px-4 py-3 text-left">Program</th>
                                <th class="px-4 py-3 text-left"><a href="{{ $sortUrl('semester') }}" class="inline-flex items-center gap-1 hover:text-slate-700">Semester @if(request('sort') === 'semester')<span>{{ request('direction', 'desc') === 'asc' ? '↑' : '↓' }}</span>@endif</a></th>
                                <th class="px-4 py-3 text-left">Subject</th>
                                <th class="px-4 py-3 text-left">Class time</th>
                                <th class="px-4 py-3 text-left">Present</th>
                                <th class="px-4 py-3 text-left">Absent</th>
                                <th class="px-4 py-3 text-left">Completion</th>
                                <th class="px-4 py-3 text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($attendanceSessions as $session)
                                @php
                                    $completionStatus = $session->records_count > 0 ? 'done' : ($session->date->isPast() ? 'late' : 'pending');
                                    $teacherName = $session->teacher?->user?->name ?? 'Unassigned';
                                    $teacherAvatar = $session->teacher?->user?->avatar;
                                @endphp
                                <tr class="group transition hover:bg-slate-50/70">
                                    <td class="px-4 py-3.5">
                                        <p class="font-semibold text-slate-900">{{ bsDate($session->date, 'Y, F d') ?: '—' }}</p>
                                        <p class="text-[11px] text-slate-400">{{ bsDate($session->date, 'D') ?: ($session->date?->format('D') ?? '—') }}</p>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-3">
                                            @if($teacherAvatar)
                                                <img src="{{ asset('storage/' . $teacherAvatar) }}" alt="" class="h-9 w-9 rounded-xl object-cover ring-2 ring-slate-100">
                                            @else
                                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-slate-700 to-slate-900 text-xs font-black text-white">{{ strtoupper(substr($teacherName, 0, 1)) }}</div>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="font-semibold text-slate-900">{{ $teacherName }}</p>
                                                <p class="truncate text-[11px] text-slate-400">{{ $session->teacher?->designation ?? 'Teacher' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-600">{{ $session->program?->department?->name ?? $session->program?->department?->code ?? '—' }}</td>
                                    <td class="px-4 py-3.5 text-slate-600">{{ $session->program?->name ?? '—' }}</td>
                                    <td class="px-4 py-3.5">
                                        <span class="rounded-full bg-violet-50 px-2.5 py-1 text-[11px] font-bold text-violet-700 ring-1 ring-violet-100">Sem {{ $session->semester }}</span>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <p class="font-semibold text-slate-900">{{ $session->subject?->name ?? '—' }}</p>
                                        <p class="text-[11px] text-slate-400">{{ $session->subject?->code }}</p>
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-600">{{ $session->period ?? 'Period not set' }}</td>
                                    <td class="px-4 py-3.5 font-semibold text-emerald-600">{{ $session->present_records_count ?? 0 }}</td>
                                    <td class="px-4 py-3.5 font-semibold text-rose-600">{{ $session->absent_records_count ?? 0 }}</td>
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $statusTone[$completionStatus] ?? $statusTone['pending'] }}">
                                            {{ $completionStatus === 'done' ? 'Done' : ($completionStatus === 'late' ? 'Late' : 'Pending') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <a href="{{ route('admin.attendance.sessions.show', $session) }}"
                                           class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-[#8B0000]/30 hover:text-[#8B0000]">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-6 py-16 text-center">
                                        <div class="mx-auto max-w-md">
                                            <p class="text-base font-bold text-slate-700">No attendance sessions found</p>
                                            <p class="mt-1 text-sm text-slate-400">Try a wider date range or clear the filters to review class attendance.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-4">
                {{ $attendanceSessions->links() }}
            </div>
        </div>

        {{-- Student monitoring table --}}
        <div x-show="tab === 'students'" x-cloak class="p-5">
            <div class="overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="sticky top-0 bg-slate-50/95 backdrop-blur">
                            <tr class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">
                                <th class="px-4 py-3 text-left">Student</th>
                                <th class="px-4 py-3 text-left">Program</th>
                                <th class="px-4 py-3 text-left">Semester</th>
                                <th class="px-4 py-3 text-left">Attendance %</th>
                                <th class="px-4 py-3 text-left">Absent</th>
                                <th class="px-4 py-3 text-left">Risk Level</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($studentRows as $row)
                                <tr class="group transition hover:bg-slate-50/70">
                                    <td class="px-4 py-3.5">
                                        <a href="{{ route('admin.students.show', ['student' => $row['id'], 'tab' => 'attendance']) }}" class="flex items-center gap-3">
                                            @if($row['avatar'])
                                                <img src="{{ asset('storage/' . $row['avatar']) }}" alt="" class="h-10 w-10 rounded-xl object-cover ring-2 ring-slate-100">
                                            @else
                                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-[#8B0000] to-rose-700 text-xs font-black text-white">{{ strtoupper(substr($row['name'], 0, 1)) }}</div>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="font-semibold text-slate-900">{{ $row['name'] }}</p>
                                                <p class="truncate text-[11px] text-slate-400">{{ $row['student']->student_no ?? $row['student']->roll_number ?? 'No student number' }}</p>
                                            </div>
                                        </a>
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-600">{{ $row['program'] ?? '—' }}</td>
                                    <td class="px-4 py-3.5">
                                        <span class="rounded-full bg-sky-50 px-2.5 py-1 text-[11px] font-bold text-sky-700 ring-1 ring-sky-100">Sem {{ $row['semester'] }}</span>
                                    </td>
                                    <td class="px-4 py-3.5 font-bold text-slate-900">{{ number_format($row['attendance_rate'], 1) }}%</td>
                                    <td class="px-4 py-3.5 text-slate-600">{{ $row['absent'] }}</td>
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $riskTone[$row['risk']] ?? $riskTone['Low'] }}">
                                            {{ $row['risk'] }} risk
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <div class="mx-auto max-w-md">
                                            <p class="text-base font-bold text-slate-700">No student attendance rows yet</p>
                                            <p class="mt-1 text-sm text-slate-400">Attendance summaries will appear once classes are marked.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-4">
                {{ $studentRows->links() }}
            </div>
        </div>

        {{-- Rules tab --}}
        <div x-show="tab === 'rules'" x-cloak class="p-5 space-y-6" id="attendance-rules">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach($rules['cards'] as $card)
                    @php $tone = $toneStyles[$card['tone']] ?? $toneStyles['slate']; @endphp
                    <article class="rounded-[1.5rem] border {{ $tone['card'] }} p-5 shadow-sm">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">{{ $card['title'] }}</p>
                        <p class="mt-3 text-3xl font-black tracking-tight text-slate-950">{{ $card['value'] }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ $card['note'] }}</p>
                    </article>
                @endforeach
            </div>

            <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
                <article class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Detection summary</p>
                            <h3 class="mt-1 text-xl font-black text-slate-950">Abnormal pattern detection</h3>
                        </div>
                        <span class="rounded-full bg-rose-50 px-3 py-1.5 text-xs font-bold text-[#8B0000]">Automated</span>
                    </div>
                    <div class="mt-4 space-y-3">
                        @foreach($rules['alerts'] as $alert)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-sm font-bold text-slate-900">{{ $alert['title'] }}</p>
                                <p class="mt-1 text-sm leading-6 text-slate-600">{{ $alert['message'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </article>

                <article class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Operating notes</p>
                            <h3 class="mt-1 text-xl font-black text-slate-950">Late warnings and timetable sync</h3>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">CTEVT style</span>
                    </div>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4">
                            <p class="text-sm font-bold text-emerald-800">Auto-sync with timetable</p>
                            <p class="mt-1 text-sm leading-6 text-emerald-700">Attendance sessions are expected to originate from timetable blocks, keeping semester rotation aligned.</p>
                        </div>
                        <div class="rounded-2xl border border-amber-100 bg-amber-50/70 p-4">
                            <p class="text-sm font-bold text-amber-800">Late attendance warning</p>
                            <p class="mt-1 text-sm leading-6 text-amber-700">Marking delays are surfaced as pending or late sessions so the principal can follow up quickly.</p>
                        </div>
                        <div class="rounded-2xl border border-sky-100 bg-sky-50/70 p-4 md:col-span-2">
                            <p class="text-sm font-bold text-sky-800">Multi-semester support</p>
                            <p class="mt-1 text-sm leading-6 text-sky-700">Running semesters can coexist in one academic session, including delayed or restarted exam cycles.</p>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>
</div>
@endsection

