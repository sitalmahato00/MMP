@extends('layouts.app')
@section('title', 'Upload Media')

@section('content')
<x-page-header title="Upload Media" subtitle="Add images or documents to the system."
               back="{{ route('admin.media.index') }}"/>

<form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="max-w-2xl space-y-6">
    @csrf
    <x-form-section title="File Upload">
        <x-form-row :cols="1">
            <x-form-field label="File" name="file" :required="true">
                <x-file-input name="file" accept="image/*,.pdf,.doc,.docx" label="Upload image or document (JPG, PNG, PDF, DOC)"/>
            </x-form-field>
            <x-form-field label="Media Type" name="type" :required="true">
                <x-select name="type" :required="true">
                    <option value="gallery" {{ old('type') === 'gallery' ? 'selected' : '' }}>Gallery (Public Image)</option>
                    <option value="document" {{ old('type') === 'document' ? 'selected' : '' }}>Document (Internal)</option>
                </x-select>
            </x-form-field>
        </x-form-row>
    </x-form-section>
    <div class="flex items-center gap-3">
        <x-btn type="submit">Upload</x-btn>
        <x-btn href="{{ route('admin.media.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
