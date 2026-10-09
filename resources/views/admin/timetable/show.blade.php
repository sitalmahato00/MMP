@extends('layouts.app')

@section('title', 'Timetable - ' . ($timetable->program?->name ?? 'Routine'))

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-800 dark:bg-slate-800 dark:text-slate-200">
                    {{ $timetable->academicSession?->name }}
                </span>
                @if($timetable->is_active)
                    <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Active</span>
                @else
                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-400">Inactive</span>
                @endif
            </div>
            <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                {{ $timetable->program?->name }} — Semester {{ $timetable->semester }}
                @if($timetable->section) (Section {{ $timetable->section }}) @endif
            </h1>
            <p class="mt-0.5 text-sm text-slate-500">
                Department: {{ $timetable->program?->department?->name ?? '—' }} &bull; Effective From: {{ $timetable->effective_from ? bsDate($timetable->effective_from, 'F d, Y') . ' (' . bsDate($timetable->effective_from) . ' BS)' : '—' }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.timetable.export', $timetable) }}" target="_blank"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print / Export
            </a>
            <a href="{{ route('admin.timetable.edit', $timetable) }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </a>
            <form method="POST" action="{{ route('admin.timetable.destroy', $timetable) }}" onsubmit="return confirm('Delete this timetable routine?')" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-red-50 px-4 py-2 text-sm font-bold text-red-600 hover:bg-red-100 dark:bg-red-950/40 dark:text-red-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete
                </button>
            </form>
        </div>
    </div>

    {{-- Add Slot Form --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4">Add Schedule Slot</h2>
        <form method="POST" action="{{ route('admin.timetable.slots.store', $timetable) }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-7 items-end">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Day *</label>
                <select name="day_of_week" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    @foreach($days as $dayNum => $dayName)
                        <option value="{{ $dayNum }}">{{ $dayName }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Start Time *</label>
                <input type="time" name="start_time" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">End Time *</label>
                <input type="time" name="end_time" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
            </div>
            <div class="lg:col-span-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Subject *</label>
                <select name="subject_id" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="">Select Subject</option>
                    @foreach($subjects as $subj)
                        <option value="{{ $subj->id }}">{{ $subj->code }} - {{ $subj->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Teacher</label>
                <select name="teacher_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    <option value="">Select Teacher</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->user?->name }} ({{ $teacher->department?->name ?? '' }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white hover:bg-slate-800 dark:bg-slate-700">
                    + Add Slot
                </button>
            </div>
        </form>
    </div>

    {{-- Timetable Routine Visual Grid --}}
    <div class="space-y-4">
        @foreach($days as $dayNum => $dayName)
            @php
                $daySlots = $timetable->slots->where('day_of_week', $dayNum)->sortBy('start_time');
            @endphp
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-red-100 font-bold text-red-800 text-sm dark:bg-red-950/60 dark:text-red-300">
                            {{ substr($dayName, 0, 3) }}
                        </span>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ $dayName }}</h3>
                    </div>
                    <span class="text-xs text-slate-400 font-medium">{{ $daySlots->count() }} Periods</span>
                </div>

                @if($daySlots->isNotEmpty())
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach($daySlots as $slot)
                            <div class="group relative rounded-xl border border-slate-100 bg-slate-50/70 p-3.5 hover:border-slate-300 dark:border-slate-800 dark:bg-slate-800/40 transition">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="font-mono text-xs font-bold text-red-700 dark:text-red-400">
                                        {{ \Carbon\Carbon::parse($slot->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('h:i A') }}
                                    </span>
                                    <form method="POST" action="{{ route('admin.timetable.slots.destroy', ['timetable' => $timetable, 'slot' => $slot]) }}" onsubmit="return confirm('Remove this slot?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-400 hover:text-red-600 transition" title="Delete Slot">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </form>
                                </div>
                                <h4 class="mt-2 text-sm font-bold text-slate-900 dark:text-white">
                                    {{ $slot->subject?->name ?? 'Subject' }}
                                </h4>
                                <p class="text-xs text-slate-500 font-mono">{{ $slot->subject?->code }}</p>
                                <div class="mt-2 flex items-center justify-between text-xs text-slate-600 dark:text-slate-300">
                                    <span>{{ $slot->teacher?->user?->name ?? 'No teacher assigned' }}</span>
                                    @if($slot->room_no)
                                        <span class="rounded bg-slate-200/60 px-1.5 py-0.5 font-mono text-[11px] dark:bg-slate-700">Room {{ $slot->room_no }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="py-2 text-xs italic text-slate-400">No classes scheduled on {{ $dayName }}.</p>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
