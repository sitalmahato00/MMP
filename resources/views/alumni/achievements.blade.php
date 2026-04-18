@extends('layouts.app')
@section('title', 'Achievements')

@section('content')
<x-page-header title="Achievements" subtitle="Add awards, certifications, and notable accomplishments."/>

@if(session('success'))
<div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
@endif

<div class="max-w-4xl space-y-6">
    {{-- Achievement List --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h3 class="font-bold text-slate-900">My Achievements</h3>
        </div>
        <div class="p-5">
            @forelse($achievements as $achievement)
            <div class="flex items-start gap-4 {{ !$loop->last ? 'mb-4 pb-4 border-b border-slate-50' : '' }}">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-amber-50">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-900">{{ $achievement->title }}</p>
                    @if($achievement->year)<p class="text-xs text-slate-500">{{ $achievement->year }}</p>@endif
                    @if($achievement->description)<p class="text-xs text-slate-600 mt-1">{{ $achievement->description }}</p>@endif
                    @if($achievement->certificate_path)
                        <a href="{{ asset('storage/'.$achievement->certificate_path) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-semibold text-[#8B0000] hover:underline mt-1">View Certificate</a>
                    @endif
                </div>
                <form method="POST" action="{{ route('alumni.achievements.destroy', $achievement) }}" onsubmit="return confirm('Remove this achievement?')">
                    @csrf @method('DELETE')
                    <button class="text-xs text-red-500 hover:text-red-700 font-semibold">Remove</button>
                </form>
            </div>
            @empty
            <div class="text-center py-8">
                <svg class="mx-auto w-10 h-10 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                <p class="text-sm text-slate-500 font-semibold">No achievements yet</p>
                <p class="text-xs text-slate-400 mt-1">Add your awards and certificates below.</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Add Achievement --}}
    <div x-data="{ open: false }" class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <button @click="open = !open" class="flex w-full items-center justify-between border-b border-slate-100 px-5 py-4 text-left">
            <h3 class="font-bold text-slate-900">Add Achievement</h3>
            <svg :class="open && 'rotate-180'" class="w-4 h-4 text-slate-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div x-show="open" x-cloak class="p-5">
            <form method="POST" action="{{ route('alumni.achievements.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <x-form-row>
                    <x-form-field label="Title" name="title" :required="true">
                        <x-input name="title" :value="old('title')" :required="true" placeholder="e.g. Dean's List 2082"/>
                    </x-form-field>
                    <x-form-field label="Year" name="year">
                        <x-input name="year" :value="old('year')" placeholder="e.g. 2082"/>
                    </x-form-field>
                    <x-form-field label="Description" name="description" span="full">
                        <x-textarea name="description" rows="2" placeholder="Brief description…">{{ old('description') }}</x-textarea>
                    </x-form-field>
                    <x-form-field label="Certificate" name="certificate" span="full">
                        <x-file-input name="certificate" accept=".pdf,image/*" label="Upload certificate (PDF or image, max 5 MB)"/>
                    </x-form-field>
                </x-form-row>
                <x-btn type="submit">Add Achievement</x-btn>
            </form>
        </div>
    </div>
</div>
@endsection