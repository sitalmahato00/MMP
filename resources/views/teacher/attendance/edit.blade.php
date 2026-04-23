@extends('layouts.app')

@section('title', 'Edit Attendance')

@section('content')
<div class="space-y-6">
    {{-- Session Messages --}}
    @if ($message = Session::get('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 shadow-sm" id="success-notification">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-emerald-800">{{ $message }}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="flex-shrink-0 text-emerald-800 hover:opacity-75">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        @php Session::forget('success'); @endphp
    @endif

    @if ($message = Session::get('error'))
        <div class="rounded-lg border border-rose-200 bg-rose-50 p-4 shadow-sm" id="error-notification">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-rose-800">{{ $message }}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="flex-shrink-0 text-rose-800 hover:opacity-75">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        @php Session::forget('error'); @endphp
    @endif

    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl lg:rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-amber-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Attendance</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        Edit Attendance
                    </h1>
                </div>
                <a href="{{ route('teacher.attendance.show', $attendanceSession) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back
                </a>
            </div>
        </div>
    </section>

    {{-- Form --}}
    <form action="{{ route('teacher.attendance.update', $attendanceSession) }}" method="POST" class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-sm space-y-6" id="attendance-form">
        @csrf
        @method('PUT')

        {{-- Date and Period --}}
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Date (BS) *</label>
                <x-bs-date-picker name="date" :value="bsDate($attendanceSession->date, 'Y-m-d')" placeholder="YYYY-MM-DD" required
                                  class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('date') border-rose-500 @enderror"/>
                @error('date')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Period</label>
                <input type="text" name="period" placeholder="e.g., Period 1, 9:00 AM" value="{{ old('period', $attendanceSession->period) }}" 
                    class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
        </div>

        {{-- Students Attendance --}}
        <div id="students-container" class="space-y-3">
            <label class="block text-sm font-semibold text-slate-900">Student Attendance *</label>
            <div class="rounded-lg border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50">
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Student</th>
                                <th class="px-4 py-3 text-center font-semibold text-slate-700">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Remarks</th>
                            </tr>
                        </thead>
                        <tbody id="students-list" class="divide-y divide-slate-100">
                            @foreach($attendanceSession->attendances as $index => $attendance)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3">
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $attendance->student->user->name }}</p>
                                            <p class="text-xs text-slate-500">{{ $attendance->student->student_no }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input type="hidden" name="attendances[{{ $index }}][student_id]" value="{{ $attendance->student_id }}">
                                        <select name="attendances[{{ $index }}][status]" required class="rounded-lg border border-slate-300 px-3 py-1 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                            <option value="present" {{ $attendance->status === 'present' ? 'selected' : '' }}>Present</option>
                                            <option value="absent" {{ $attendance->status === 'absent' ? 'selected' : '' }}>Absent</option>
                                            <option value="late" {{ $attendance->status === 'late' ? 'selected' : '' }}>Late</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="text" name="attendances[{{ $index }}][remarks]" placeholder="Optional remarks" value="{{ old('attendances.' . $index . '.remarks', $attendance->remarks) }}"
                                            class="w-full rounded-lg border border-slate-300 px-3 py-1 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex gap-3 border-t border-slate-100 pt-6">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-6 py-2 text-sm font-semibold text-white transition hover:bg-amber-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Update Attendance
            </button>
            <a href="{{ route('teacher.attendance.show', $attendanceSession) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-6 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
// Auto-dismiss notifications after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const successNotification = document.getElementById('success-notification');
    const errorNotification = document.getElementById('error-notification');
    
    if (successNotification) {
        setTimeout(() => {
            successNotification.remove();
        }, 5000);
    }
    
    if (errorNotification) {
        setTimeout(() => {
            errorNotification.remove();
        }, 5000);
    }
});
</script>
@endsection
