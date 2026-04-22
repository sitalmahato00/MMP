@extends('layouts.app')

@section('title', 'Record Attendance')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl lg:rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-emerald-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Attendance</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        Record Attendance
                    </h1>
                </div>
                <a href="{{ route('teacher.attendance.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back
                </a>
            </div>
        </div>
    </section>

    {{-- Form --}}
    <form action="{{ route('teacher.attendance.store') }}" method="POST" class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-sm space-y-6">
        @csrf

        {{-- Subject Selection --}}
        <div>
            <label class="block text-sm font-semibold text-slate-900 mb-2">Subject *</label>
            <select name="subject_id" id="subject_id" required class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('subject_id') border-rose-500 @enderror">
                <option value="">Select a subject...</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                        {{ $subject->name }} ({{ $subject->code }})
                    </option>
                @endforeach
            </select>
            @error('subject_id')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Date and Period --}}
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Date *</label>
                <input type="date" name="date" required value="{{ old('date') }}" 
                    class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('date') border-rose-500 @enderror">
                @error('date')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-900 mb-2">Period</label>
                <input type="text" name="period" placeholder="e.g., Period 1, 9:00 AM" value="{{ old('period') }}" 
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
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-slate-500">
                                    Select a subject to load students
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex gap-3 border-t border-slate-100 pt-6">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Save Attendance
            </button>
            <a href="{{ route('teacher.attendance.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-6 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
document.getElementById('subject_id').addEventListener('change', async function() {
    const subjectId = this.value;
    const studentsList = document.getElementById('students-list');
    
    if (!subjectId) {
        studentsList.innerHTML = '<tr><td colspan="3" class="px-4 py-8 text-center text-slate-500">Select a subject to load students</td></tr>';
        return;
    }

    try {
        // Fetch students for this subject
        const response = await fetch(`/api/subjects/${subjectId}/students`);
        const students = await response.json();
        
        if (students.length === 0) {
            studentsList.innerHTML = '<tr><td colspan="3" class="px-4 py-8 text-center text-slate-500">No students enrolled in this subject</td></tr>';
            return;
        }

        let html = '';
        students.forEach((student, index) => {
            html += `
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <div>
                            <p class="font-semibold text-slate-900">${student.user.name}</p>
                            <p class="text-xs text-slate-500">${student.student_no}</p>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <select name="attendances[${index}][student_id]" class="hidden" value="${student.id}">
                            <option value="${student.id}"></option>
                        </select>
                        <input type="hidden" name="attendances[${index}][student_id]" value="${student.id}">
                        <select name="attendances[${index}][status]" required class="rounded-lg border border-slate-300 px-3 py-1 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                        </select>
                    </td>
                    <td class="px-4 py-3">
                        <input type="text" name="attendances[${index}][remarks]" placeholder="Optional remarks" 
                            class="w-full rounded-lg border border-slate-300 px-3 py-1 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </td>
                </tr>
            `;
        });
        
        studentsList.innerHTML = html;
    } catch (error) {
        console.error('Error loading students:', error);
        studentsList.innerHTML = '<tr><td colspan="3" class="px-4 py-8 text-center text-rose-500">Error loading students</td></tr>';
    }
});
</script>
@endsection
