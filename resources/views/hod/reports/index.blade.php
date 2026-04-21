@extends('layouts.app')
@section('title', 'Reports & Analytics')

@section('content')
<x-page-header title="Reports & Analytics" subtitle="Department performance insights and data exports."/>

{{-- Overview Stats --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Total Students</p>
                <p class="mt-1 text-2xl font-bold text-slate-800">{{ $totalStudents }}</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100">
                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Total Teachers</p>
                <p class="mt-1 text-2xl font-bold text-slate-800">{{ $totalTeachers }}</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100">
                <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Programs</p>
                <p class="mt-1 text-2xl font-bold text-slate-800">{{ $totalPrograms }}</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-100">
                <svg class="h-6 w-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Attendance Rate</p>
                <p class="mt-1 text-2xl font-bold text-emerald-600">{{ $overallAttendanceRate }}%</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100">
                <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

{{-- Recent Activity --}}
<div class="mb-8 rounded-2xl border border-slate-200 bg-white p-6">
    <h2 class="text-lg font-bold text-slate-800 mb-4">Recent Activity (Last 30 Days)</h2>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $recentAttendanceSessions }}</p>
            <p class="text-sm text-slate-500">Attendance Sessions</p>
        </div>
        <div class="text-center">
            <p class="text-2xl font-bold text-emerald-600">{{ $recentExams }}</p>
            <p class="text-sm text-slate-500">Exams Conducted</p>
        </div>
        <div class="text-center">
            <p class="text-2xl font-bold text-violet-600">{{ $overallPassRate }}%</p>
            <p class="text-sm text-slate-500">Overall Pass Rate</p>
        </div>
    </div>
</div>

{{-- Report Categories --}}
<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    {{-- Attendance Reports --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100">
                <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Attendance Reports</h3>
        </div>
        <p class="text-sm text-slate-600 mb-4">
            Student attendance analysis, session reports, and subject-wise attendance rates.
        </p>
        <div class="flex gap-2">
            <a href="{{ route('hod.reports.attendance') }}" 
               class="flex-1 rounded-xl bg-blue-600 px-4 py-2 text-center text-sm font-semibold text-white transition hover:bg-blue-700">
                View Reports
            </a>
            <a href="{{ route('hod.reports.export', 'attendance') }}" 
               class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Export
            </a>
        </div>
    </div>

    {{-- Performance Reports --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100">
                <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Performance Reports</h3>
        </div>
        <p class="text-sm text-slate-600 mb-4">
            Student academic performance, exam results, and subject-wise analysis.
        </p>
        <div class="flex gap-2">
            <a href="{{ route('hod.reports.performance') }}" 
               class="flex-1 rounded-xl bg-emerald-600 px-4 py-2 text-center text-sm font-semibold text-white transition hover:bg-emerald-700">
                View Reports
            </a>
            <a href="{{ route('hod.reports.export', 'performance') }}" 
               class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Export
            </a>
        </div>
    </div>

    {{-- Department Overview --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100">
                <svg class="h-5 w-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Department Overview</h3>
        </div>
        <p class="text-sm text-slate-600 mb-4">
            Comprehensive department statistics, trends, and summary reports.
        </p>
        <div class="flex gap-2">
            <a href="{{ route('hod.reports.department') }}" 
               class="flex-1 rounded-xl bg-violet-600 px-4 py-2 text-center text-sm font-semibold text-white transition hover:bg-violet-700">
                View Reports
            </a>
        </div>
    </div>

    {{-- Data Exports --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100">
                <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Data Exports</h3>
        </div>
        <p class="text-sm text-slate-600 mb-4">
            Export department data in CSV format for external analysis and reporting.
        </p>
        <div class="space-y-2">
            <a href="{{ route('hod.reports.export', 'students') }}" 
               class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Export Students
            </a>
            <a href="{{ route('hod.reports.export', 'teachers') }}" 
               class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Export Teachers
            </a>
        </div>
    </div>
</div>
@endsection