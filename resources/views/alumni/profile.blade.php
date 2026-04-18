@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
<x-page-header title="My Profile" subtitle="Update your personal details, bio, and social links."/>

@if(session('success'))
<div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('alumni.profile.update') }}" enctype="multipart/form-data" class="max-w-4xl space-y-6">
    @csrf @method('PUT')

    <x-form-section title="Personal Details" subtitle="Your identity and contact details.">
        <x-form-row>
            <x-form-field label="Full Name" name="name">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700">{{ auth()->user()->name }}</div>
                <p class="mt-1 text-xs text-slate-400">Contact admin to change your name.</p>
            </x-form-field>
            <x-form-field label="Email" name="email">
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700">{{ auth()->user()->email }}</div>
            </x-form-field>
            <x-form-field label="Phone" name="phone">
                <x-input name="phone" :value="old('phone', auth()->user()->phone)"/>
            </x-form-field>
            <x-form-field label="Address" name="address" span="full">
                <x-textarea name="address" rows="2">{{ old('address', auth()->user()->address) }}</x-textarea>
            </x-form-field>
            <x-form-field label="Profile Photo" name="avatar" span="full">
                @if(auth()->user()->avatar)
                    <div class="mb-2 flex items-center gap-3">
                        <img src="{{ asset('storage/'.auth()->user()->avatar) }}" class="h-12 w-12 rounded-xl object-cover ring-2 ring-slate-100"/>
                        <span class="text-xs text-slate-500">Current photo</span>
                    </div>
                @endif
                <x-file-input name="avatar" accept="image/*" label="Upload new photo (max 2 MB)"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Bio & Skills" subtitle="Tell the community about yourself.">
        <x-form-row>
            <x-form-field label="Bio" name="bio" span="full">
                <x-textarea name="bio" rows="4" placeholder="Write a short biography…">{{ old('bio', $alumnus?->bio) }}</x-textarea>
            </x-form-field>
            <x-form-field label="Skills" name="skills" span="full">
                <x-input name="skills" :value="old('skills', $alumnus?->skills ? implode(', ', $alumnus->skills) : '')" placeholder="e.g. PHP, Laravel, React, Python (comma-separated)"/>
                <p class="mt-1 text-xs text-slate-400">Separate skills with commas.</p>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Social Links" subtitle="Your online presence.">
        <x-form-row>
            <x-form-field label="LinkedIn URL" name="linkedin_url">
                <x-input name="linkedin_url" :value="old('linkedin_url', $alumnus?->linkedin_url)" placeholder="https://linkedin.com/in/..."/>
            </x-form-field>
            <x-form-field label="GitHub URL" name="github_url">
                <x-input name="github_url" :value="old('github_url', $alumnus?->github_url)" placeholder="https://github.com/..."/>
            </x-form-field>
            <x-form-field label="Portfolio URL" name="portfolio_url">
                <x-input name="portfolio_url" :value="old('portfolio_url', $alumnus?->portfolio_url)" placeholder="https://..."/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <div class="flex items-center gap-3">
        <x-btn type="submit">Save Profile</x-btn>
    </div>
</form>
@endsection