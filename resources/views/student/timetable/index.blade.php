@extends('layouts.app')

@section('title', 'My Timetable')

@section('content')
<div class="space-y-6">
    @if($timetable)
        {{-- Overall Attendance Summary --}}
        @if($subjectAttendance->count() > 0)
        <section class="grid gap-3 sm:gap-4 grid-cols-2 lg:grid-cols-4">
            @php
                $totalClasses = $subjectAttendance->sum('total');
                $totalPresent = $subjectAttendance->sum('present');
                $totalAbsent = $subjectAttendance->sum('absent');
                $totalLate = $subjectAttendance->sum('late');
                $overallRate = $totalClasses > 0 ? round(($totalPresent / $totalClasses) * 100, 1) : 0;
            @endphp
            
            <div class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] p-4 shadow-sm">
                <div class="flex items-start justify-between">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                        <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ number_format($totalClasses) }}</span>
                    <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Classes</p>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] p-4 shadow-sm">
                <div class="flex items-start justify-between">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/30">
                        <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ number_format($totalPresent) }}</span>
                    <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Present</p>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] p-4 shadow-sm">
                <div class="flex items-start justify-between">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-50 dark:bg-red-900/30">
                        <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ number_format($totalAbsent) }}</span>
                    <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Absent</p>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] p-4 shadow-sm">
                <div class="flex items-start justify-between">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30">
                        <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ number_format($overallRate, 1) }}</span>
                        <span class="text-sm font-medium text-slate-400 dark:text-slate-500">%</span>
                    </div>
                    <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400 dark:text-slate-500">Attendance Rate</p>
                </div>
            </div>
        </section>
        @endif

        {{-- Timetable Info --}}
        <section class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] shadow-sm p-4">
            <div class="flex flex-wrap items-center gap-4 text-sm">
                <div>
                    <span class="text-slate-600 dark:text-slate-400">Program:</span>
                    <span class="ml-1 font-medium text-slate-900 dark:text-slate-100">{{ $student->program->name }}</span>
                </div>
                <span class="text-slate-300 dark:text-slate-600">•</span>
                <div>
                    <span class="text-slate-600 dark:text-slate-400">Semester:</span>
                    <span class="ml-1 font-medium text-slate-900 dark:text-slate-100">{{ $student->current_semester }}</span>
                </div>
                <span class="text-slate-300 dark:text-slate-600">•</span>
                <div>
                    <span class="text-slate-600 dark:text-slate-400">Academic Year:</span>
                    <span class="ml-1 font-medium text-slate-900 dark:text-slate-100">{{ $timetable->academicSession->name ?? 'N/A' }}</span>
                </div>
                <span class="text-slate-300 dark:text-slate-600">•</span>
                <div>
                    <span class="text-slate-600 dark:text-slate-400">Effective From:</span>
                    <span class="ml-1 font-medium text-slate-900 dark:text-slate-100">{{ bsDate($timetable->effective_from, 'F d, Y') }}</span>
                </div>
            </div>
        </section>

        {{-- Timetable Grid --}}
        <section class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] shadow-sm overflow-hidden">
            <x-timetable-grid :slots="$slots" :subjects="$subjects" :teachers="$teachers" />
        </section>

        {{-- Subject-wise Attendance --}}
        @if($subjectAttendance->count() > 0)
        <section class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] shadow-sm">
            <div class="border-b border-slate-100 dark:border-[#1e3a5f] px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Subject-wise Attendance</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Your attendance rate for each subject this semester</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 dark:bg-[#0D1B35] text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3 text-left">Subject</th>
                            <th class="px-5 py-3 text-center">Total Classes</th>
                            <th class="px-5 py-3 text-center">Present</th>
                            <th class="px-5 py-3 text-center">Absent</th>
                            <th class="px-5 py-3 text-center">Late</th>
                            <th class="px-5 py-3 text-left">Attendance Rate</th>
                            <th class="px-5 py-3 text-left">Last Class</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-[#1e3a5f]">
                        @foreach($subjectAttendance as $data)
                            <tr class="hover:bg-slate-50 dark:hover:bg-[#1e3a5f]">
                                <td class="px-5 py-4">
                                    <div class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $data['subject']->name }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $data['subject']->code }}</div>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="text-sm text-slate-900 dark:text-slate-100">{{ $data['total'] }}</span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="text-sm font-medium text-emerald-600">{{ $data['present'] }}</span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="text-sm font-medium text-red-600">{{ $data['absent'] }}</span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="text-sm font-medium text-amber-600">{{ $data['late'] }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $data['rate'] }}%</div>
                                        <div class="w-24 bg-slate-200 dark:bg-[#1e3a5f] rounded-full h-1.5">
                                            <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $data['rate'] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    @if($data['last_class'])
                                        <div class="text-sm text-slate-900 dark:text-slate-100">{{ bsDate($data['last_class'], 'F d') }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ bsDate($data['last_class'], 'l') }}</div>
                                    @else
                                        <span class="text-xs text-slate-400">No classes yet</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        @endif
    @else
        {{-- No Timetable --}}
        <section class="rounded-xl border border-slate-200 dark:border-[#1e3a5f] bg-white dark:bg-[#132044] shadow-sm p-12">
            <div class="text-center">
                <svg class="mx-auto h-16 w-16 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-slate-900 dark:text-slate-100">No Timetable Available</h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Your class timetable for <strong>Semester {{ $student->current_semester }}</strong> has not been created yet.
                </p>
                <p class="mt-4 text-xs text-slate-400">
                    Please contact your department if you believe this is an error.
                </p>
                </p>
            </div>
        </section>
    @endif
</div>
@endsection
