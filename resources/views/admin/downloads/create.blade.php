@extends('layouts.app')
@section('title', 'Add Download')

@section('content')
<x-page-header title="Add Download" subtitle="Upload a resource for public download."
               back="{{ route('admin.downloads.index') }}"/>

<form method="POST" action="{{ route('admin.downloads.store') }}" enctype="multipart/form-data" class="max-w-2xl space-y-6">
    @csrf
    <x-form-section title="Resource Details">
        <x-form-row>
            <x-form-field label="Title" name="title" :required="true" span="full">
                <x-input name="title" :required="true" placeholder="e.g. Admission Form 2081"/>
            </x-form-field>
            <x-form-field label="Category" name="category">
                <x-input name="category" placeholder="e.g. Forms, Syllabus, Reports"/>
            </x-form-field>
            <x-form-field label="Visibility" name="is_public">
                <label class="flex items-center gap-3 cursor-pointer mt-2">
                    <input type="checkbox" name="is_public" value="1" checked class="w-4 h-4 accent-[#8B0000] rounded">
                    <span class="text-sm text-gray-600">Publicly available</span>
                </label>
            </x-form-field>
            <x-form-field label="File" name="file" :required="true" span="full">
                <x-file-input name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip" label="Upload document (PDF, Word, Excel, ZIP)"/>
            </x-form-field>
            <x-form-field label="Description" name="description" span="full">
                <x-textarea name="description" rows="3" placeholder="Optional notes..."/>
            </x-form-field>
        </x-form-row>
    </x-form-section>
    <div class="flex items-center gap-3">
        <x-btn type="submit">Upload Download</x-btn>
        <x-btn href="{{ route('admin.downloads.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
