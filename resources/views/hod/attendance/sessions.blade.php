@extends('layouts.app')

@section('title', 'Attendance Session Details')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">{{ $department->name }} Department</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        Attendance Session Details
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">
                        {{ $session->subject->name }} - {{ bsDate($session->date, 'F d, Y') }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('hod.attendance.edit', $session) }}" 
                       class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Session
                    </a>
                    <a href="{{ route('hod.attendance.index') }}" 
                       class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Sessions
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Session Info --}}
    <section class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Date (BS)</label>
                <p class="text-lg font-semibold text-slate-900">{{ bsDate($session->date, 'F d, Y') }}</p>
                <p class="text-xs text-slate-500">{{ bsDate($session->date, 'l') }}</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Subject</label>
                <p class="text-lg font-semibold text-slate-900">{{ $session->subject->name }}</p>
                <p class="text-xs text-slate-500">{{ $session->subject->code }}</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Teacher</label>
                <p class="text-lg font-semibold text-slate-900">{{ $session->teacher->user->name }}</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Period</label>
                <p class="text-lg font-semibold text-slate-900">{{ $session->period }}</p>
            </div>
        </div>
    </section>

    {{-- Attendance Summary --}}
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold tracking-tight text-slate-900">{{ $presentCount }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Present</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-50">
                    <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold tracking-tight text-slate-900">{{ $absentCount }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Absent</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50">
                    <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold tracking-tight text-slate-900">{{ $totalStudents }}</span>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Total Students</p>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50">
                    <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline gap-1">
                    <span class="text-2xl font-bold tracking-tight text-slate-900">{{ $attendanceRate }}</span>
                    <span class="text-sm font-medium text-slate-400">%</span>
                </div>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">Attendance Rate</p>
            </div>
        </div>
    </section>

    {{-- Student Attendance Details --}}
    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">Student Attendance</h2>
            <p class="text-xs text-slate-500">Individual attendance records for this session</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 text-xs font-medium text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Roll No.</th>
                        <th class="px-5 py-3 text-left">Student Name</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($session->attendances as $attendance)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <div class="text-sm font-medium text-slate-900">
                                    {{ $attendance->student->roll_number ?? $attendance->student->student_no }}
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $attendance->student->user->avatar_url }}" alt="{{ $attendance->student->user->name }}" 
                                         class="h-8 w-8 rounded-full object-cover">
                                    <div>
                                        <div class="text-sm font-medium text-slate-900">{{ $attendance->student->user->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $attendance->student->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $statusColors = [
                                        'present' => 'bg-emerald-100 text-emerald-800',
                                        'absent' => 'bg-red-100 text-red-800',
                                        'late' => 'bg-amber-100 text-amber-800',
                                        'excused' => 'bg-blue-100 text-blue-800',
                                    ];
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColors[$attendance->status] ?? 'bg-slate-100 text-slate-800' }}">
                                    {{ ucfirst($attendance->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm text-slate-900">{{ $attendance->remarks ?: '—' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <p class="mt-2 text-sm text-slate-500">No attendance records found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection