@extends('layouts.app')
@section('title', 'Edit Notice')

@section('content')
<x-page-header title="Edit Notice" subtitle="Update notice information and content."
               back="{{ route('hod.notices.index') }}"/>

<form method="POST" action="{{ route('hod.notices.update', $notice) }}" enctype="multipart/form-data"
      x-data="{
          type: '{{ old('type', $notice->type) }}',
          showProgramFields() { return this.type === 'program'; }
      }"
      class="max-w-4xl space-y-6">
    @csrf
    @method('PUT')

    {{-- ── 1. BASIC INFORMATION ──────────────────────────── --}}
    <x-form-section title="Basic Information" subtitle="Notice title, content, and type.">
        <x-form-row>
            <x-form-field label="Notice Title" name="title" :required="true" span="full">
                <x-input name="title" :value="old('title', $notice->title)" :required="true" 
                         placeholder="Enter notice title"/>
            </x-form-field>

            <x-form-field label="Notice Type" name="type" :required="true">
                <x-select name="type" :required="true" x-model="type">
                    <option value="department" @selected(old('type', $notice->type) === 'department')>Department Notice</option>
                    <option value="program" @selected(old('type', $notice->type) === 'program')>Program Specific</option>
                </x-select>
            </x-form-field>

            <x-form-field label="Status" name="is_published">
                <x-select name="is_published">
                    <option value="0" @selected(old('is_published', $notice->is_published ? '1' : '0') === '0')>Save as Draft</option>
                    <option value="1" @selected(old('is_published', $notice->is_published ? '1' : '0') === '1')>Publish Now</option>
                </x-select>
            </x-form-field>
        </x-form-row>

        {{-- Program-specific fields --}}
        <div x-show="showProgramFields()" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0">
            <x-form-row>
                <x-form-field label="Target Program" name="program_id">
                    <x-select name="program_id">
                        <option value="">All Programs</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" @selected(old('program_id', $notice->program_id) == $program->id)>
                                {{ $program->name }}
                            </option>
                        @endforeach
                    </x-select>
                </x-form-field>

                <x-form-field label="Target Semester" name="semester">
                    <x-select name="semester">
                        <option value="">All Semesters</option>
                        @for($i = 1; $i <= 8; $i++)
                            <option value="{{ $i }}" @selected(old('semester', $notice->semester) == $i)>Semester {{ $i }}</option>
                        @endfor
                    </x-select>
                </x-form-field>
            </x-form-row>
        </div>
    </x-form-section>

    {{-- ── 2. CONTENT ─────────────────────────────────────── --}}
    <x-form-section title="Content" subtitle="Notice content and attachment.">
        <x-form-row>
            <x-form-field label="Notice Content" name="content" :required="true" span="full">
                <x-textarea name="content" rows="8" :required="true" 
                           placeholder="Write your notice content here...">{{ old('content', $notice->content) }}</x-textarea>
            </x-form-field>

            <x-form-field label="Attachment" name="attachment" span="full">
                @if($notice->attachment)
                    <div class="mb-3 flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <svg class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                        <span class="text-sm text-slate-700">Current attachment</span>
                        <a href="{{ asset('storage/' . $notice->attachment) }}" target="_blank"
                           class="text-sm text-blue-600 hover:text-blue-800">View</a>
                    </div>
                @endif
                <x-file-input name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" 
                             label="Upload new attachment (max 10 MB)"/>
                <p class="mt-1.5 text-xs text-slate-500">
                    @if($notice->attachment)
                        Leave empty to keep current attachment. 
                    @endif
                    Supported: PDF, DOC, DOCX, JPG, PNG
                </p>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <div class="flex items-center gap-3 pb-6">
        <x-btn type="submit">Update Notice</x-btn>
        <x-btn href="{{ route('hod.notices.index') }}" variant="secondary">Cancel</x-btn>
        <form method="POST" action="{{ route('hod.notices.destroy', $notice) }}" 
              onsubmit="return confirm('Are you sure you want to delete this notice?')" class="ml-auto">
            @csrf
            @method('DELETE')
            <x-btn type="submit" variant="danger">Delete Notice</x-btn>
        </form>
    </div>
</form>
@endsection