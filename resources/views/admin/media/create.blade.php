@extends('layouts.app')
@section('title', 'Upload Media')

@section('content')
<x-form-layout title="Upload Media" subtitle="Add images or documents to the system." back="{{ route('admin.media.index') }}">
    <x-slot name="breadcrumb">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.media.index') }}" class="hover:text-slate-900">Media</a>
            <span>/</span>
            <span class="font-semibold text-slate-900">Upload Media</span>
        </nav>
    </x-slot>

    <x-slot name="sidebar">
        <x-form-sidebar />
    </x-slot>

<form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="max-w-2xl space-y-6">
    @csrf
    <x-form-section title="File Upload">
        <x-form-row :cols="1">
            <x-form-field label="Files" name="files" :required="true">
                <div class="space-y-2">
                    <label for="files"
                           class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed border-gray-200 rounded-xl bg-gray-50 hover:bg-gray-100 hover:border-[#8B0000]/40 cursor-pointer transition-all duration-200 group">
                        <div class="flex flex-col items-center justify-center gap-2 text-center px-4">
                            <svg class="w-8 h-8 text-gray-300 group-hover:text-[#8B0000]/60 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-xs text-gray-400 group-hover:text-gray-600 transition-colors">Drag & drop or click to select files</p>
                            <p class="text-[10px] text-gray-300">JPG, PNG, PDF, DOC — up to 20 files, 10 MB each</p>
                        </div>
                        <input type="file" id="files" name="files[]" accept="image/*,.pdf,.doc,.docx" multiple class="hidden">
                    </label>
                    <div id="file-preview" class="grid grid-cols-2 gap-2 text-xs text-gray-500"></div>
                </div>
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

<script>
document.getElementById('files')?.addEventListener('change', function() {
    const preview = document.getElementById('file-preview');
    preview.innerHTML = '';
    Array.from(this.files).forEach(f => {
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded border border-gray-100';
        if (f.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(f);
            img.className = 'w-8 h-8 rounded object-cover flex-shrink-0';
            div.appendChild(img);
        } else {
            div.innerHTML = `<svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`;
        }
        const span = document.createElement('span');
        span.className = 'truncate';
        span.textContent = f.name;
        div.appendChild(span);
        const size = document.createElement('span');
        size.className = 'text-gray-300 ml-auto flex-shrink-0';
        size.textContent = (f.size/1024/1024).toFixed(1) + ' MB';
        div.appendChild(size);
        preview.appendChild(div);
    });
});
</script>
@endsection
