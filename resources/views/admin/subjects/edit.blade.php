@extends('layouts.app')

@section('title', 'Edit Subject - ' . $subject->name)

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Edit Subject</h1>
            <p class="mt-0.5 text-sm text-slate-500">Update course configuration, marking scheme, and syllabus document.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.subjects.show', $subject) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                View Subject
            </a>
            <a href="{{ route('admin.subjects.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                Cancel
            </a>
        </div>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('admin.subjects.update', $subject) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Basic Information --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4">Basic Information</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Academic Program *</label>
                    <select name="program_id" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" {{ old('program_id', $subject->program_id) == $program->id ? 'selected' : '' }}>
                                {{ $program->name }} ({{ $program->department?->name ?? 'No Dept' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Semester *</label>
                    <select name="semester" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        @for($s = 1; $s <= 6; $s++)
                            <option value="{{ $s }}" {{ old('semester', $subject->semester) == $s ? 'selected' : '' }}>Semester {{ $s }}</option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Subject Name *</label>
                    <input type="text" name="name" value="{{ old('name', $subject->name) }}" required
                           class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Subject Code *</label>
                    <input type="text" name="code" value="{{ old('code', $subject->code) }}" required
                           class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm font-mono focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Subject Type *</label>
                    <select name="type" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        <option value="both" {{ old('type', $subject->type) === 'both' ? 'selected' : '' }}>Theory & Practical</option>
                        <option value="theory" {{ old('type', $subject->type) === 'theory' ? 'selected' : '' }}>Theory Only</option>
                        <option value="practical" {{ old('type', $subject->type) === 'practical' ? 'selected' : '' }}>Practical Only</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Credit Hours</label>
                    <input type="number" name="credit_hours" value="{{ old('credit_hours', $subject->credit_hours) }}" min="0" max="10"
                           class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                </div>
            </div>
        </div>

        {{-- Marking Scheme --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4">Marking Scheme</h2>
            
            <div class="grid gap-6 sm:grid-cols-2">
                {{-- Theory Marks --}}
                <div class="rounded-xl border border-blue-100 bg-blue-50/40 p-4 dark:border-blue-900/30 dark:bg-blue-950/20">
                    <h3 class="text-sm font-bold text-blue-900 dark:text-blue-300 mb-3">Theory Scheme</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-slate-600 dark:text-slate-400 mb-1">Internal Full Marks</label>
                            <input type="number" name="full_marks_internal_theory" value="{{ old('full_marks_internal_theory', $subject->full_marks_internal_theory) }}" min="0"
                                   class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-800">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 dark:text-slate-400 mb-1">Internal Pass Marks</label>
                            <input type="number" name="pass_marks_internal_theory" value="{{ old('pass_marks_internal_theory', $subject->pass_marks_internal_theory) }}" min="0"
                                   class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-800">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 dark:text-slate-400 mb-1">External Full Marks</label>
                            <input type="number" name="full_marks_external_theory" value="{{ old('full_marks_external_theory', $subject->full_marks_external_theory) }}" min="0"
                                   class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-800">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 dark:text-slate-400 mb-1">External Pass Marks</label>
                            <input type="number" name="pass_marks_external_theory" value="{{ old('pass_marks_external_theory', $subject->pass_marks_external_theory) }}" min="0"
                                   class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-800">
                        </div>
                    </div>
                </div>

                {{-- Practical Marks --}}
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/40 p-4 dark:border-emerald-900/30 dark:bg-emerald-950/20">
                    <h3 class="text-sm font-bold text-emerald-900 dark:text-emerald-300 mb-3">Practical Scheme</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-slate-600 dark:text-slate-400 mb-1">Internal Full Marks</label>
                            <input type="number" name="full_marks_internal_practical" value="{{ old('full_marks_internal_practical', $subject->full_marks_internal_practical) }}" min="0"
                                   class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-800">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 dark:text-slate-400 mb-1">Internal Pass Marks</label>
                            <input type="number" name="pass_marks_internal_practical" value="{{ old('pass_marks_internal_practical', $subject->pass_marks_internal_practical) }}" min="0"
                                   class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-800">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 dark:text-slate-400 mb-1">External Full Marks</label>
                            <input type="number" name="full_marks_external_practical" value="{{ old('full_marks_external_practical', $subject->full_marks_external_practical) }}" min="0"
                                   class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-800">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-600 dark:text-slate-400 mb-1">External Pass Marks</label>
                            <input type="number" name="pass_marks_external_practical" value="{{ old('pass_marks_external_practical', $subject->pass_marks_external_practical) }}" min="0"
                                   class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-800">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Syllabus & Details --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4">Syllabus & Details</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Syllabus Document (PDF)</label>
                    @if($subject->syllabus)
                        <div class="mb-2 flex items-center gap-2 text-xs text-blue-600 dark:text-blue-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.5L19 7.5V19a2 2 0 01-2 2z"/></svg>
                            <a href="{{ Storage::disk('public')->url($subject->syllabus) }}" target="_blank" class="hover:underline font-semibold">Current Syllabus PDF</a>
                        </div>
                    @endif
                    <input type="file" name="syllabus" accept=".pdf"
                           class="w-full rounded-xl border border-slate-200 p-2 text-sm text-slate-500 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200 dark:border-slate-700 dark:bg-slate-800">
                    <p class="mt-1 text-xs text-slate-400">Leave empty to keep existing file.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">Description / Outline</label>
                    <textarea name="details" rows="3"
                              class="w-full rounded-xl border border-slate-200 p-3 text-sm focus:border-red-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white">{{ old('details', $subject->details) }}</textarea>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $subject->is_active) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500">
                    <label for="is_active" class="text-sm font-medium text-slate-700 dark:text-slate-300">Subject is currently active</label>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.subjects.index') }}" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                Cancel
            </a>
            <button type="submit" class="rounded-xl bg-[#8B0000] px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-[#6b0000] transition">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
