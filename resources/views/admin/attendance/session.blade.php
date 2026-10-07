@extends('layouts.app')
@section('title', 'Attendance Session Details')

@section('content')
<div x-data="{ query: '' }" class="space-y-6">
    {{-- Header (Matching HOD aesthetic with Admin theme) --}}
    <section class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="absolute inset-0 bg-gradient-to-br from-rose-50/50 via-white to-slate-50/40 dark:from-rose-950/20 dark:via-slate-900 dark:to-slate-900"></div>
        <div class="relative px-5 py-5 sm:px-6 sm:py-6 lg:px-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-md bg-[#8B0000]/10 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wider text-[#8B0000] dark:bg-rose-950/40 dark:text-rose-400">
                            {{ $attendanceSession->program?->department?->name ?? 'All Departments' }} Department
                        </span>
                        <span class="text-xs font-semibold text-slate-400">·</span>
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                            {{ $attendanceSession->academicSession?->name ?? 'Current Session' }} · Sem {{ $attendanceSession->semester }}
                        </span>
                    </div>
                    <h1 class="mt-1.5 text-2xl font-black tracking-tight text-slate-900 dark:text-white sm:text-3xl">
                        Attendance Session Details
                    </h1>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                        {{ $attendanceSession->subject?->name }} ({{ $attendanceSession->subject?->code }}) — {{ bsDate($attendanceSession->date, 'F d, Y') }} ({{ bsDate($attendanceSession->date, 'l') }})
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <a href="{{ route('admin.attendance.sessions.edit', $attendanceSession) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-[#6b0000]">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Session
                    </a>
                    <form method="POST" action="{{ route('admin.attendance.sessions.destroy', $attendanceSession) }}"
                          onsubmit="return confirm('Are you sure you want to delete this session and all its attendance records?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2 text-xs font-bold text-[#8B0000] transition hover:bg-rose-100 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete
                        </button>
                    </form>
                    <a href="{{ route('admin.attendance.sessions') }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Sessions
                    </a>
                    @if($attendanceSession->teacher_id)
                        <a href="{{ route('admin.teachers.show', ['teacher' => $attendanceSession->teacher_id, 'tab' => 'attendance']) }}"
                           class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            Teacher Profile
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Session Metadata Info Grid (Matching HOD 4-box card) --}}
    <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Date (BS & Day)</label>
                <p class="text-base font-bold text-slate-900 dark:text-white">{{ bsDate($attendanceSession->date, 'F d, Y') }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ bsDate($attendanceSession->date, 'l') }} ({{ \Carbon\Carbon::parse($attendanceSession->date)->format('M d, Y') }})</p>
            </div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Subject & Course</label>
                <p class="text-base font-bold text-slate-900 dark:text-white">{{ $attendanceSession->subject?->name ?? '—' }}</p>
                <p class="text-xs font-mono font-semibold text-[#8B0000] dark:text-rose-400">{{ $attendanceSession->subject?->code ?? 'N/A' }} · {{ ucfirst($attendanceSession->subject?->type ?? 'Theory') }}</p>
            </div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Instructor / Teacher</label>
                <p class="text-base font-bold text-slate-900 dark:text-white">{{ $attendanceSession->teacher?->user?->name ?? 'Unassigned' }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $attendanceSession->teacher?->designation ?? 'Faculty Member' }}</p>
            </div>
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Class & Period</label>
                @php
                    $isLabSession = stripos($attendanceSession->period, 'lab') !== false || stripos($attendanceSession->period, 'practical') !== false || $attendanceSession->subject?->type === 'practical';
                @endphp
                <p class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-1.5 flex-wrap">
                    <span>{{ $attendanceSession->period ?? 'Period 1' }}</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold {{ $isLabSession ? 'bg-purple-100 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300' }}">
                        {{ $isLabSession ? 'Lab Session' : 'Theory Session' }}
                    </span>
                    <span class="text-slate-400 font-normal">·</span>
                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ $attendanceSession->section ? 'Sec ' . $attendanceSession->section : 'All Sections' }}</span>
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $attendanceSession->program?->name ?? 'Program' }} (Sem {{ $attendanceSession->semester }})</p>
            </div>
        </div>
    </section>

    {{-- Attendance Summary Cards (Matching HOD 4 KPI status blocks) --}}
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Present --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-start justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">Active</span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">{{ $summary['present'] }}</span>
                <p class="mt-0.5 text-xs font-bold uppercase tracking-wider text-slate-400">Present Students</p>
            </div>
        </div>

        {{-- Absent --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-start justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                @if($summary['absent'] > 0)
                    <span class="inline-flex rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-bold uppercase text-rose-700 dark:bg-rose-950/40 dark:text-rose-400">Notice</span>
                @endif
            </div>
            <div class="mt-3">
                <span class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">{{ $summary['absent'] }}</span>
                <p class="mt-0.5 text-xs font-bold uppercase tracking-wider text-slate-400">Absent Students</p>
            </div>
        </div>

        {{-- Total Students --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-start justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-slate-400">Roster</span>
            </div>
            <div class="mt-3">
                <span class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">{{ $summary['total'] }}</span>
                <p class="mt-0.5 text-xs font-bold uppercase tracking-wider text-slate-400">Total Enrolled</p>
            </div>
        </div>

        {{-- Attendance Rate --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-start justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="h-2 w-16 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800 self-center">
                    <div class="h-full rounded-full {{ $summary['completion'] >= 80 ? 'bg-emerald-500' : ($summary['completion'] >= 60 ? 'bg-amber-500' : 'bg-rose-500') }}"
                         style="width: {{ min(100, $summary['completion']) }}%"></div>
                </div>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">{{ $summary['completion'] }}</span>
                    <span class="text-sm font-bold text-slate-400">%</span>
                </div>
                <p class="mt-0.5 text-xs font-bold uppercase tracking-wider text-slate-400">Attendance Rate</p>
            </div>
        </div>
    </section>

    {{-- Student Attendance Details (Full-width clean table matching HOD structure) --}}
    <section class="rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-800 gap-3">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Student Attendance Records</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Individual student status with inline toggle and real-time updates</p>
            </div>
            <div class="w-full sm:w-72">
                <input x-model="query" type="text" placeholder="Search by name, roll, or ID..."
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-1 focus:ring-[#8B0000] dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/80 font-bold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                        <th class="px-5 py-3.5" style="width: 15%;">Roll / Student ID</th>
                        <th class="px-5 py-3.5" style="width: 35%;">Student Name</th>
                        <th class="px-5 py-3.5" style="width: 25%;">Attendance Status</th>
                        <th class="px-5 py-3.5" style="width: 25%;">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($records as $record)
                        @php
                            $student = $record->student;
                            $studentName = $student?->user?->name ?? 'Student';
                            $status = $record->status ?? 'present';
                        @endphp
                        <tr data-search="{{ strtolower($studentName . ' ' . ($student->student_no ?? '') . ' ' . ($student->roll_number ?? '')) }}"
                            x-show="query === '' || $el.dataset.search.includes(query.toLowerCase())"
                            class="hover:bg-slate-50/80 transition dark:hover:bg-slate-800/40">
                            {{-- Roll No --}}
                            <td class="px-5 py-4 font-mono font-bold text-slate-700 dark:text-slate-300">
                                <div>{{ $student?->roll_number ?: ($student?->student_no ?? '—') }}</div>
                                @if($student?->roll_number && $student?->student_no)
                                    <div class="text-[10px] font-normal text-slate-400">ID: {{ $student->student_no }}</div>
                                @endif
                            </td>

                            {{-- Student Name & Avatar --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if($student?->user?->avatar)
                                        <img src="{{ asset('storage/' . $student->user->avatar) }}" alt="{{ $studentName }}"
                                             class="h-9 w-9 rounded-full object-cover ring-1 ring-slate-200 dark:ring-slate-700">
                                    @else
                                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-[#8B0000] to-rose-700 text-xs font-black text-white">
                                            {{ strtoupper(substr($studentName, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $studentName }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $student?->user?->email ?? 'No email' }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Status with Quick Toggle --}}
                            <td class="px-5 py-4"
                                x-data="{
                                    recordStatus: '{{ $status }}',
                                    updating: false,
                                    setRecordStatus(val) {
                                        if (this.updating || this.recordStatus === val) return;
                                        this.updating = true;
                                        const prev = this.recordStatus;
                                        this.recordStatus = val;
                                        fetch('{{ route('admin.attendance.records.toggle', $record) }}', {
                                            method: 'PATCH',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Accept': 'application/json'
                                            },
                                            body: JSON.stringify({ status: val })
                                        })
                                        .then(r => r.json())
                                        .then(data => {
                                            this.updating = false;
                                            if (!data.success) { this.recordStatus = prev; }
                                        })
                                        .catch(() => {
                                            this.recordStatus = prev;
                                            this.updating = false;
                                        });
                                    }
                                }">
                                <div class="flex items-center gap-2.5">
                                    {{-- Active Status Pill --}}
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider"
                                          :class="{
                                              'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300': recordStatus === 'present',
                                              'bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-300': recordStatus === 'absent',
                                              'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300': recordStatus === 'late',
                                              'bg-blue-100 text-blue-800 dark:bg-blue-950/50 dark:text-blue-300': recordStatus === 'excused'
                                          }"
                                          x-text="recordStatus.toUpperCase()">
                                        {{ strtoupper($status) }}
                                    </span>

                                    {{-- Interactive P/A/L/E buttons --}}
                                    <div class="inline-flex rounded-lg bg-slate-100 p-0.5 text-[10px] font-black text-slate-500 dark:bg-slate-800">
                                        <button type="button" @click="setRecordStatus('present')" :disabled="updating"
                                                :class="recordStatus === 'present' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                                                class="rounded-md px-2 py-0.5 transition cursor-pointer disabled:opacity-50" title="Mark Present">P</button>
                                        <button type="button" @click="setRecordStatus('absent')" :disabled="updating"
                                                :class="recordStatus === 'absent' ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                                                class="rounded-md px-2 py-0.5 transition cursor-pointer disabled:opacity-50" title="Mark Absent">A</button>
                                        <button type="button" @click="setRecordStatus('late')" :disabled="updating"
                                                :class="recordStatus === 'late' ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                                                class="rounded-md px-2 py-0.5 transition cursor-pointer disabled:opacity-50" title="Mark Late">L</button>
                                        <button type="button" @click="setRecordStatus('excused')" :disabled="updating"
                                                :class="recordStatus === 'excused' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'"
                                                class="rounded-md px-2 py-0.5 transition cursor-pointer disabled:opacity-50" title="Mark Excused">E</button>
                                    </div>
                                </div>
                            </td>

                            {{-- Remarks --}}
                            <td class="px-5 py-4 text-slate-600 dark:text-slate-300">
                                {{ $record->remarks ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center text-slate-400">
                                <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <p class="mt-2 text-sm font-semibold text-slate-500">No student attendance records found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Table Pagination --}}
        @if($records->hasPages())
            <div class="flex items-center justify-between border-t border-slate-200 px-5 py-3.5 dark:border-slate-800">
                <div class="text-xs text-slate-500">
                    Showing {{ $records->firstItem() }} to {{ $records->lastItem() }} of {{ $records->total() }} students
                </div>
                <div>
                    {{ $records->links() }}
                </div>
            </div>
        @endif
    </section>
</div>
@endsection
