@extends('layouts.app')

@section('title', 'HOD Dashboard')

@section('content')
<div class="mb-8 flex justify-between items-end">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Department Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1 border-l-2 border-primary-500 pl-2">
            Department of {{ $department->name ?? 'Unknown' }}
        </p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <x-card class="border-t-4 border-t-blue-500">
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-50 text-blue-600 mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <h3 class="text-3xl font-bold text-gray-900">{{ number_format($stats['student_count']) }}</h3>
            <p class="text-sm font-medium text-gray-500 mt-1">Active Students</p>
        </div>
    </x-card>

    <x-card class="border-t-4 border-t-purple-500">
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-purple-50 text-purple-600 mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
            <h3 class="text-3xl font-bold text-gray-900">{{ number_format($stats['teacher_count']) }}</h3>
            <p class="text-sm font-medium text-gray-500 mt-1">Department Teachers</p>
        </div>
    </x-card>
    
    <x-card class="border-t-4 border-t-orange-500">
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-orange-50 text-orange-600 mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <h3 class="text-3xl font-bold text-gray-900">0</h3>
            <p class="text-sm font-medium text-gray-500 mt-1">Pending Mark Approvals</p>
        </div>
    </x-card>
</div>

<x-card>
    <x-slot name="header">
        <h3 class="font-bold text-gray-800">Department Notices</h3>
    </x-slot>
    <div class="space-y-3">
        @forelse($recentNotices as $notice)
            <div class="p-4 rounded-lg bg-gray-50 border border-gray-100 flex justify-between items-start">
                <div>
                    <h4 class="font-semibold text-gray-900">{{ $notice->title }}</h4>
                    <p class="text-xs text-gray-500 mt-1">{{ $notice->created_at->format('M d, Y h:i A') }} • By {{ $notice->author->name ?? 'System' }}</p>
                </div>
                <span class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-800">Published</span>
            </div>
        @empty
            <p class="text-sm text-gray-500">No notices found.</p>
        @endforelse
    </div>
</x-card>
@endsection
