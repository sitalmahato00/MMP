@extends('layouts.app')

@section('title', 'Edit Assignment')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl lg:rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-cyan-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Assignment</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        Edit Assignment
                    </h1>
                </div>
                <a href="{{ route('teacher.assignments.show', $assignment) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back
                </a>
            </div>
        </div>
    </section>

    {{-- Form --}}
    <form action="{{ route('teacher.assignments.update', $assignment) }}" method="POST" enctype="multipart/form-data" class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-sm space-y-6">
        @csrf
        @method('PUT')

        {{-- Subject Selection --}}
        <div>
            <label class="block text-sm font-semibold text-slate-900 mb-2">Subject *</label>
            <select name="subject_id" required class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('subject_id') border-rose-500 @enderror">
                <option value="">Select a subject...</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ (old('subject_id', $assignment->subject_id) == $subject->id) ? 'selected' : '' }}>
                        {{ $subject->name }} ({{ $subject->code }})
                    </option>
                @endforeach
            </select>
            @error('subject_id')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Title --}}
        <div>
            <label class="block text-sm font-semibold text-slate-900 mb-2">Title *</label>
            <input type="text" name="title" required value="{{ old('title', $assignment->title) }}" placeholder="Assignment title..." 
                class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('title') border-rose-500 @enderror">
            @error('title')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Description --}}
        <div>
            <label class="block text-sm font-semibold text-slate-900 mb-2">Description</label>
            <textarea name="description" rows="4" placeholder="Assignment description..." 
                class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('description') border-rose-500 @enderror">{{ old('description', $assignment->description) }}</textarea>
            @error('description')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Due Date --}}
        <div>
            <label class="block text-sm font-semibold text-slate-900 mb-2">Due Date (BS) *</label>
            <x-bs-date-picker name="due_date_bs" adName="due_date" :value="old('due_date_bs', \App\Helpers\NepaliDateHelper::toBS($assignment->due_date))" required 
                class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('due_date') border-rose-500 @enderror"/>
            @error('due_date')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-slate-500">Due date cannot be in the past</p>
        </div>

        {{-- Current Attachment --}}
        @if($assignment->attachment)
        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
            <p class="text-sm font-semibold text-slate-900 mb-2">Current Attachment</p>
            <a href="{{ asset('storage/' . ltrim($assignment->attachment, '/')) }}" target="_blank" class="inline-flex items-center gap-2 text-sm text-cyan-600 hover:text-cyan-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                </svg>
                View Current File
            </a>
        </div>
        @endif

        {{-- New Attachment --}}
        <div>
            <label class="block text-sm font-semibold text-slate-900 mb-2">{{ $assignment->attachment ? 'Replace Attachment' : 'Attachment' }}</label>
            <div class="relative">
                <input type="file" name="attachment" class="hidden" id="attachment-input">
                <label for="attachment-input" class="flex items-center justify-center gap-2 rounded-lg border-2 border-dashed border-slate-300 px-4 py-6 text-center cursor-pointer transition hover:border-blue-500 hover:bg-blue-50">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Click to upload or drag and drop</p>
                        <p class="text-xs text-slate-500">PDF, DOC, DOCX up to 10MB</p>
                    </div>
                </label>
                <p id="file-name" class="mt-2 text-xs text-slate-600"></p>
            </div>
            @error('attachment')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Actions --}}
        <div class="flex gap-3 border-t border-slate-100 pt-6">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-cyan-600 px-6 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Update Assignment
            </button>
            <a href="{{ route('teacher.assignments.show', $assignment) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-6 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
document.getElementById('attachment-input').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || '';
    document.getElementById('file-name').textContent = fileName ? `Selected: ${fileName}` : '';
});
</script>
@endsection
