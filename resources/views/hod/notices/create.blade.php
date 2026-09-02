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

    {{-- ── 2. CONTENT & IMAGES ──────────────────────────── --}}
    <x-form-section title="Content & Media" subtitle="Notice body, cover photo, and gallery attachments.">
        <x-form-row>
            <x-form-field label="Notice Content" name="content" :required="true" span="full">
                <x-textarea name="content" rows="7" :required="true" 
                           placeholder="Write your notice content here...">{{ old('content') }}</x-textarea>
            </x-form-field>

            {{-- Dedicated Cover Image --}}
            <x-form-field label="Cover Image / Featured Photo (Optional)" name="cover_image" span="full">
                <div x-data="{ preview: null }" class="space-y-2">
                    <label class="flex h-32 w-full cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-blue-200 bg-blue-50/30 transition-all duration-200 hover:border-blue-500 hover:bg-blue-50/60 overflow-hidden relative">
                        <template x-if="!preview">
                            <div class="flex flex-col items-center justify-center gap-1.5 px-4 text-center">
                                <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-xs font-semibold text-blue-900">Upload Cover Image (JPG, PNG, WebP, GIF)</p>
                                <p class="text-[11px] text-slate-400">Displayed at the very top of popups, detail pages, and cards</p>
                            </div>
                        </template>
                        <template x-if="preview">
                            <div class="relative w-full h-full flex items-center justify-center bg-slate-900">
                                <img :src="preview" alt="Cover Preview" class="h-full w-auto object-contain">
                                <span class="absolute bottom-1 right-2 bg-black/60 text-white text-[10px] px-2 py-0.5 rounded font-bold">Change Cover</span>
                            </div>
                        </template>
                        <input type="file" name="cover_image" accept="image/*" class="hidden"
                               @change="const file = $event.target.files[0]; if (file) { const r = new FileReader(); r.onload = e => preview = e.target.result; r.readAsDataURL(file); }">
                    </label>
                </div>
            </x-form-field>

            {{-- Additional Files / Documents / Gallery --}}
            <x-form-field label="Additional Gallery Images & Documents (Optional)" name="attachments" span="full">
                <input type="file" name="attachments[]" multiple accept="image/*,.pdf,.doc,.docx"
                       class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                <p class="mt-1 text-[11px] text-slate-400">Upload multiple photos, documents, or PDF routines</p>
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