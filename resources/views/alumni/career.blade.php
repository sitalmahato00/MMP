@extends('layouts.app')
@section('title', 'Career')

@section('content')
<x-page-header title="Career" subtitle="Manage your employment status and work history."/>

@if(session('success'))
<div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
@endif

<div class="max-w-4xl space-y-6">
    {{-- Current Status --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h3 class="font-bold text-slate-900">Current Status</h3>
        </div>
        <div class="p-5 space-y-2">
            @php
                $statusMap = [
                    'employed'   => ['label' => 'Employed',   'cls' => 'bg-emerald-50 text-emerald-700'],
                    'studying'   => ['label' => 'Studying',   'cls' => 'bg-blue-50 text-blue-700'],
                    'freelancing'=> ['label' => 'Freelancing', 'cls' => 'bg-violet-50 text-violet-700'],
                    'unemployed' => ['label' => 'Unemployed', 'cls' => 'bg-amber-50 text-amber-700'],
                    'unknown'    => ['label' => 'Unknown',    'cls' => 'bg-slate-100 text-slate-600'],
                ];
                $st = $statusMap[$alumnus?->employment_status] ?? $statusMap['unknown'];
            @endphp
            <div class="flex flex-wrap items-center gap-4">
                <span class="rounded-lg px-3 py-1 text-xs font-bold {{ $st['cls'] }}">{{ $st['label'] }}</span>
                @if($alumnus?->current_job)
                    <span class="text-sm text-slate-700">{{ $alumnus->current_job }}@if($alumnus->company_name) at <strong>{{ $alumnus->company_name }}</strong>@endif</span>
                @endif
                @if($alumnus?->work_location)
                    <span class="text-xs text-slate-500">· {{ $alumnus->work_location }}</span>
                @endif
            </div>
            <p class="text-xs text-slate-400 mt-1">Add a new employment record below to update your current status automatically.</p>
        </div>
    </div>

    {{-- Employment History --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4 flex items-center justify-between">
            <h3 class="font-bold text-slate-900">Employment History</h3>
        </div>
        <div class="p-5">
            @forelse($alumnus?->employmentHistory ?? [] as $job)
            <div class="flex items-start gap-4 {{ !$loop->last ? 'mb-4 pb-4 border-b border-slate-50' : '' }}">
                <div class="flex-shrink-0 mt-1">
                    <div class="h-3 w-3 rounded-full {{ $job->is_current ? 'bg-emerald-500' : 'bg-slate-300' }}"></div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-900">{{ $job->job_title }}</p>
                    <p class="text-xs text-slate-600">{{ $job->company_name }}@if($job->location) · {{ $job->location }}@endif</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $job->start_date ? bsDate($job->start_date, 'M Y') : '—' }} — {{ $job->is_current ? 'Present' : ($job->end_date ? bsDate($job->end_date, 'M Y') : '—') }}</p>
                    @if($job->description)<p class="text-xs text-slate-600 mt-1">{{ $job->description }}</p>@endif
                </div>
                <form method="POST" action="{{ route('alumni.career.destroy-employment', $job) }}" onsubmit="return confirm('Remove this record?')">
                    @csrf @method('DELETE')
                    <button class="text-xs text-red-500 hover:text-red-700 font-semibold">Remove</button>
                </form>
            </div>
            @empty
            <p class="text-sm text-slate-500 italic text-center py-4">No employment history added yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Add Employment Form --}}
    <div x-data="{ open: false }" class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <button @click="open = !open" class="flex w-full items-center justify-between border-b border-slate-100 px-5 py-4 text-left">
            <h3 class="font-bold text-slate-900">Add Employment Record</h3>
            <svg :class="open && 'rotate-180'" class="w-4 h-4 text-slate-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div x-show="open" x-cloak class="p-5">
            <form method="POST" action="{{ route('alumni.career.store-employment') }}" class="space-y-4">
                @csrf
                <x-form-row>
                    <x-form-field label="Job Title" name="job_title" :required="true">
                        <x-input name="job_title" :value="old('job_title')" :required="true" placeholder="e.g. Software Developer"/>
                    </x-form-field>
                    <x-form-field label="Company" name="company_name" :required="true">
                        <x-input name="company_name" :value="old('company_name')" :required="true" placeholder="Company name"/>
                    </x-form-field>
                    <x-form-field label="Location" name="location">
                        <x-input name="location" :value="old('location')" placeholder="e.g. Kathmandu"/>
                    </x-form-field>
                    <x-form-field label="Start Date (BS)" name="start_date">
                        <x-bs-date-picker name="start_date" :value="old('start_date')" placeholder="YYYY-MM-DD"/>
                    </x-form-field>
                    <x-form-field label="End Date (BS)" name="end_date">
                        <x-bs-date-picker name="end_date" :value="old('end_date')" placeholder="YYYY-MM-DD"/>
                    </x-form-field>
                    <x-form-field label="Currently Working" name="is_current">
                        <label class="inline-flex items-center gap-3 cursor-pointer">
                            <input type="hidden" name="is_current" value="0"/>
                            <input type="checkbox" name="is_current" value="1" @checked(old('is_current'))
                                   class="rounded border-slate-300 text-[#8B0000] focus:ring-red-200"/>
                            <span class="text-sm text-slate-700">I currently work here</span>
                        </label>
                    </x-form-field>
                    <x-form-field label="Description" name="description" span="full">
                        <x-textarea name="description" rows="2" placeholder="Brief description of your role">{{ old('description') }}</x-textarea>
                    </x-form-field>
                </x-form-row>
                <x-btn type="submit">Add Employment</x-btn>
            </form>
        </div>
    </div>
</div>
@endsection