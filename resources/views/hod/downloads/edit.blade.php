@extends('layouts.app')
@section('title', 'Edit Resource')

@section('content')
<x-page-header title="Edit Resource" subtitle="Update resource details." back="{{ route('hod.downloads.index') }}"/>

<form method="POST" action="{{ route('hod.downloads.update', $download) }}" enctype="multipart/form-data" class="max-w-2xl space-y-6">
    @csrf
    @method('PUT')
    <x-form-section title="Resource Details">
        <x-form-row>
            <x-form-field label="Title" name="title" :required="true" span="full">
                <x-input name="title" :required="true" value="{{ old('title', $download->title) }}"/>
            </x-form-field>
            <x-form-field label="Category" name="category" :required="true">
                <select name="category" required class="form-select">
                    <option value="Forms & Downloads" @selected($download->category == 'Forms & Downloads')>Forms & Downloads</option>
                    <option value="Syllabus" @selected($download->category == 'Syllabus')>Syllabus</option>
                    <option value="Notes" @selected($download->category == 'Notes')>Notes</option>
                    <option value="Question Bank" @selected($download->category == 'Question Bank')>Question Bank</option>
                    <option value="Reports & Publications" @selected($download->category == 'Reports & Publications')>Reports & Publications</option>
                </select>
            </x-form-field>
            <x-form-field label="Make Public" name="is_public" :required="true">
                <select name="is_public" required class="form-select">
                    <option value="0" @selected(!$download->is_public)>Private (Department Only)</option>
                    <option value="1" @selected($download->is_public)>Public (Visible to Everyone)</option>
                </select>
            </x-form-field>
            <x-form-field label="File" name="file" span="full">
                <x-file-input name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip,.jpg,.jpeg,.png" label="Replace document or image (optional)"/>
                <p class="text-xs text-gray-400 mt-1">Leave blank to keep the current file.</p>
            </x-form-field>
            <x-form-field label="Description" name="description" span="full">
                <x-textarea name="description" rows="3">{{ old('description', $download->description) }}</x-textarea>
            </x-form-field>
        </x-form-row>
    </x-form-section>
    <div class="flex items-center gap-3">
        <x-btn type="submit">Update Resource</x-btn>
        <x-btn href="{{ route('hod.downloads.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
