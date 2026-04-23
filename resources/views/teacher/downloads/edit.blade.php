@extends('layouts.app')

@section('title', 'Edit Resource')

@section('content')
<div class="space-y-6">
    {{-- ═══════════════════════════════════════════════════════════
         1. PAGE HEADER
    ═══════════════════════════════════════════════════════════ --}}
    <x-page-header 
        title="Edit Resource" 
        subtitle="Update resource details and file"
        icon="edit"
    >
        <x-slot:breadcrumb>
            <x-breadcrumb-item href="{{ route('teacher.dashboard') }}" icon="home">Dashboard</x-breadcrumb-item>
            <x-breadcrumb-item href="{{ route('teacher.downloads.index') }}">Resources</x-breadcrumb-item>
            <x-breadcrumb-item>Edit</x-breadcrumb-item>
        </x-slot:breadcrumb>

        <x-slot:actions>
            <x-btn href="{{ route('teacher.downloads.index') }}" variant="secondary" icon="arrow-left">
                Back to Resources
            </x-btn>
        </x-slot:actions>
    </x-page-header>

    {{-- ═══════════════════════════════════════════════════════════
         2. EDIT FORM
    ═══════════════════════════════════════════════════════════ --}}
    <x-card>
        <form method="POST" action="{{ route('teacher.downloads.update', $download) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid gap-6 md:grid-cols-2">
                {{-- Title --}}
                <div class="md:col-span-2">
                    <label for="title" class="block text-sm font-medium text-slate-700 mb-2">
                        Resource Title <span class="text-red-500">*</span>
                    </label>
                    <x-input 
                        id="title"
                        name="title" 
                        :value="old('title', $download->title)"
                        placeholder="e.g., Chapter 1 Notes, Lab Manual, Assignment Questions"
                        required
                    />
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Subject --}}
                <div>
                    <label for="subject_id" class="block text-sm font-medium text-slate-700 mb-2">
                        Subject <span class="text-red-500">*</span>
                    </label>
                    <x-select id="subject_id" name="subject_id" required>
                        <option value="">Select Subject</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id', $download->subject_id) == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }} ({{ $subject->program->name }} - Sem {{ $subject->semester }})
                            </option>
                        @endforeach
                    </x-select>
                    @error('subject_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Category --}}
                <div>
                    <label for="category" class="block text-sm font-medium text-slate-700 mb-2">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <x-select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="notes" {{ old('category', $download->category) == 'notes' ? 'selected' : '' }}>Notes</option>
                        <option value="syllabus" {{ old('category', $download->category) == 'syllabus' ? 'selected' : '' }}>Syllabus</option>
                        <option value="assignment" {{ old('category', $download->category) == 'assignment' ? 'selected' : '' }}>Assignment</option>
                        <option value="lab_manual" {{ old('category', $download->category) == 'lab_manual' ? 'selected' : '' }}>Lab Manual</option>
                        <option value="question_bank" {{ old('category', $download->category) == 'question_bank' ? 'selected' : '' }}>Question Bank</option>
                        <option value="reference" {{ old('category', $download->category) == 'reference' ? 'selected' : '' }}>Reference Material</option>
                        <option value="other" {{ old('category', $download->category) == 'other' ? 'selected' : '' }}>Other</option>
                    </x-select>
                    @error('category')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Visibility --}}
                <div class="md:col-span-2">
                    <label for="visibility" class="block text-sm font-medium text-slate-700 mb-2">
                        Visibility <span class="text-red-500">*</span>
                    </label>
                    <x-select id="visibility" name="visibility" required>
                        <option value="">Select Visibility</option>
                        <option value="public" {{ old('visibility', $download->visibility) == 'public' ? 'selected' : '' }}>Public (Visible to everyone including website visitors)</option>
                        <option value="students" {{ old('visibility', $download->visibility) == 'students' ? 'selected' : '' }}>Students Only (Only enrolled students can access)</option>
                        <option value="private" {{ old('visibility', $download->visibility) == 'private' ? 'selected' : '' }}>Private (Only you can access)</option>
                    </x-select>
                    @error('visibility')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-slate-500">
                        Choose who can view and download this resource
                    </p>
                </div>

                {{-- Description --}}
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-slate-700 mb-2">
                        Description
                    </label>
                    <textarea 
                        id="description"
                        name="description" 
                        rows="4"
                        class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        placeholder="Brief description of the resource..."
                    >{{ old('description', $download->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Current File Info --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Current File
                    </label>
                    <div class="flex items-center gap-3 p-4 rounded-lg bg-slate-50 border border-slate-200">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                            <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-slate-900 truncate">{{ $download->file_name }}</p>
                            <p class="text-xs text-slate-500">{{ number_format($download->file_size / 1024, 2) }} KB</p>
                        </div>
                        <a href="{{ route('teacher.downloads.file', $download) }}" target="_blank" class="text-blue-600 hover:text-blue-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- File Upload (Optional) --}}
                <div class="md:col-span-2">
                    <label for="file" class="block text-sm font-medium text-slate-700 mb-2">
                        Replace File (Optional)
                    </label>
                    <input 
                        type="file" 
                        id="file"
                        name="file"
                        accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar"
                        class="block w-full text-sm text-slate-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-lg file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100
                            cursor-pointer"
                    />
                    @error('file')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-slate-500">
                        Leave empty to keep the current file. Supported formats: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, ZIP, RAR (Max: 20MB)
                    </p>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200">
                <x-btn href="{{ route('teacher.downloads.index') }}" variant="secondary">
                    Cancel
                </x-btn>
                <x-btn type="submit" variant="primary" icon="save">
                    Update Resource
                </x-btn>
            </div>
        </form>
    </x-card>
</div>
@endsection
