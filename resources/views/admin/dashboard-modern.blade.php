@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $sessionName = $selectedSession?->name ?? $activeSession?->name ?? '2081-2082';
    $semesters   = $runningSemesters ?? [];

    $totalActiveUsers  = ($currentStudents ?? 0) + ($totalTeachers ?? 0) + ($totalParents ?? 0);
    $attendanceRate    = $attendanceSummary['rate'] ?? 0;
    $passRate          = $passSummary['rate'] ?? 0;
    $attendancePresent = $attendanceSummary['present'] ?? 0;
    $attendanceTotal   = $attendanceSummary['total'] ?? 0;
    $passPassed        = $passSummary['passed'] ?? 0;
    $passTotal         = $passSummary['total'] ?? 0;
    $deptCount         = $departmentCount ?? 0;

    $gd          = $gradeDistribution ?? ['labels' => [], 'data' => [], 'counts' => [], 'hasData' => false];
    $gradeColors = ['#2563EB', '#F97316', '#DC2626', '#7C3AED', '#EAB308', '#991B1B'];
    $defaultGradeLabels = ['A+ (90-100)', 'A (80-89)', 'B+ (70-79)', 'B (60-69)', 'C (50-59)', 'F (<50)'];
    $gradeLabels = !empty($gd['labels']) ? $gd['labels'] : $defaultGradeLabels;
    $gradePcts   = !empty($gd['data']) ? $gd['data'] : [10, 40, 20, 30, 0, 0];
    $gradeCounts = !empty($gd['counts']) ? $gd['counts'] : [1, 4, 2, 3, 0, 0];

    $hasAttendance = !empty($attendanceChartData['7']['data']) && array_sum($attendanceChartData['7']['data']) > 0;
@endphp

<div id="principal-dashboard" class="mx-auto max-w-[1520px] space-y-5">

    {{-- ══════════════════════════════════════════════
         ROW 1 · TOP WELCOME BANNER
    ══════════════════════════════════════════════ --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-5 lg:p-6 shadow-xs relative overflow-hidden flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

        {{-- Left: Greeting + Badges --}}
        <div class="flex flex-col justify-center min-w-0 z-10">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 flex items-center gap-2">
                Good Afternoon, {{ auth()->user()->name ?? 'Admin' }}! <span class="text-2xl">👋</span>
            </h1>
            <p class="mt-1 text-xs text-gray-500 font-normal">Welcome back to the administration dashboard.</p>

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
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600">
                    <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                    {{ $sessionName }}
                </span>

                <!-- Sem 1 Badge -->
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-blue-600"></span>
                    Sem 1
                </span>

                <!-- Sem 3 Badge -->
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-600">
                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                    Sem 3
                </span>
            </div>
        </div>

        {{-- Center: College Building Illustration --}}
        <div class="hidden xl:flex items-center justify-center shrink-0 z-0">
            <svg class="w-56 h-28" viewBox="0 0 240 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Flag & Pole -->
                <path d="M120 12v18M120 12l14 5-14 5" stroke="#EF4444" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="#FEE2E2"/>
                <!-- Roof Triangle -->
                <path d="M95 40l25-18 25 18H95z" stroke="#EF4444" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="#FFF1F2"/>
                <!-- Center Tower -->
                <rect x="100" y="40" width="40" height="65" rx="2" stroke="#EF4444" stroke-width="1.8" fill="#FFFFFF"/>
                <circle cx="120" cy="52" r="5" stroke="#EF4444" stroke-width="1.5" fill="#FEE2E2"/>
                <rect x="112" y="66" width="6" height="8" rx="1" stroke="#EF4444" stroke-width="1.2" fill="#FEE2E2"/>
                <rect x="122" y="66" width="6" height="8" rx="1" stroke="#EF4444" stroke-width="1.2" fill="#FEE2E2"/>
                <path d="M114 105V88a6 6 0 0112 0v17" stroke="#EF4444" stroke-width="1.8" fill="#FEE2E2"/>
                <!-- Left Wing -->
                <rect x="45" y="55" width="55" height="50" rx="2" stroke="#3B82F6" stroke-width="1.8" fill="#FFFFFF"/>
                <rect x="53" y="65" width="8" height="9" rx="1" stroke="#3B82F6" stroke-width="1.2" fill="#EFF6FF"/>
                <rect x="67" y="65" width="8" height="9" rx="1" stroke="#3B82F6" stroke-width="1.2" fill="#EFF6FF"/>
                <rect x="81" y="65" width="8" height="9" rx="1" stroke="#3B82F6" stroke-width="1.2" fill="#EFF6FF"/>
                <rect x="53" y="82" width="8" height="9" rx="1" stroke="#3B82F6" stroke-width="1.2" fill="#EFF6FF"/>
                <rect x="67" y="82" width="8" height="9" rx="1" stroke="#3B82F6" stroke-width="1.2" fill="#EFF6FF"/>
                <rect x="81" y="82" width="8" height="9" rx="1" stroke="#3B82F6" stroke-width="1.2" fill="#EFF6FF"/>
                <!-- Right Wing -->
                <rect x="140" y="55" width="55" height="50" rx="2" stroke="#F97316" stroke-width="1.8" fill="#FFFFFF"/>
                <rect x="149" y="65" width="8" height="9" rx="1" stroke="#F97316" stroke-width="1.2" fill="#FFF7ED"/>
                <rect x="163" y="65" width="8" height="9" rx="1" stroke="#F97316" stroke-width="1.2" fill="#FFF7ED"/>
                <rect x="177" y="65" width="8" height="9" rx="1" stroke="#F97316" stroke-width="1.2" fill="#FFF7ED"/>
                <rect x="149" y="82" width="8" height="9" rx="1" stroke="#F97316" stroke-width="1.2" fill="#FFF7ED"/>
                <rect x="163" y="82" width="8" height="9" rx="1" stroke="#F97316" stroke-width="1.2" fill="#FFF7ED"/>
                <rect x="177" y="82" width="8" height="9" rx="1" stroke="#F97316" stroke-width="1.2" fill="#FFF7ED"/>
                <!-- Trees & Ground Line -->
                <path d="M20 105h200" stroke="#CBD5E1" stroke-width="1.8" stroke-linecap="round"/>
                <path d="M30 105c-6-10-2-20 6-20s12 10 6 20" stroke="#10B981" stroke-width="1.8" fill="#ECFDF5"/>
                <path d="M210 105c-6-10-2-20 6-20s12 10 6 20" stroke="#10B981" stroke-width="1.8" fill="#ECFDF5"/>
            </svg>
        </div>

        {{-- Right: Actions & Timestamp --}}
        <div class="flex flex-col items-start lg:items-end justify-between gap-4 z-10">
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('admin.students.create') }}"
                   class="inline-flex items-center gap-1.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 px-4 py-2.5 text-xs font-bold text-white shadow-xs transition-all hover:shadow-sm">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Student
                </a>
                <a href="{{ route('admin.notices.create') }}"
                   class="inline-flex items-center gap-1.5 rounded-xl bg-[#F97316] hover:bg-orange-600 px-4 py-2.5 text-xs font-bold text-white shadow-xs transition-all hover:shadow-sm">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Create Notice
                </a>
                <a href="{{ route('admin.attendance.index') }}"
                   class="inline-flex items-center gap-1.5 rounded-xl bg-[#DC2626] hover:bg-red-700 px-4 py-2.5 text-xs font-bold text-white shadow-xs transition-all hover:shadow-sm">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Attendance Overview
                </a>
            </div>

            <div class="flex items-center gap-2 text-xs text-gray-400 font-medium">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>2083, Bhadra 17</span>
                <span class="text-gray-300">|</span>
                <span>Updated at {{ $lastUpdated->format('h:i A') }}</span>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         ROW 2 · METRIC STAT CARDS (4 Columns with Distinct Colors)
    ══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Card 1: Total Active Users (Blue) --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs flex items-center justify-between transition-all hover:shadow-sm">
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-[#2563EB] text-white shadow-xs">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-2xl font-black text-gray-900 tracking-tight leading-tight">{{ number_format($totalActiveUsers) }}</p>
                    <p class="text-xs font-semibold text-gray-600 leading-tight">Total Active Users</p>
                    <p class="text-[11px] text-gray-400 truncate mt-0.5">Students + Teachers + Parents</p>
                </div>
            </div>
            <!-- Mini Bar Chart Graphic (Blue) -->
            <div class="flex items-end gap-1 shrink-0 opacity-80" aria-hidden="true">
                <div class="w-1.5 h-3 bg-blue-500 rounded-xs"></div>
                <div class="w-1.5 h-5 bg-blue-500 rounded-xs"></div>
                <div class="w-1.5 h-4 bg-blue-500 rounded-xs"></div>
                <div class="w-1.5 h-7 bg-blue-500 rounded-xs"></div>
            </div>
        </div>

        {{-- Card 2: Attendance Rate (Green / Emerald) --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs flex items-center justify-between transition-all hover:shadow-sm">
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-[#10B981] text-white shadow-xs">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-2xl font-black text-gray-900 tracking-tight leading-tight">{{ number_format($attendanceRate, 1) }}%</p>
                    <p class="text-xs font-semibold text-gray-600 leading-tight">Attendance Rate</p>
                    <p class="text-[11px] text-gray-400 truncate mt-0.5">{{ number_format($attendancePresent) }} / {{ number_format($attendanceTotal) }} Present</p>
                </div>
            </div>
            <!-- Mini Bar Chart Graphic (Green) -->
            <div class="flex items-end gap-1 shrink-0 opacity-80" aria-hidden="true">
                <div class="w-1.5 h-2 bg-emerald-500 rounded-xs"></div>
                <div class="w-1.5 h-4 bg-emerald-500 rounded-xs"></div>
                <div class="w-1.5 h-6 bg-emerald-500 rounded-xs"></div>
                <div class="w-1.5 h-8 bg-emerald-500 rounded-xs"></div>
            </div>
        </div>

        {{-- Card 3: Pass Rate (Purple / Violet) --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs flex items-center justify-between transition-all hover:shadow-sm">
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-[#7C3AED] text-white shadow-xs">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-2xl font-black text-gray-900 tracking-tight leading-tight">{{ number_format($passRate, 1) }}%</p>
                    <p class="text-xs font-semibold text-gray-600 leading-tight">Pass Rate</p>
                    <p class="text-[11px] text-gray-400 truncate mt-0.5">{{ number_format($passPassed) }} / {{ number_format($passTotal) }} Passed</p>
                </div>
            </div>
            <!-- Mini Bar Chart Graphic (Purple) -->
            <div class="flex items-end gap-1 shrink-0 opacity-80" aria-hidden="true">
                <div class="w-1.5 h-4 bg-purple-500 rounded-xs"></div>
                <div class="w-1.5 h-7 bg-purple-500 rounded-xs"></div>
                <div class="w-1.5 h-5 bg-purple-500 rounded-xs"></div>
                <div class="w-1.5 h-8 bg-purple-500 rounded-xs"></div>
            </div>
        </div>

        {{-- Card 4: Total Departments (Orange / Amber) --}}
        <a href="{{ route('admin.departments.index') }}"
           class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs flex items-center justify-between transition-all hover:shadow-sm">
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-[#F97316] text-white shadow-xs">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-2xl font-black text-gray-900 tracking-tight leading-tight">{{ number_format($deptCount) }}</p>
                    <p class="text-xs font-semibold text-gray-600 leading-tight">Total Departments</p>
                    <p class="text-[11px] text-gray-400 truncate mt-0.5">Active Departments</p>
                </div>
            </div>
            <!-- Mini Bar Chart Graphic (Orange) -->
            <div class="flex items-end gap-1 shrink-0 opacity-80" aria-hidden="true">
                <div class="w-1.5 h-3 bg-orange-500 rounded-xs"></div>
                <div class="w-1.5 h-6 bg-orange-500 rounded-xs"></div>
                <div class="w-1.5 h-4 bg-orange-500 rounded-xs"></div>
                <div class="w-1.5 h-8 bg-orange-500 rounded-xs"></div>
            </div>
        </a>
    </div>

    {{-- ══════════════════════════════════════════════
         ROW 3 · CHARTS (Side-by-Side)
    ══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        {{-- Left Chart: Attendance Trend (lg:col-span-7) --}}
        <div class="lg:col-span-7 rounded-2xl border border-gray-100 bg-white p-5 shadow-xs flex flex-col justify-between">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">Attendance Trend</p>
                        <p class="text-xs text-gray-400">Daily attendance percentage over time</p>
                    </div>
                </div>
                <div class="flex items-center gap-1 rounded-lg bg-gray-100 p-1 text-xs font-semibold">
                    <button type="button" data-att-period="7"
                            class="att-filter-btn rounded-md bg-[#2563EB] px-3 py-1.5 text-white transition-all shadow-xs">
                        7 Days
                    </button>
                    <button type="button" data-att-period="30"
                            class="att-filter-btn rounded-md px-3 py-1.5 text-gray-600 hover:text-gray-900 transition-all">
                        30 Days
                    </button>
                    <button type="button" data-att-period="session"
                            class="att-filter-btn rounded-md px-3 py-1.5 text-gray-600 hover:text-gray-900 transition-all">
                        Session
                    </button>
                </div>
            </div>

            <div class="pt-5" style="height: 260px; position: relative;">
                <canvas id="attendance-trend-chart"></canvas>
            </div>
        </div>

        {{-- Right Chart: Grade Distribution (lg:col-span-5) --}}
        <div class="lg:col-span-5 rounded-2xl border border-gray-100 bg-white p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center gap-2.5 border-b border-gray-100 pb-4">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-900">Grade Distribution</p>
                    <p class="text-xs text-gray-400">Student performance breakdown by grade</p>
                </div>
            </div>

            <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-6">
                <!-- Donut Chart -->
                <div style="width: 170px; height: 170px; flex-shrink: 0; position: relative;">
                    <canvas id="grade-donut-chart"></canvas>
                </div>

                <!-- Legend List -->
                <div class="flex-1 min-w-0 space-y-2.5 w-full">
                    @foreach($gradeLabels as $i => $gl)
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="h-2.5 w-2.5 flex-shrink-0 rounded-full" style="background-color: {{ $gradeColors[$i] ?? '#94A3B8' }};"></span>
                                <span class="text-gray-700 font-medium truncate">{{ $gl }}</span>
                            </div>
                            <span class="flex-shrink-0 font-semibold text-gray-500">
                                {{ $gradePcts[$i] ?? 0 }}% ({{ $gradeCounts[$i] ?? 0 }})
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         ROW 4 · QUICK ACCESS ACTIONS (Bottom Row with Distinct Colors)
    ══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">

        {{-- 1. Students (Blue) --}}
        <a href="{{ route('admin.students.index') }}"
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

        {{-- 2. Teachers (Amber / Orange) --}}
        <a href="{{ route('admin.teachers.index') }}"
           class="rounded-2xl border border-gray-100 bg-white p-3.5 shadow-xs flex items-center gap-3 transition-all hover:shadow-md hover:-translate-y-0.5">
            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-[#D97706] text-white">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold text-gray-900 leading-tight">Teachers</p>
                <p class="text-[10px] text-gray-400 font-normal truncate mt-0.5">Manage Teachers</p>
            </div>
        </a>

        {{-- 3. Departments (Red) --}}
        <a href="{{ route('admin.departments.index') }}"
           class="rounded-2xl border border-gray-100 bg-white p-3.5 shadow-xs flex items-center gap-3 transition-all hover:shadow-md hover:-translate-y-0.5">
            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-[#DC2626] text-white">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold text-gray-900 leading-tight">Departments</p>
                <p class="text-[10px] text-gray-400 font-normal truncate mt-0.5">Manage Departments</p>
            </div>
        </a>

        {{-- 4. Attendance (Emerald / Green) --}}
        <a href="{{ route('admin.attendance.index') }}"
           class="rounded-2xl border border-gray-100 bg-white p-3.5 shadow-xs flex items-center gap-3 transition-all hover:shadow-md hover:-translate-y-0.5">
            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-[#059669] text-white">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold text-gray-900 leading-tight">Attendance</p>
                <p class="text-[10px] text-gray-400 font-normal truncate mt-0.5">Mark Attendance</p>
            </div>
        </a>

        {{-- 5. Exams (Purple / Indigo) --}}
        <a href="{{ route('admin.exams.index') }}"
           class="rounded-2xl border border-gray-100 bg-white p-3.5 shadow-xs flex items-center gap-3 transition-all hover:shadow-md hover:-translate-y-0.5">
            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-[#7C3AED] text-white">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold text-gray-900 leading-tight">Exams</p>
                <p class="text-[10px] text-gray-400 font-normal truncate mt-0.5">Manage Exams</p>
            </div>
        </a>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
function initDashboardCharts() {
    if (typeof Chart === 'undefined') {
        setTimeout(initDashboardCharts, 50);
        return;
    }

    // ── Attendance Trend Line Chart ─────────────────────────────────
    const attData = @json($attendanceChartData ?? []);
    const defaultLabels7 = ['11 Bhadra', '12 Bhadra', '13 Bhadra', '14 Bhadra', '15 Bhadra', '16 Bhadra', '17 Bhadra'];
    const defaultData7   = [59, 69, 79, 74, 80, 90, 81.5];

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
        // Destroy existing chart if any
        if (window._attChartInstance) {
            try { window._attChartInstance.destroy(); } catch {}
        }

        const ctx = attCanvas.getContext('2d');
        const grad = ctx.createLinearGradient(0, 0, 0, 240);
        grad.addColorStop(0, 'rgba(37, 99, 235, 0.20)');
        grad.addColorStop(1, 'rgba(37, 99, 235, 0.01)');

        window._attChartInstance = new Chart(attCanvas, {
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
                if (window._attChartInstance) {
                    window._attChartInstance.data.labels = set.labels;
                    window._attChartInstance.data.datasets[0].data = set.data;
                    window._attChartInstance.update();
                }
            });
        });
    }

    // ── Grade Distribution Donut Chart ──────────────────────────────
    const gradeCanvas = document.getElementById('grade-donut-chart');
    if (gradeCanvas) {
        if (window._gradeChartInstance) {
            try { window._gradeChartInstance.destroy(); } catch {}
        }

        const gd = @json($gradeDistribution ?? []);
        const colors = ['#2563EB', '#F97316', '#DC2626', '#7C3AED', '#EAB308', '#991B1B'];
        const defaultData = [10, 40, 20, 30, 0, 0];
        const defaultLabels = ['A+ (90-100)', 'A (80-89)', 'B+ (70-79)', 'B (60-69)', 'C (50-59)', 'F (<50)'];

        const chartData = (gd?.hasData && gd?.data?.length) ? gd.data : defaultData;
        const chartLabels = (gd?.labels?.length) ? gd.labels : defaultLabels;

        window._gradeChartInstance = new Chart(gradeCanvas, {
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
    document.addEventListener('DOMContentLoaded', initDashboardCharts);
} else {
    initDashboardCharts();
}
window.addEventListener('load', initDashboardCharts);
window.addEventListener('mmp:page-loaded', initDashboardCharts);
</script>
@endpush

