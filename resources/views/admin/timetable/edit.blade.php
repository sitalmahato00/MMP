@extends('layouts.app')

@section('title', 'Edit Timetable')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Edit Timetable</h1>
            <p class="mt-0.5 text-sm text-slate-500">Update schedule routine dates and configuration.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.timetable.show', $timetable) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                View Routine
            </a>
            <a href="{{ route('admin.timetable.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                Cancel
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.timetable.update', $timetable) }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Academic Session *</label>
            <select name="academic_session_id" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                @foreach($academicSessions as $session)
                    <option value="{{ $session->id }}" {{ old('academic_session_id', $timetable->academic_session_id) == $session->id ? 'selected' : '' }}>
                        {{ $session->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Program *</label>
            <select name="program_id" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                @foreach($programs as $program)
                    <option value="{{ $program->id }}" {{ old('program_id', $timetable->program_id) == $program->id ? 'selected' : '' }}>
                        {{ $program->name }} ({{ $program->department?->name ?? 'No Dept' }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Semester *</label>
                <select name="semester" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    @for($s = 1; $s <= 6; $s++)
                        <option value="{{ $s }}" {{ old('semester', $timetable->semester) == $s ? 'selected' : '' }}>Semester {{ $s }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Section</label>
                <input type="text" name="section" value="{{ old('section', $timetable->section) }}" placeholder="e.g. A, B"
                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Effective From Date *</label>
                <input type="date" name="effective_from" value="{{ old('effective_from', $timetable->effective_from?->format('Y-m-d')) }}" required
                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ old('start_date', $timetable->start_date?->format('Y-m-d')) }}"
                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
            </div>
        </div>

        <div class="flex items-center gap-2 pt-2">
            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $timetable->is_active) ? 'checked' : '' }}
                   class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500">
            <label for="is_active" class="text-sm font-medium text-slate-700 dark:text-slate-300">Set as active timetable</label>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
            <a href="{{ route('admin.timetable.index') }}" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                Cancel
            </a>
            <button type="submit" class="rounded-xl bg-[#8B0000] px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#6b0000] transition">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
