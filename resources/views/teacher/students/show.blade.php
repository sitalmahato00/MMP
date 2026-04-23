@extends('layouts.app')

@section('title', $student->user->name . ' - Student Details')

@section('content')
<div class="space-y-6">
    {{-- ═══════════════════════════════════════════════════════════
         1. PAGE HEADER
    ═══════════════════════════════════════════════════════════ --}}
    <x-page-header 
        :title="$student->user->name" 
        subtitle="Student Profile & Academic Records (View Only)"
        icon="user"
    >
        <x-slot:breadcrumb>
            <x-breadcrumb-item href="{{ route('teacher.dashboard') }}" icon="home">Dashboard</x-breadcrumb-item>
            <x-breadcrumb-item href="{{ route('teacher.students.index') }}">Students</x-breadcrumb-item>
            <x-breadcrumb-item>{{ $student->user->name }}</x-breadcrumb-item>
        </x-slot:breadcrumb>

        <x-slot:actions>
            <x-btn href="{{ route('teacher.students.index') }}" variant="outline" icon="arrow-left">
                Back to Students
            </x-btn>
        </x-slot:actions>
    </x-page-header>

    {{-- ═══════════════════════════════════════════════════════════
         2. STUDENT INFO CARD
    ═══════════════════════════════════════════════════════════ --}}
    <x-card>
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
            {{-- Profile Photo & Basic Info --}}
            <div class="flex items-center gap-4 lg:flex-col lg:items-center lg:text-center">
                <img src="{{ $student->user->avatar_url }}" 
                    alt="{{ $student->user->name }}" 
                    class="h-20 w-20 rounded-full object-cover ring-4 ring-white shadow-lg lg:h-32 lg:w-32">
                <div class="min-w-0 flex-1 lg:flex-none">
                    <h2 class="text-xl font-bold text-slate-900">{{ $student->user->name }}</h2>
                    <p class="text-sm text-slate-600">{{ $student->student_no }}</p>
                    @if($student->roll_number)
                        <p class="text-sm text-slate-500">Roll: {{ $student->roll_number }}</p>
                    @endif
                    @php
                        $statusVariants = [
                            'active' => 'emerald',
                            'inactive' => 'slate',
                            'graduated' => 'blue',
                            'suspended' => 'red',
                        ];
                    @endphp
                    <div class="mt-2">
                        <x-badge :variant="$statusVariants[$student->status] ?? 'slate'">
                            {{ ucfirst($student->status) }}
                        </x-badge>
                    </div>
                </div>
            </div>

            {{-- Student Details Grid --}}
            <div class="flex-1 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {{-- Personal Information --}}
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 mb-3">Personal Information</h3>
                    <div class="space-y-2">
                        <div>
                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Email</p>
                            <p class="text-sm text-slate-900">{{ $student->user->email }}</p>
                        </div>
                        @if($student->user->phone)
                            <div>
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Phone</p>
                                <p class="text-sm text-slate-900">{{ $student->user->phone }}</p>
                            </div>
                        @endif
                        @if($student->user->gender)
                            <div>
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Gender</p>
                                <p class="text-sm text-slate-900 capitalize">{{ $student->user->gender }}</p>
                            </div>
                        @endif
                        @if($student->user->dob)
                            <div>
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Date of Birth</p>
                                <p class="text-sm text-slate-900">{{ bsDate($student->user->dob, 'F d, Y') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Academic Information --}}
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 mb-3">Academic Information</h3>
                    <div class="space-y-2">
                        <div>
                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Program</p>
                            <p class="text-sm text-slate-900">{{ $student->program->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Department</p>
                            <p class="text-sm text-slate-900">{{ $student->program->department->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Current Semester</p>
                            <p class="text-sm text-slate-900">Semester {{ $student->current_semester }}</p>
                        </div>
                        @if($student->section)
                            <div>
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Section</p>
                                <p class="text-sm text-slate-900">{{ $student->section }}</p>
                            </div>
                        @endif
                        @if($student->batch)
                            <div>
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Batch</p>
                                <p class="text-sm text-slate-900">{{ $student->batch }}</p>
                            </div>
                        @endif
                        @if($student->admission_date)
                            <div>
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Admission Date</p>
                                <p class="text-sm text-slate-900">{{ bsDate($student->admission_date, 'F d, Y') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Contact & Emergency --}}
                <div>
                    <h3 class="text-sm font-semibold text-slate-900 mb-3">Emergency Contact</h3>
                    <div class="space-y-2">
                        @if($student->guardian_name)
                            <div>
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Guardian Name</p>
                                <p class="text-sm text-slate-900">{{ $student->guardian_name }}</p>
                            </div>
                        @endif
                        @if($student->guardian_phone)
                            <div>
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Guardian Phone</p>
                                <p class="text-sm text-slate-900">{{ $student->guardian_phone }}</p>
                            </div>
                        @endif
                        @if($student->blood_group)
                            <div>
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Blood Group</p>
                                <p class="text-sm text-slate-900">{{ $student->blood_group }}</p>
                            </div>
                        @endif
                        @if($student->user->address)
                            <div>
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Address</p>
                                <p class="text-sm text-slate-900">{{ $student->user->address }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </x-card>

    {{-- ═══════════════════════════════════════════════════════════
         3. STATISTICS CARDS
    ═══════════════════════════════════════════════════════════ --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-card class="text-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-50 mx-auto">
                <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-slate-900">{{ $attendanceStats['attendance_rate'] }}%</p>
                <p class="text-sm text-slate-500">Attendance Rate</p>
                <p class="text-xs text-slate-400 mt-1">{{ $attendanceStats['present_count'] }}/{{ $attendanceStats['total_classes'] }} classes</p>
            </div>
        </x-card>

        <x-card class="text-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50 mx-auto">
                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.832 18.477 19.246 18 17.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-slate-900">{{ $marksSummary->count() }}</p>
                <p class="text-sm text-slate-500">Exam Records</p>
                <p class="text-xs text-slate-400 mt-1">Your subjects only</p>
            </div>
        </x-card>

        <x-card class="text-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-50 mx-auto">
                <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-slate-900">{{ $assignments->count() }}</p>
                <p class="text-sm text-slate-500">Assignments</p>
                <p class="text-xs text-slate-400 mt-1">Current semester</p>
            </div>
        </x-card>

        <x-card class="text-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-violet-50 mx-auto">
                <svg class="h-6 w-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM6 20h12a6 6 0 00-6-6 6 6 0 00-6 6z"/>
                </svg>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-slate-900">{{ $student->parents->count() }}</p>
                <p class="text-sm text-slate-500">Parents/Guardians</p>
                <p class="text-xs text-slate-400 mt-1">Registered</p>
            </div>
        </x-card>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         4. ATTENDANCE CHART & PARENTS
    ═══════════════════════════════════════════════════════════ --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Monthly Attendance Chart --}}
        <x-card>
            <x-slot:header>
                <h3 class="text-base font-semibold text-slate-900">Attendance Trend</h3>
                <p class="text-sm text-slate-500">Last 6 months attendance in your classes</p>
            </x-slot:header>
            
            @if($monthlyAttendance->count() > 0)
                <div class="h-64">
                    <canvas id="attendanceChart"></canvas>
                </div>
            @else
                <div class="flex items-center justify-center h-64">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <p class="mt-2 text-sm text-slate-500">No attendance data available</p>
                    </div>
                </div>
            @endif
        </x-card>

        {{-- Parents/Guardians --}}
        <x-card>
            <x-slot:header>
                <h3 class="text-base font-semibold text-slate-900">Parents/Guardians</h3>
                <p class="text-sm text-slate-500">Emergency contacts and family information</p>
            </x-slot:header>
            
            @if($student->parents->count() > 0)
                <div class="space-y-4">
                    @foreach($student->parents as $parent)
                        <div class="flex items-center gap-4 rounded-lg border border-slate-200 p-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                                <svg class="h-6 w-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-slate-900">{{ $parent->user->name }}</h4>
                                <p class="text-sm text-slate-600">{{ $parent->user->email }}</p>
                                @if($parent->user->phone)
                                    <p class="text-sm text-slate-500">{{ $parent->user->phone }}</p>
                                @endif
                                @if($parent->relation_to_student)
                                    <p class="text-xs text-slate-400 capitalize">{{ $parent->relation_to_student }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM6 20h12a6 6 0 00-6-6 6 6 0 00-6 6z"/>
                    </svg>
                    <p class="mt-2 text-sm text-slate-500">No parents/guardians registered</p>
                </div>
            @endif
        </x-card>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         5. MARKS & ASSIGNMENTS
    ═══════════════════════════════════════════════════════════ --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Recent Marks --}}
        <x-card>
            <x-slot:header>
                <h3 class="text-base font-semibold text-slate-900">Recent Marks</h3>
                <p class="text-sm text-slate-500">Exam results from your subjects</p>
            </x-slot:header>
            
            @if($marksSummary->count() > 0)
                <div class="space-y-3">
                    @foreach($marksSummary as $mark)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 p-4">
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-slate-900 truncate">{{ $mark->subject->name }}</h4>
                                <p class="text-sm text-slate-600">{{ $mark->exam->name ?? 'Exam' }}</p>
                                <p class="text-xs text-slate-500">{{ bsDate($mark->created_at, 'F d, Y') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-slate-900">{{ $mark->total_marks }}%</p>
                                @php
                                    $statusVariants = [
                                        'draft' => 'slate',
                                        'submitted' => 'amber',
                                        'approved' => 'blue',
                                        'published' => 'emerald',
                                    ];
                                @endphp
                                <x-badge :variant="$statusVariants[$mark->status] ?? 'slate'" size="sm">
                                    {{ ucfirst($mark->status) }}
                                </x-badge>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-slate-500">No marks recorded yet</p>
                </div>
            @endif
        </x-card>

        {{-- Assignments --}}
        <x-card>
            <x-slot:header>
                <h3 class="text-base font-semibold text-slate-900">Assignments</h3>
                <p class="text-sm text-slate-500">Current semester assignments from your subjects</p>
            </x-slot:header>
            
            @if($assignments->count() > 0)
                <div class="space-y-3">
                    @foreach($assignments->take(5) as $assignment)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 p-4">
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-slate-900 truncate">{{ $assignment->title }}</h4>
                                <p class="text-sm text-slate-600">{{ $assignment->subject_name }}</p>
                                <p class="text-xs text-slate-500">Due: {{ bsDate($assignment->due_date, 'F d, Y') }}</p>
                            </div>
                            <div class="text-right">
                                @if($assignment->submission_id)
                                    @php
                                        $submissionVariants = [
                                            'submitted' => 'blue',
                                            'graded' => 'emerald',
                                            'late' => 'amber',
                                        ];
                                    @endphp
                                    <x-badge :variant="$submissionVariants[$assignment->submission_status] ?? 'blue'" size="sm">
                                        {{ ucfirst($assignment->submission_status) }}
                                    </x-badge>
                                    @if($assignment->obtained_marks)
                                        <p class="text-sm font-semibold text-slate-900 mt-1">{{ $assignment->obtained_marks }}/{{ $assignment->total_marks }}</p>
                                    @endif
                                @else
                                    <x-badge variant="slate" size="sm">Not Submitted</x-badge>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-slate-500">No assignments found</p>
                </div>
            @endif
        </x-card>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($monthlyAttendance->count() > 0)
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    const attendanceData = @json($monthlyAttendance);
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: attendanceData.map(item => item.label),
            datasets: [{
                label: 'Present',
                data: attendanceData.map(item => item.present),
                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                borderColor: 'rgb(16, 185, 129)',
                borderWidth: 1
            }, {
                label: 'Absent',
                data: attendanceData.map(item => item.absent),
                backgroundColor: 'rgba(239, 68, 68, 0.8)',
                borderColor: 'rgb(239, 68, 68)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(148, 163, 184, 0.1)',
                    },
                    ticks: {
                        color: '#64748b',
                        font: {
                            size: 11
                        }
                    }
                },
                x: {
                    grid: {
                        display: false,
                    },
                    ticks: {
                        color: '#64748b',
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });
    @endif
});
</script>
@endpush
@endsection