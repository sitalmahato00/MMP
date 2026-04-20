@extends('layouts.app')

@section('title', 'Alumni Dashboard')

@section('content')
@php
    $kpiCards = [
        [
            'title' => 'Profile Completion',
            'value' => number_format($profileCompletion),
            'suffix' => '%',
            'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
            'tone' => 'blue',
        ],
        [
            'title' => 'Projects Shared',
            'value' => number_format($data['projects_count']),
            'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'tone' => 'violet',
        ],
        [
            'title' => 'Achievements',
            'value' => number_format($data['achievements_count']),
            'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
            'tone' => 'amber',
        ],
        [
            'title' => 'Employment Records',
            'value' => number_format($data['employment_count']),
            'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
            'tone' => 'emerald',
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
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-amber-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Alumni Dashboard</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        {{ $greeting }}, {{ auth()->user()->name }}
                    </h1>
                    @if($alumnus)
                        <p class="mt-1 text-sm text-slate-600">
                            {{ $alumnus->program->name ?? 'N/A' }} · Graduated {{ $alumnus->graduation_year ?? 'N/A' }}
                        </p>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2">
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
                <div class="absolute bottom-0 left-0 right-0 h-0.5 {{ $t['bar'] }} opacity-40"></div>
            </div>
        @endforeach
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         3. PROFILE COMPLETION ALERT
    ═══════════════════════════════════════════════════════════ --}}
    @if($profileCompletion < 100)
        <section class="rounded-xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-amber-900">Complete Your Profile</h3>
                    <p class="mt-1 text-xs text-amber-700">
                        Your profile is {{ $profileCompletion }}% complete. Add more information to help us stay connected and showcase your achievements.
                    </p>
                    <div class="mt-3">
                        <div class="h-2 w-full overflow-hidden rounded-full bg-amber-200">
                            <div class="h-full bg-amber-500 transition-all duration-500" style="width: {{ $profileCompletion }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ═══════════════════════════════════════════════════════════
         4. QUICK ACTIONS & NOTICES
    ═══════════════════════════════════════════════════════════ --}}
    <section class="grid gap-5 lg:grid-cols-2">
        {{-- Quick Actions --}}
        <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Quick Actions</h2>
                <p class="text-xs text-slate-500">Manage your alumni profile</p>
            </div>
            <div class="p-5">
                <div class="grid gap-3 sm:grid-cols-2">
                    <a href="{{ route('alumni.profile.edit') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-blue-300 hover:bg-blue-50/50">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-900">Edit Profile</p>
                            <p class="text-xs text-slate-500">Update your information</p>
                        </div>
                    </a>

                    <a href="{{ route('alumni.projects.index') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-violet-300 hover:bg-violet-50/50">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-900">My Projects</p>
                            <p class="text-xs text-slate-500">View and add projects</p>
                        </div>
                    </a>

                    <a href="{{ route('alumni.achievements.index') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-amber-300 hover:bg-amber-50/50">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-900">Achievements</p>
                            <p class="text-xs text-slate-500">Share your success</p>
                        </div>
                    </a>

                    <a href="{{ route('alumni.career.index') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 p-4 transition hover:border-emerald-300 hover:bg-emerald-50/50">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-900">Career</p>
                            <p class="text-xs text-slate-500">Update employment</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        {{-- Recent Notices --}}
        <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-900">Recent Notices</h2>
                <p class="text-xs text-slate-500">Latest announcements</p>
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
