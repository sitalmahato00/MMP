@extends('layouts.app')

@section('title', 'HOD Dashboard')

@section('content')
@php
    $sessionName = $session?->name ?? '2081-2082';
    $semesters   = $runningSemesters ?? ['Sem 1', 'Sem 3'];

    $studentCount   = $data['student_count'] ?? 0;
    $teacherCount   = $data['teacher_count'] ?? 0;
    $programCount   = $data['program_count'] ?? 0;
    $attendanceRate = $data['attendance_rate'] ?? 0;

    $gd          = $gradeDistribution ?? ['labels' => [], 'data' => [], 'counts' => [], 'hasData' => false];
    $gradeColors = ['#2563EB', '#F97316', '#DC2626', '#7C3AED', '#EAB308', '#991B1B'];
    $defaultGradeLabels = ['A+ (90-100)', 'A (80-89)', 'B+ (70-79)', 'B (60-69)', 'C (50-59)', 'F (<50)'];
    $gradeLabels = !empty($gd['labels']) ? $gd['labels'] : $defaultGradeLabels;
    $gradePcts   = !empty($gd['data']) ? $gd['data'] : [10, 40, 20, 30, 0, 0];
    $gradeCounts = !empty($gd['counts']) ? $gd['counts'] : [1, 4, 2, 3, 0, 0];

    $latestAttDay = !empty($attendanceChartData['7']['labels']) ? end($attendanceChartData['7']['labels']) : '17 Bhadra';
    $latestAttRate = !empty($attendanceChartData['7']['data']) ? end($attendanceChartData['7']['data']) : $attendanceRate;
@endphp

<div id="hod-dashboard" class="mx-auto max-w-[1520px] space-y-5">

    {{-- ══════════════════════════════════════════════
         ROW 1 · TOP WELCOME BANNER
    ══════════════════════════════════════════════ --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-5 lg:p-6 shadow-xs relative overflow-hidden flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

        {{-- Left: Greeting + Badges --}}
        <div class="flex flex-col justify-center min-w-0 z-10 flex-1">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 flex items-center gap-2">
                {{ $greeting }}, {{ auth()->user()->name }}! <span class="text-2xl">👋</span>
            </h1>
            <p class="mt-1 text-xs text-gray-500 font-normal">
                @if($department)
                    Welcome back to the {{ $department->name }} department dashboard.
                @else
                    Welcome back to the department dashboard.
                @endif
            </p>

            {{-- Badges Row --}}
            <div class="mt-4 flex flex-wrap items-center gap-2">
                <!-- AY Badge -->
                <span class="inline-flex items-center gap-1.5 rounded-lg border border-[#0000FF] bg-blue-50/50 px-2.5 py-1 text-xs font-semibold text-[#0000FF]">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    AY 2081
                </span>

                <!-- Session Badge -->
                <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-600">
                    <span class="h-2 w-2 rounded-full border border-gray-400"></span>
                    {{ $sessionName }}
                </span>

                <!-- Semester Badges -->
                @foreach($semesters as $idx => $sem)
                    @php
                        $isOdd = ($idx % 2 === 0);
                    @endphp
                    <span class="inline-flex items-center gap-1.5 rounded-lg border {{ $isOdd ? 'border-blue-200 bg-blue-50/50 text-blue-700' : 'border-red-200 bg-red-50/50 text-red-700' }} px-2.5 py-1 text-xs font-medium">
                        <span class="h-2 w-2 rounded-full {{ $isOdd ? 'bg-[#0000FF]' : 'bg-[#FF0000]' }}"></span>
                        {{ $sem }}
                    </span>
                @endforeach
            </div>
        </div>

        {{-- Center: College Building Architectural Line Art Illustration --}}
        <div class="hidden md:flex flex-shrink-0 items-center justify-center pointer-events-none px-4">
            <svg class="h-24 w-44 text-slate-700" viewBox="0 0 220 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Roof & Flag -->
                <path d="M110 8 L110 22" stroke="#DC2626" stroke-width="2" stroke-linecap="round"/>
                <path d="M110 8 C115 6 120 11 125 9 L125 15 C120 17 115 12 110 14 Z" fill="#DC2626"/>
                <path d="M95 24 L110 14 L125 24 Z" fill="#FEE2E2" stroke="#DC2626" stroke-width="1.8" stroke-linejoin="round"/>
                
                <!-- Main Central Pediment -->
                <path d="M80 40 L110 22 L140 40 Z" fill="#FEF3C7" stroke="#EA580C" stroke-width="2" stroke-linejoin="round"/>
                <rect x="85" y="40" width="50" height="42" fill="#FFFBEB" stroke="#EA580C" stroke-width="2"/>
                
                <!-- Windows in Center -->
                <rect x="92" y="46" width="8" height="10" rx="1" fill="#FEE2E2" stroke="#DC2626" stroke-width="1.2"/>
                <rect x="106" y="46" width="8" height="10" rx="1" fill="#FEE2E2" stroke="#DC2626" stroke-width="1.2"/>
                <rect x="120" y="46" width="8" height="10" rx="1" fill="#FEE2E2" stroke="#DC2626" stroke-width="1.2"/>
                <rect x="92" y="60" width="8" height="10" rx="1" fill="#FEE2E2" stroke="#DC2626" stroke-width="1.2"/>
                <rect x="106" y="60" width="8" height="10" rx="1" fill="#FEE2E2" stroke="#DC2626" stroke-width="1.2"/>
                <rect x="120" y="60" width="8" height="10" rx="1" fill="#FEE2E2" stroke="#DC2626" stroke-width="1.2"/>
                
                <!-- Left Wing -->
                <rect x="42" y="50" width="43" height="32" fill="#EFF6FF" stroke="#2563EB" stroke-width="2"/>
                <rect x="49" y="56" width="7" height="9" rx="1" fill="#DBEAFE" stroke="#2563EB" stroke-width="1.2"/>
                <rect x="62" y="56" width="7" height="9" rx="1" fill="#DBEAFE" stroke="#2563EB" stroke-width="1.2"/>
                <rect x="74" y="56" width="7" height="9" rx="1" fill="#DBEAFE" stroke="#2563EB" stroke-width="1.2"/>
                <rect x="49" y="68" width="7" height="9" rx="1" fill="#DBEAFE" stroke="#2563EB" stroke-width="1.2"/>
                <rect x="62" y="68" width="7" height="9" rx="1" fill="#DBEAFE" stroke="#2563EB" stroke-width="1.2"/>
                <rect x="74" y="68" width="7" height="9" rx="1" fill="#DBEAFE" stroke="#2563EB" stroke-width="1.2"/>

                <!-- Right Wing -->
                <rect x="135" y="50" width="43" height="32" fill="#EFF6FF" stroke="#2563EB" stroke-width="2"/>
                <rect x="142" y="56" width="7" height="9" rx="1" fill="#DBEAFE" stroke="#2563EB" stroke-width="1.2"/>
                <rect x="154" y="56" width="7" height="9" rx="1" fill="#DBEAFE" stroke="#2563EB" stroke-width="1.2"/>
                <rect x="166" y="56" width="7" height="9" rx="1" fill="#DBEAFE" stroke="#2563EB" stroke-width="1.2"/>
                <rect x="142" y="68" width="7" height="9" rx="1" fill="#DBEAFE" stroke="#2563EB" stroke-width="1.2"/>
                <rect x="154" y="68" width="7" height="9" rx="1" fill="#DBEAFE" stroke="#2563EB" stroke-width="1.2"/>
                <rect x="166" y="68" width="7" height="9" rx="1" fill="#DBEAFE" stroke="#2563EB" stroke-width="1.2"/>

                <!-- Ground Line & Base -->
                <path d="M20 82 L200 82" stroke="#2563EB" stroke-width="2" stroke-linecap="round"/>
                <path d="M30 86 L190 86" stroke="#94A3B8" stroke-width="1.5" stroke-linecap="round"/>

                <!-- Left & Right Trees -->
                <path d="M32 82 L32 72" stroke="#059669" stroke-width="2"/>
                <circle cx="32" cy="66" r="7" fill="#DCFCE7" stroke="#059669" stroke-width="1.5"/>
                <path d="M190 82 L190 72" stroke="#DC2626" stroke-width="2"/>
                <circle cx="190" cy="66" r="7" fill="#FEE2E2" stroke="#DC2626" stroke-width="1.5"/>
            </svg>
        </div>

        {{-- Right: 3 Quick Action Buttons + Timestamp --}}
        <div class="flex flex-col items-start lg:items-end justify-between gap-4 z-10 flex-shrink-0">
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Add Student (Blue) -->
                <a href="{{ route('hod.students.create') }}"
                   class="inline-flex items-center gap-1.5 rounded-xl bg-[#0000FF] hover:bg-blue-800 text-white px-3.5 py-2 text-xs font-bold shadow-xs transition-all hover:shadow hover:-translate-y-0.5">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Add Student</span>
                </a>

                <!-- Create Notice (Orange) -->
                <a href="{{ route('hod.notices.create') }}"
                   class="inline-flex items-center gap-1.5 rounded-xl bg-[#FF6600] hover:bg-[#EA580C] text-white px-3.5 py-2 text-xs font-bold shadow-xs transition-all hover:shadow hover:-translate-y-0.5">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                    <span>Create Notice</span>
                </a>

                <!-- Attendance Overview (Red) -->
                <a href="{{ route('hod.attendance.index') }}"
                   class="inline-flex items-center gap-1.5 rounded-xl bg-[#FF0000] hover:bg-[#DC2626] text-white px-3.5 py-2 text-xs font-bold shadow-xs transition-all hover:shadow hover:-translate-y-0.5">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Attendance Overview</span>
                </a>
            </div>

            <!-- Timestamp Line -->
            <div class="flex items-center gap-2 text-[11px] text-gray-500 font-medium">
                <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ bsDate($lastUpdated, 'Y, F d') }}</span>
                <span class="text-gray-300">|</span>
                <span>Updated at {{ $lastUpdated->format('h:i A') }}</span>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         ROW 2 · 4 KPI METRIC CARDS WITH SPARKLINE BARS
    ══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- 1. Total Students --}}
        <a href="{{ route('hod.students.index') }}"
           class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs flex items-center justify-between transition-all hover:shadow-md hover:-translate-y-0.5">
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-[#2563EB] text-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-2xl font-bold tracking-tight text-gray-900 leading-none">{{ $studentCount }}</p>
                    <p class="text-xs font-semibold text-gray-700 truncate mt-1">Total Students</p>
                    <p class="text-[10px] text-gray-400 truncate mt-0.5">Active students in department</p>
                </div>
            </div>
            <!-- Mini Sparkline Bars (Blue) -->
            <div class="flex items-end gap-1 h-8 flex-shrink-0 pl-2">
                <span class="w-1 bg-blue-300 rounded-full h-3"></span>
                <span class="w-1 bg-blue-400 rounded-full h-5"></span>
                <span class="w-1 bg-blue-500 rounded-full h-4"></span>
                <span class="w-1 bg-blue-600 rounded-full h-8"></span>
            </div>
        </a>

        {{-- 2. Department Teachers --}}
        <a href="{{ route('hod.teachers.index') }}"
           class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs flex items-center justify-between transition-all hover:shadow-md hover:-translate-y-0.5">
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-[#059669] text-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-2xl font-bold tracking-tight text-gray-900 leading-none">{{ $teacherCount }}</p>
                    <p class="text-xs font-semibold text-gray-700 truncate mt-1">Department Teachers</p>
                    <p class="text-[10px] text-gray-400 truncate mt-0.5">Total assigned teachers</p>
                </div>
            </div>
            <!-- Mini Sparkline Bars (Green) -->
            <div class="flex items-end gap-1 h-8 flex-shrink-0 pl-2">
                <span class="w-1 bg-emerald-300 rounded-full h-3"></span>
                <span class="w-1 bg-emerald-400 rounded-full h-6"></span>
                <span class="w-1 bg-emerald-500 rounded-full h-4"></span>
                <span class="w-1 bg-emerald-600 rounded-full h-7"></span>
            </div>
        </a>

        {{-- 3. Programs Offered --}}
        <a href="{{ route('hod.subjects.index') }}"
           class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs flex items-center justify-between transition-all hover:shadow-md hover:-translate-y-0.5">
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-[#7C3AED] text-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-2xl font-bold tracking-tight text-gray-900 leading-none">{{ $programCount }}</p>
                    <p class="text-xs font-semibold text-gray-700 truncate mt-1">Programs Offered</p>
                    <p class="text-[10px] text-gray-400 truncate mt-0.5">Active programs</p>
                </div>
            </div>
            <!-- Mini Sparkline Bars (Purple) -->
            <div class="flex items-end gap-1 h-8 flex-shrink-0 pl-2">
                <span class="w-1 bg-purple-300 rounded-full h-4"></span>
                <span class="w-1 bg-purple-400 rounded-full h-5"></span>
                <span class="w-1 bg-purple-500 rounded-full h-3"></span>
                <span class="w-1 bg-purple-600 rounded-full h-8"></span>
            </div>
        </a>

        {{-- 4. Attendance Rate --}}
        <a href="{{ route('hod.attendance.index') }}"
           class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs flex items-center justify-between transition-all hover:shadow-md hover:-translate-y-0.5">
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-[#EA580C] text-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-2xl font-bold tracking-tight text-gray-900 leading-none">{{ number_format($attendanceRate, 1) }}%</p>
                    <p class="text-xs font-semibold text-gray-700 truncate mt-1">Attendance Rate</p>
                    <p class="text-[10px] text-gray-400 truncate mt-0.5">Last 7 days average</p>
                </div>
            </div>
            <!-- Mini Sparkline Bars (Orange) -->
            <div class="flex items-end gap-1 h-8 flex-shrink-0 pl-2">
                <span class="w-1 bg-orange-300 rounded-full h-3"></span>
                <span class="w-1 bg-orange-400 rounded-full h-5"></span>
                <span class="w-1 bg-orange-500 rounded-full h-7"></span>
                <span class="w-1 bg-orange-600 rounded-full h-8"></span>
            </div>
        </a>

    </div>

    {{-- ══════════════════════════════════════════════
         ROW 3 · ATTENDANCE TREND & GRADE DISTRIBUTION
    ══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">

        {{-- LEFT 7 COLS: Attendance Trend Line Chart --}}
        <div class="lg:col-span-7 rounded-2xl border border-gray-100 bg-white p-5 lg:p-6 shadow-xs flex flex-col justify-between">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-[#2563EB]">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Attendance Trend</h3>
                        <p class="text-[11px] text-gray-400">Daily attendance percentage over time</p>
                    </div>
                </div>

                {{-- 7 Days / 30 Days / Session Filter Pills --}}
                <div class="inline-flex rounded-xl border border-gray-200 bg-gray-50/80 p-0.5 text-xs font-semibold">
                    <button type="button" data-att-period="7"
                            class="att-filter-btn rounded-lg px-3 py-1.5 transition-all bg-[#2563EB] text-white shadow-xs">
                        7 Days
                    </button>
                    <button type="button" data-att-period="30"
                            class="att-filter-btn rounded-lg px-3 py-1.5 transition-all text-gray-600 hover:text-gray-900">
                        30 Days
                    </button>
                    <button type="button" data-att-period="session"
                            class="att-filter-btn rounded-lg px-3 py-1.5 transition-all text-gray-600 hover:text-gray-900">
                        Session
                    </button>
                </div>
            </div>

            {{-- Line Chart Container --}}
            <div class="relative w-full h-64 sm:h-72 mt-2">
                <canvas id="attendance-trend-chart"></canvas>
            </div>
        </div>

        {{-- RIGHT 5 COLS: Grade Distribution Donut Chart --}}
        <div class="lg:col-span-5 rounded-2xl border border-gray-100 bg-white p-5 lg:p-6 shadow-xs flex flex-col justify-between">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-[#2563EB]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-900">Grade Distribution</h3>
                    <p class="text-[11px] text-gray-400">Student performance breakdown by grade</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-center my-auto">
                {{-- Donut Canvas --}}
                <div class="sm:col-span-7 relative flex items-center justify-center h-52">
                    <canvas id="grade-donut-chart"></canvas>
                </div>

                {{-- Legend Breakdown List --}}
                <div class="sm:col-span-5 flex flex-col justify-center space-y-2.5 text-xs">
                    @foreach($gradeLabels as $i => $label)
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-gray-600 font-medium truncate">
                                <span class="h-2.5 w-2.5 rounded-full flex-shrink-0" style="background-color: {{ $gradeColors[$i] ?? '#2563EB' }};"></span>
                                <span class="truncate text-[11px]">{{ $label }}</span>
                            </span>
                            <span class="font-bold text-gray-800 text-[11px] pl-2">
                                {{ $gradePcts[$i] ?? 0 }}% <span class="text-gray-400 font-normal">({{ $gradeCounts[$i] ?? 0 }})</span>
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════
         ROW 4 · QUICK ACCESS ACTIONS (Bottom 6 Buttons)
    ══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">

        {{-- 1. Students (Blue) --}}
        <a href="{{ route('hod.students.index') }}"
           class="rounded-2xl border border-gray-100 bg-white p-3.5 shadow-xs flex items-center gap-3 transition-all hover:shadow-md hover:-translate-y-0.5">
            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-[#2563EB] text-white">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold text-gray-900 leading-tight">Students</p>
                <p class="text-[10px] text-gray-400 font-normal truncate mt-0.5">Manage Students</p>
            </div>
        </a>

        {{-- 2. Teachers (Green) --}}
        <a href="{{ route('hod.teachers.index') }}"
           class="rounded-2xl border border-gray-100 bg-white p-3.5 shadow-xs flex items-center gap-3 transition-all hover:shadow-md hover:-translate-y-0.5">
            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-[#059669] text-white">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold text-gray-900 leading-tight">Teachers</p>
                <p class="text-[10px] text-gray-400 font-normal truncate mt-0.5">Manage Teachers</p>
            </div>
        </a>

        {{-- 3. Subjects (Purple) --}}
        <a href="{{ route('hod.subjects.index') }}"
           class="rounded-2xl border border-gray-100 bg-white p-3.5 shadow-xs flex items-center gap-3 transition-all hover:shadow-md hover:-translate-y-0.5">
            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-[#7C3AED] text-white">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold text-gray-900 leading-tight">Subjects</p>
                <p class="text-[10px] text-gray-400 font-normal truncate mt-0.5">Manage Subjects</p>
            </div>
        </a>

        {{-- 4. Attendance (Orange) --}}
        <a href="{{ route('hod.attendance.index') }}"
           class="rounded-2xl border border-gray-100 bg-white p-3.5 shadow-xs flex items-center gap-3 transition-all hover:shadow-md hover:-translate-y-0.5">
            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-[#EA580C] text-white">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold text-gray-900 leading-tight">Attendance</p>
                <p class="text-[10px] text-gray-400 font-normal truncate mt-0.5">Mark Attendance</p>
            </div>
        </a>

        {{-- 5. Exams & Marks (Red) --}}
        <a href="{{ route('hod.exams.index') }}"
           class="rounded-2xl border border-gray-100 bg-white p-3.5 shadow-xs flex items-center gap-3 transition-all hover:shadow-md hover:-translate-y-0.5">
            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-[#DC2626] text-white">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold text-gray-900 leading-tight">Exams & Marks</p>
                <p class="text-[10px] text-gray-400 font-normal truncate mt-0.5">Manage Exams & Marks</p>
            </div>
        </a>

        {{-- 6. Timetable (Navy Blue) --}}
        <a href="{{ route('hod.timetable.index') }}"
           class="rounded-2xl border border-gray-100 bg-white p-3.5 shadow-xs flex items-center gap-3 transition-all hover:shadow-md hover:-translate-y-0.5">
            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-[#1E40AF] text-white">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold text-gray-900 leading-tight">Timetable</p>
                <p class="text-[10px] text-gray-400 font-normal truncate mt-0.5">Manage Timetable</p>
            </div>
        </a>

    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
function initHodCharts() {
    if (typeof Chart === 'undefined') {
        setTimeout(initHodCharts, 50);
        return;
    }

    // ── Attendance Trend Line Chart ─────────────────────────────────
    const attData = @json($attendanceChartData ?? []);
    const defaultLabels7 = ['11 Bhadra', '12 Bhadra', '13 Bhadra', '14 Bhadra', '15 Bhadra', '16 Bhadra', '17 Bhadra'];
    const defaultData7   = [59, 69, 79, 74, 80, 90, 84.2];

    const attSets = {
        '7':       {
            labels: (attData?.['7']?.labels && attData['7'].labels.length > 0) ? attData['7'].labels : defaultLabels7,
            data:   (attData?.['7']?.data && attData['7'].data.length > 0) ? attData['7'].data : defaultData7,
        },
        '30':      {
            labels: (attData?.['30']?.labels && attData['30'].labels.length > 0) ? attData['30'].labels : defaultLabels7,
            data:   (attData?.['30']?.data && attData['30'].data.length > 0) ? attData['30'].data : defaultData7,
        },
        'session': {
            labels: (attData?.['session']?.labels && attData['session'].labels.length > 0) ? attData['session'].labels : defaultLabels7,
            data:   (attData?.['session']?.data && attData['session'].data.length > 0) ? attData['session'].data : defaultData7,
        },
    };

    const attCanvas = document.getElementById('attendance-trend-chart');
    if (attCanvas) {
        if (window._hodAttChartInstance) {
            try { window._hodAttChartInstance.destroy(); } catch {}
        }

        const ctx = attCanvas.getContext('2d');
        const grad = ctx.createLinearGradient(0, 0, 0, 240);
        grad.addColorStop(0, 'rgba(37, 99, 235, 0.20)');
        grad.addColorStop(1, 'rgba(37, 99, 235, 0.01)');

        window._hodAttChartInstance = new Chart(attCanvas, {
            type: 'line',
            data: {
                labels: attSets['7'].labels,
                datasets: [{
                    label: 'Attendance %',
                    data: attSets['7'].data,
                    borderColor: '#2563EB',
                    backgroundColor: grad,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#2563EB',
                    pointBorderColor: '#FFFFFF',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderWidth: 2.5,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#FFFFFF',
                        titleColor: '#1E293B',
                        bodyColor: '#2563EB',
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 12, weight: 'bold' },
                        borderColor: '#E2E8F0',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                        callbacks: {
                            label: ctx => ctx.parsed.y + '%'
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748B', font: { size: 10.5, weight: '500' } },
                    },
                    y: {
                        min: 0,
                        max: 100,
                        grid: { color: 'rgba(226, 232, 240, 0.6)' },
                        ticks: {
                            stepSize: 20,
                            color: '#64748B',
                            font: { size: 10.5, weight: '500' },
                            callback: v => v + '%'
                        },
                    },
                },
            },
        });

        document.querySelectorAll('.att-filter-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.att-filter-btn').forEach(b => {
                    b.classList.remove('bg-[#2563EB]', 'text-white', 'shadow-xs');
                    b.classList.add('text-gray-600');
                });
                this.classList.remove('text-gray-600');
                this.classList.add('bg-[#2563EB]', 'text-white', 'shadow-xs');

                const set = attSets[this.dataset.attPeriod];
                if (window._hodAttChartInstance) {
                    window._hodAttChartInstance.data.labels = set.labels;
                    window._hodAttChartInstance.data.datasets[0].data = set.data;
                    window._hodAttChartInstance.update();
                }
            });
        });
    }

    // ── Grade Distribution Donut Chart ──────────────────────────────
    const gradeCanvas = document.getElementById('grade-donut-chart');
    if (gradeCanvas) {
        if (window._hodGradeChartInstance) {
            try { window._hodGradeChartInstance.destroy(); } catch {}
        }

        const gd = @json($gradeDistribution ?? []);
        const colors = ['#2563EB', '#F97316', '#DC2626', '#7C3AED', '#EAB308', '#991B1B'];
        const defaultData = [10, 40, 20, 30, 0, 0];
        const defaultLabels = ['A+ (90-100)', 'A (80-89)', 'B+ (70-79)', 'B (60-69)', 'C (50-59)', 'F (<50)'];

        const chartData = (gd?.hasData && gd?.data?.length) ? gd.data : defaultData;
        const chartLabels = (gd?.labels?.length) ? gd.labels : defaultLabels;

        window._hodGradeChartInstance = new Chart(gradeCanvas, {
            type: 'doughnut',
            data: {
                labels: chartLabels,
                datasets: [{
                    data: chartData,
                    backgroundColor: colors,
                    borderWidth: 3,
                    borderColor: '#FFFFFF',
                    hoverOffset: 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#FFFFFF',
                        titleColor: '#1E293B',
                        bodyColor: '#1E293B',
                        borderColor: '#E2E8F0',
                        borderWidth: 1,
                        padding: 10,
                        callbacks: {
                            label: ctx => {
                                const count = gd?.counts?.[ctx.dataIndex];
                                return ctx.label + ': ' + ctx.parsed + '%' + (count !== undefined ? ' (' + count + ')' : '');
                            }
                        }
                    },
                },
            },
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHodCharts);
} else {
    initHodCharts();
}
window.addEventListener('load', initHodCharts);
</script>
@endpush
