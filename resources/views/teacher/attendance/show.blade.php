@extends('layouts.app')

@section('title', 'Attendance Details')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl lg:rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-emerald-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Attendance</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        {{ $attendanceSession->subject->name }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">{{ bsDate($attendanceSession->date, 'F d, Y') }}</p>
                </div>
                <a href="{{ route('teacher.attendance.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back
                </a>
            </div>
        </div>
    </section>

    {{-- Session Info --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase">Subject</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">{{ $attendanceSession->subject->name }}</p>
            <p class="text-xs text-slate-500">{{ $attendanceSession->subject->code }}</p>
        </div>
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase">Date</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">{{ bsDate($attendanceSession->date, 'M d, Y') }}</p>
            <p class="text-xs text-slate-500">{{ $attendanceSession->period ?? 'No period specified' }}</p>
        </div>
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-500 uppercase">Total Records</p>
            <p class="mt-2 text-lg font-semibold text-slate-900">{{ $attendanceSession->attendances->count() }}</p>
            <p class="text-xs text-slate-500">
                {{ $attendanceSession->attendances->where('status', 'present')->count() }} Present
            </p>
        </div>
    </div>

    {{-- Attendance Records --}}
    <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 px-4 py-3 sm:px-6">
            <h2 class="text-sm font-semibold text-slate-900">Attendance Records</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Student</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-700">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendanceSession->attendances as $attendance)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $attendance->student->user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($attendance->student->user->name) }}" 
                                        alt="{{ $attendance->student->user->name }}" class="h-8 w-8 rounded-full">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $attendance->student->user->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $attendance->student->student_no }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($attendance->status === 'present')
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                        <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        Present
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700">
                                        <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                        Absent
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $attendance->remarks ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center">
                                <p class="text-sm text-slate-500">No attendance records</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
