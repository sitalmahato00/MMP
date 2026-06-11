@extends('layouts.app')
@section('title', 'Add Download')

@section('content')
<x-form-layout title="Add Download" subtitle="Upload a public or private resource." back="{{ route('admin.downloads.index') }}">
    <x-slot name="breadcrumb">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.downloads.index') }}" class="hover:text-slate-900">Downloads</a>
            <span>/</span>
            <span class="font-semibold text-slate-900">Add Download</span>
        </nav>
    </x-slot>

    <x-slot name="sidebar">
        <x-form-sidebar />
    </x-slot>

    <form method="POST" action="{{ route('admin.downloads.store') }}" enctype="multipart/form-data" class="max-w-2xl space-y-6">
    @csrf
    <x-form-section title="Resource Details">
        <x-form-row>
            <x-form-field label="Title" name="title" :required="true" span="full">
                <x-input name="title" :required="true" placeholder="e.g. Admission Form 2081"/>
            </x-form-field>
            <x-form-field label="Category" name="category" hint="Suggested categories: Forms, Syllabus, Notes, Question Bank, Reports, Publications.">
                <x-input name="category" list="download-category-options" placeholder="e.g. Syllabus"/>
            </x-form-field>
            <x-form-field label="Visibility" name="is_public">
                <label class="flex items-center gap-3 cursor-pointer mt-2">
                    <input type="checkbox" name="is_public" value="1" checked class="w-4 h-4 accent-[#8B0000] rounded">
                    <span class="text-sm text-gray-600">Publicly available</span>
                </label>
                <p class="text-xs text-gray-400 mt-2">Unchecked files are stored privately and can only be accessed by authenticated admin users.</p>
            </x-form-field>
            <x-form-field label="File" name="file" :required="true" span="full">
                <x-file-input name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip" label="Upload document (PDF, Word, Excel, ZIP)"/>
            </x-form-field>
            <x-form-field label="Description" name="description" span="full">
                <x-textarea name="description" rows="3" placeholder="Optional notes..."/>
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
        <x-btn type="submit">Upload Download</x-btn>
        <x-btn href="{{ route('admin.downloads.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
</x-form-layout>
@endsection
