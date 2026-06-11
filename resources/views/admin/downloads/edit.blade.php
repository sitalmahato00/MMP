@extends('layouts.app')
@section('title', 'Edit Download')

@section('content')
<x-form-layout title="Edit Download" subtitle="Edit file and visibility settings." back="{{ route('admin.downloads.index') }}">
    <x-slot name="breadcrumb">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.downloads.index') }}" class="hover:text-slate-900">Downloads</a>
            <span>/</span>
            <span class="font-semibold text-slate-900">Edit Download</span>
        </nav>
    </x-slot>

    <x-slot name="sidebar">
        <x-form-sidebar />
    </x-slot>

    <form method="POST" action="{{ route('admin.downloads.update', $download) }}" enctype="multipart/form-data" class="max-w-2xl space-y-6">
    @csrf @method('PUT')
    <x-form-section title="Resource Details">
        <x-form-row>
            <x-form-field label="Title" name="title" :required="true" span="full">
                <x-input name="title" :value="$download->title" :required="true"/>
            </x-form-field>
            <x-form-field label="Category" name="category" hint="Suggested categories: Forms, Syllabus, Notes, Question Bank, Reports, Publications.">
                <x-input name="category" :value="$download->category" list="download-category-options" placeholder="e.g. Notes"/>
            </x-form-field>
            <x-form-field label="Visibility" name="is_public">
                <label class="flex items-center gap-3 cursor-pointer mt-2">
                    <input type="checkbox" name="is_public" value="1" {{ $download->is_public ? 'checked' : '' }} class="w-4 h-4 accent-[#8B0000] rounded">
                    <span class="text-sm text-gray-600">Publicly available</span>
                </label>
                <p class="text-xs text-gray-400 mt-2">Private files are moved to protected storage and served only through authenticated admin access.</p>
            </x-form-field>
            <x-form-field label="Replace File" name="file" span="full">
                <x-file-input name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip" :current="$download->file_path"/>
            </x-form-field>
            <x-form-field label="Description" name="description" span="full">
                <x-textarea name="description" rows="3">{{ $download->description }}</x-textarea>
            </x-form-field>
        </x-form-row>
    </x-form-section>
    <datalist id="download-category-options">
        <option value="Forms"></option>
        <option value="Syllabus"></option>
        <option value="Notes"></option>
        <option value="Question Bank"></option>
        <option value="Reports"></option>
        <option value="Publications"></option>
        <option value="Admissions"></option>
        <option value="General"></option>
    </datalist>
    <div class="flex items-center gap-3">
        <x-btn type="submit">Save Changes</x-btn>
        <x-btn href="{{ route('admin.downloads.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
</x-form-layout>
@endsection
