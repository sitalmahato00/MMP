@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')
@php
    $kpiCards = [
        [
            'title' => 'Attendance Rate',
            'value' => number_format($data['attendance_rate'], 1),
            'suffix' => '%',
            'note' => 'Last 30 days',
            'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
            'tone' => 'emerald',
        ],
        [
            'title' => 'Pending Assignments',
            'value' => number_format($data['pending_assignments']),
            'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'tone' => 'amber',
        ],
        [
            'title' => 'Published Results',
            'value' => number_format($data['published_results']),
            'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
            'tone' => 'violet',
        ],
        [
            'title' => 'Today\'s Classes',
            'value' => number_format($todaySlots->count()),
            'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
            'tone' => 'blue',
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
         1. TOP HEADER
    ═══════════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden rounded-xl lg:rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Student Dashboard</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        {{ $greeting }}, {{ auth()->user()->name }}
                    </h1>
                    @if($student)
                        <p class="mt-1 text-sm text-slate-600">
                            {{ $student->program->name ?? 'N/A' }} · Semester {{ $student->current_semester ?? 'N/A' }}
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
         2. KPI METRICS
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
         3. TODAY'S CLASSES
    ═══════════════════════════════════════════════════════════ --}}
    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">Today's Classes</h2>
            <p class="text-xs text-slate-500">{{ bsDate(now(), 'l, F d, Y') }}</p>
        </div>
        <div class="p-5">
            @if($todaySlots->isEmpty())
                <div class="py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-slate-500">No classes scheduled for today</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($todaySlots as $slot)
                        <div class="flex items-center gap-4 rounded-lg border border-slate-200 p-4 transition hover:bg-slate-50">
                            <div class="flex flex-col items-center justify-center rounded-lg bg-blue-50 px-3 py-2">
                                <span class="text-xs font-semibold text-blue-600">{{ \Carbon\Carbon::parse($slot->start_time)->format('h:i A') }}</span>
                                <span class="text-[10px] text-blue-500">to</span>
                                <span class="text-xs font-semibold text-blue-600">{{ \Carbon\Carbon::parse($slot->end_time)->format('h:i A') }}</span>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-slate-900">{{ $slot->subject->name ?? 'Subject' }}</h3>
                                <p class="text-xs text-slate-500">
                                    {{ $slot->teacher->user->name ?? 'Teacher TBA' }} · {{ $slot->room ?? 'Room TBA' }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         4. ASSIGNMENTS & NOTICES
    ═══════════════════════════════════════════════════════════ --}}
    <section class="grid gap-5 lg:grid-cols-2">
        {{-- Upcoming Assignments --}}
        <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Upcoming Assignments</h2>
                <p class="text-xs text-slate-500">Due soon</p>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($upcomingAssignments as $assignment)
                    <div class="flex gap-3 px-5 py-3.5 transition hover:bg-slate-50">
                        <div class="flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                            <span class="text-[8px] font-semibold leading-none">{{ bsDate($assignment->due_date, 'Y') }}</span>
                            <span class="text-sm font-bold leading-none">{{ bsDate($assignment->due_date, 'd') }}</span>
                            <span class="text-[7px] font-semibold uppercase leading-none">{{ bsDate($assignment->due_date, 'F') }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-900">{{ $assignment->title }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ $assignment->subject->name ?? 'N/A' }} · Due {{ bsDate($assignment->due_date, 'F d, Y') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="mt-2 text-xs text-slate-400">No upcoming assignments</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Notices --}}
        <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Recent Notices</h2>
                <p class="text-xs text-slate-500">Department and general notices</p>
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
    </section>
</div>
@endsection
