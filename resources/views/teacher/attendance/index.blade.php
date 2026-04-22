@extends('layouts.app')

@section('title', 'Attendance Management')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl lg:rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-emerald-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Teacher</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        Attendance Management
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">Record and manage student attendance</p>
                </div>
                <a href="{{ route('teacher.attendance.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Attendance
                </a>
            </div>
        </div>
    </section>

    {{-- Filters --}}
    <x-filter-bar action="{{ route('teacher.attendance.index') }}">
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Subject name..." 
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Subject</label>
            <select name="subject_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                <option value="">All Subjects</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                        {{ $subject->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">From Date</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" 
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">To Date</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" 
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
        </div>
    </x-filter-bar>

    {{-- Attendance Sessions Table --}}
    <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Subject</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">Date</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-700">Period</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-700">Records</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sessions as $session)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $session->subject->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $session->subject->code }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700">
                                    {{ bsDate($session->date, 'M d, Y') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-slate-600">
                                {{ $session->period ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                    {{ $session->attendances_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('teacher.attendance.show', $session) }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-100">
                                        View
                                    </a>
                                    <a href="{{ route('teacher.attendance.edit', $session) }}" class="inline-flex items-center gap-2 rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-600 transition hover:bg-amber-100">
                                        Edit
                                    </a>
                                    <form action="{{ route('teacher.attendance.destroy', $session) }}" method="POST" class="inline" onsubmit="return confirm('Delete this attendance record?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-100">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="mt-2 text-sm text-slate-500">No attendance records</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($sessions->hasPages())
        <div class="flex justify-center">
            {{ $sessions->links() }}
        </div>
    @endif
</div>
@endsection
