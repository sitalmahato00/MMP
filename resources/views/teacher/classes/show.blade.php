@extends('layouts.app')

@section('title', $subject->name . ' - Students')

@section('content')
<div class="space-y-6">
    {{-- ═══════════════════════════════════════════════════════════
         1. PAGE HEADER
    ═══════════════════════════════════════════════════════════ --}}
    <x-page-header 
        :title="$subject->name" 
        :subtitle="$subject->program->name . ' - Semester ' . $subject->semester"
        icon="user-group"
    >
        <x-slot:breadcrumb>
            <x-breadcrumb-item href="{{ route('teacher.dashboard') }}" icon="home">Dashboard</x-breadcrumb-item>
            <x-breadcrumb-item href="{{ route('teacher.classes.index') }}">My Classes</x-breadcrumb-item>
            <x-breadcrumb-item>{{ $subject->name }}</x-breadcrumb-item>
        </x-slot:breadcrumb>

        <x-slot:actions>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-500">
                    {{ $totalStudents }} total students
                </span>
                <span class="inline-flex items-center gap-2 rounded-lg bg-emerald-100 px-3 py-1.5 text-xs font-medium text-emerald-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    {{ $activeStudents }} active
                </span>
                <x-btn href="{{ route('teacher.classes.index') }}" variant="secondary" icon="arrow-left">
                    Back to Classes
                </x-btn>
            </div>
        </x-slot:actions>
    </x-page-header>

    {{-- ═══════════════════════════════════════════════════════════
         2. SUBJECT INFO CARD
    ═══════════════════════════════════════════════════════════ --}}
    <x-card>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Subject Code</p>
                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $subject->code }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Department</p>
                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $subject->program->department->name }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Credit Hours</p>
                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $subject->credit_hours ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Type</p>
                <p class="mt-1 text-sm font-semibold text-slate-900 capitalize">{{ $subject->type ?? 'Theory' }}</p>
            </div>
        </div>
    </x-card>

    {{-- ═══════════════════════════════════════════════════════════
         3. SEARCH & FILTERS
    ═══════════════════════════════════════════════════════════ --}}
    <x-search-filter>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <x-input 
                    name="search" 
                    placeholder="Search students..." 
                    :value="request('search')"
                    icon="search"
                />
            </div>
            
            <div>
                <x-select name="section" placeholder="All Sections">
                    @foreach($sections as $section)
                        <option value="{{ $section }}" {{ request('section') == $section ? 'selected' : '' }}>
                            Section {{ $section }}
                        </option>
                    @endforeach
                </x-select>
            </div>

            <div>
                <x-select name="status" placeholder="All Status">
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="graduated" {{ request('status') == 'graduated' ? 'selected' : '' }}>Graduated</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                </x-select>
            </div>
        </div>
    </x-search-filter>

    {{-- ═══════════════════════════════════════════════════════════
         4. STUDENTS LIST WITH VIEW TOGGLE
    ═══════════════════════════════════════════════════════════ --}}
    <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm" 
         x-data="{ 
             view: localStorage.getItem('mmp_teacher_class_students_view') || 'table',
             toggleView(newView) {
                 this.view = newView;
                 localStorage.setItem('mmp_teacher_class_students_view', newView);
             }
         }">
        <div class="border-b border-slate-100 px-6 py-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Enrolled Students</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Showing {{ $students->count() }} of {{ $students->total() }} students
                    </p>
                </div>
                
                <div class="flex items-center gap-2">
                    <div class="flex rounded-lg bg-slate-100 p-1">
                        <button @click="toggleView('table')" 
                                :class="view === 'table' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                class="flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition-all">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            Table
                        </button>
                        <button @click="toggleView('cards')" 
                                :class="view === 'cards' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                class="flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition-all">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            Cards
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table View --}}
        <div x-show="view === 'table'" class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50">
                            <th class="px-6 py-3 text-left font-semibold text-slate-700">Student</th>
                            <th class="px-6 py-3 text-left font-semibold text-slate-700">Student No.</th>
                            <th class="px-6 py-3 text-center font-semibold text-slate-700">Section</th>
                            <th class="px-6 py-3 text-center font-semibold text-slate-700">Attendance</th>
                            <th class="px-6 py-3 text-center font-semibold text-slate-700">Status</th>
                            <th class="px-6 py-3 text-right font-semibold text-slate-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($students as $student)
                            @php
                                $attendanceRate = $student->total_attendance > 0 
                                    ? round(($student->present_count / $student->total_attendance) * 100, 1) 
                                    : 0;
                            @endphp
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $student->user->avatar_url }}" 
                                            alt="{{ $student->user->name }}" 
                                            class="h-10 w-10 rounded-full object-cover">
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900 truncate">{{ $student->user->name }}</p>
                                            <p class="text-xs text-slate-500 truncate">{{ $student->user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-slate-900">{{ $student->student_no }}</p>
                                        @if($student->roll_number)
                                            <p class="text-xs text-slate-500">Roll: {{ $student->roll_number }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($student->section)
                                        <x-badge variant="slate">{{ $student->section }}</x-badge>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-sm font-semibold {{ $attendanceRate >= 75 ? 'text-emerald-600' : ($attendanceRate >= 50 ? 'text-amber-600' : 'text-red-600') }}">
                                            {{ $attendanceRate }}%
                                        </span>
                                        <span class="text-xs text-slate-500">{{ $student->present_count }}/{{ $student->total_attendance }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $statusVariants = [
                                            'active' => 'emerald',
                                            'inactive' => 'slate',
                                            'graduated' => 'blue',
                                            'suspended' => 'red',
                                        ];
                                    @endphp
                                    <x-badge :variant="$statusVariants[$student->status] ?? 'slate'">
                                        {{ ucfirst($student->status) }}
                                    </x-badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <x-btn 
                                        href="{{ route('teacher.students.show', $student) }}" 
                                        variant="ghost" 
                                        size="sm"
                                        icon="eye"
                                    >
                                        View
                                    </x-btn>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12">
                                    <x-empty-state 
                                        icon="user-group"
                                        title="No students found"
                                        description="No students match your current filters."
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Cards View --}}
        <div x-show="view === 'cards'" class="p-6">
            @if($students->count() > 0)
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($students as $student)
                        @php
                            $attendanceRate = $student->total_attendance > 0 
                                ? round(($student->present_count / $student->total_attendance) * 100, 1) 
                                : 0;
                            $statusVariants = [
                                'active' => 'emerald',
                                'inactive' => 'slate',
                                'graduated' => 'blue',
                                'suspended' => 'red',
                            ];
                        @endphp
                        <x-card class="group cursor-pointer transition-all hover:shadow-md hover:-translate-y-0.5">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $student->user->avatar_url }}" 
                                        alt="{{ $student->user->name }}" 
                                        class="h-12 w-12 rounded-full object-cover">
                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-semibold text-slate-900 truncate">{{ $student->user->name }}</h3>
                                        <p class="text-xs text-slate-500 truncate">{{ $student->student_no }}</p>
                                    </div>
                                </div>
                                <x-badge :variant="$statusVariants[$student->status] ?? 'slate'" size="sm">
                                    {{ ucfirst($student->status) }}
                                </x-badge>
                            </div>
                            
                            <div class="mt-4 space-y-2">
                                @if($student->section)
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-slate-500">Section</span>
                                        <span class="font-medium text-slate-900">{{ $student->section }}</span>
                                    </div>
                                @endif
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-500">Attendance</span>
                                    <span class="font-medium {{ $attendanceRate >= 75 ? 'text-emerald-600' : ($attendanceRate >= 50 ? 'text-amber-600' : 'text-red-600') }}">
                                        {{ $attendanceRate }}%
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-500">Classes</span>
                                    <span class="font-medium text-slate-900">{{ $student->present_count }}/{{ $student->total_attendance }}</span>
                                </div>
                                @if($student->roll_number)
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-slate-500">Roll No.</span>
                                        <span class="font-medium text-slate-900">{{ $student->roll_number }}</span>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="mt-4 pt-4 border-t border-slate-100">
                                <x-btn 
                                    href="{{ route('teacher.students.show', $student) }}" 
                                    variant="ghost" 
                                    size="sm" 
                                    class="w-full justify-center"
                                    icon="eye"
                                >
                                    View Details
                                </x-btn>
                            </div>
                        </x-card>
                    @endforeach
                </div>
            @else
                <x-empty-state 
                    icon="user-group"
                    title="No students found"
                    description="No students match your current filters."
                />
            @endif
        </div>

        {{-- Pagination --}}
        @if($students->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $students->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
