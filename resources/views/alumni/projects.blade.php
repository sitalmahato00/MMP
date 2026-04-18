@extends('layouts.app')
@section('title', 'My Projects')

@section('content')
<x-page-header title="My Projects" subtitle="Showcase your minor and major final year projects."/>

@if(session('success'))
<div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
@endif

@php
    $minor = $alumnus?->projects?->firstWhere('type', 'minor');
    $major = $alumnus?->projects?->firstWhere('type', 'major');
@endphp

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 max-w-5xl">
    {{-- Minor Project --}}
    <div class="rounded-2xl border {{ $minor ? 'border-cyan-200' : 'border-dashed border-slate-300' }} bg-white shadow-sm overflow-hidden">
        <div class="border-b {{ $minor ? 'border-cyan-100 bg-cyan-50/50' : 'border-slate-100' }} px-5 py-4 flex items-center justify-between">
            <h3 class="font-bold {{ $minor ? 'text-cyan-900' : 'text-slate-900' }}">Minor Project</h3>
            <a href="{{ route('alumni.projects.edit', 'minor') }}" class="text-xs font-semibold text-[#8B0000] hover:underline">{{ $minor ? 'Edit' : 'Add' }} →</a>
        </div>
        <div class="p-5">
            @if($minor)
                <h4 class="text-base font-bold text-slate-900 mb-2">{{ $minor->title }}</h4>
                @if($minor->description)
                    <p class="text-sm text-slate-600 mb-3 line-clamp-4">{{ $minor->description }}</p>
                @endif
                @if($minor->supervisor)
                    <p class="text-xs text-slate-500 mb-2">Supervisor: <strong class="text-slate-700">{{ $minor->supervisor }}</strong></p>
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
                        <a href="{{ asset('storage/'.$minor->report_path) }}" target="_blank" class="text-xs font-semibold text-cyan-700 hover:underline">📄 Report</a>
                    @endif
                    @if($minor->github_url)
                        <a href="{{ $minor->github_url }}" target="_blank" rel="noopener" class="text-xs font-semibold text-slate-700 hover:underline">GitHub</a>
                    @endif
                    @if($minor->demo_url)
                        <a href="{{ $minor->demo_url }}" target="_blank" rel="noopener" class="text-xs font-semibold text-violet-700 hover:underline">Demo</a>
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
                <div class="text-center py-8">
                    <svg class="mx-auto w-10 h-10 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    <p class="text-sm text-slate-500 font-semibold">No minor project yet</p>
                    <p class="text-xs text-slate-400 mt-1">Upload your minor project to showcase it publicly.</p>
                    <a href="{{ route('alumni.projects.edit', 'minor') }}" class="mt-3 inline-block rounded-xl bg-cyan-600 px-4 py-2 text-xs font-bold text-white hover:bg-cyan-700 transition">Add Minor Project</a>
                </div>
            @endif
        </div>
    </div>

    {{-- Major Project --}}
    <div class="rounded-2xl border {{ $major ? 'border-violet-200' : 'border-dashed border-slate-300' }} bg-white shadow-sm overflow-hidden">
        <div class="border-b {{ $major ? 'border-violet-100 bg-violet-50/50' : 'border-slate-100' }} px-5 py-4 flex items-center justify-between">
            <h3 class="font-bold {{ $major ? 'text-violet-900' : 'text-slate-900' }}">Major Project</h3>
            <a href="{{ route('alumni.projects.edit', 'major') }}" class="text-xs font-semibold text-[#8B0000] hover:underline">{{ $major ? 'Edit' : 'Add' }} →</a>
        </div>
        <div class="p-5">
            @if($major)
                <h4 class="text-base font-bold text-slate-900 mb-2">{{ $major->title }}</h4>
                @if($major->description)
                    <p class="text-sm text-slate-600 mb-3 line-clamp-4">{{ $major->description }}</p>
                @endif
                @if($major->supervisor)
                    <p class="text-xs text-slate-500 mb-2">Supervisor: <strong class="text-slate-700">{{ $major->supervisor }}</strong></p>
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
                        <a href="{{ asset('storage/'.$major->report_path) }}" target="_blank" class="text-xs font-semibold text-violet-700 hover:underline">📄 Report</a>
                    @endif
                    @if($major->github_url)
                        <a href="{{ $major->github_url }}" target="_blank" rel="noopener" class="text-xs font-semibold text-slate-700 hover:underline">GitHub</a>
                    @endif
                    @if($major->demo_url)
                        <a href="{{ $major->demo_url }}" target="_blank" rel="noopener" class="text-xs font-semibold text-violet-700 hover:underline">Demo</a>
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
                <div class="text-center py-8">
                    <svg class="mx-auto w-10 h-10 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    <p class="text-sm text-slate-500 font-semibold">No major project yet</p>
                    <p class="text-xs text-slate-400 mt-1">Upload your major project to showcase it publicly.</p>
                    <a href="{{ route('alumni.projects.edit', 'major') }}" class="mt-3 inline-block rounded-xl bg-violet-600 px-4 py-2 text-xs font-bold text-white hover:bg-violet-700 transition">Add Major Project</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection