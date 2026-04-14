@extends('layouts.app')

@section('title', 'Principal Dashboard')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">Principal Dashboard</h1>
    <p class="text-sm text-gray-500 mt-1">System Overview & KPIs</p>
</div>

<!-- KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <x-card class="border-l-4 border-l-blue-500 hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-xl bg-blue-50 text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Students</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_students']) }}</p>
            </div>
        </div>
    </x-card>

    <x-card class="border-l-4 border-l-purple-500 hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-xl bg-purple-50 text-purple-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Teachers</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_teachers']) }}</p>
            </div>
        </div>
    </x-card>
    
    <x-card class="border-l-4 border-l-orange-500 hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-xl bg-orange-50 text-orange-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Departments</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_departments']) }}</p>
            </div>
        </div>
    </x-card>

    <x-card class="border-l-4 border-l-green-500 hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="p-3 rounded-xl bg-green-50 text-green-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Alumni Network</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_alumni']) }}</p>
            </div>
        </div>
    </x-card>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Notice Board -->
    <div class="lg:col-span-2">
        <x-card>
            <x-slot name="header">
                <h3 class="font-bold text-gray-800">Recent Notices</h3>
                <a href="#" class="text-sm font-medium text-blue-600 hover:underline">View All</a>
            </x-slot>
            
            <div class="space-y-4">
                @forelse($recentNotices as $notice)
                    <div class="flex gap-4 p-3 hover:bg-gray-50 rounded-lg transition-colors border border-transparent hover:border-gray-100">
                        <div class="flex-shrink-0 flex flex-col items-center justify-center w-12 h-12 bg-blue-50 text-blue-700 rounded-lg border border-blue-100">
                            <span class="text-xs font-bold leading-none">{{ $notice->created_at->format('M') }}</span>
                            <span class="text-lg font-black leading-none mt-0.5">{{ $notice->created_at->format('d') }}</span>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">{{ $notice->title }}</h4>
                            <p class="text-sm text-gray-500 line-clamp-1 mt-0.5">{{ $notice->content }}</p>
                            <div class="flex gap-2 mt-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 capitalize">{{ $notice->type }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No notices</h3>
                        <p class="mt-1 text-sm text-gray-500">No notices have been published recently.</p>
                    </div>
                @endforelse
            </div>
        </x-card>
    </div>

    <!-- System Audit Logs -->
    <div>
        <x-card>
            <x-slot name="header">
                <h3 class="font-bold text-gray-800">System Activity</h3>
                <span class="text-xs text-gray-500">Last 10 actions</span>
            </x-slot>
            
            <div class="flow-root">
                <ul role="list" class="-mb-8">
                    @forelse($recentLogs as $log)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white bg-gray-100">
                                            <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </span>
                                    </div>
                                    <div class="flex flex-col flex-1 min-w-0 pb-1">
                                        <p class="text-sm text-gray-800">
                                            <span class="font-medium text-gray-900">{{ $log->user->name ?? 'System' }}</span>
                                            <span class="text-gray-500">{{ $log->action }}</span>
                                        </p>
                                        <div class="mt-0.5 whitespace-nowrap text-xs text-gray-500">
                                            {{ $log->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="text-center py-4 text-sm text-gray-500">No recent activity</li>
                    @endforelse
                </ul>
            </div>
        </x-card>
    </div>
</div>
@endsection
