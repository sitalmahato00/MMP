@extends('layouts.app')
@section('title', 'Alumni Dashboard')

@section('content')
@php
    $user = auth()->user();
    $gradients = ['from-blue-500 to-indigo-600','from-violet-500 to-purple-600','from-emerald-500 to-teal-600','from-amber-500 to-orange-600','from-rose-500 to-pink-600','from-cyan-500 to-sky-600'];
    $grad = $gradients[($alumnus->id ?? 0) % 6];
@endphp

{{-- Welcome Header --}}
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Welcome back, {{ $user->name }}</h1>
    <p class="text-sm text-gray-500 mt-1">
        {{ $alumnus?->department?->name ?? 'Alumni' }} · Batch {{ $alumnus?->graduation_year ?? '—' }}
        @if($alumnus?->current_job) · {{ $alumnus->current_job }}@if($alumnus->company_name) at {{ $alumnus->company_name }}@endif @endif
    </p>
</div>

{{-- Profile Completion --}}
@if($profileCompletion < 100)
<div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50/50 p-5">
    <div class="flex items-start gap-4">
        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-amber-100">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-bold text-amber-800">Complete Your Profile</p>
            <p class="text-xs text-amber-700 mt-0.5">Your profile is {{ $profileCompletion }}% complete. Add more details to increase visibility.</p>
            <div class="mt-2 h-2 w-full rounded-full bg-amber-200">
                <div class="h-2 rounded-full bg-amber-500 transition-all" style="width: {{ $profileCompletion }}%"></div>
            </div>
        </div>
        <a href="{{ route('alumni.profile.index') }}" class="flex-shrink-0 rounded-xl bg-amber-600 px-4 py-2 text-xs font-bold text-white hover:bg-amber-700 transition">Complete Profile</a>
    </div>
</div>
@endif

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900">{{ $profileCompletion }}%</p>
                <p class="text-xs text-slate-500">Profile</p>
            </div>
        </div>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50">
                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900">{{ $alumnus?->projects?->count() ?? 0 }}</p>
                <p class="text-xs text-slate-500">Projects</p>
            </div>
        </div>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900">{{ $alumnus?->achievementRecords?->count() ?? 0 }}</p>
                <p class="text-xs text-slate-500">Achievements</p>
            </div>
        </div>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900">{{ $alumnus?->employmentHistory?->count() ?? 0 }}</p>
                <p class="text-xs text-slate-500">Positions</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Quick Links --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h3 class="font-bold text-slate-900">Quick Actions</h3>
            </div>
            <div class="grid grid-cols-2 gap-3 p-5">
                <a href="{{ route('alumni.profile.index') }}" class="flex items-center gap-3 rounded-xl border border-slate-100 p-4 hover:bg-slate-50 transition group">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 group-hover:bg-blue-100 transition">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-900">Edit Profile</p>
                        <p class="text-xs text-slate-500">Update your information</p>
                    </div>
                </a>
                <a href="{{ route('alumni.career.index') }}" class="flex items-center gap-3 rounded-xl border border-slate-100 p-4 hover:bg-slate-50 transition group">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 group-hover:bg-emerald-100 transition">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-900">Career History</p>
                        <p class="text-xs text-slate-500">Manage employment records</p>
                    </div>
                </a>
                <a href="{{ route('alumni.projects.index') }}" class="flex items-center gap-3 rounded-xl border border-slate-100 p-4 hover:bg-slate-50 transition group">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 group-hover:bg-violet-100 transition">
                        <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-900">My Projects</p>
                        <p class="text-xs text-slate-500">Upload minor & major projects</p>
                    </div>
                </a>
                <a href="{{ route('alumni.achievements.index') }}" class="flex items-center gap-3 rounded-xl border border-slate-100 p-4 hover:bg-slate-50 transition group">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 group-hover:bg-amber-100 transition">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-900">Achievements</p>
                        <p class="text-xs text-slate-500">Add awards & certificates</p>
                    </div>
                </a>
            </div>
        </div>

        {{-- Projects Summary --}}
        @if($alumnus?->projects?->count())
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 flex items-center justify-between">
                <h3 class="font-bold text-slate-900">My Projects</h3>
                <a href="{{ route('alumni.projects.index') }}" class="text-xs font-semibold text-[#8B0000] hover:underline">View All →</a>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($alumnus->projects as $project)
                <div class="rounded-xl border {{ $project->type === 'minor' ? 'border-cyan-100 bg-cyan-50/30' : 'border-violet-100 bg-violet-50/30' }} p-4">
                    <span class="inline-block rounded-lg {{ $project->type === 'minor' ? 'bg-cyan-100 text-cyan-700' : 'bg-violet-100 text-violet-700' }} px-2 py-0.5 text-[10px] font-bold uppercase mb-2">{{ $project->type }}</span>
                    <h4 class="text-sm font-bold text-slate-900">{{ $project->title }}</h4>
                    @if($project->technologies && count($project->technologies))
                        <div class="flex flex-wrap gap-1 mt-2">
                            @foreach(array_slice($project->technologies, 0, 3) as $tech)
                                <span class="rounded bg-white/80 px-1.5 py-0.5 text-[10px] font-semibold text-slate-600">{{ $tech }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Right Column --}}
    <div class="space-y-6">
        {{-- Notices --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 flex items-center justify-between">
                <h3 class="font-bold text-slate-900">Recent Notices</h3>
                <a href="{{ route('alumni.notices.index') }}" class="text-xs font-semibold text-[#8B0000] hover:underline">All →</a>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($recentNotices as $notice)
                <div class="px-5 py-3">
                    <p class="text-sm font-semibold text-slate-900 line-clamp-1">{{ $notice->title }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ bsDate($notice->published_at ?? $notice->created_at) }}</p>
                </div>
                @empty
                <div class="px-5 py-6 text-center">
                    <p class="text-sm text-slate-500 italic">No recent notices.</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Profile Card --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 p-5 text-center">
                @if($user->avatar)
                    <img src="{{ asset('storage/'.$user->avatar) }}" class="mx-auto h-16 w-16 rounded-2xl object-cover ring-4 ring-white/20"/>
                @else
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br {{ $grad }} text-2xl font-black text-white ring-4 ring-white/10">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <p class="mt-3 text-sm font-bold text-white">{{ $user->name }}</p>
                <p class="text-xs text-slate-400">{{ $alumnus?->department?->name }}</p>
            </div>
            <div class="p-4 space-y-2">
                @if($alumnus?->linkedin_url)
                <a href="{{ $alumnus->linkedin_url }}" target="_blank" rel="noopener" class="flex items-center gap-2 text-xs text-slate-600 hover:text-blue-600">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    LinkedIn
                </a>
                @endif
                @if($alumnus?->github_url)
                <a href="{{ $alumnus->github_url }}" target="_blank" rel="noopener" class="flex items-center gap-2 text-xs text-slate-600 hover:text-slate-900">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                    GitHub
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection