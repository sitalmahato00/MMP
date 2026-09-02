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
                        @for($i = 1; $i <= 6; $i++)
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

    {{-- ── 3. MAIN WEBSITE & POPUP REQUEST ──────────────── --}}
    <x-form-section title="Main Website & Popup Request" subtitle="Request administration to display this notice on the public home page and as a popup modal.">
        <div class="space-y-4 rounded-xl border border-blue-100 bg-blue-50/50 p-4 dark:border-blue-900/40 dark:bg-blue-950/20">
            @if($notice->main_site_status === 'approved')
                <div class="flex items-center gap-2 rounded-lg bg-emerald-50 border border-emerald-200 p-3 text-emerald-800 text-xs font-semibold">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Approved by Administrator for Main Website Display {{ $notice->is_popup ? '(and Active as Popup)' : '' }}
                </div>
            @elseif($notice->main_site_status === 'pending')
                <div class="flex items-center gap-2 rounded-lg bg-amber-50 border border-amber-200 p-3 text-amber-800 text-xs font-semibold">
                    <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Pending Administrator Review for Main Website Display
                </div>
            @endif

            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="request_main_site" value="1" @checked(old('request_main_site', $notice->main_site_requested))
                       class="h-4 w-4 mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <div>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">Request Display on Main Website</span>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Send a request to the college administration to feature this department notice on the public homepage noticeboard.</p>
                </div>
            </label>

            <label class="flex items-start gap-3 cursor-pointer pl-7">
                <input type="checkbox" name="request_as_popup" value="1" @checked(old('request_as_popup', $notice->request_as_popup))
                       class="h-4 w-4 mt-0.5 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                <div>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">Also Request as Main Homepage Popup Modal</span>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Request administration to display this as an urgent popup screen to visitors on the main website.</p>
                </div>
            </label>

            <div class="pl-7 pt-2">
                <x-form-field label="Request Note for Administrator (Optional)" name="request_note" span="full">
                    <x-input name="request_note" :value="old('request_note', $notice->request_note)" placeholder="e.g., Urgent examination announcement for all engineering students"/>
                </x-form-field>
            </div>
        </div>
    </x-form-section>

    <div class="flex items-center gap-3 pb-6">
        <x-btn type="submit">Update Notice</x-btn>
        <x-btn href="{{ route('hod.notices.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection