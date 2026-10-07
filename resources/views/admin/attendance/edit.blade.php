@extends('layouts.app')

@section('title', 'Edit Attendance Session')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Edit Attendance Session</h1>
            <p class="mt-0.5 text-sm text-slate-500">
                {{ $attendanceSession->subject?->name }} &bull; {{ $attendanceSession->program?->name }} Sem {{ $attendanceSession->semester }}
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.attendance.sessions.show', $attendanceSession) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                View Sheet
            </a>
            <a href="{{ route('admin.attendance.sessions') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                Cancel
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.attendance.sessions.update', $attendanceSession) }}" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Session Settings --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4">Session Info</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Teacher *</label>
                    <select name="teacher_id" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ $attendanceSession->teacher_id == $t->id ? 'selected' : '' }}>
                                {{ $t->user?->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Date (BS) *</label>
                    <x-bs-date-picker name="date" :value="old('date', $attendanceSession->date ? bsDate($attendanceSession->date, 'Y-m-d') : '')" :required="true" placeholder="YYYY-MM-DD"
                                      class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"/>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Period *</label>
                    <input type="text" name="period" value="{{ $attendanceSession->period }}" required
                           class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                </div>
            </div>
        </div>

        {{-- Students Attendance Table --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4">Student Records</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-slate-100 bg-slate-50 text-xs font-semibold uppercase text-slate-500 dark:border-slate-800 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-2.5">Roll</th>
                            <th class="px-4 py-2.5">Student Name</th>
                            <th class="px-4 py-2.5 text-center">Present</th>
                            <th class="px-4 py-2.5 text-center">Absent</th>
                            <th class="px-4 py-2.5 text-center">Late</th>
                            <th class="px-4 py-2.5">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($attendanceSession->attendances as $att)
                            <tr>
                                <td class="px-4 py-2.5 font-mono text-xs font-bold text-slate-700 dark:text-slate-300">
                                    {{ $att->student?->roll_number ?? $att->student?->student_no ?? '—' }}
                                </td>
                                <td class="px-4 py-2.5 font-semibold text-slate-900 dark:text-white">
                                    {{ $att->student?->user?->name ?? 'Unknown Student' }}
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <input type="radio" name="attendances[{{ $att->student_id }}]" value="present" {{ $att->status === 'present' ? 'checked' : '' }}
                                           class="h-4 w-4 text-emerald-600 focus:ring-emerald-500">
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <input type="radio" name="attendances[{{ $att->student_id }}]" value="absent" {{ $att->status === 'absent' ? 'checked' : '' }}
                                           class="h-4 w-4 text-rose-600 focus:ring-rose-500">
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <input type="radio" name="attendances[{{ $att->student_id }}]" value="late" {{ $att->status === 'late' ? 'checked' : '' }}
                                           class="h-4 w-4 text-amber-600 focus:ring-amber-500">
                                </td>
                                <td class="px-4 py-2.5">
                                    <input type="text" name="remarks[{{ $att->student_id }}]" value="{{ $att->remarks }}" placeholder="Remarks"
                                           class="w-full rounded-lg border border-slate-200 px-2.5 py-1 text-xs dark:bg-slate-800 dark:border-slate-700 dark:text-white">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('admin.attendance.sessions') }}" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                    Cancel
                </a>
                <button type="submit" class="rounded-xl bg-[#8B0000] px-8 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#6b0000] transition">
                    Save Updates
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
