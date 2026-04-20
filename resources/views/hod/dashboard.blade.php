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
                    <p class="mt-1 text-sm text-slate-600">Department of {{ $department->name ?? 'Unknown' }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        {{ $session?->name ?? 'No active session' }}
                    </span>
                    <span class="text-xs text-slate-500">
                        Updated {{ bsDate($lastUpdated, 'F d, Y') }}, {{ $lastUpdated->format('h:i A') }}
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
        @endforeach
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         3. MAIN CONTENT – Notices & Quick Actions
    ═══════════════════════════════════════════════════════════ --}}
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
                        @if($notice->department_id == $department->id)
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
                <div class="grid gap-3 sm:grid-cols-2">
                    <a href="#" class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-blue-300 hover:bg-blue-50/50">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-900">View Students</p>
                            <p class="text-xs text-slate-500">Manage department students</p>
                        </div>
                    </a>

                    <a href="#" class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-emerald-300 hover:bg-emerald-50/50">
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

                    <a href="#" class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-violet-300 hover:bg-violet-50/50">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-900">Exam Results</p>
                            <p class="text-xs text-slate-500">View and manage marks</p>
                        </div>
                    </a>

                    <a href="#" class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-amber-300 hover:bg-amber-50/50">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-900">Timetable</p>
                            <p class="text-xs text-slate-500">View class schedules</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
