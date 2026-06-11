@extends('layouts.app')
@section('title', 'Edit Alumni')

@section('content')
<x-form-layout title="Edit Alumni" subtitle="Update alumni record and career information." back="{{ route('admin.alumni.show', $alumnus) }}">
    <x-slot name="breadcrumb">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.alumni.index') }}" class="hover:text-slate-900">Alumni</a>
            <span>/</span>
            <span class="font-semibold text-slate-900">Edit Alumni</span>
        </nav>
    </x-slot>

    <x-slot name="sidebar">
        <x-form-sidebar />
    </x-slot>

    <form id="alumni-edit-form" method="POST" action="{{ route('admin.alumni.update', $alumnus) }}" enctype="multipart/form-data"
          class="space-y-6">
    @csrf @method('PUT')

    {{-- 1. PERSONAL INFORMATION --}}
    <x-form-section title="Personal Information" subtitle="Alumni identity and contact details.">
        <x-form-row>
            <x-form-field label="Full Name" name="name" :required="true">
                <x-input name="name" :value="old('name', $alumnus->user?->name)" :required="true"/>
            </x-form-field>
            <x-form-field label="Email Address" name="email" :required="true">
                <x-input name="email" type="email" :value="old('email', $alumnus->user?->email)" :required="true"/>
            </x-form-field>
            <x-form-field label="Phone Number" name="phone">
                <x-input name="phone" :value="old('phone', $alumnus->user?->phone)"/>
            </x-form-field>
            <x-form-field label="Address" name="address" span="full">
                <x-textarea name="address" rows="2">{{ old('address', $alumnus->user?->address) }}</x-textarea>
            </x-form-field>
            <x-form-field label="Profile Photo" name="avatar" span="full">
                @if($alumnus->user?->avatar)
                    <div class="mb-2 flex items-center gap-3">
                        <img src="{{ asset('storage/'.$alumnus->user->avatar) }}" class="h-12 w-12 rounded-xl object-cover ring-2 ring-slate-100"/>
                        <span class="text-xs text-slate-500">Current photo</span>
                    </div>
                @endif
                <x-file-input name="avatar" accept="image/*" label="Upload new photo (max 2 MB)"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- 2. ACADEMIC INFORMATION --}}
    <x-form-section title="Academic Information" subtitle="Department, program, and graduation details.">
        <x-form-row>
            <x-form-field label="Department" name="department_id" :required="true">
                <x-select name="department_id" :required="true">
                    <option value="">Select Department</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" @selected(old('department_id', $alumnus->department_id) == $d->id)>{{ $d->name }}</option>
                    @endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Program" name="program_id" :required="true">
                <x-select name="program_id" :required="true">
                    <option value="">Select Program</option>
                    @foreach($programs as $p)
                        <option value="{{ $p->id }}" @selected(old('program_id', $alumnus->program_id) == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Roll Number" name="roll_number">
                <x-input name="roll_number" :value="old('roll_number', $alumnus->roll_number)"/>
            </x-form-field>
            <x-form-field label="Admission Year" name="admission_year">
                <x-input name="admission_year" :value="old('admission_year', $alumnus->admission_year)"/>
            </x-form-field>
            <x-form-field label="Graduation Year" name="graduation_year" :required="true">
                <x-input name="graduation_year" :value="old('graduation_year', $alumnus->graduation_year)" :required="true"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- 3. CAREER INFORMATION --}}
    <x-form-section title="Career Information" subtitle="Current employment and professional details.">
        <x-form-row>
            <x-form-field label="Current Job Title" name="current_job">
                <x-input name="current_job" :value="old('current_job', $alumnus->current_job)"/>
            </x-form-field>
            <x-form-field label="Company Name" name="company_name">
                <x-input name="company_name" :value="old('company_name', $alumnus->company_name)"/>
            </x-form-field>
            <x-form-field label="Work Location" name="work_location">
                <x-input name="work_location" :value="old('work_location', $alumnus->work_location)"/>
            </x-form-field>
            <x-form-field label="Employment Status" name="employment_status">
                <x-select name="employment_status">
                    @foreach(['unknown'=>'Unknown','employed'=>'Employed','studying'=>'Studying','freelancing'=>'Freelancing','unemployed'=>'Unemployed'] as $val => $lbl)
                        <option value="{{ $val }}" @selected(old('employment_status', $alumnus->employment_status) === $val)>{{ $lbl }}</option>
                    @endforeach
                </x-select>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- 4. BIO & LINKS --}}
    <x-form-section title="Bio & Social Links" subtitle="Biography and online presence.">
        <x-form-row>
            <x-form-field label="Bio" name="bio" span="full">
                <x-textarea name="bio" rows="3">{{ old('bio', $alumnus->bio) }}</x-textarea>
            </x-form-field>
            <x-form-field label="LinkedIn URL" name="linkedin_url">
                <x-input name="linkedin_url" :value="old('linkedin_url', $alumnus->linkedin_url)"/>
            </x-form-field>
            <x-form-field label="GitHub URL" name="github_url">
                <x-input name="github_url" :value="old('github_url', $alumnus->github_url)"/>
            </x-form-field>
            <x-form-field label="Portfolio URL" name="portfolio_url">
                <x-input name="portfolio_url" :value="old('portfolio_url', $alumnus->portfolio_url)"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- 5. ACHIEVEMENTS --}}
    <x-form-section title="Achievements" subtitle="Notable achievements (text).">
        <x-form-row>
            <x-form-field label="Achievements" name="achievements" span="full">
                <x-textarea name="achievements" rows="3">{{ old('achievements', $alumnus->achievements) }}</x-textarea>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- 6. ACCOUNT & OPTIONS --}}
    <x-form-section title="Account & Options" subtitle="Status, visibility, and featuring.">
        <x-form-row>
            <x-form-field label="Active" name="is_active">
                <label class="inline-flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="is_active" value="0"/>
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $alumnus->user?->is_active))
                           class="rounded border-slate-300 text-[#8B0000] focus:ring-red-200"/>
                    <span class="text-sm text-slate-700">Account is active</span>
                </label>
            </x-form-field>
            <x-form-field label="Featured" name="is_featured">
                <label class="inline-flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="is_featured" value="0"/>
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $alumnus->is_featured))
                           class="rounded border-slate-300 text-[#8B0000] focus:ring-red-200"/>
                    <span class="text-sm text-slate-700">Show as featured alumni</span>
                </label>
            </x-form-field>
            <x-form-field label="Profile Visibility" name="visibility">
                <x-select name="visibility">
                    <option value="public" @selected(old('visibility', $alumnus->visibility) === 'public')>Public</option>
                    <option value="private" @selected(old('visibility', $alumnus->visibility) === 'private')>Private</option>
                </x-select>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- SUBMIT --}}
    <x-slot name="footer">
        <div class="flex flex-wrap items-center gap-3">
            <x-btn type="submit" form="alumni-edit-form">Update Alumni</x-btn>
            <a href="{{ route('admin.alumni.show', $alumnus) }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
        </div>
    </x-slot>
    </form>
</x-form-layout>
@endsection