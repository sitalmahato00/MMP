@extends('layouts.app')
@section('title', 'Department Overview')

@section('content')
<x-page-header title="Department Overview" subtitle="Comprehensive department statistics and insights."
               back="{{ route('hod.reports.index') }}"/>

{{-- Department Summary --}}
<div class="mb-8 rounded-2xl border border-slate-200 bg-white p-6">
    <h2 class="text-lg font-bold text-slate-800 mb-6">{{ $department->name }} Summary</h2>
    
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Student Statistics --}}
        <div>
            <h3 class="text-md font-bold text-slate-700 mb-4">Student Statistics</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-600">Total Students</span>
                    <span class="text-lg font-bold text-slate-800">{{ $stats['students']['total'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-600">Active Students</span>
                    <span class="text-lg font-bold text-emerald-600">{{ $stats['students']['active'] }}</span>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <p class="text-sm font-semibold text-slate-700 mb-2">By Semester</p>
                    <div class="space-y-1">
                        @foreach($stats['students']['by_semester'] as $semester)
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600">Semester {{ $semester->semester }}</span>
                                <span class="font-semibold text-slate-800">{{ $semester->count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Teacher Statistics --}}
        <div>
            <h3 class="text-md font-bold text-slate-700 mb-4">Teacher Statistics</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-600">Total Teachers</span>
                    <span class="text-lg font-bold text-slate-800">{{ $stats['teachers']['total'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-600">Active Teachers</span>
                    <span class="text-lg font-bold text-emerald-600">{{ $stats['teachers']['active'] }}</span>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <p class="text-sm font-semibold text-slate-700 mb-2">By Designation</p>
                    <div class="space-y-1">
                        @foreach($stats['teachers']['by_designation'] as $designation)
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600">{{ ucfirst($designation->designation) }}</span>
                                <span class="font-semibold text-slate-800">{{ $designation->count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Performance Metrics --}}
<div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Programs</p>
                <p class="mt-1 text-2xl font-bold text-slate-800">{{ $stats['programs'] }}</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100">
                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Attendance Rate</p>
                <p class="mt-1 text-2xl font-bold text-emerald-600">{{ $stats['attendance_rate'] }}%</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100">
                <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Pass Rate</p>
                <p class="mt-1 text-2xl font-bold text-violet-600">{{ $stats['pass_rate'] }}%</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-100">
                <svg class="h-6 w-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Student-Teacher Ratio</p>
                <p class="mt-1 text-2xl font-bold text-amber-600">
                    {{ $stats['teachers']['active'] > 0 ? round($stats['students']['active'] / $stats['teachers']['active'], 1) : 0 }}:1
                </p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100">
                <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

{{-- Recent Activity --}}
<div class="rounded-2xl border border-slate-200 bg-white p-6">
    <h2 class="text-lg font-bold text-slate-800 mb-6">Recent Activity (Last 30 Days)</h2>
    
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <div class="text-center p-4 rounded-xl bg-blue-50">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 mx-auto mb-3">
                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
            <p class="text-2xl font-bold text-blue-600">{{ $recentActivity['attendance_sessions'] }}</p>
            <p class="text-sm text-slate-600">Attendance Sessions</p>
        </div>

        <div class="text-center p-4 rounded-xl bg-emerald-50">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 mx-auto mb-3">
                <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-2xl font-bold text-emerald-600">{{ $recentActivity['exams_conducted'] }}</p>
            <p class="text-sm text-slate-600">Exams Conducted</p>
        </div>

        <div class="text-center p-4 rounded-xl bg-violet-50">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-100 mx-auto mb-3">
                <svg class="h-6 w-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <p class="text-2xl font-bold text-violet-600">{{ $recentActivity['notices_published'] }}</p>
            <p class="text-sm text-slate-600">Notices Published</p>
        </div>
    </div>
</div>
@endsection