@extends('layouts.app')
@section('title', 'Create Notice')

@section('content')
<x-page-header title="Create Notice" subtitle="Create a new notice for your department or programs."
               back="{{ route('hod.notices.index') }}"/>

<form method="POST" action="{{ route('hod.notices.store') }}" enctype="multipart/form-data"
      x-data="{
          type: '{{ old('type', 'department') }}',
          showProgramFields() { return this.type === 'program'; }
      }"
      class="max-w-4xl space-y-6">
    @csrf

    {{-- ── 1. BASIC INFORMATION ──────────────────────────── --}}
    <x-form-section title="Basic Information" subtitle="Notice title, content, and type.">
        <x-form-row>
            <x-form-field label="Notice Title" name="title" :required="true" span="full">
                <x-input name="title" :value="old('title')" :required="true" 
                         placeholder="Enter notice title"/>
            </x-form-field>

            <x-form-field label="Notice Type" name="type" :required="true">
                <x-select name="type" :required="true" x-model="type">
                    <option value="department" @selected(old('type', 'department') === 'department')>Department Notice</option>
                    <option value="program" @selected(old('type') === 'program')>Program Specific</option>
                </x-select>
            </x-form-field>

            <x-form-field label="Publish Status" name="is_published">
                <x-select name="is_published">
                    <option value="1" @selected(old('is_published', '1') === '1')>Publish Immediately</option>
                    <option value="0" @selected(old('is_published') === '0')>Save as Draft</option>
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
                            <option value="{{ $program->id }}" @selected(old('program_id') == $program->id)>
                                {{ $program->name }}
                            </option>
                        @endforeach
                    </x-select>
                </x-form-field>

                <x-form-field label="Target Semester" name="semester">
                    <x-select name="semester">
                        <option value="">All Semesters</option>
                        @for($i = 1; $i <= 6; $i++)
                            <option value="{{ $i }}" @selected(old('semester') == $i)>Semester {{ $i }}</option>
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
                           placeholder="Write your notice content here...">{{ old('content') }}</x-textarea>
            </x-form-field>

            <x-form-field label="Attachment" name="attachment" span="full">
                <x-file-input name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" 
                             label="Upload attachment (max 10 MB)"/>
                <p class="mt-1.5 text-xs text-slate-500">Supported: PDF, DOC, DOCX, JPG, PNG</p>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- ── 3. MAIN WEBSITE & POPUP REQUEST ──────────────── --}}
    <x-form-section title="Main Website & Popup Request" subtitle="Request administration to display this notice on the public home page and as a popup modal.">
        <div class="space-y-4 rounded-xl border border-blue-100 bg-blue-50/50 p-4 dark:border-blue-900/40 dark:bg-blue-950/20">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="request_main_site" value="1" @checked(old('request_main_site', false))
                       class="h-4 w-4 mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <div>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">Request Display on Main Website</span>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Send a request to the college administration to feature this department notice on the public homepage noticeboard.</p>
                </div>
            </label>

            <label class="flex items-start gap-3 cursor-pointer pl-7">
                <input type="checkbox" name="request_as_popup" value="1" @checked(old('request_as_popup', false))
                       class="h-4 w-4 mt-0.5 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                <div>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">Also Request as Main Homepage Popup Modal</span>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Request administration to display this as an urgent popup screen to visitors on the main website.</p>
                </div>
            </label>

            <div class="pl-7 pt-2">
                <x-form-field label="Request Note for Administrator (Optional)" name="request_note" span="full">
                    <x-input name="request_note" :value="old('request_note')" placeholder="e.g., Urgent examination announcement for all engineering students"/>
                </x-form-field>
            </div>
        </div>
    </x-form-section>

    <div class="flex items-center gap-3 pb-6">
        <x-btn type="submit">Create Notice</x-btn>
        <x-btn href="{{ route('hod.notices.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection