@extends('layouts.app')

@section('title', 'Parent Dashboard')

@section('content')
<div class="space-y-6">
    {{-- ═══════════════════════════════════════════════════════════
         1. TOP HEADER
    ═══════════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden rounded-xl lg:rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-emerald-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Parent Dashboard</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        {{ $greeting }}, {{ auth()->user()->name }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">Monitor your children's academic progress</p>
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
         2. ACADEMIC OVERVIEW
    ═══════════════════════════════════════════════════════════ --}}
    @if($children->isEmpty())
        <section class="rounded-xl border border-slate-200/80 bg-white p-12 text-center shadow-sm">
            <svg class="mx-auto h-16 w-16 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <p class="mt-4 text-sm text-slate-500">No children linked to your account</p>
        </section>
    @else
        {{-- Children Academic Overview --}}
        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-900">Academic Overview</h2>
            </div>
            
            @foreach($childrenSummaries as $summary)
                @php
                    $student = $summary['student'];
                    $attPct = $summary['attendancePct'];
                    $percentageRate = $summary['percentageRate'];
                    $publishedAssessments = $summary['publishedAssessments'];
                    $distinctionAssessments = $summary['distinctionAssessments'];
                    $pendingAssignments = $summary['pendingAssignments'];
                    
                    $attColor = $attPct === null ? 'slate' : ($attPct >= 75 ? 'emerald' : ($attPct >= 60 ? 'amber' : 'red'));
                @endphp
                
                <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
                    {{-- Student Header --}}
                    <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                @if($student->user->avatar)
                                    <img src="{{ Storage::url($student->user->avatar) }}" alt="{{ $student->user->name }}" class="h-10 w-10 rounded-full object-cover">
                                @else
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h3 class="text-base font-semibold text-slate-900">{{ $student->user->name }}</h3>
                                <p class="text-xs text-slate-500">
                                    {{ $student->program->name ?? 'N/A' }} · Semester {{ $student->current_semester ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Student Stats --}}
                    <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-4">
                        {{-- Attendance --}}
                        <div class="rounded-lg border border-slate-200 p-4">
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-{{ $attColor }}-50 text-{{ $attColor }}-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-medium text-slate-500">Attendance</p>
                                    <p class="text-lg font-bold text-slate-900">
                                        {{ $attPct !== null ? number_format($attPct, 1) . '%' : 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            <p class="mt-1 text-[10px] text-slate-400">Overall attendance</p>
                        </div>

                        {{-- Percentage Rate --}}
                        <div class="rounded-lg border border-slate-200 p-4">
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-medium text-slate-500">Percentage Rate</p>
                                    <p class="text-lg font-bold text-slate-900">
                                        {{ $percentageRate !== null ? number_format($percentageRate, 1) . '%' : 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            <p class="mt-1 text-[10px] text-slate-400">
                                {{ $publishedAssessments }} published assessment{{ $publishedAssessments === 1 ? '' : 's' }}
                                @if($distinctionAssessments > 0)
                                    · {{ $distinctionAssessments }} distinction{{ $distinctionAssessments === 1 ? '' : 's' }}
                                @endif
                            </p>
                        </div>

                        {{-- Pending Assignments --}}
                        <div class="rounded-lg border border-slate-200 p-4">
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-medium text-slate-500">Pending</p>
                                    <p class="text-lg font-bold text-slate-900">{{ $pendingAssignments }}</p>
                                </div>
                            </div>
                            <p class="mt-1 text-[10px] text-slate-400">Assignments</p>
                        </div>

                        {{-- Status --}}
                        <div class="rounded-lg border border-slate-200 p-4">
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-medium text-slate-500">Status</p>
                                    <p class="text-lg font-bold text-slate-900">
                                        @if($student->status === 'active')
                                            <span class="text-emerald-600">Active</span>
                                        @else
                                            <span class="text-slate-400">{{ ucfirst($student->status) }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <p class="mt-1 text-[10px] text-slate-400">Current status</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </section>

        {{-- Today's Classes Section --}}
        <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">Today's Classes</h2>
                        <p class="text-xs text-slate-500">{{ bsDate(now(), 'F d, Y, l') }}</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            @php
                $hasAnyTodayClasses = $childrenSummaries->filter(fn($s) => $s['todayAttendance']->isNotEmpty())->isNotEmpty();
            @endphp
            
            @if($hasAnyTodayClasses)
                <div class="divide-y divide-slate-100">
                    @foreach($childrenSummaries as $summary)
                        @if($summary['todayAttendance']->isNotEmpty())
                            <div class="p-5">
                                {{-- Student Name Header --}}
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white">
                                        @if($summary['student']->user->avatar)
                                            <img src="{{ Storage::url($summary['student']->user->avatar) }}" alt="{{ $summary['student']->user->name }}" class="h-10 w-10 rounded-full object-cover">
                                        @else
                                            <span class="text-sm font-bold">{{ strtoupper(substr($summary['student']->user->name, 0, 2)) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-semibold text-slate-900">{{ $summary['student']->user->name }}</h3>
                                        <p class="text-xs text-slate-500">{{ $summary['todayAttendance']->count() }} {{ $summary['todayAttendance']->count() === 1 ? 'class' : 'classes' }} today</p>
                                    </div>
                                </div>
                                
                                {{-- Classes List --}}
                                <div class="space-y-2">
                                    @foreach($summary['todayAttendance'] as $attendance)
                                        <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 hover:bg-slate-100 transition">
                                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg 
                                                    {{ $attendance['status'] === 'present' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }}">
                                                    @if($attendance['status'] === 'present')
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    @else
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    @endif
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-slate-900 truncate">{{ $attendance['subject_name'] }}</p>
                                                    <p class="text-xs text-slate-500">{{ $attendance['period'] }} · {{ $attendance['time'] }}</p>
                                                </div>
                                            </div>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium shrink-0
                                                {{ $attendance['status'] === 'present' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                                {{ ucfirst($attendance['status']) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="p-12 text-center">
                    <svg class="mx-auto h-16 w-16 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="mt-4 text-sm text-slate-500">No classes marked for today yet</p>
                    <p class="mt-1 text-xs text-slate-400">Attendance will appear here once marked</p>
                </div>
            @endif
        </section>

        {{-- My Children Section --}}
        <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">My Children</h2>
                        <p class="text-xs text-slate-500">{{ $children->count() }} {{ $children->count() === 1 ? 'child' : 'children' }} enrolled</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="divide-y divide-slate-100">
                @foreach($children as $child)
                    <div class="px-5 py-4 transition hover:bg-slate-50">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white">
                                @if($child->user->avatar)
                                    <img src="{{ Storage::url($child->user->avatar) }}" alt="{{ $child->user->name }}" class="h-12 w-12 rounded-full object-cover">
                                @else
                                    <span class="text-sm font-bold">{{ strtoupper(substr($child->user->name, 0, 2)) }}</span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-semibold text-slate-900 truncate">{{ $child->user->name }}</h3>
                                <p class="text-xs text-slate-500 truncate">
                                    {{ $child->program->name ?? 'N/A' }} · Semester {{ $child->current_semester ?? 'N/A' }}
                                </p>
                                <p class="text-xs text-slate-400 truncate">{{ $child->department->name ?? 'N/A' }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($child->status === 'active')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                                        {{ ucfirst($child->status) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ═══════════════════════════════════════════════════════════
         3. RECENT NOTICES – Internal and CTEVT with Tabs
    ═══════════════════════════════════════════════════════════ --}}
    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm" x-data="{ activeTab: 'internal' }">
        <div class="border-b border-slate-100 px-5 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Recent Notices</h2>
                    <p class="text-xs text-slate-500">Internal and CTEVT announcements</p>
                </div>
            </div>
            
            {{-- Notice Tabs --}}
            <div class="mt-3 flex space-x-1 rounded-lg bg-slate-100 p-1">
                <button @click="activeTab = 'internal'" 
                        :class="activeTab === 'internal' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                        class="flex-1 rounded-md px-3 py-1.5 text-xs font-medium transition-all">
                    Internal
                </button>
                <button @click="activeTab = 'ctevt'" 
                        :class="activeTab === 'ctevt' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                        class="flex-1 rounded-md px-3 py-1.5 text-xs font-medium transition-all">
                    CTEVT
                </button>
            </div>
        </div>
        
        {{-- Internal Notices --}}
        <div x-show="activeTab === 'internal'" class="divide-y divide-slate-100">
            @forelse($recentNotices as $notice)
                <div class="flex gap-3 px-5 py-3.5 transition hover:bg-slate-50">
                    <div class="flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                        <span class="text-[8px] font-semibold leading-none">{{ bsDate($notice->created_at, 'Y') }}</span>
                        <span class="text-sm font-bold leading-none">{{ bsDate($notice->created_at, 'd') }}</span>
                        <span class="text-[7px] font-semibold uppercase leading-none">{{ bsDate($notice->created_at, 'F') }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-slate-900">{{ $notice->title }}</p>
                        <div class="mt-0.5 flex items-center gap-2 text-xs text-slate-500">
                            <span>{{ bsDate($notice->created_at, 'F d, Y') }}</span>
                            <span>·</span>
                            <span>{{ $notice->author->name ?? 'System' }}</span>
                            @if($notice->department)
                                <span>·</span>
                                <span class="text-blue-600">{{ $notice->department->name }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="mt-2 text-xs text-slate-400">No notices available</p>
                </div>
            @endforelse
        </div>
        
        {{-- CTEVT Notices Tab --}}
        <div x-show="activeTab === 'ctevt'" class="divide-y divide-slate-100" x-cloak x-data="{ ctevtSubTab: 'general' }">
            <div class="flex gap-1 bg-slate-50 p-2">
                <button @click="ctevtSubTab = 'general'" :class="ctevtSubTab === 'general' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-600 hover:bg-white/50'" class="flex-1 rounded-md px-2.5 py-1.5 text-[10px] font-semibold transition">
                    General
                </button>
                <button @click="ctevtSubTab = 'result'" :class="ctevtSubTab === 'result' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-600 hover:bg-white/50'" class="flex-1 rounded-md px-2.5 py-1.5 text-[10px] font-semibold transition">
                    Results
                </button>
            </div>

            <div x-show="ctevtSubTab === 'general'" class="divide-y divide-slate-100 max-h-[350px] sm:max-h-[450px] overflow-y-auto">
                @if(isset($ctevtGeneralNotices['items']) && count($ctevtGeneralNotices['items']) > 0)
                    @foreach($ctevtGeneralNotices['items'] as $notice)
                        <a href="{{ $notice['url'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="block px-4 py-2.5 transition hover:bg-slate-50">
                            <div class="flex items-center gap-2">
                                <span class="shrink-0 rounded bg-red-50 px-1.5 py-0.5 text-[8px] font-bold text-red-600">CTEVT</span>
                                <span class="min-w-0 flex-1 truncate text-[11px] font-medium text-slate-700">{{ $notice['title'] ?? 'Notice' }}</span>
                            </div>
                            @if(!empty($notice['updated_date']))
                                <p class="mt-1 text-[9px] text-slate-400">{{ $notice['updated_date'] }}</p>
                            @endif
                        </a>
                    @endforeach
                @else
                    <p class="py-8 text-center text-xs text-slate-400">No general notices available.</p>
                @endif
            </div>

            <div x-show="ctevtSubTab === 'result'" x-cloak class="divide-y divide-slate-100 max-h-[350px] sm:max-h-[450px] overflow-y-auto">
                @if(isset($ctevtResultNotices['items']) && count($ctevtResultNotices['items']) > 0)
                    @foreach($ctevtResultNotices['items'] as $notice)
                        <a href="{{ $notice['url'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="block px-4 py-2.5 transition hover:bg-slate-50">
                            <div class="flex items-center gap-2">
                                <span class="shrink-0 rounded bg-emerald-50 px-1.5 py-0.5 text-[8px] font-bold text-emerald-600">CTEVT</span>
                                <span class="min-w-0 flex-1 truncate text-[11px] font-medium text-slate-700">{{ $notice['title'] ?? 'Result' }}</span>
                            </div>
                            @if(!empty($notice['updated_date']))
                                <p class="mt-1 text-[9px] text-slate-400">{{ $notice['updated_date'] }}</p>
                            @endif
                        </a>
                    @endforeach
                @else
                    <p class="py-8 text-center text-xs text-slate-400">No result notices available.</p>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection
