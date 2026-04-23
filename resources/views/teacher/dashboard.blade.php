@extends('layouts.app')

@section('title', 'Teacher Dashboard')

@section('content')
@php
    $kpiCards = [
        [
            'title' => 'My Subjects',
            'value' => number_format($data['subjects_count']),
            'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
            'tone' => 'violet',
        ],
        [
            'title' => 'Sessions This Month',
            'value' => number_format($data['sessions_this_month']),
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
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-violet-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Teacher Dashboard</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        {{ $greeting }}, {{ auth()->user()->name }}
                    </h1>
                    @if($teacher && $teacher->department)
                        <p class="mt-1 text-sm text-slate-600">Department of {{ $teacher->department->name }}</p>
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
                    </div>
                    <p class="mt-0.5 text-[11px] font-medium uppercase tracking-wider text-slate-400">{{ $card['title'] }}</p>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-0.5 {{ $t['bar'] }} opacity-40"></div>
            </div>
        @endforeach
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         3. TODAY'S SCHEDULE
    ═══════════════════════════════════════════════════════════ --}}
    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">Today's Schedule</h2>
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
                                <p class="text-xs text-slate-500">{{ $slot->timetable->program->name ?? 'Program' }} - {{ $slot->room ?? 'Room TBA' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         4. SUBJECTS & NOTICES
    ═══════════════════════════════════════════════════════════ --}}
    <section class="grid gap-5 lg:grid-cols-2">
        {{-- My Subjects --}}
        <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">My Subjects</h2>
                <p class="text-xs text-slate-500">Current academic session</p>
            </div>
            <div class="p-5">
                @if($subjects->isEmpty())
                    <div class="py-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <p class="mt-2 text-sm text-slate-400">No subjects assigned</p>
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach($subjects as $subject)
                            <div class="flex items-center gap-3 rounded-lg border border-slate-200 p-3 transition hover:bg-slate-50">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-900">{{ $subject->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $subject->code }} · {{ $subject->program->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

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
                                <span class="shrink-0 rounded bg-blue-50 px-1.5 py-0.5 text-[8px] font-bold text-blue-600">CTEVT</span>
                                <span class="min-w-0 flex-1 truncate text-[11px] font-medium text-slate-700">{{ $notice['title'] ?? 'Notice' }}</span>
                            </div>
                            @if(!empty($notice['updated_date']))
                                <p class="mt-1 text-[9px] text-slate-400">{{ $notice['updated_date'] }}</p>
                            @endif
                        </a>
                    @empty
                        <p class="py-8 text-center text-xs text-slate-400">No general notices available.</p>
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
                        <p class="py-8 text-center text-xs text-slate-400">No result notices available.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
