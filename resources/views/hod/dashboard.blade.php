@extends('layouts.app')

@section('title', 'HOD Dashboard')

@section('content')
@php
    $kpiCards = [
        [
            'title' => 'Total Students',
            'value' => number_format($data['student_count']),
            'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a4 4 0 11-8 0 4 4 0 018 0z',
            'tone' => 'blue',
        ],
        [
            'title' => 'Department Teachers',
            'value' => number_format($data['teacher_count']),
            'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
            'tone' => 'emerald',
        ],
        [
            'title' => 'Programs Offered',
            'value' => number_format($data['program_count']),
            'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
            'tone' => 'violet',
        ],
        [
            'title' => 'Attendance Rate',
            'value' => number_format($data['attendance_rate'], 1),
            'suffix' => '%',
            'note' => 'Last 7 days',
            'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
            'tone' => 'amber',
        ],
    ];

    $toneMap = [
        'blue'    => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'ring' => 'ring-blue-100', 'bar' => 'bg-blue-500'],
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'ring' => 'ring-emerald-100', 'bar' => 'bg-emerald-500'],
        'violet'  => ['bg' => 'bg-violet-50', 'text' => 'text-violet-600', 'ring' => 'ring-violet-100', 'bar' => 'bg-violet-500'],
        'amber'   => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'ring' => 'ring-amber-100', 'bar' => 'bg-amber-500'],
    ];
@endphp

<div class="space-y-4 sm:space-y-6">
    {{-- ═══════════════════════════════════════════════════════════
         1. TOP HEADER – Department Overview
    ═══════════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden rounded-xl lg:rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Head of Department Dashboard</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        {{ $greeting }}, {{ auth()->user()->name }}
                    </h1>
                    @if($department)
                        <p class="mt-1 text-sm text-slate-600">Department of {{ $department->name }}</p>
                    @else
                        <p class="mt-1 text-sm text-amber-600">
                            <svg class="inline h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            No department assigned yet. Please contact the Principal.
                        </p>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        {{ $session?->name ?? 'No active session' }}
                    </span>
                    <span class="text-xs text-slate-500">
                        Updated {{ bsDate($lastUpdated, 'F d, Y') }}, {{ bsDateTime($lastUpdated, '', 'h:i A') }}
                    </span>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         2. KPI METRICS – 4 Cards
    ═══════════════════════════════════════════════════════════ --}}
    <section class="grid gap-3 sm:gap-4 grid-cols-2 lg:grid-cols-4">
        @foreach($kpiCards as $card)
            @php $t = $toneMap[$card['tone']] ?? $toneMap['blue']; @endphp
            @if($department && $card['title'] === 'Total Students')
                <a href="{{ route('hod.students.index') }}" class="group relative overflow-hidden rounded-xl border border-slate-200/80 bg-white p-3 sm:p-4 shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                    <div class="flex items-start justify-between">
                        <div class="flex h-8 w-8 sm:h-9 sm:w-9 items-center justify-center rounded-lg {{ $t['bg'] }}">
                            <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4 {{ $t['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2 sm:mt-3">
                        <div class="flex items-baseline gap-1">
                            <span class="text-lg sm:text-2xl font-bold tracking-tight text-slate-900">{{ $card['value'] }}</span>
                            @if(!empty($card['suffix']))
                                <span class="text-xs sm:text-sm font-medium text-slate-400">{{ $card['suffix'] }}</span>
                            @endif
                        </div>
                        <p class="mt-0.5 text-[10px] sm:text-[11px] font-medium uppercase tracking-wider text-slate-400">{{ $card['title'] }}</p>
                    </div>
                    @if(!empty($card['note']))
                        <p class="mt-1 sm:mt-2 text-[10px] sm:text-[11px] text-slate-500">{{ $card['note'] }}</p>
                    @endif
                    <div class="absolute bottom-0 left-0 right-0 h-0.5 {{ $t['bar'] }} opacity-40"></div>
                </a>
            @else
                <div class="group relative overflow-hidden rounded-xl border border-slate-200/80 bg-white p-3 sm:p-4 shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                    <div class="flex items-start justify-between">
                        <div class="flex h-8 w-8 sm:h-9 sm:w-9 items-center justify-center rounded-lg {{ $t['bg'] }}">
                            <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4 {{ $t['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2 sm:mt-3">
                        <div class="flex items-baseline gap-1">
                            <span class="text-lg sm:text-2xl font-bold tracking-tight text-slate-900">{{ $card['value'] }}</span>
                            @if(!empty($card['suffix']))
                                <span class="text-xs sm:text-sm font-medium text-slate-400">{{ $card['suffix'] }}</span>
                            @endif
                        </div>
                        <p class="mt-0.5 text-[10px] sm:text-[11px] font-medium uppercase tracking-wider text-slate-400">{{ $card['title'] }}</p>
                    </div>
                    @if(!empty($card['note']))
                        <p class="mt-1 sm:mt-2 text-[10px] sm:text-[11px] text-slate-500">{{ $card['note'] }}</p>
                    @endif
                    <div class="absolute bottom-0 left-0 right-0 h-0.5 {{ $t['bar'] }} opacity-40"></div>
                </div>
            @endif
        @endforeach
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         3. ANALYTICS CHARTS – Department Performance
    ═══════════════════════════════════════════════════════════ --}}
    @if($department && isset($chartData))
    <section class="grid gap-5 lg:grid-cols-2">
        {{-- Grade Distribution Donut Chart --}}
        <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 sm:px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Student Grade Distribution</h2>
                <p class="text-xs text-slate-500">Active students' exam performance breakdown</p>
            </div>
            <div class="p-4 sm:p-5">
                @php $hasGrades = array_sum($chartData['grades'] ?? []) > 0; @endphp
                @if($hasGrades)
                    <canvas class="h-48 sm:h-64 w-full" id="gradeChart"></canvas>
                @else
                    <div class="text-center text-slate-400 py-12">No grade data available.</div>
                @endif
            </div>
        </div>

        {{-- Today's Classes Schedule with Attendance --}}
        <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 sm:px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Today's Classes & Attendance</h2>
                <p class="text-xs text-slate-500">{{ bsDate(now(), 'Y F d, l') }} - Class schedule with attendance status</p>
            </div>
            <div class="p-4 sm:p-5">
                @if($chartData['todayClasses']->count() > 0)
                    <div class="space-y-3 max-h-64 sm:max-h-80 overflow-y-auto">
                        @foreach($chartData['todayClasses'] as $class)
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 rounded-lg bg-slate-50 hover:bg-slate-100 transition-colors">
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                                        <span class="text-sm font-semibold text-slate-900">{{ $class['subject'] }}</span>
                                        <span class="text-xs text-slate-500">({{ $class['subject_code'] }})</span>
                                        @if($class['type'] !== 'Theory')
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                                {{ $class['type'] }}
                                            </span>
                                        @endif
                                        {{-- Attendance Status Badge --}}
                                        @if($class['attendance_marked'])
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                                                <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
                                                </svg>
                                                Attendance Marked
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                                                <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/>
                                                </svg>
                                                Pending
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-600 mt-0.5">{{ $class['teacher'] }}</p>
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 mt-0.5">
                                        <p class="text-xs text-slate-500">{{ $class['program'] }}</p>
                                        @if($class['room'])
                                            <span class="text-xs text-slate-400">• Room {{ $class['room'] }}</span>
                                        @endif
                                    </div>
                                    {{-- Attendance Details --}}
                                    @if($class['attendance_marked'])
                                        <div class="flex items-center gap-3 mt-2 text-xs">
                                            <span class="text-slate-600">
                                                <span class="font-medium text-emerald-600">{{ $class['present_count'] }}</span> Present
                                            </span>
                                            <span class="text-slate-600">
                                                <span class="font-medium text-red-600">{{ $class['absent_count'] }}</span> Absent
                                            </span>
                                            <span class="text-slate-600">
                                                Total: <span class="font-medium">{{ $class['total_students_marked'] }}</span>
                                            </span>
                                            <span class="text-slate-600">
                                                Rate: <span class="font-medium text-blue-600">{{ $class['attendance_rate'] }}%</span>
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div class="mt-2 sm:mt-0 sm:ml-3 flex-shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $class['time'] }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 sm:py-12">
                        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="mt-2 text-sm text-slate-400">No classes scheduled for today</p>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Attendance Trend Chart --}}
    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 sm:px-5 py-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Attendance Trend</h2>
                    <p class="text-xs text-slate-500">Daily attendance percentage (Last 7 days)</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                        Current Week
                    </span>
                </div>
            </div>
        </div>
        <div class="p-4 sm:p-5">
            @php $hasAttendance = !empty($chartData['attendance']) && count($chartData['attendance']) > 0; @endphp
            @if($hasAttendance)
                <canvas class="h-48 sm:h-64 w-full" id="attendanceChart"></canvas>
            @else
                <div class="text-center text-slate-400 py-12">No attendance data available.</div>
            @endif
        </div>
    </section>
    @endif

    {{-- ═══════════════════════════════════════════════════════════
         4. MAIN CONTENT – Notices & Quick Actions
    ═══════════════════════════════════════════════════════════ --}}
    
    @if(!$department)
        {{-- No Department Warning --}}
        <section class="rounded-xl border-2 border-amber-200 bg-amber-50 p-6">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-amber-100">
                    <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-amber-900">No Department Assigned</h3>
                    <p class="mt-1 text-sm text-amber-700">
                        You have been assigned the HOD role, but no department has been linked to your account yet. 
                        Please contact the Principal to assign you to a department. Once assigned, you will have full access to department management features.
                    </p>
                </div>
            </div>
        </section>
    @endif
    
    <section class="grid gap-5 lg:grid-cols-2">
        {{-- Recent Notices with Tabs --}}
        <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm" x-data="{ activeTab: 'department' }">
            <div class="border-b border-slate-100 px-5 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">Recent Notices</h2>
                        <p class="text-xs text-slate-500">Department and CTEVT announcements</p>
                    </div>
                </div>
                
                {{-- Notice Tabs --}}
                <div class="mt-3 flex space-x-1 rounded-lg bg-slate-100 p-1">
                    <button @click="activeTab = 'department'" 
                            :class="activeTab === 'department' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                            class="flex-1 rounded-md px-3 py-1.5 text-xs font-medium transition-all">
                        Department
                    </button>
                    <button @click="activeTab = 'ctevt'" 
                            :class="activeTab === 'ctevt' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                            class="flex-1 rounded-md px-3 py-1.5 text-xs font-medium transition-all">
                        CTEVT
                    </button>
                </div>
            </div>
            
            {{-- Department Notices --}}
            <div x-show="activeTab === 'department'" class="divide-y divide-slate-100">
                @forelse($recentNotices as $notice)
                    <div class="flex gap-3 px-5 py-3.5 transition hover:bg-slate-50">
                        <div class="flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                            <span class="text-[8px] font-semibold leading-none">{{ bsDate($notice->created_at, 'Y') }}</span>
                            <span class="text-sm font-bold leading-none">{{ bsDate($notice->created_at, 'd') }}</span>
                            <span class="text-[7px] font-semibold uppercase leading-none">{{ bsDate($notice->created_at, 'F') }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-900">{{ $notice->title }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ bsDate($notice->created_at, 'F d, Y') }} · {{ $notice->author->name ?? 'System' }}</p>
                        </div>
                        @if($department && $notice->department_id == $department->id)
                            <span class="shrink-0 self-center rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-600">Dept</span>
                        @endif
                    </div>
                @empty
                    <div class="py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="mt-2 text-xs text-slate-400">No department notices</p>
                    </div>
                @endforelse
            </div>
            
            {{-- CTEVT Notices Tab --}}
            <div x-show="activeTab === 'ctevt'" class="divide-y divide-slate-100" x-cloak x-data="{ ctevtSubTab: 'general' }">
                <div class="flex gap-1 bg-slate-50 p-2">
                    <button @click="ctevtSubTab = 'general'" :class="ctevtSubTab === 'general' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-600 hover:bg-white/50'" class="flex-1 rounded-md px-2.5 py-1.5 text-[10px] font-semibold transition">
                        General ({{ collect($ctevtGeneralNotices['items'] ?? [])->count() }})
                    </button>
                    <button @click="ctevtSubTab = 'result'" :class="ctevtSubTab === 'result' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-600 hover:bg-white/50'" class="flex-1 rounded-md px-2.5 py-1.5 text-[10px] font-semibold transition">
                        Results ({{ collect($ctevtResultNotices['items'] ?? [])->count() }})
                    </button>
                </div>

                <div x-show="ctevtSubTab === 'general'" class="divide-y divide-slate-100 max-h-[350px] sm:max-h-[450px] overflow-y-auto">
                    @forelse(collect($ctevtGeneralNotices['items'] ?? []) as $notice)
                        <a href="{{ $notice['url'] ?? ($ctevtGeneralNotices['page_url'] ?? '#') }}" target="_blank" rel="noopener noreferrer" class="block px-4 py-2.5 transition hover:bg-slate-50">
                            <div class="flex items-center gap-2">
                                <span class="shrink-0 rounded bg-red-50 px-1.5 py-0.5 text-[8px] font-bold text-red-600">CTEVT</span>
                                <span class="min-w-0 flex-1 truncate text-[11px] font-medium text-slate-700">{{ $notice['title'] ?? 'Notice' }}</span>
                            </div>
                            @if(!empty($notice['updated_date']))
                                <p class="mt-1 text-[9px] text-slate-400">{{ $notice['updated_date'] }}</p>
                            @endif
                        </a>
                    @empty
                        @php $ctevtState = $ctevtGeneralNotices['source_state'] ?? 'unavailable'; @endphp
                        @if($ctevtState === 'cached')
                            <div class="py-6 px-4 text-center">
                                <p class="text-xs font-medium text-amber-600 mb-1">📦 Showing Cached Notices</p>
                                <p class="text-[10px] text-slate-400">Live API unavailable, displaying stored notices</p>
                            </div>
                        @else
                            <p class="py-8 text-center text-xs text-slate-400">No general notices available.</p>
                        @endif
                    @endforelse
                </div>

                <div x-show="ctevtSubTab === 'result'" x-cloak class="divide-y divide-slate-100 max-h-[350px] sm:max-h-[450px] overflow-y-auto">
                    @forelse(collect($ctevtResultNotices['items'] ?? []) as $notice)
                        <a href="{{ $notice['url'] ?? ($ctevtResultNotices['page_url'] ?? '#') }}" target="_blank" rel="noopener noreferrer" class="block px-4 py-2.5 transition hover:bg-slate-50">
                            <div class="flex items-center gap-2">
                                <span class="shrink-0 rounded bg-emerald-50 px-1.5 py-0.5 text-[8px] font-bold text-emerald-600">CTEVT</span>
                                <span class="min-w-0 flex-1 truncate text-[11px] font-medium text-slate-700">{{ $notice['title'] ?? 'Result' }}</span>
                            </div>
                            @if(!empty($notice['updated_date']))
                                <p class="mt-1 text-[9px] text-slate-400">{{ $notice['updated_date'] }}</p>
                            @endif
                        </a>
                    @empty
                        @php $ctevtResultState = $ctevtResultNotices['source_state'] ?? 'unavailable'; @endphp
                        @if($ctevtResultState === 'cached')
                            <div class="py-6 px-4 text-center">
                                <p class="text-xs font-medium text-amber-600 mb-1">📦 Showing Cached Notices</p>
                                <p class="text-[10px] text-slate-400">Live API unavailable, displaying stored notices</p>
                            </div>
                        @else
                            <p class="py-8 text-center text-xs text-slate-400">No result notices available.</p>
                        @endif
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Quick Actions</h2>
                <p class="text-xs text-slate-500">Common department tasks</p>
            </div>
            <div class="p-5">
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @if($department)
                        <a href="{{ route('hod.students.index') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-blue-300 hover:bg-blue-50/50">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-900">Students</p>
                                <p class="text-xs text-slate-500">Manage department students</p>
                            </div>
                        </a>

                        <a href="{{ route('hod.teachers.index') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-indigo-300 hover:bg-indigo-50/50">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-900">Teachers</p>
                                <p class="text-xs text-slate-500">Manage department teachers</p>
                            </div>
                        </a>

                        <a href="{{ route('hod.attendance.index') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-emerald-300 hover:bg-emerald-50/50">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-900">Attendance</p>
                                <p class="text-xs text-slate-500">View attendance records</p>
                            </div>
                        </a>

                        <a href="{{ route('hod.exams.index') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-violet-300 hover:bg-violet-50/50">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-900">Exams & Marks</p>
                                <p class="text-xs text-slate-500">Manage exams and results</p>
                            </div>
                        </a>

                        <a href="{{ route('hod.timetable.index') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-amber-300 hover:bg-amber-50/50">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-900">Timetable</p>
                                <p class="text-xs text-slate-500">Manage class schedules</p>
                            </div>
                        </a>

                        <a href="{{ route('hod.notices.index') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-cyan-300 hover:bg-cyan-50/50">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-cyan-50 text-cyan-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM11 19H6a2 2 0 01-2-2V7a2 2 0 012-2h5m5 0v5"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-900">Notices</p>
                                <p class="text-xs text-slate-500">Department announcements</p>
                            </div>
                        </a>

                        <a href="{{ route('hod.media.index') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-purple-300 hover:bg-purple-50/50">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-50 text-purple-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-900">Gallery</p>
                                <p class="text-xs text-slate-500">Manage photos & files</p>
                            </div>
                        </a>

                        <a href="{{ route('hod.alumni.index') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-orange-300 hover:bg-orange-50/50">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-50 text-orange-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-900">Alumni Preparation</p>
                                <p class="text-xs text-slate-500">Graduating students</p>
                            </div>
                        </a>
                    @else
                        <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4 opacity-60 cursor-not-allowed">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-400">View Students</p>
                                <p class="text-xs text-slate-400">Requires department assignment</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4 opacity-60 cursor-not-allowed">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-400">Attendance</p>
                                <p class="text-xs text-slate-400">Requires department assignment</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4 opacity-60 cursor-not-allowed">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-400">Exam Results</p>
                                <p class="text-xs text-slate-400">Requires department assignment</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4 opacity-60 cursor-not-allowed">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-400">Timetable</p>
                                <p class="text-xs text-slate-400">Requires department assignment</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@if($department && isset($chartData))
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart.js default configuration
    Chart.defaults.font.family = 'Inter, sans-serif';
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#64748b';
    
    // Grade Distribution Donut Chart
    const gradeCtx = document.getElementById('gradeChart');
    if (gradeCtx) {
        const gradeData = {!! json_encode($chartData['grades']) !!};
        console.log('Grade Data:', gradeData);
        
        const gradeLabels = ['A+ (90-100)', 'A (80-89)', 'B+ (70-79)', 'B (60-69)', 'C (50-59)', 'F (<50)'];
        const gradeValues = ['A+', 'A', 'B+', 'B', 'C', 'F'].map(grade => gradeData[grade] || 0);
        console.log('Grade Values:', gradeValues);
        
        const gradeColors = [
            '#22c55e',  // Green for A+
            '#3b82f6',  // Blue for A
            '#8b5cf6',  // Purple for B+
            '#f59e0b',  // Orange for B
            '#eab308',  // Yellow for C
            '#ef4444'   // Red for F
        ];
        
        new Chart(gradeCtx, {
            type: 'doughnut',
            data: {
                labels: gradeLabels,
                datasets: [{
                    data: gradeValues,
                    backgroundColor: gradeColors,
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: window.innerWidth < 640 ? 'bottom' : 'right',
                        labels: {
                            padding: 15,
                            font: {
                                size: window.innerWidth < 640 ? 10 : 11
                            },
                            generateLabels: function(chart) {
                                const data = chart.data;
                                return data.labels.map((label, i) => ({
                                    text: label + ' (' + data.datasets[0].data[i] + ')',
                                    fillStyle: data.datasets[0].backgroundColor[i],
                                    hidden: false,
                                    index: i
                                }));
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Attendance Trend Chart (similar to admin dashboard)
    const attendanceCtx = document.getElementById('attendanceChart');
    if (attendanceCtx) {
        const attendanceData = {!! json_encode($chartData['attendance']) !!};
        console.log('Attendance Data:', attendanceData);
        
        new Chart(attendanceCtx, {
            type: 'line',
            data: {
                labels: attendanceData.map(item => item.date_short),
                datasets: [{
                    label: 'Attendance %',
                    data: attendanceData.map(item => item.rate),
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37,99,235,0.08)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.38,
                    pointRadius: 4,
                    pointBackgroundColor: '#2563eb',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                const index = context[0].dataIndex;
                                return attendanceData[index].date_bs;
                            },
                            label: function(context) {
                                return 'Attendance: ' + context.parsed.y + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 0,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            },
                            font: {
                                size: window.innerWidth < 640 ? 10 : 12
                            }
                        },
                        grid: {
                            color: '#f1f5f9'
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: window.innerWidth < 640 ? 9 : 11
                            },
                            maxRotation: window.innerWidth < 640 ? 45 : 0
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
    
    // Handle responsive chart updates
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            Chart.helpers.each(Chart.instances, function(instance) {
                if (instance.chart.canvas.id === 'gradeChart') {
                    // Update legend position for grade chart
                    instance.options.plugins.legend.position = window.innerWidth < 640 ? 'bottom' : 'right';
                    instance.options.plugins.legend.labels.font.size = window.innerWidth < 640 ? 10 : 11;
                }
                if (instance.chart.canvas.id === 'attendanceChart') {
                    // Update font sizes for attendance chart
                    instance.options.scales.y.ticks.font.size = window.innerWidth < 640 ? 10 : 12;
                    instance.options.scales.x.ticks.font.size = window.innerWidth < 640 ? 9 : 11;
                    instance.options.scales.x.ticks.maxRotation = window.innerWidth < 640 ? 45 : 0;
                }
                instance.update();
            });
        }, 250);
    });
});
</script>
@endif
@endpush
@endsection
