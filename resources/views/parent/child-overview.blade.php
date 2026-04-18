@extends('layouts.app')
@section('title', ($student->user?->name ?? 'Child') . ' — Overview')

@section('content')
<div x-data="{ tab: 'overview' }">

{{-- Back + Student Header --}}
<div class="mb-6">
    <a href="{{ route('parent.dashboard') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 mb-3">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Dashboard
    </a>
    <div class="flex flex-wrap items-center gap-4">
        @if($student->user?->avatar)
            <img src="{{ asset('storage/'.$student->user->avatar) }}" class="h-16 w-16 rounded-2xl object-cover ring-2 ring-slate-100"/>
        @else
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-2xl font-bold text-white">
                {{ strtoupper(substr($student->user?->name ?? 'S', 0, 1)) }}
            </div>
        @endif
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $student->user?->name }}</h1>
            <p class="text-sm text-slate-500">
                {{ $student->student_no }} · {{ $student->department?->name }} · {{ $student->program?->name }} · Semester {{ $student->current_semester }}
            </p>
        </div>
    </div>
</div>

{{-- Quick Stats --}}
@php
    $attColor = $attendancePct === null ? 'slate' : ($attendancePct >= 75 ? 'emerald' : ($attendancePct >= 50 ? 'amber' : 'red'));
@endphp
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm text-center">
        <p class="text-2xl font-black text-{{ $attColor }}-700">{{ $attendancePct !== null ? $attendancePct.'%' : '—' }}</p>
        <p class="text-xs text-slate-500 mt-1">Attendance</p>
        @if($attendancePct !== null)
        <div class="mt-2 h-1.5 rounded-full bg-{{ $attColor }}-100 overflow-hidden">
            <div class="h-full rounded-full bg-{{ $attColor }}-500" style="width: {{ $attendancePct }}%"></div>
        </div>
        @endif
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm text-center">
        <p class="text-2xl font-black text-blue-700">{{ $avgMarks ?? '—' }}</p>
        <p class="text-xs text-slate-500 mt-1">Avg Marks</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm text-center">
        <p class="text-2xl font-black text-violet-700">{{ $present }}</p>
        <p class="text-xs text-slate-500 mt-1">Days Present</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm text-center">
        <p class="text-2xl font-black text-red-700">{{ $absent }}</p>
        <p class="text-xs text-slate-500 mt-1">Days Absent</p>
    </div>
</div>

{{-- Tabs --}}
<div class="mb-6 flex gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm overflow-x-auto">
    @foreach(['overview' => 'Overview', 'attendance' => 'Attendance', 'marks' => 'Results'] as $key => $label)
    <button @click="tab='{{ $key }}'"
            :class="tab==='{{ $key }}' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50'"
            class="rounded-lg px-4 py-2 text-sm font-semibold transition whitespace-nowrap">
        {{ $label }}
    </button>
    @endforeach
</div>

{{-- OVERVIEW TAB --}}
<div x-show="tab==='overview'" x-cloak class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Student Info --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h3 class="font-bold text-slate-900">Student Information</h3>
            </div>
            <div class="p-5 space-y-3">
                @php
                    $infoFields = [
                        ['label' => 'Name', 'value' => $student->user?->name],
                        ['label' => 'Student No.', 'value' => $student->student_no],
                        ['label' => 'Department', 'value' => $student->department?->name],
                        ['label' => 'Program', 'value' => $student->program?->name],
                        ['label' => 'Semester', 'value' => $student->current_semester],
                        ['label' => 'Email', 'value' => $student->user?->email],
                        ['label' => 'Phone', 'value' => $student->user?->phone],
                    ];
                @endphp
                @foreach($infoFields as $f)
                <div class="flex justify-between py-2 border-b border-slate-50 last:border-0">
                    <span class="text-xs font-semibold text-slate-500">{{ $f['label'] }}</span>
                    <span class="text-sm text-slate-900">{{ $f['value'] ?? '—' }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Subjects --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h3 class="font-bold text-slate-900">Current Subjects</h3>
            </div>
            <div class="p-5">
                @if($student->subjects->count())
                <div class="space-y-2">
                    @foreach($student->subjects as $subject)
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $subject->name }}</p>
                            <p class="text-xs text-slate-500">{{ $subject->code }}</p>
                        </div>
                        <span class="text-xs font-bold text-slate-500">Cr. {{ $subject->credit_hours }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-500 italic">No subjects assigned.</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ATTENDANCE TAB --}}
<div x-show="tab==='attendance'" x-cloak class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4 flex items-center justify-between">
            <h3 class="font-bold text-slate-900">Attendance Summary</h3>
            <div class="flex gap-3 text-xs">
                <span class="text-emerald-600 font-bold">Present: {{ $present }}</span>
                <span class="text-red-600 font-bold">Absent: {{ $absent }}</span>
                <span class="text-amber-600 font-bold">Late: {{ $late }}</span>
            </div>
        </div>
        <div class="p-5">
            @if($totalAtt > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-2 text-left text-xs font-semibold text-slate-500">Date</th>
                            <th class="py-2 text-left text-xs font-semibold text-slate-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($student->attendances as $att)
                        <tr>
                            <td class="py-2 text-slate-700">{{ bsDate($att->date ?? $att->created_at, 'Y-m-d') }}</td>
                            <td class="py-2">
                                @php
                                    $statusColors = ['present' => 'emerald', 'absent' => 'red', 'late' => 'amber', 'excused' => 'blue'];
                                    $c = $statusColors[$att->status] ?? 'slate';
                                @endphp
                                <span class="rounded-lg bg-{{ $c }}-50 px-2 py-0.5 text-xs font-bold text-{{ $c }}-700">{{ ucfirst($att->status) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-sm text-slate-500 italic text-center py-4">No attendance records found.</p>
            @endif
        </div>
    </div>
</div>

{{-- MARKS TAB --}}
<div x-show="tab==='marks'" x-cloak class="space-y-6">
    @forelse($marksByExam as $examName => $marks)
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h3 class="font-bold text-slate-900">{{ $examName }}</h3>
        </div>
        <div class="p-5 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="py-2 text-left text-xs font-semibold text-slate-500">Subject</th>
                        <th class="py-2 text-center text-xs font-semibold text-slate-500">Theory</th>
                        <th class="py-2 text-center text-xs font-semibold text-slate-500">Practical</th>
                        <th class="py-2 text-center text-xs font-semibold text-slate-500">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($marks as $mark)
                    <tr>
                        <td class="py-2 text-slate-700">{{ $mark->subject?->name ?? '—' }}</td>
                        <td class="py-2 text-center text-slate-700">{{ $mark->theory ?? '—' }}</td>
                        <td class="py-2 text-center text-slate-700">{{ $mark->practical ?? '—' }}</td>
                        <td class="py-2 text-center font-bold text-slate-900">{{ ($mark->theory ?? 0) + ($mark->practical ?? 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center shadow-sm">
        <p class="text-sm text-slate-500">No published results yet.</p>
    </div>
    @endforelse
</div>

</div>
@endsection
