@extends('layouts.app')

@section('title', 'Mark Attendance')

@section('content')
<div class="space-y-6">
    {{-- Notifications --}}
    <div id="notification" class="hidden fixed top-4 right-4 z-50 max-w-md"></div>

    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl lg:rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Teacher</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        Mark Attendance
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">Mark attendance for any class or lab session</p>
                </div>
                <a href="{{ route('teacher.attendance.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Attendance
                </a>
            </div>
        </div>
    </section>

    {{-- Form --}}
    <form action="{{ route('teacher.attendance.store') }}" method="POST" class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-sm space-y-6" id="attendance-form" onsubmit="return handleFormSubmit(event)">
        @csrf

        {{-- Select Class Details --}}
        <div class="space-y-4">
            <h2 class="text-sm font-semibold text-slate-900">Select Class Details</h2>
            
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Subject *</label>
                    <select name="subject_id" id="subject_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('subject_id') border-rose-500 @enderror">
                        <option value="">Select subject...</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" data-type="{{ $subject->type }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Date (BS) *</label>
                    <x-bs-date-picker name="date" :value="old('date')" placeholder="YYYY-MM-DD" required
                                      class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('date') border-rose-500 @enderror"/>
                    @error('date')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Period *</label>
                    <input type="text" name="period" placeholder="e.g., 1st Period, Morning" value="{{ old('period') }}" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            <button type="button" id="load-students-btn" class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                Load Students
            </button>
        </div>

        {{-- Attendance Category --}}
        <div class="border-t border-slate-100 pt-6 space-y-3" id="category-section">
            <label class="block text-xs font-medium text-slate-700">Attendance Category *</label>
            <div class="grid gap-4 sm:grid-cols-2" id="category-options">
                <label class="flex items-start gap-3 rounded-lg border border-slate-200 p-4 cursor-pointer hover:bg-slate-50 @error('category') border-rose-500 @enderror" id="class-option">
                    <input type="radio" name="category" value="class" {{ old('category', 'class') === 'class' ? 'checked' : '' }} class="mt-1">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Class/Theory Session</p>
                        <p class="text-xs text-slate-500">Regular classroom lectures and theory sessions</p>
                    </div>
                </label>
                <label class="flex items-start gap-3 rounded-lg border border-slate-200 p-4 cursor-pointer hover:bg-slate-50" id="lab-option">
                    <input type="radio" name="category" value="lab" {{ old('category') === 'lab' ? 'checked' : '' }} class="mt-1">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Lab/Practical Session</p>
                        <p class="text-xs text-slate-500">Laboratory work and practical sessions</p>
                    </div>
                </label>
            </div>
            @error('category')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Quick Actions --}}
        <div class="flex gap-2">
            <button type="button" id="mark-all-present" class="inline-flex items-center gap-2 rounded-lg bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-200">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Mark All Present
            </button>
            <button type="button" id="mark-all-absent" class="inline-flex items-center gap-2 rounded-lg bg-rose-100 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-200">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Mark All Absent
            </button>
            <button type="button" id="mark-all-late" class="inline-flex items-center gap-2 rounded-lg bg-amber-100 px-4 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-200">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Mark All Late
            </button>
        </div>

        {{-- Students Count --}}
        <div id="students-count" class="text-sm text-slate-600">
            No students loaded
        </div>

        {{-- Students Attendance Table --}}
        <div id="students-container" class="space-y-3">
            <div class="rounded-lg border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50">
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Roll No.</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Student Name</th>
                                <th class="px-4 py-3 text-center font-semibold text-slate-700">Present</th>
                                <th class="px-4 py-3 text-center font-semibold text-slate-700">Absent</th>
                                <th class="px-4 py-3 text-center font-semibold text-slate-700">Late</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Remarks</th>
                            </tr>
                        </thead>
                        <tbody id="students-list" class="divide-y divide-slate-100">
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                    Click "Load Students" to display students
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
let currentStudents = [];

// Notification function
function showNotification(message, type = 'success', duration = 5000) {
    const notificationDiv = document.getElementById('notification');
    
    let bgColor, borderColor, textColor, icon;
    
    if (type === 'success') {
        bgColor = 'bg-emerald-50';
        borderColor = 'border-emerald-200';
        textColor = 'text-emerald-800';
        icon = '<svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
    } else if (type === 'error') {
        bgColor = 'bg-rose-50';
        borderColor = 'border-rose-200';
        textColor = 'text-rose-800';
        icon = '<svg class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
    } else if (type === 'warning') {
        bgColor = 'bg-amber-50';
        borderColor = 'border-amber-200';
        textColor = 'text-amber-800';
        icon = '<svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 4v2M7.08 6.47a9 9 0 1 1 9.84 0"/></svg>';
    } else {
        bgColor = 'bg-blue-50';
        borderColor = 'border-blue-200';
        textColor = 'text-blue-800';
        icon = '<svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
    }
    
    notificationDiv.innerHTML = `
        <div class="rounded-lg border ${borderColor} ${bgColor} p-4 shadow-lg">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    ${icon}
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium ${textColor}">${message}</p>
                </div>
                <button onclick="document.getElementById('notification').classList.add('hidden')" class="flex-shrink-0 ${textColor} hover:opacity-75">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    `;
    
    notificationDiv.classList.remove('hidden');
    
    if (duration > 0) {
        setTimeout(() => {
            notificationDiv.classList.add('hidden');
        }, duration);
    }
}

// Handle subject change to show/hide category options
document.getElementById('subject_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const subjectType = selectedOption.getAttribute('data-type');
    
    const classOption = document.getElementById('class-option');
    const labOption = document.getElementById('lab-option');
    const classRadio = classOption.querySelector('input[value="class"]');
    const labRadio = labOption.querySelector('input[value="lab"]');
    
    // Reset all options to visible first
    classOption.style.display = 'flex';
    labOption.style.display = 'flex';
    
    // Uncheck both radios first
    classRadio.checked = false;
    labRadio.checked = false;
    
    if (!subjectType) {
        // No subject selected, show both but don't check any
        return;
    }
    
    if (subjectType === 'lab' || subjectType === 'practical') {
        // Show only lab option
        classOption.style.display = 'none';
        labOption.style.display = 'flex';
        labRadio.checked = true;
    } else if (subjectType === 'theory') {
        // Show only class option
        classOption.style.display = 'flex';
        labOption.style.display = 'none';
        classRadio.checked = true;
    } else if (subjectType === 'both') {
        // Show both options
        classOption.style.display = 'flex';
        labOption.style.display = 'flex';
        classRadio.checked = true;
    } else {
        // Default to class if unknown type
        classOption.style.display = 'flex';
        labOption.style.display = 'none';
        classRadio.checked = true;
    }
});

// Trigger change event on page load if subject is pre-selected
if (document.getElementById('subject_id').value) {
    document.getElementById('subject_id').dispatchEvent(new Event('change'));
}

document.getElementById('load-students-btn').addEventListener('click', async function() {
    const subjectId = document.getElementById('subject_id').value;
    
    if (!subjectId) {
        alert('Please select a subject first');
        return;
    }

    try {
        const response = await fetch(`/teacher/attendance/load-students/${subjectId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const students = await response.json();
        
        currentStudents = students;
        
        if (students.length === 0) {
            document.getElementById('students-count').textContent = 'No students found';
            document.getElementById('students-list').innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No students enrolled in this subject</td></tr>';
            return;
        }

        document.getElementById('students-count').textContent = `${students.length} students found`;

        let html = '';
        students.forEach((student, index) => {
            html += `
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <span class="text-sm font-medium text-slate-900">${student.student_no}</span>
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-sm font-semibold text-slate-900">${student.user.name}</p>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <input type="hidden" name="attendances[${index}][student_id]" value="${student.id}">
                        <input type="radio" name="attendances[${index}][status]" value="present" class="status-radio">
                    </td>
                    <td class="px-4 py-3 text-center">
                        <input type="radio" name="attendances[${index}][status]" value="absent" class="status-radio">
                    </td>
                    <td class="px-4 py-3 text-center">
                        <input type="radio" name="attendances[${index}][status]" value="late" class="status-radio">
                    </td>
                    <td class="px-4 py-3">
                        <input type="text" name="attendances[${index}][remarks]" placeholder="Optional remarks" 
                            class="w-full rounded-lg border border-slate-300 px-2 py-1 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </td>
                </tr>
            `;
        });
        
        document.getElementById('students-list').innerHTML = html;
    } catch (error) {
        console.error('Error loading students:', error);
        alert('Error loading students: ' + error.message);
        document.getElementById('students-list').innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-rose-500">Error loading students: ' + error.message + '</td></tr>';
    }
});

// Mark all present
document.getElementById('mark-all-present').addEventListener('click', function() {
    document.querySelectorAll('input[value="present"]').forEach(radio => radio.checked = true);
});

// Mark all absent
document.getElementById('mark-all-absent').addEventListener('click', function() {
    document.querySelectorAll('input[value="absent"]').forEach(radio => radio.checked = true);
});

// Mark all late
document.getElementById('mark-all-late').addEventListener('click', function() {
    document.querySelectorAll('input[value="late"]').forEach(radio => radio.checked = true);
});

// Handle form submission
function handleFormSubmit(event) {
    const form = document.getElementById('attendance-form');
    const subjectId = document.getElementById('subject_id').value;
    const date = form.querySelector('input[name="date"]').value;
    const period = form.querySelector('input[name="period"]').value;
    const category = form.querySelector('input[name="category"]:checked');
    const studentRows = document.querySelectorAll('#students-list tr');
    
    // Validation
    if (!subjectId) {
        showNotification('Please select a subject', 'error');
        event.preventDefault();
        return false;
    }
    
    if (!date) {
        showNotification('Please select a date', 'error');
        event.preventDefault();
        return false;
    }
    
    if (!period) {
        showNotification('Please enter a period', 'error');
        event.preventDefault();
        return false;
    }
    
    if (!category) {
        showNotification('Please select an attendance category', 'error');
        event.preventDefault();
        return false;
    }
    
    if (studentRows.length === 0 || studentRows[0].querySelector('td[colspan]')) {
        showNotification('Please load students first', 'error');
        event.preventDefault();
        return false;
    }
    
    // Check if at least one student has attendance marked
    const hasMarkedAttendance = Array.from(document.querySelectorAll('input[name^="attendances"][value]')).some(input => input.checked);
    if (!hasMarkedAttendance) {
        showNotification('Please mark attendance for at least one student', 'error');
        event.preventDefault();
        return false;
    }
    
    // Don't show loading notification - let Laravel handle the success message
    return true;
}
</script>
@endsection
