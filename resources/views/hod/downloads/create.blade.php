@extends('layouts.app')
@section('title', 'Upload Resource')

@section('content')
<x-page-header title="Upload Resource" subtitle="Upload a resource for your department." back="{{ route('hod.downloads.index') }}"/>

<form method="POST" action="{{ route('hod.downloads.store') }}" enctype="multipart/form-data" class="max-w-2xl space-y-6">
    @csrf
    <x-form-section title="Resource Details">
        <x-form-row>
            <x-form-field label="Title" name="title" :required="true" span="full">
                <x-input name="title" :required="true" placeholder="e.g. Syllabus 2083"/>
            </x-form-field>
            <x-form-field label="Category" name="category" :required="true">
                <select name="category" required class="form-select">
                    <option value="">Select Category</option>
                    <option value="Forms & Downloads">Forms & Downloads</option>
                    <option value="Syllabus">Syllabus</option>
                    <option value="Notes">Notes</option>
                    <option value="Question Bank">Question Bank</option>
                    <option value="Reports & Publications">Reports & Publications</option>
                </select>
            </x-form-field>
            <x-form-field label="Make Public" name="is_public" :required="true">
                <select name="is_public" required class="form-select">
                    <option value="0">Private (Department Only)</option>
                    <option value="1">Public (Visible to Everyone)</option>
                </select>
            </x-form-field>
            <x-form-field label="File" name="file" :required="true" span="full">
                <x-file-input name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip,.jpg,.jpeg,.png" label="Upload document or image (PDF, Word, Excel, ZIP, Images)"/>
            </x-form-field>
            <x-form-field label="Description" name="description" span="full">
                <x-textarea name="description" rows="3" placeholder="Optional notes..."/>
            </x-form-field>
        </x-form-row>
    </x-form-section>
    <div class="flex items-center gap-3">
        <x-btn type="submit">Upload Resource</x-btn>
        <x-btn href="{{ route('hod.downloads.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
