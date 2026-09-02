@extends('layouts.app')

@section('title', 'Attendance Reports')

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
                        Attendance Reports
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">Student-wise attendance analysis and statistics</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('hod.attendance.index') }}" 
                       class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Attendance
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Filters --}}
    <section class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-700 mb-1">Search Student</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search by name or email..."
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            
            <div class="min-w-[150px]">
                <label class="block text-xs font-medium text-slate-700 mb-1">Program</label>
                <select name="program_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Programs</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                            {{ $program->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-[120px]">
                <label class="block text-xs font-medium text-slate-700 mb-1">Semester</label>
                <select name="semester" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Semesters</option>
                    @for($i = 1; $i <= 6; $i++)
                        <option value="{{ $i }}" {{ request('semester') == $i ? 'selected' : '' }}>
                            Semester {{ $i }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Filter
                </button>
                <a href="{{ route('hod.attendance.reports') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Clear
                </a>
            </div>
        </form>
    </section>

    {{-- Student Attendance Table --}}
    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">Student Attendance Summary</h2>
            <p class="text-xs text-slate-500">Overall attendance statistics for each student</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 text-xs font-medium text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Student</th>
                        <th class="px-5 py-3 text-left">Program</th>
                        <th class="px-5 py-3 text-center">Total Sessions</th>
                        <th class="px-5 py-3 text-center">Present</th>
                        <th class="px-5 py-3 text-center">Absent</th>
                        <th class="px-5 py-3 text-center">Attendance Rate</th>
                        <th class="px-5 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $student)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $student->user->avatar_url }}" alt="{{ $student->user->name }}" 
                                         class="h-8 w-8 rounded-full object-cover">
                                    <div>
                                        <div class="text-sm font-medium text-slate-900">{{ $student->user->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $student->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-sm text-slate-900">{{ $student->program->name ?? 'N/A' }}</div>
                                <div class="text-xs text-slate-500">Semester {{ $student->current_semester }}</div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <div class="text-sm font-medium text-slate-900">{{ $student->total_sessions }}</div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <div class="text-sm font-medium text-emerald-600">{{ $student->present_sessions }}</div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <div class="text-sm font-medium text-red-600">{{ $student->absent_sessions }}</div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="text-sm font-medium text-slate-900">{{ $student->attendance_rate }}%</div>
                                    <div class="w-16 bg-slate-200 rounded-full h-1.5">
                                        <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $student->attendance_rate }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <a href="{{ route('hod.students.show', $student) }}"
                                   class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-blue-50 hover:text-blue-600 transition" title="View Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <p class="mt-2 text-sm text-slate-500">No students found</p>
                                <p class="text-xs text-slate-400">Try adjusting your filters or check if students are enrolled</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">
                {{ $students->links() }}
            </div>
        @endif
    </section>
</div>
@endsection