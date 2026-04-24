@extends('layouts.app')
@section('title', 'Attendance')

@section('content')
@php
    $sessionLabel = $selectedSession?->name ?? 'Current session';
    $statusTone = [
        'done' => 'bg-emerald-50 text-emerald-700',
        'pending' => 'bg-amber-50 text-amber-700',
        'late' => 'bg-rose-50 text-rose-700',
    ];
@endphp

<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Attendance</h1>
            <p class="mt-0.5 text-sm text-slate-500">Monitor attendance sessions, teacher completion, and student records.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.attendance.index', ['export' => 'csv']) }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export CSV
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-6">
        @foreach($kpis as $kpi)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold text-slate-500">{{ $kpi['label'] }}</p>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ $kpi['value'] }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ $kpi['note'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.attendance.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <input type="hidden" name="session_id" value="{{ $selectedSession?->id }}">
        
        <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            <div class="relative xl:col-span-2">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Search by name, ID, subject..." 
                       class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
            </div>

            <select name="department_id" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                <option value="">All Departments</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" @selected($filters['departmentId'] === $department->id)>
                        {{ $department->code ? $department->code . ' - ' : '' }}{{ $department->name }}
                    </option>
                @endforeach
            </select>

            <select name="program_id" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                <option value="">All Programs</option>
                @foreach($programs as $program)
                    <option value="{{ $program->id }}" @selected($filters['programId'] === $program->id)>
                        {{ $program->code ? $program->code . ' - ' : '' }}{{ $program->name }}
                    </option>
                @endforeach
            </select>

            <select name="semester" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                <option value="">All Semesters</option>
                @for($semester = 1; $semester <= 8; $semester++)
                    <option value="{{ $semester }}" @selected($filters['semester'] === $semester)>Semester {{ $semester }}</option>
                @endfor
            </select>

            <select name="teacher_id" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                <option value="">All Teachers</option>
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}" @selected($filters['teacherId'] === $teacher->id)>
                        {{ $teacher->user?->name }}
                    </option>
                @endforeach
            </select>

            <select name="date_range" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                @foreach($rangeOptions as $option)
                    <option value="{{ $option['value'] }}" @selected($filters['dateRange'] === $option['value'])>{{ $option['label'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-500">
                Session: <span class="font-semibold text-slate-700">{{ $sessionLabel }}</span>
            </p>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="rounded-xl bg-[#8B0000] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#7a0000] shadow-sm">Apply Filters</button>
                <a href="{{ route('admin.attendance.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-500 transition hover:bg-slate-50">Reset</a>
            </div>
        </div>
    </form>

    {{-- Teacher Reliability --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Teacher Reliability</h2>
                <p class="text-sm text-slate-500">Attendance completion by teacher</p>
            </div>
            <div class="text-sm text-slate-500">{{ $teacherRows->count() }} teachers</div>
        </div>

        <div class="mmp-table-wrap">
            <table class="mmp-table divide-y divide-slate-200 text-left">
                <thead class="bg-slate-50/80">
                    <tr class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">
                        <th class="px-6 py-4">Teacher</th>
                        <th class="px-6 py-4">Sessions</th>
                        <th class="px-6 py-4">Completed</th>
                        <th class="px-6 py-4">Pending</th>
                        <th class="px-6 py-4">Reliability</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($teacherRows as $row)
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    @if($row['avatar'])
                                        <img src="{{ asset('storage/' . $row['avatar']) }}" alt="" class="h-10 w-10 rounded-xl object-cover">
                                    @else
                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-slate-700 to-slate-900 text-xs font-black text-white">
                                            {{ strtoupper(substr($row['name'], 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-semibold text-slate-900">{{ $row['name'] }}</div>
                                        <div class="text-sm text-slate-500">{{ $row['department'] ?? 'Unassigned' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-slate-600">{{ $row['total_sessions'] }}</td>
                            <td class="px-6 py-5 text-emerald-600 font-semibold">{{ $row['completed_sessions'] }}</td>
                            <td class="px-6 py-5 text-rose-600 font-semibold">{{ $row['pending_sessions'] }}</td>
                            <td class="px-6 py-5 font-bold text-slate-900">{{ number_format($row['reliability'], 1) }}%</td>
                            <td class="px-6 py-5">
                                <div class="flex justify-end">
                                    @if($row['id'])
                                        <a href="{{ route('admin.teachers.show', ['teacher' => $row['id'], 'tab' => 'attendance']) }}"
                                           class="rounded-full border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">
                                            View Details
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-sm text-slate-500">No teacher attendance data yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    {{-- Attendance Sessions --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Attendance Sessions</h2>
                <p class="text-sm text-slate-500">All class attendance records</p>
            </div>
            <div class="text-sm text-slate-500">{{ $attendanceSessions->total() }} sessions</div>
        </div>

        <div class="mmp-table-wrap">
            <table class="mmp-table divide-y divide-slate-200 text-left">
                <thead class="bg-slate-50/80">
                    <tr class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Teacher</th>
                        <th class="px-6 py-4">Program</th>
                        <th class="px-6 py-4">Semester</th>
                        <th class="px-6 py-4">Subject</th>
                        <th class="px-6 py-4">Present</th>
                        <th class="px-6 py-4">Absent</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendanceSessions as $session)
                        @php
                            $completionStatus = $session->records_count > 0 ? 'done' : ($session->date->isPast() ? 'late' : 'pending');
                            $teacherName = $session->teacher?->user?->name ?? 'Unassigned';
                        @endphp
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="px-6 py-5">
                                <div class="font-semibold text-slate-900">{{ bsDate($session->date, 'F d, Y') ?: '—' }}</div>
                                <div class="text-sm text-slate-500">{{ bsDate($session->date, 'l') ?: '—' }}</div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="font-semibold text-slate-900">{{ $teacherName }}</div>
                                <div class="text-sm text-slate-500">{{ $session->teacher?->designation ?? 'Teacher' }}</div>
                            </td>
                            <td class="px-6 py-5 text-slate-600">{{ $session->program?->name ?? '—' }}</td>
                            <td class="px-6 py-5">
                                <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">Sem {{ $session->semester }}</span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="font-semibold text-slate-900">{{ $session->subject?->name ?? '—' }}</div>
                                <div class="text-sm text-slate-500">{{ $session->subject?->code }}</div>
                            </td>
                            <td class="px-6 py-5 font-semibold text-emerald-600">{{ $session->present_records_count ?? 0 }}</td>
                            <td class="px-6 py-5 font-semibold text-rose-600">{{ $session->absent_records_count ?? 0 }}</td>
                            <td class="px-6 py-5">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusTone[$completionStatus] ?? $statusTone['pending'] }}">
                                    {{ ucfirst($completionStatus) }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex justify-end">
                                    <a href="{{ route('admin.attendance.sessions.show', $session) }}"
                                       class="rounded-full border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-[#8B0000] hover:text-[#8B0000]">
                                        View
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-16 text-center text-sm text-slate-500">No attendance sessions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 px-6 py-4">
            {{ $attendanceSessions->links() }}
        </div>
    </div>
</div>
@endsection
