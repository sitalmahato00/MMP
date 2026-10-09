@extends('layouts.app')

@section('title', 'Create Assignment')

@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Create Assignment</h1>
            <p class="mt-0.5 text-sm text-slate-500">Publish a course homework or assignment for students.</p>
        </div>
        <a href="{{ route('admin.assignments.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
            Cancel
        </a>
    </div>

    <form method="POST" action="{{ route('admin.assignments.store') }}" enctype="multipart/form-data" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-5">
        @csrf

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Subject *</label>
            <select name="subject_id" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                <option value="">Select Subject</option>
                @foreach($subjects as $subj)
                    <option value="{{ $subj->id }}" {{ old('subject_id') == $subj->id ? 'selected' : '' }}>
                        {{ $subj->code }} - {{ $subj->name }} ({{ $subj->program?->name ?? 'Prog' }} Sem {{ $subj->semester }})
                    </option>
                @endforeach
            </select>
            @error('subject_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Teacher *</label>
            <select name="teacher_id" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                <option value="">Select Teacher</option>
                @foreach($teachers as $t)
                    <option value="{{ $t->id }}" {{ old('teacher_id') == $t->id ? 'selected' : '' }}>
                        {{ $t->user?->name }} ({{ $t->department?->name ?? 'No Dept' }})
                    </option>
                @endforeach
            </select>
            @error('teacher_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Assignment Title *</label>
            <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Lab Report 1: Sorting Algorithms"
                   class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
            @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Description / Instructions</label>
            <textarea name="description" rows="4" placeholder="Assignment questions, submission requirements, and guidelines..."
                      class="w-full rounded-xl border border-slate-200 p-3 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">{{ old('description') }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Due Date (BS) *</label>
                <x-bs-date-picker name="due_date" :value="old('due_date', bsDate(now()->addDays(7)))" adName="due_date_ad" required />
                @error('due_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Section (Optional)</label>
                <input type="text" name="section" value="{{ old('section') }}" placeholder="e.g. A, B"
                       class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Attachment (Optional)</label>
            <input type="file" name="attachment"
                   class="w-full rounded-xl border border-slate-200 p-2 text-sm text-slate-500 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200 dark:border-slate-700 dark:bg-slate-800">
            <p class="mt-1 text-xs text-slate-400">PDF, docx, zip, etc. up to 10MB.</p>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
            <a href="{{ route('admin.assignments.index') }}" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                Cancel
            </a>
            <button type="submit" class="rounded-xl bg-[#8B0000] px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#6b0000] transition">
                Create Assignment
            </button>
        </div>
    </form>
</div>
@endsection
