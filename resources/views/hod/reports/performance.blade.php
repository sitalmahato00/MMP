@extends('layouts.app')
@section('title', 'Performance Reports')

@section('content')
<x-page-header title="Performance Reports" subtitle="Student academic performance and exam results analysis."
               back="{{ route('hod.reports.index') }}"/>

{{-- Filters --}}
<div class="mb-6 rounded-2xl border border-slate-200 bg-white p-4">
    <form method="GET" class="flex flex-wrap items-end gap-4">
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
                @for($i = 1; $i <= 8; $i++)
                    <option value="{{ $i }}" @selected(request('semester') == $i)>{{ $i }}</option>
                @endfor
            </select>
        </div>
        <div>
            <x-btn type="submit">Filter</x-btn>
        </div>
    </form>
</div>

{{-- Subject-wise Performance Summary --}}
<div class="mb-8 rounded-2xl border border-slate-200 bg-white p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-slate-800">Subject-wise Performance</h2>
        <x-export-dropdown>
            <a href="{{ route('hod.reports.export', 'performance') }}?{{ http_build_query(request()->all()) }}" 
               class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">
                Export Performance Data
            </a>
        </x-export-dropdown>
    </div>

    @if($subjectPerformance->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left py-3 font-semibold text-slate-700">Subject</th>
                        <th class="text-center py-3 font-semibold text-slate-700">Total Attempts</th>
                        <th class="text-center py-3 font-semibold text-slate-700">Average Marks</th>
                        <th class="text-center py-3 font-semibold text-slate-700">Passed</th>
                        <th class="text-center py-3 font-semibold text-slate-700">Pass Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subjectPerformance as $subject)
                        <tr class="border-b border-slate-100">
                            <td class="py-3 font-medium text-slate-800">{{ $subject->subject_name }}</td>
                            <td class="py-3 text-center text-slate-600">{{ $subject->total_attempts }}</td>
                            <td class="py-3 text-center text-slate-600">{{ number_format($subject->avg_marks, 1) }}</td>
                            <td class="py-3 text-center text-emerald-600 font-semibold">{{ $subject->passed }}</td>
                            <td class="py-3 text-center">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold
                                    {{ $subject->pass_rate >= 80 ? 'bg-emerald-100 text-emerald-700' : 
                                       ($subject->pass_rate >= 60 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                    {{ $subject->pass_rate }}%
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-8 text-slate-500">
            <p>No performance data found for the selected criteria.</p>
        </div>
    @endif
</div>

{{-- Student Performance Details --}}
<div class="rounded-2xl border border-slate-200 bg-white p-6">
    <h2 class="text-lg font-bold text-slate-800 mb-4">Student Performance Details</h2>

    @if($studentPerformance->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left py-3 font-semibold text-slate-700">Student</th>
                        <th class="text-left py-3 font-semibold text-slate-700">Program</th>
                        <th class="text-center py-3 font-semibold text-slate-700">Total Exams</th>
                        <th class="text-center py-3 font-semibold text-slate-700">Average Marks</th>
                        <th class="text-center py-3 font-semibold text-slate-700">Passed</th>
                        <th class="text-center py-3 font-semibold text-slate-700">Pass Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($studentPerformance as $student)
                        <tr class="border-b border-slate-100">
                            <td class="py-3">
                                <div>
                                    <p class="font-medium text-slate-800">{{ $student->user?->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $student->user?->email }}</p>
                                </div>
                            </td>
                            <td class="py-3 text-slate-600">{{ $student->program?->name }}</td>
                            <td class="py-3 text-center text-slate-600">{{ $student->total_exams }}</td>
                            <td class="py-3 text-center text-slate-600">{{ $student->avg_marks }}</td>
                            <td class="py-3 text-center text-emerald-600 font-semibold">{{ $student->passed_exams }}</td>
                            <td class="py-3 text-center">
                                @if($student->total_exams > 0)
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold
                                        {{ $student->pass_rate >= 80 ? 'bg-emerald-100 text-emerald-700' : 
                                           ($student->pass_rate >= 60 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                        {{ $student->pass_rate }}%
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $studentPerformance->links() }}
        </div>
    @else
        <div class="text-center py-8 text-slate-500">
            <p>No student performance data found for the selected criteria.</p>
        </div>
    @endif
</div>
@endsection