@extends('layouts.app')
@section('title', ucfirst($type) . ' Project')

@section('content')
<x-page-header title="{{ ucfirst($type) }} Project"
               subtitle="Upload or update your {{ $type }} project details."
               back="{{ route('alumni.projects.index') }}"/>

@if(session('success'))
<div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('alumni.projects.update', $type) }}" enctype="multipart/form-data"
      class="max-w-4xl space-y-6">
    @csrf @method('PUT')

    <x-form-section title="Project Details" subtitle="Core information about your {{ $type }} project.">
        <x-form-row>
            <x-form-field label="Project Title" name="title" :required="true" span="full">
                <x-input name="title" :value="old('title', $project?->title)" :required="true" placeholder="e.g. College Management System"/>
            </x-form-field>
            <x-form-field label="Description" name="description" span="full">
                <x-textarea name="description" rows="4" placeholder="Describe your project, its objectives, and outcomes…">{{ old('description', $project?->description) }}</x-textarea>
            </x-form-field>
            <x-form-field label="Supervisor" name="supervisor">
                <x-input name="supervisor" :value="old('supervisor', $project?->supervisor)" placeholder="e.g. Prof. Ram Sharma"/>
            </x-form-field>
            <x-form-field label="Year" name="year">
                <x-input name="year" :value="old('year', $project?->year)" placeholder="e.g. 2082"/>
            </x-form-field>
            <x-form-field label="Technologies" name="technologies" span="full">
                <x-input name="technologies" :value="old('technologies', $project?->technologies ? implode(', ', $project->technologies) : '')" placeholder="e.g. Laravel, React, MySQL (comma-separated)"/>
            </x-form-field>
            <x-form-field label="Team Members" name="team_members" span="full">
                <x-input name="team_members" :value="old('team_members', $project?->team_members ? implode(', ', $project->team_members) : '')" placeholder="e.g. Hari Bahadur, Sita Kumari (comma-separated)"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Links" subtitle="Source code and live demo links.">
        <x-form-row>
            <x-form-field label="GitHub URL" name="github_url">
                <x-input name="github_url" :value="old('github_url', $project?->github_url)" placeholder="https://github.com/..."/>
            </x-form-field>
            <x-form-field label="Live Demo URL" name="demo_url">
                <x-input name="demo_url" :value="old('demo_url', $project?->demo_url)" placeholder="https://..."/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Files" subtitle="Upload report PDF, cover image, and screenshots.">
        <x-form-row>
            <x-form-field label="Report (PDF)" name="report" span="full">
                @if($project?->report_path)
                    <div class="mb-2">
                        <a href="{{ asset('storage/'.$project->report_path) }}" target="_blank" class="text-xs font-semibold text-[#8B0000] hover:underline">📄 View current report</a>
                    </div>
                @endif
                <x-file-input name="report" accept=".pdf" label="Upload PDF report (max 10 MB)"/>
            </x-form-field>
            <x-form-field label="Cover Image" name="cover_image" span="full">
                @if($project?->cover_image)
                    <div class="mb-2">
                        <img src="{{ asset('storage/'.$project->cover_image) }}" class="h-20 rounded-lg object-cover"/>
                    </div>
                @endif
                <x-file-input name="cover_image" accept="image/*" label="Upload cover image (max 2 MB)"/>
            </x-form-field>
            <x-form-field label="Screenshots" name="screenshots" span="full">
                @if($project?->screenshots && count($project->screenshots))
                    <div class="mb-2 flex flex-wrap gap-2">
                        @foreach($project->screenshots as $ss)
                            <img src="{{ asset('storage/'.$ss) }}" class="h-16 rounded-lg object-cover"/>
                        @endforeach
                    </div>
                    <p class="text-xs text-slate-400 mb-1">Uploading new screenshots will replace existing ones.</p>
                @endif
                <x-file-input name="screenshots[]" accept="image/*" label="Upload screenshots (max 2 MB each)" multiple/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Visibility" subtitle="Control whether this project is publicly visible.">
        <x-form-row>
            <x-form-field label="Visible" name="is_visible">
                <label class="inline-flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="is_visible" value="0"/>
                    <input type="checkbox" name="is_visible" value="1" @checked(old('is_visible', $project?->is_visible ?? true))
                           class="rounded border-slate-300 text-[#8B0000] focus:ring-red-200"/>
                    <span class="text-sm text-slate-700">Show in public directory</span>
                </label>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <div class="flex items-center gap-3">
        <x-btn type="submit">Save Project</x-btn>
        <a href="{{ route('alumni.projects.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
    </div>
</form>
@endsection