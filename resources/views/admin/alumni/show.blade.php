@extends('layouts.app')
@section('title', $alumnus->user?->name ?? 'Alumni')

@section('content')
@php
    $isActive = $alumnus->user?->is_active ?? false;
    $gradients = ['from-blue-500 to-indigo-600','from-violet-500 to-purple-600','from-emerald-500 to-teal-600','from-amber-500 to-orange-600','from-rose-500 to-pink-600','from-cyan-500 to-sky-600'];
    $grad = $gradients[$alumnus->id % 6];
    $statusMap = [
        'employed'   => ['label' => 'Employed',   'cls' => 'bg-emerald-50 text-emerald-700'],
        'studying'   => ['label' => 'Studying',   'cls' => 'bg-blue-50 text-blue-700'],
        'freelancing'=> ['label' => 'Freelancing', 'cls' => 'bg-violet-50 text-violet-700'],
        'unemployed' => ['label' => 'Unemployed', 'cls' => 'bg-amber-50 text-amber-700'],
        'unknown'    => ['label' => 'Unknown',    'cls' => 'bg-slate-100 text-slate-600'],
    ];
    $st = $statusMap[$alumnus->employment_status] ?? $statusMap['unknown'];
@endphp

<div x-data="{ tab: '{{ request('tab', 'overview') }}' }">

{{-- HERO HEADER --}}
<div class="relative mb-6 overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 shadow-lg">
    <div class="absolute inset-0 opacity-5" style="background-image:url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    <div class="relative px-6 py-7">
        <div class="flex flex-wrap items-start gap-5">
            @if($alumnus->user?->avatar)
                <img src="{{ asset('storage/'.$alumnus->user->avatar) }}" alt="" class="h-20 w-20 flex-shrink-0 rounded-2xl object-cover ring-4 ring-white/20 shadow-lg"/>
            @else
                <div class="flex h-20 w-20 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br {{ $grad }} text-3xl font-black text-white shadow-lg ring-4 ring-white/10">
                    {{ strtoupper(substr($alumnus->user?->name ?? 'A', 0, 1)) }}
                </div>
            @endif

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl font-black text-white leading-tight">{{ $alumnus->user?->name }}</h1>
                    @if($alumnus->is_featured)
                        <span class="rounded-lg px-2.5 py-1 text-xs font-bold bg-amber-50 text-amber-700 ring-1 ring-amber-200">★ Featured</span>
                    @endif
                    <span class="rounded-lg px-2.5 py-1 text-xs font-bold {{ $st['cls'] }}">{{ $st['label'] }}</span>
                </div>
                <p class="mt-1 text-sm text-slate-400">
                    @if($alumnus->current_job){{ $alumnus->current_job }}@endif
                    @if($alumnus->company_name) at {{ $alumnus->company_name }}@endif
                    @if(!$alumnus->current_job && !$alumnus->company_name) Alumni @endif
                    · Batch {{ $alumnus->graduation_year }}
                </p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @if($alumnus->user?->phone)
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-xs text-slate-200">
                        <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ $alumnus->user->phone }}
                    </span>
                    @endif
                    @if($alumnus->user?->email)
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 text-xs text-slate-200">
                        <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        {{ $alumnus->user->email }}
                    </span>
                    @endif
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-violet-500/30 px-3 py-1.5 text-xs font-bold text-violet-200">
                        {{ $alumnus->department?->name }} · {{ $alumnus->program?->name }}
                    </span>
                </div>
            </div>

            <div class="flex flex-shrink-0 flex-wrap gap-2">
                <form method="POST" action="{{ route('admin.alumni.toggle-featured', $alumnus) }}">
                    @csrf
                    <button class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/20 transition">
                        <svg class="w-4 h-4" fill="{{ $alumnus->is_featured ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        {{ $alumnus->is_featured ? 'Unfeature' : 'Feature' }}
                    </button>
                </form>
                <a href="{{ route('admin.alumni.edit', $alumnus) }}" class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                <a href="{{ route('admin.alumni.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back
                </a>
            </div>
        </div>
    </div>
</div>

{{-- TABS --}}
<div class="mb-6 flex gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm overflow-x-auto">
    @foreach(['overview' => 'Overview', 'career' => 'Career', 'projects' => 'Projects', 'achievements' => 'Achievements', 'account' => 'Account'] as $key => $label)
    <button @click="tab='{{ $key }}'"
            :class="tab==='{{ $key }}' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50'"
            class="rounded-lg px-4 py-2 text-sm font-semibold transition whitespace-nowrap">
        {{ $label }}
    </button>
    @endforeach
</div>

{{-- OVERVIEW TAB --}}
<div x-show="tab==='overview'" x-cloak class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Personal Info --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h3 class="font-bold text-slate-900">Personal Information</h3>
            </div>
            <div class="p-5 space-y-3">
                @foreach([
                    ['label' => 'Full Name',  'value' => $alumnus->user?->name],
                    ['label' => 'Email',      'value' => $alumnus->user?->email],
                    ['label' => 'Phone',      'value' => $alumnus->user?->phone],
                    ['label' => 'Address',    'value' => $alumnus->user?->address],
                    ['label' => 'Bio',        'value' => Str::limit($alumnus->bio, 200)],
                ] as $f)
                <div class="flex justify-between py-2 border-b border-slate-50 last:border-0">
                    <span class="text-xs font-semibold text-slate-500">{{ $f['label'] }}</span>
                    <span class="text-sm text-slate-900 text-right max-w-[60%]">{{ $f['value'] ?? '—' }}</span>
                </div>
                @endforeach
                @if($alumnus->skills && count($alumnus->skills))
                <div class="pt-2">
                    <span class="text-xs font-semibold text-slate-500">Skills</span>
                    <div class="flex flex-wrap gap-1.5 mt-1.5">
                        @foreach($alumnus->skills as $skill)
                            <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $skill }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Academic Info --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h3 class="font-bold text-slate-900">Academic Information</h3>
            </div>
            <div class="p-5 space-y-3">
                @foreach([
                    ['label' => 'Department',      'value' => $alumnus->department?->name],
                    ['label' => 'Program',         'value' => $alumnus->program?->name],
                    ['label' => 'Roll Number',     'value' => $alumnus->roll_number],
                    ['label' => 'Admission Year',  'value' => $alumnus->admission_year],
                    ['label' => 'Graduation Year', 'value' => $alumnus->graduation_year],
                    ['label' => 'Alumni ID',       'value' => $alumnus->id],
                ] as $f)
                <div class="flex justify-between py-2 border-b border-slate-50 last:border-0">
                    <span class="text-xs font-semibold text-slate-500">{{ $f['label'] }}</span>
                    <span class="text-sm text-slate-900">{{ $f['value'] ?? '—' }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Social Links --}}
    @if($alumnus->linkedin_url || $alumnus->github_url || $alumnus->portfolio_url)
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h3 class="font-bold text-slate-900">Social & Links</h3>
        </div>
        <div class="p-5 flex flex-wrap gap-3">
            @if($alumnus->linkedin_url)
            <a href="{{ $alumnus->linkedin_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                LinkedIn
            </a>
            @endif
            @if($alumnus->github_url)
            <a href="{{ $alumnus->github_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                GitHub
            </a>
            @endif
            @if($alumnus->portfolio_url)
            <a href="{{ $alumnus->portfolio_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>
                Portfolio
            </a>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- CAREER TAB --}}
<div x-show="tab==='career'" x-cloak class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h3 class="font-bold text-slate-900">Current Position</h3>
        </div>
        <div class="p-5 space-y-3">
            @foreach([
                ['label' => 'Job Title',    'value' => $alumnus->current_job],
                ['label' => 'Company',      'value' => $alumnus->company_name],
                ['label' => 'Location',     'value' => $alumnus->work_location],
                ['label' => 'Status',       'value' => $st['label']],
            ] as $f)
            <div class="flex justify-between py-2 border-b border-slate-50 last:border-0">
                <span class="text-xs font-semibold text-slate-500">{{ $f['label'] }}</span>
                <span class="text-sm text-slate-900">{{ $f['value'] ?? '—' }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Employment History --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h3 class="font-bold text-slate-900">Employment History</h3>
        </div>
        <div class="p-5">
            @forelse($alumnus->employmentHistory as $job)
            <div class="flex gap-4 {{ !$loop->last ? 'mb-4 pb-4 border-b border-slate-50' : '' }}">
                <div class="flex-shrink-0 mt-1">
                    <div class="h-3 w-3 rounded-full {{ $job->is_current ? 'bg-emerald-500' : 'bg-slate-300' }}"></div>
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-900">{{ $job->job_title }}</p>
                    <p class="text-xs text-slate-600">{{ $job->company_name }}@if($job->location) · {{ $job->location }}@endif</p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        {{ $job->start_date?->format('M Y') ?? '—' }} — {{ $job->is_current ? 'Present' : ($job->end_date?->format('M Y') ?? '—') }}
                    </p>
                    @if($job->description)
                        <p class="text-xs text-slate-600 mt-1">{{ $job->description }}</p>
                    @endif
                </div>
            </div>
            @empty
            <p class="text-sm text-slate-500 italic text-center py-4">No employment history recorded.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- PROJECTS TAB --}}
<div x-show="tab==='projects'" x-cloak class="space-y-6">
    @php
        $minor = $alumnus->projects->firstWhere('type', 'minor');
        $major = $alumnus->projects->firstWhere('type', 'major');
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Minor Project --}}
        <div class="rounded-2xl border {{ $minor ? 'border-cyan-200' : 'border-slate-200' }} bg-white shadow-sm overflow-hidden">
            <div class="border-b {{ $minor ? 'border-cyan-100 bg-cyan-50/50' : 'border-slate-100' }} px-5 py-4 flex items-center justify-between">
                <h3 class="font-bold {{ $minor ? 'text-cyan-900' : 'text-slate-900' }}">Minor Project</h3>
                @if($minor && $minor->status === 'completed')
                    <span class="rounded-lg bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-700">Complete</span>
                @endif
            </div>
            <div class="p-5">
                @if($minor)
                    <h4 class="text-base font-bold text-slate-900 mb-2">{{ $minor->title }}</h4>
                    @if($minor->description)
                        <p class="text-sm text-slate-600 mb-3 line-clamp-3">{{ $minor->description }}</p>
                    @endif
                    @if($minor->supervisor)
                        <p class="text-xs text-slate-500 mb-2">Supervisor: <span class="font-semibold text-slate-700">{{ $minor->supervisor }}</span></p>
                    @endif
                    @if($minor->technologies && count($minor->technologies))
                        <div class="flex flex-wrap gap-1 mb-3">
                            @foreach($minor->technologies as $tech)
                                <span class="rounded bg-cyan-50 px-2 py-0.5 text-[10px] font-bold text-cyan-700">{{ $tech }}</span>
                            @endforeach
                        </div>
                    @endif
                    <div class="flex flex-wrap gap-2">
                        @if($minor->report_path)
                            <a href="{{ asset('storage/'.$minor->report_path) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-semibold text-cyan-700 hover:underline">📄 PDF Report</a>
                        @endif
                        @if($minor->github_url)
                            <a href="{{ $minor->github_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-700 hover:underline">GitHub</a>
                        @endif
                        @if($minor->demo_url)
                            <a href="{{ $minor->demo_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-xs font-semibold text-violet-700 hover:underline">Live Demo</a>
                        @endif
                    </div>
                    @if($minor->screenshots && count($minor->screenshots))
                        <div class="mt-3 grid grid-cols-3 gap-2">
                            @foreach(array_slice($minor->screenshots, 0, 3) as $ss)
                                <img src="{{ asset('storage/'.$ss) }}" class="rounded-lg object-cover h-16 w-full" alt="Screenshot"/>
                            @endforeach
                        </div>
                    @endif
                @else
                    <p class="text-sm text-slate-500 italic text-center py-6">No minor project uploaded.</p>
                @endif
            </div>
        </div>

        {{-- Major Project --}}
        <div class="rounded-2xl border {{ $major ? 'border-violet-200' : 'border-slate-200' }} bg-white shadow-sm overflow-hidden">
            <div class="border-b {{ $major ? 'border-violet-100 bg-violet-50/50' : 'border-slate-100' }} px-5 py-4 flex items-center justify-between">
                <h3 class="font-bold {{ $major ? 'text-violet-900' : 'text-slate-900' }}">Major Project</h3>
                @if($major && $major->status === 'completed')
                    <span class="rounded-lg bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-700">Complete</span>
                @endif
            </div>
            <div class="p-5">
                @if($major)
                    <h4 class="text-base font-bold text-slate-900 mb-2">{{ $major->title }}</h4>
                    @if($major->description)
                        <p class="text-sm text-slate-600 mb-3 line-clamp-3">{{ $major->description }}</p>
                    @endif
                    @if($major->supervisor)
                        <p class="text-xs text-slate-500 mb-2">Supervisor: <span class="font-semibold text-slate-700">{{ $major->supervisor }}</span></p>
                    @endif
                    @if($major->technologies && count($major->technologies))
                        <div class="flex flex-wrap gap-1 mb-3">
                            @foreach($major->technologies as $tech)
                                <span class="rounded bg-violet-50 px-2 py-0.5 text-[10px] font-bold text-violet-700">{{ $tech }}</span>
                            @endforeach
                        </div>
                    @endif
                    <div class="flex flex-wrap gap-2">
                        @if($major->report_path)
                            <a href="{{ asset('storage/'.$major->report_path) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-semibold text-violet-700 hover:underline">📄 PDF Report</a>
                        @endif
                        @if($major->github_url)
                            <a href="{{ $major->github_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-700 hover:underline">GitHub</a>
                        @endif
                        @if($major->demo_url)
                            <a href="{{ $major->demo_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-xs font-semibold text-violet-700 hover:underline">Live Demo</a>
                        @endif
                    </div>
                    @if($major->screenshots && count($major->screenshots))
                        <div class="mt-3 grid grid-cols-3 gap-2">
                            @foreach(array_slice($major->screenshots, 0, 3) as $ss)
                                <img src="{{ asset('storage/'.$ss) }}" class="rounded-lg object-cover h-16 w-full" alt="Screenshot"/>
                            @endforeach
                        </div>
                    @endif
                @else
                    <p class="text-sm text-slate-500 italic text-center py-6">No major project uploaded.</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ACHIEVEMENTS TAB --}}
<div x-show="tab==='achievements'" x-cloak class="space-y-4">
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h3 class="font-bold text-slate-900">Achievements & Awards</h3>
        </div>
        <div class="p-5">
            @if($alumnus->getAttributeValue('achievements'))
                <div class="text-sm text-slate-700 leading-relaxed mb-4">{!! nl2br(e($alumnus->getAttributeValue('achievements'))) !!}</div>
            @endif
            @forelse($alumnus->achievementRecords as $achievement)
            <div class="flex gap-4 {{ !$loop->last ? 'mb-4 pb-4 border-b border-slate-50' : '' }}">
                <div class="flex-shrink-0 mt-1">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-900">{{ $achievement->title }}</p>
                    @if($achievement->year)<p class="text-xs text-slate-500">{{ $achievement->year }}</p>@endif
                    @if($achievement->description)<p class="text-xs text-slate-600 mt-1">{{ $achievement->description }}</p>@endif
                    @if($achievement->certificate_path)
                        <a href="{{ asset('storage/'.$achievement->certificate_path) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-semibold text-[#8B0000] hover:underline mt-1">View Certificate</a>
                    @endif
                </div>
            </div>
            @empty
                @if(!$alumnus->getAttributeValue('achievements'))
                <p class="text-sm text-slate-500 italic text-center py-4">No achievements recorded.</p>
                @endif
            @endforelse
        </div>
    </div>
</div>

{{-- ACCOUNT TAB --}}
<div x-show="tab==='account'" x-cloak class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h3 class="font-bold text-slate-900">Account Management</h3>
        </div>
        <div class="p-5 space-y-4">
            <div class="flex items-center justify-between rounded-xl bg-slate-50 p-4">
                <div>
                    <p class="text-sm font-bold text-slate-900">Account Status</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $isActive ? 'Active and can log in.' : 'Account disabled.' }}</p>
                </div>
                @if($isActive)
                    <span class="rounded-lg bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Active</span>
                @else
                    <span class="rounded-lg bg-red-50 px-3 py-1 text-xs font-bold text-red-700">Disabled</span>
                @endif
            </div>
            <div class="flex items-center justify-between rounded-xl bg-slate-50 p-4">
                <div>
                    <p class="text-sm font-bold text-slate-900">Profile Visibility</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $alumnus->visibility === 'public' ? 'Visible in public directory.' : 'Hidden from public.' }}</p>
                </div>
                <span class="rounded-lg {{ $alumnus->visibility === 'public' ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-600' }} px-3 py-1 text-xs font-bold">{{ ucfirst($alumnus->visibility) }}</span>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.alumni.edit', $alumnus) }}" class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2 text-sm font-bold text-white hover:bg-[#7a0000] transition">Edit Account</a>
                <form method="POST" action="{{ route('admin.alumni.destroy', $alumnus) }}" onsubmit="return confirm('Delete this alumni record permanently?')">
                    @csrf @method('DELETE')
                    <button class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-white px-4 py-2 text-sm font-bold text-red-600 hover:bg-red-50 transition">Delete Account</button>
                </form>
            </div>
        </div>
    </div>
</div>

</div>
@endsection