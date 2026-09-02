@extends('layouts.app')

@section('title', 'Mark Attendance')

@section('content')
<div class="space-y-6" x-data="attendanceMarker()">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">{{ $department->name }} Department</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        Mark Attendance
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">Mark attendance for any class or lab session</p>
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

    {{-- Selection Form --}}
    <section class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Select Class Details</h2>
        
        <form method="GET" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Program</label>
                <select name="program_id" onchange="this.form.submit()" 
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select Program</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                            {{ $program->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Semester</label>
                <select name="semester" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select Semester</option>
                    @for($i = 1; $i <= 6; $i++)
                        <option value="{{ $i }}" {{ request('semester') == $i ? 'selected' : '' }}>
                            Semester {{ $i }}
                        </option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Section</label>
                <input type="text" name="section" value="{{ request('section') }}" 
                       placeholder="e.g., A, B, C (leave empty for all)"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                <p class="mt-1 text-xs text-slate-500">For lab sessions, specify section to filter students</p>
            </div>

            <div class="flex items-end">
                <button type="submit" 
                        class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Load Students
                </button>
            </div>
        </form>
    </section>

    @if($students->count() > 0)
        {{-- Attendance Form --}}
        <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Mark Attendance</h2>
                <p class="text-sm text-slate-500">{{ $students->count() }} students found</p>
            </div>

            <form method="POST" action="{{ route('hod.attendance.store') }}" class="p-6">
                @csrf
                
                {{-- Hidden Fields --}}
                <input type="hidden" name="academic_session_id" value="{{ $academicSession->id }}">
                <input type="hidden" name="program_id" value="{{ request('program_id') }}">
                <input type="hidden" name="semester" value="{{ request('semester') }}">
                <input type="hidden" name="section" value="{{ request('section') }}">

                {{-- Session Details --}}
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Subject *</label>
                        <select name="subject_id" required x-model="selectedSubject" @change="updateAttendanceType()"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select Subject</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" data-type="{{ $subject->type }}">
                                    {{ $subject->name }} ({{ $subject->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Teacher *</label>
                        <select name="teacher_id" required
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select Teacher</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Date (BS) *</label>
                        <x-bs-date-picker name="date" :value="bsDate(today(), 'Y-m-d')" :required="true"
                                          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500"/>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Period *</label>
                        <input type="text" name="period" placeholder="e.g., 1st Period, Morning" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                {{-- Attendance Type --}}
                <div class="mb-6 p-4 bg-slate-50 rounded-lg border border-slate-200">
                    <label class="block text-sm font-medium text-slate-700 mb-3">Attendance Category *</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="flex items-start gap-3 p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-white transition-colors" 
                               :class="attendanceType === 'class' ? 'bg-blue-50 border-blue-300' : 'bg-white'">
                            <input type="radio" name="attendance_type" value="class" x-model="attendanceType" required
                                   class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300">
                            <div>
                                <span class="text-sm font-medium text-slate-900">Class/Theory Session</span>
                                <p class="text-xs text-slate-500 mt-1">Regular classroom lectures and theory sessions</p>
                            </div>
                        </label>
                        <label class="flex items-start gap-3 p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-white transition-colors" 
                               :class="attendanceType === 'lab' ? 'bg-blue-50 border-blue-300' : 'bg-white'"
                               x-show="canMarkLab">
                            <input type="radio" name="attendance_type" value="lab" x-model="attendanceType"
                                   class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300">
                            <div>
                                <span class="text-sm font-medium text-slate-900">Lab/Practical Session</span>
                                <p class="text-xs text-slate-500 mt-1">Laboratory work and practical sessions (may require section filtering)</p>
                            </div>
                        </label>
                    </div>
                    <div x-show="!canMarkLab && selectedSubject" class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                        <p class="text-xs text-amber-700">
                            <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            This subject does not have lab/practical sessions. Only class attendance is available.
                        </p>
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
                                <th class="px-4 py-3 text-center">Section</th>
                                <th class="px-4 py-3 text-center">Present</th>
                                <th class="px-4 py-3 text-center">Absent</th>
                                <th class="px-4 py-3 text-center">Late</th>
                                <th class="px-4 py-3 text-center">Excused</th>
                                <th class="px-4 py-3 text-left">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($students as $student)
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
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">
                                            {{ $student->section ?: '—' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input type="radio" name="attendances[{{ $student->id }}]" value="present" 
                                               x-model="attendance[{{ $student->id }}]"
                                               class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-slate-300">
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input type="radio" name="attendances[{{ $student->id }}]" value="absent" 
                                               x-model="attendance[{{ $student->id }}]"
                                               class="h-4 w-4 text-red-600 focus:ring-red-500 border-slate-300">
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input type="radio" name="attendances[{{ $student->id }}]" value="late" 
                                               x-model="attendance[{{ $student->id }}]"
                                               class="h-4 w-4 text-amber-600 focus:ring-amber-500 border-slate-300">
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input type="radio" name="attendances[{{ $student->id }}]" value="excused" 
                                               x-model="attendance[{{ $student->id }}]"
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="text" name="remarks[{{ $student->id }}]" 
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
                        Mark Attendance
                    </button>
                </div>
            </form>
        </section>
    @elseif(request('program_id') && request('semester'))
        {{-- No Students Found --}}
        <section class="rounded-xl border border-slate-200/80 bg-white p-12 shadow-sm text-center">
            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <p class="mt-2 text-sm text-slate-500">No students found for the selected program and semester</p>
            <p class="text-xs text-slate-400">Please check if students are enrolled in this program/semester</p>
        </section>
    @endif
</div>

<script>
function attendanceMarker() {
    return {
        selectedSubject: '',
        attendanceType: 'class',
        canMarkLab: false,
        attendance: @json($students->pluck('id')->mapWithKeys(fn($id) => [$id => 'present'])->toArray()),
        
        updateAttendanceType() {
            const subjectSelect = document.querySelector('select[name="subject_id"]');
            const selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
            const subjectType = selectedOption.getAttribute('data-type');
            
            this.canMarkLab = ['practical', 'both'].includes(subjectType);
            
            if (!this.canMarkLab && this.attendanceType === 'lab') {
                this.attendanceType = 'class';
            }
        },
        
        markAll(status) {
            Object.keys(this.attendance).forEach(studentId => {
                this.attendance[studentId] = status;
            });
        }
    }
}
</script>
@endsection