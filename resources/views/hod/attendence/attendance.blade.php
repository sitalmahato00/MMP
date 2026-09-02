@extends('layouts.app')
@section('title', 'Attendance Reports')

@section('content')
<x-page-header title="Attendance Reports" subtitle="Student attendance analysis and statistics."
               back="{{ route('hod.reports.index') }}"/>

{{-- Date Range Filter --}}
<div class="mb-6 rounded-2xl border border-slate-200 bg-white p-4">
    <form method="GET" class="flex flex-wrap items-end gap-4">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-semibold text-slate-700 mb-2">Date From (BS)</label>
            <x-bs-date-picker name="date_from" :value="$dateFrom"/>
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-semibold text-slate-700 mb-2">Date To (BS)</label>
            <x-bs-date-picker name="date_to" :value="$dateTo"/>
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-semibold text-slate-700 mb-2">Program</label>
            <select name="program_id" class="block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All Programs</option>
                @foreach($programs as $program)
                    <option value="{{ $program->id }}" @selected(request('program_id') == $program->id)>
                        {{ $program->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[120px]">
            <label class="block text-sm font-semibold text-slate-700 mb-2">Semester</label>
            <select name="semester" class="block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All</option>
                @for($i = 1; $i <= 6; $i++)
                    <option value="{{ $i }}" @selected(request('semester') == $i)>{{ $i }}</option>
                @endfor
            </select>
        </div>
        <div>
            <x-btn type="submit">Filter</x-btn>
        </div>
    </form>
</div>

{{-- Subject-wise Attendance Summary --}}
<div class="mb-8 rounded-2xl border border-slate-200 bg-white p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-slate-800">Subject-wise Attendance</h2>
        <x-export-dropdown>
            <a href="{{ route('hod.reports.export', 'attendance') }}?{{ http_build_query(request()->all()) }}" 
               class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">
                Export Attendance Data
            </a>
        </x-export-dropdown>
    </div>

    @if($subjectAttendance->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left py-3 font-semibold text-slate-700">Subject</th>
                        <th class="text-center py-3 font-semibold text-slate-700">Sessions</th>
                        <th class="text-center py-3 font-semibold text-slate-700">Total Records</th>
                        <th class="text-center py-3 font-semibold text-slate-700">Present</th>
                        <th class="text-center py-3 font-semibold text-slate-700">Attendance Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subjectAttendance as $subject)
                        <tr class="border-b border-slate-100">
                            <td class="py-3 font-medium text-slate-800">{{ $subject->subject_name }}</td>
                            <td class="py-3 text-center text-slate-600">{{ $subject->total_sessions }}</td>
                            <td class="py-3 text-center text-slate-600">{{ $subject->total_records }}</td>
                            <td class="py-3 text-center text-slate-600">{{ $subject->present_count }}</td>
                            <td class="py-3 text-center">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold
                                    {{ $subject->attendance_rate >= 80 ? 'bg-emerald-100 text-emerald-700' : 
                                       ($subject->attendance_rate >= 60 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                    {{ $subject->attendance_rate }}%
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-8 text-slate-500">
            <p>No attendance data found for the selected period.</p>
        </div>
    @endif
</div>

{{-- Student Attendance Details --}}
<div class="rounded-2xl border border-slate-200 bg-white p-6">
    <h2 class="text-lg font-bold text-slate-800 mb-4">Student Attendance Details</h2>

    @if($studentAttendance->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left py-3 font-semibold text-slate-700">Student</th>
                        <th class="text-left py-3 font-semibold text-slate-700">Program</th>
                        <th class="text-center py-3 font-semibold text-slate-700">Total Sessions</th>
                        <th class="text-center py-3 font-semibold text-slate-700">Present</th>
                        <th class="text-center py-3 font-semibold text-slate-700">Absent</th>
                        <th class="text-center py-3 font-semibold text-slate-700">Attendance Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($studentAttendance as $student)
                        <tr class="border-b border-slate-100">
                            <td class="py-3">
                                <div>
                                    <p class="font-medium text-slate-800">{{ $student->user?->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $student->user?->email }}</p>
                                </div>
                            </td>
                            <td class="py-3 text-slate-600">{{ $student->program?->name }}</td>
                            <td class="py-3 text-center text-slate-600">{{ $student->total_sessions }}</td>
                            <td class="py-3 text-center text-emerald-600 font-semibold">{{ $student->present_sessions }}</td>
                            <td class="py-3 text-center text-red-600 font-semibold">{{ $student->total_sessions - $student->present_sessions }}</td>
                            <td class="py-3 text-center">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold
                                    {{ $student->attendance_rate >= 80 ? 'bg-emerald-100 text-emerald-700' : 
                                       ($student->attendance_rate >= 60 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                    {{ $student->attendance_rate }}%
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $studentAttendance->links() }}
        </div>
    @else
        <div class="text-center py-8 text-slate-500">
            <p>No student attendance data found for the selected period.</p>
        </div>
    @endif
</div>
@endsection