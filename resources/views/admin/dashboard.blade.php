@extends('layouts.app')

@section('title', 'Principal Dashboard')

@section('content')
<div class="mb-8 flex justify-between items-end">
    <div>
        <h1 class="text-2xl font-black text-[#8B0000] font-serif uppercase tracking-wider">Principal Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1 font-medium">System Overview & Live KPIs for {{ $stats['active_session'] }}</p>
    </div>
    <div class="hidden sm:block">
        <a href="#" class="inline-flex items-center px-4 py-2 bg-[#8B0000] border border-transparent rounded shadow-sm text-sm font-bold text-white hover:bg-[#6A0000] focus:outline-none transition-colors">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Generate Report
        </a>
    </div>
</div>

<!-- Primary KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <x-card class="border-l-4 border-l-[#8B0000] hover:-translate-y-1 transition-transform shadow-md">
        <div class="flex items-center">
            <div class="p-3 rounded bg-red-50 text-[#8B0000]">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div class="ml-4">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Total Students</p>
                <p class="text-2xl font-black text-gray-900">{{ number_format($stats['total_students']) }}</p>
            </div>
        </div>
    </x-card>

    <x-card class="border-l-4 border-l-yellow-500 hover:-translate-y-1 transition-transform shadow-md">
        <div class="flex items-center">
            <div class="p-3 rounded bg-yellow-50 text-yellow-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
            <div class="ml-4">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Master Teachers</p>
                <p class="text-2xl font-black text-gray-900">{{ number_format($stats['total_teachers']) }}</p>
            </div>
        </div>
    </x-card>
    
    <x-card class="border-l-4 border-l-[#DAA520] hover:-translate-y-1 transition-transform shadow-md">
        <div class="flex items-center">
            <div class="p-3 rounded bg-yellow-50 text-[#DAA520]">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <div class="ml-4">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Departments</p>
                <p class="text-2xl font-black text-gray-900">{{ number_format($stats['total_departments']) }}</p>
            </div>
        </div>
    </x-card>

    <x-card class="border-l-4 border-l-[#404040] hover:-translate-y-1 transition-transform shadow-md">
        <div class="flex items-center">
            <div class="p-3 rounded bg-gray-100 text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="ml-4">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Alumni Network</p>
                <p class="text-2xl font-black text-gray-900">{{ number_format($stats['total_alumni']) }}</p>
            </div>
        </div>
    </x-card>
</div>

<!-- Advanced Charts and Rate Dashboards -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Attendance Rate Chart -->
    <x-card class="flex flex-col items-center justify-center text-center p-6 relative overflow-hidden shadow-md">
        <h3 class="font-bold text-gray-800 absolute top-4 left-5">Live Attendance Rate</h3>
        <div class="relative w-36 h-36 mt-6">
            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                <!-- Background Circle -->
                <path class="text-gray-100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3"></path>
                <!-- Progress Circle -->
                <path class="text-[#8B0000]" stroke-dasharray="{{ $stats['attendance_rate'] }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3"></path>
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-3xl font-black text-gray-900">{{ $stats['attendance_rate'] }}%</span>
                <span class="text-[10px] uppercase font-bold text-gray-400">Past 30 Days</span>
            </div>
        </div>
    </x-card>

    <!-- Pass Rate Target -->
    <x-card class="flex flex-col items-center justify-center text-center p-6 relative shadow-md">
        <h3 class="font-bold text-gray-800 absolute top-4 left-5">Overall Pass Ratio (CTEVT)</h3>
        <div class="w-full px-4 mt-8">
            <div class="flex justify-between items-end mb-2">
                <span class="text-xl font-black text-gray-900">{{ $stats['pass_rate'] }}%</span>
                <span class="text-xs font-bold text-green-600">+1.2% Trend</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3.5 mb-4">
                <div class="bg-yellow-500 h-3.5 rounded-full" style="width: {{ $stats['pass_rate'] }}%"></div>
            </div>
            <p class="text-xs font-medium text-gray-500 text-left">Internal and External evaluated theory marks aggregate comparison against previous session.</p>
        </div>
    </x-card>

    <!-- Upcoming Events List -->
    <x-card class="shadow-md">
        <x-slot name="header">
            <h3 class="font-bold text-gray-800">Upcoming Events</h3>
            <a href="#" class="text-xs font-bold text-[#8B0000] hover:underline uppercase">Full Calendar</a>
        </x-slot>
        <div class="space-y-4">
            @forelse($upcomingEvents as $event)
                <div class="flex items-start gap-4">
                    <div class="flex flex-col items-center justify-center w-12 h-14 bg-yellow-50 rounded border border-yellow-100 flex-shrink-0 text-yellow-800">
                        <span class="text-[10px] font-bold uppercase">{{ $event->created_at->format('M') }}</span>
                        <span class="text-lg font-black leading-none">{{ $event->created_at->format('d') }}</span>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-gray-900">{{ $event->title }}</h4>
                        <p class="text-xs text-gray-500 line-clamp-2 mt-0.5">{{ $event->content }}</p>
                    </div>
                </div>
            @empty
                <p class="text-xs text-gray-500 text-center py-4">No upcoming events scheduled.</p>
            @endforelse
        </div>
    </x-card>
</div>

<!-- Lower Information Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Department Breakdown Table -->
    <x-card class="shadow-md">
        <x-slot name="header">
            <h3 class="font-bold text-gray-800">Department Overview</h3>
            <a href="#" class="text-xs font-bold text-gray-500 hover:text-[#8B0000] uppercase">Manage</a>
        </x-slot>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-bold">Department Code</th>
                        <th scope="col" class="px-4 py-3 font-bold">Total Students</th>
                        <th scope="col" class="px-4 py-3 font-bold text-right">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats['departments_data'] ?? [] as $dept)
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="px-4 py-3 font-bold text-gray-900">{{ current(explode(' ', $dept->name)) ?? $dept->code }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-bold">{{ $dept->students_count ?? 0 }} enrolled</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="w-2.5 h-2.5 rounded-full bg-green-500 inline-block mr-1"></span> Active
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-xs text-gray-500">No departments configured yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    <!-- Recent Notices -->
    <x-card class="shadow-md">
        <x-slot name="header">
            <h3 class="font-bold text-gray-800">Notice Board Feed</h3>
            <a href="#" class="text-xs font-bold text-[#8B0000] hover:underline uppercase">Post Notice</a>
        </x-slot>
        
        <div class="space-y-4">
            @forelse($recentNotices as $notice)
                <div class="flex gap-4 p-3 hover:bg-red-50 rounded transition-colors border border-transparent hover:border-red-100">
                    <div class="flex-shrink-0 flex flex-col items-center justify-center w-12 h-12 bg-white text-[#8B0000] rounded border border-gray-200 shadow-sm">
                        <span class="text-[10px] font-bold leading-none uppercase">{{ $notice->created_at->format('M') }}</span>
                        <span class="text-lg font-black leading-none mt-1">{{ $notice->created_at->format('d') }}</span>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-gray-900">{{ $notice->title }}</h4>
                        <p class="text-xs text-gray-500 line-clamp-1 mt-0.5">{{ $notice->content }}</p>
                        <div class="flex gap-2 mt-1.5">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#8B0000] text-white uppercase tracking-wider">{{ $notice->type }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-gray-500">
                    <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                    </svg>
                    <h3 class="mt-2 text-xs font-bold text-gray-900 uppercase">No notices</h3>
                    <p class="mt-1 text-[10px] text-gray-500">No notices have been published recently.</p>
                </div>
            @endforelse
        </div>
    </x-card>
</div>
@endsection
