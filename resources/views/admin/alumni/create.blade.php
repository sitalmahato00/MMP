@extends('layouts.app')
@section('title', 'Add Alumni')

@section('content')
<x-form-layout title="Add Alumni" subtitle="Manually create an alumni record with career and academic details." back="{{ route('admin.alumni.index') }}">
    <x-slot name="breadcrumb">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.alumni.index') }}" class="hover:text-slate-900">Alumni</a>
            <span>/</span>
            <span class="font-semibold text-slate-900">Add Alumni</span>
        </nav>
    </x-slot>

    <x-slot name="sidebar">
        <x-form-sidebar />
    </x-slot>

    <form id="alumni-create-form" method="POST" action="{{ route('admin.alumni.store') }}" enctype="multipart/form-data"
          class="space-y-6">
    @csrf

    {{-- 1. PERSONAL INFORMATION --}}
    <x-form-section title="Personal Information" subtitle="Alumni identity and login credentials.">
        <x-form-row>
            <x-form-field label="Full Name" name="name" :required="true">
                <x-input name="name" :value="old('name')" :required="true" placeholder="Full legal name"/>
            </x-form-field>
            <x-form-field label="Email Address" name="email" :required="true">
                <x-input name="email" type="email" :value="old('email')" :required="true" placeholder="alumni@example.com"/>
            </x-form-field>
            <x-form-field label="Phone Number" name="phone">
                <x-input name="phone" :value="old('phone')" placeholder="98XXXXXXXX"/>
            </x-form-field>
            <p class="mt-4 text-sm text-slate-600">A password reset link will be sent to the alumni after account creation.</p>
            <x-form-field label="Address" name="address" span="full">
                <x-textarea name="address" rows="2" placeholder="District, Province, Country">{{ old('address') }}</x-textarea>
            </x-form-field>
            <x-form-field label="Profile Photo" name="avatar" span="full">
                <x-file-input name="avatar" accept="image/*" label="Upload photo (max 2 MB)"/>
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
                        <option value="{{ $d->id }}" @selected(old('department_id') == $d->id)>{{ $d->name }}</option>
                    @endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Program" name="program_id" :required="true">
                <x-select name="program_id" :required="true">
                    <option value="">Select Program</option>
                    @foreach($programs as $p)
                        <option value="{{ $p->id }}" @selected(old('program_id') == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Roll Number" name="roll_number">
                <x-input name="roll_number" :value="old('roll_number')" placeholder="e.g. 078-DCSIT-001"/>
            </x-form-field>
            <x-form-field label="Admission Year" name="admission_year">
                <x-input name="admission_year" :value="old('admission_year')" placeholder="e.g. 2078"/>
            </x-form-field>
            <x-form-field label="Graduation Year" name="graduation_year" :required="true">
                <x-input name="graduation_year" :value="old('graduation_year')" :required="true" placeholder="e.g. 2082"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- 3. CAREER INFORMATION --}}
    <x-form-section title="Career Information" subtitle="Current employment and professional details.">
        <x-form-row>
            <x-form-field label="Current Job Title" name="current_job">
                <x-input name="current_job" :value="old('current_job')" placeholder="e.g. Software Engineer"/>
            </x-form-field>
            <x-form-field label="Company Name" name="company_name">
                <x-input name="company_name" :value="old('company_name')" placeholder="e.g. Tech Corp"/>
            </x-form-field>
            <x-form-field label="Work Location" name="work_location">
                <x-input name="work_location" :value="old('work_location')" placeholder="e.g. Kathmandu, Nepal"/>
            </x-form-field>
            <x-form-field label="Employment Status" name="employment_status">
                <x-select name="employment_status">
                    <option value="unknown" @selected(old('employment_status', 'unknown') === 'unknown')>Unknown</option>
                    <option value="employed" @selected(old('employment_status') === 'employed')>Employed</option>
                    <option value="studying" @selected(old('employment_status') === 'studying')>Studying</option>
                    <option value="freelancing" @selected(old('employment_status') === 'freelancing')>Freelancing</option>
                    <option value="unemployed" @selected(old('employment_status') === 'unemployed')>Unemployed</option>
                </x-select>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- 4. BIO & LINKS --}}
    <x-form-section title="Bio & Social Links" subtitle="Biography and online presence.">
        <x-form-row>
            <x-form-field label="Bio" name="bio" span="full">
                <x-textarea name="bio" rows="3" placeholder="Short biography…">{{ old('bio') }}</x-textarea>
            </x-form-field>
            <x-form-field label="LinkedIn URL" name="linkedin_url">
                <x-input name="linkedin_url" :value="old('linkedin_url')" placeholder="https://linkedin.com/in/..."/>
            </x-form-field>
            <x-form-field label="GitHub URL" name="github_url">
                <x-input name="github_url" :value="old('github_url')" placeholder="https://github.com/..."/>
            </x-form-field>
            <x-form-field label="Portfolio URL" name="portfolio_url">
                <x-input name="portfolio_url" :value="old('portfolio_url')" placeholder="https://..."/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- 5. ACHIEVEMENTS --}}
    <x-form-section title="Achievements" subtitle="Notable achievements (text).">
        <x-form-row>
            <x-form-field label="Achievements" name="achievements" span="full">
                <x-textarea name="achievements" rows="3" placeholder="Awards, recognitions, notable work…">{{ old('achievements') }}</x-textarea>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- 6. OPTIONS --}}
    <x-form-section title="Options" subtitle="Visibility and featuring.">
        <x-form-row>
            <x-form-field label="Featured" name="is_featured">
                <label class="inline-flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="is_featured" value="0"/>
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured'))
                           class="rounded border-slate-300 text-[#8B0000] focus:ring-red-200"/>
                    <span class="text-sm text-slate-700">Show as featured alumni</span>
                </label>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- SUBMIT --}}
    <x-slot name="footer">
        <div class="flex flex-wrap items-center gap-3">
            <x-btn type="submit" form="alumni-create-form">Create Alumni Record</x-btn>
            <a href="{{ route('admin.alumni.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
        </div>
    </x-slot>
    </form>
</x-form-layout>
@endsection