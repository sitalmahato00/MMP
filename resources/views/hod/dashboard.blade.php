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

<div class="space-y-6">
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
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($kpiCards as $card)
            @php $t = $toneMap[$card['tone']] ?? $toneMap['blue']; @endphp
            @if($department && $card['title'] === 'Total Students')
                <a href="{{ route('hod.students.index') }}" class="group relative overflow-hidden rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                    <div class="flex items-start justify-between">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg {{ $t['bg'] }}">
                            <svg class="h-4 w-4 {{ $t['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="flex items-baseline gap-1">
                            <span class="text-2xl font-bold tracking-tight text-slate-900">{{ $card['value'] }}</span>
                            @if(!empty($card['suffix']))
                                <span class="text-sm font-medium text-slate-400">{{ $card['suffix'] }}</span>
                            @endif
                        </div>
                        <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">{{ $card['title'] }}</p>
                    </div>
                    @if(!empty($card['note']))
                        <p class="mt-2 text-[11px] text-slate-500">{{ $card['note'] }}</p>
                    @endif
                    <div class="absolute bottom-0 left-0 right-0 h-0.5 {{ $t['bar'] }} opacity-40"></div>
                </a>
            @else
                <div class="group relative overflow-hidden rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">
                    <div class="flex items-start justify-between">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg {{ $t['bg'] }}">
                            <svg class="h-4 w-4 {{ $t['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="flex items-baseline gap-1">
                            <span class="text-2xl font-bold tracking-tight text-slate-900">{{ $card['value'] }}</span>
                            @if(!empty($card['suffix']))
                                <span class="text-sm font-medium text-slate-400">{{ $card['suffix'] }}</span>
                            @endif
                        </div>
                        <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">{{ $card['title'] }}</p>
                    </div>
                    @if(!empty($card['note']))
                        <p class="mt-2 text-[11px] text-slate-500">{{ $card['note'] }}</p>
                    @endif
                    <div class="absolute bottom-0 left-0 right-0 h-0.5 {{ $t['bar'] }} opacity-40"></div>
                </div>
            @endif
        @endforeach
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         3. MAIN CONTENT – Notices & Quick Actions
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
        {{-- Recent Notices --}}
        <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Recent Notices</h2>
                    <p class="text-xs text-slate-500">Department and general notices</p>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
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
                        <p class="mt-2 text-xs text-slate-400">No recent notices</p>
                    </div>
                @endforelse
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

                        <a href="{{ route('hod.content.index') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-rose-300 hover:bg-rose-50/50">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-900">Department Content</p>
                                <p class="text-xs text-slate-500">Manage department pages</p>
                            </div>
                        </a>

                        <a href="{{ route('hod.media.index') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-purple-300 hover:bg-purple-50/50">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-50 text-purple-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-900">Media</p>
                                <p class="text-xs text-slate-500">Manage photos & files</p>
                            </div>
                        </a>

                        <a href="{{ route('hod.reports.index') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-teal-300 hover:bg-teal-50/50">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-teal-50 text-teal-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-900">Reports</p>
                                <p class="text-xs text-slate-500">Analytics & insights</p>
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
@endsection
