@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Welcome back, {{ $student?->user->name }}</h1>
        <p class="text-sm text-gray-500 mt-1">
            {{ $student?->program->name ?? 'Unknown Program' }} • Semester {{ $student?->current_semester ?? '-' }}
        </p>
    </div>
    <div class="mt-4 sm:mt-0 bg-white px-4 py-2 rounded-xl shadow-sm border border-gray-200">
        <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Roll Number</span>
        <p class="text-lg font-bold text-gray-900 leading-none mt-1">{{ $student?->roll_number ?? 'N/A' }}</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <x-card>
        <x-slot name="header">
            <h3 class="font-bold text-gray-800">Upcoming Assignments</h3>
        </x-slot>
        @if($upcomingAssignments->count() > 0)
            <ul class="divide-y divide-gray-100">
                @foreach($upcomingAssignments as $assignment)
                    <li class="py-3 flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $assignment->title }}</p>
                            <p class="text-xs text-gray-500">{{ $assignment->subject->name }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-semibold {{ $assignment->due_date->isPast() ? 'text-red-600' : 'text-blue-600' }}">
                                Due {{ $assignment->due_date->diffForHumans() }}
                            </span>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-500">No upcoming assignments.</p>
        @endif
    </x-card>

    <x-card>
        <x-slot name="header">
            <h3 class="font-bold text-gray-800">Notices & Announcements</h3>
        </x-slot>
        @if($recentNotices->count() > 0)
            <div class="space-y-3">
                @foreach($recentNotices as $notice)
                    <div class="flex items-start gap-3">
                        <div class="mt-1 w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $notice->title }}</p>
                            <p class="text-xs text-gray-500">{{ $notice->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500">No new notices.</p>
        @endif
    </x-card>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <a href="#" class="flex flex-col items-center justify-center p-6 bg-white rounded-xl border border-gray-200 hover:border-primary-500 hover:shadow-sm transition-all group">
        <div class="p-3 bg-blue-50 text-blue-600 rounded-xl group-hover:bg-blue-600 group-hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
        </div>
        <span class="mt-3 text-sm font-medium text-gray-700">Attendance</span>
    </a>
    
    <a href="#" class="flex flex-col items-center justify-center p-6 bg-white rounded-xl border border-gray-200 hover:border-primary-500 hover:shadow-sm transition-all group">
        <div class="p-3 bg-green-50 text-green-600 rounded-xl group-hover:bg-green-600 group-hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
        </div>
        <span class="mt-3 text-sm font-medium text-gray-700">Results/Marks</span>
    </a>

    <a href="#" class="flex flex-col items-center justify-center p-6 bg-white rounded-xl border border-gray-200 hover:border-primary-500 hover:shadow-sm transition-all group">
        <div class="p-3 bg-purple-50 text-purple-600 rounded-xl group-hover:bg-purple-600 group-hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
        </div>
        <span class="mt-3 text-sm font-medium text-gray-700">Timetable</span>
    </a>

    <a href="#" class="flex flex-col items-center justify-center p-6 bg-white rounded-xl border border-gray-200 hover:border-primary-500 hover:shadow-sm transition-all group">
        <div class="p-3 bg-orange-50 text-orange-600 rounded-xl group-hover:bg-orange-600 group-hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        </div>
        <span class="mt-3 text-sm font-medium text-gray-700">Downloads</span>
    </a>
</div>
@endsection
