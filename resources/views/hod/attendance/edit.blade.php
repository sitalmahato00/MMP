@extends('layouts.app')

@section('title', 'Edit Attendance')

@section('content')
<div class="space-y-6" x-data="attendanceEditor()">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">{{ $department->name }} Department</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        Edit Attendance
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">
                        {{ $attendanceSession->subject->name }} - {{ $attendanceSession->program->name }} 
                        (Semester {{ $attendanceSession->semester }})
                    </p>
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

    {{-- Session Info --}}
    <section class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Subject</label>
                <p class="text-sm font-medium text-slate-900">{{ $attendanceSession->subject->name }}</p>
                <p class="text-xs text-slate-500">{{ $attendanceSession->subject->code }}</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Program</label>
                <p class="text-sm font-medium text-slate-900">{{ $attendanceSession->program->name }}</p>
                <p class="text-xs text-slate-500">Semester {{ $attendanceSession->semester }}</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Original Date</label>
                <p class="text-sm font-medium text-slate-900">{{ bsDate($attendanceSession->date, 'M d, Y') }}</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Original Period</label>
                <p class="text-sm font-medium text-slate-900">{{ $attendanceSession->period }}</p>
            </div>
        </div>
    </section>

    {{-- Edit Form --}}
    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-6 py-4">
            <h2 class="text-lg font-semibold text-slate-900">Update Attendance</h2>
            <p class="text-sm text-slate-500">{{ $allStudents->count() }} students in this class</p>
        </div>

        <form method="POST" action="{{ route('hod.attendance.update', $attendanceSession) }}" class="p-6">
            @csrf
            @method('PUT')

            {{-- Session Details --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Teacher *</label>
                    <select name="teacher_id" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ $attendanceSession->teacher_id == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Date *</label>
                    <input type="date" name="date" value="{{ $attendanceSession->date->format('Y-m-d') }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Period *</label>
                    <input type="text" name="period" value="{{ str_replace([' (Class)', ' (Lab)'], '', $attendanceSession->period) }}" required
                           placeholder="e.g., 1st Period, Morning"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Attendance Type *</label>
                    <div class="flex gap-4">
                        <label class="flex items-center">
                            <input type="radio" name="attendance_type" value="class" 
                                   {{ $attendanceType == 'class' ? 'checked' : '' }} required
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300">
                            <span class="ml-2 text-sm text-slate-700">Class</span>
                        </label>
                        @if(in_array($attendanceSession->subject->type, ['practical', 'both']))
                            <label class="flex items-center">
                                <input type="radio" name="attendance_type" value="lab" 
                                       {{ $attendanceType == 'lab' ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300">
                                <span class="ml-2 text-sm text-slate-700">Lab</span>
                            </label>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="flex flex-wrap gap-2 mb-4">
                <button type="button" @click="markAll('present')" 
                        class="rounded-lg bg-emerald-600 px-3 py-1 text-xs font-medium text-white hover:bg-emerald-700">
                    Mark All Present
                </button>
                <button type="button" @click="markAll('absent')" 
                        class="rounded-lg bg-red-600 px-3 py-1 text-xs font-medium text-white hover:bg-red-700">
                    Mark All Absent
                </button>
                <button type="button" @click="markAll('late')" 
                        class="rounded-lg bg-amber-600 px-3 py-1 text-xs font-medium text-white hover:bg-amber-700">
                    Mark All Late
                </button>
            </div>

            {{-- Students List --}}
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 text-xs font-medium text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-left">Roll No.</th>
                            <th class="px-4 py-3 text-left">Student Name</th>
                            <th class="px-4 py-3 text-center">Present</th>
                            <th class="px-4 py-3 text-center">Absent</th>
                            <th class="px-4 py-3 text-center">Late</th>
                            <th class="px-4 py-3 text-center">Excused</th>
                            <th class="px-4 py-3 text-left">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($allStudents as $student)
                            @php
                                $existingAttendance = $attendanceSession->attendances->where('student_id', $student->id)->first();
                                $currentStatus = $existingAttendance ? $existingAttendance->status : 'present';
                                $currentRemarks = $existingAttendance ? $existingAttendance->remarks : '';
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-sm font-medium text-slate-900">
                                    {{ $student->roll_number ?? $student->student_no }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $student->user->avatar_url }}" alt="{{ $student->user->name }}" 
                                             class="h-8 w-8 rounded-full object-cover">
                                        <div>
                                            <div class="text-sm font-medium text-slate-900">{{ $student->user->name }}</div>
                                            <div class="text-xs text-slate-500">{{ $student->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="radio" name="attendances[{{ $student->id }}]" value="present" 
                                           {{ $currentStatus == 'present' ? 'checked' : '' }}
                                           x-model="attendance[{{ $student->id }}]"
                                           class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-slate-300">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="radio" name="attendances[{{ $student->id }}]" value="absent" 
                                           {{ $currentStatus == 'absent' ? 'checked' : '' }}
                                           x-model="attendance[{{ $student->id }}]"
                                           class="h-4 w-4 text-red-600 focus:ring-red-500 border-slate-300">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="radio" name="attendances[{{ $student->id }}]" value="late" 
                                           {{ $currentStatus == 'late' ? 'checked' : '' }}
                                           x-model="attendance[{{ $student->id }}]"
                                           class="h-4 w-4 text-amber-600 focus:ring-amber-500 border-slate-300">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="radio" name="attendances[{{ $student->id }}]" value="excused" 
                                           {{ $currentStatus == 'excused' ? 'checked' : '' }}
                                           x-model="attendance[{{ $student->id }}]"
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" name="remarks[{{ $student->id }}]" 
                                           value="{{ $currentRemarks }}"
                                           placeholder="Optional remarks"
                                           class="w-full rounded border border-slate-300 px-2 py-1 text-xs focus:border-blue-500 focus:ring-blue-500">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Submit Button --}}
            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('hod.attendance.index') }}" 
                   class="rounded-lg border border-slate-300 px-6 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </a>
                <button type="submit" 
                        class="rounded-lg bg-blue-600 px-6 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Update Attendance
                </button>
            </div>
        </form>
    </section>
</div>

<script>
function attendanceEditor() {
    return {
        attendance: @json($allStudents->pluck('id')->mapWithKeys(function($id) use ($attendanceSession) {
            $existing = $attendanceSession->attendances->where('student_id', $id)->first();
            return [$id => $existing ? $existing->status : 'present'];
        })->toArray()),
        
        markAll(status) {
            Object.keys(this.attendance).forEach(studentId => {
                this.attendance[studentId] = status;
            });
        }
    }
}
</script>
@endsection