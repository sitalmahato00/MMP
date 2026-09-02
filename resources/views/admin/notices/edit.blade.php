@extends('layouts.app')
@section('title', $workspace['edit_button_label'])

@section('content')
<x-form-layout title="{{ $workspace['edit_button_label'] }}" subtitle="{{ $notice->title }}" back="{{ route($workspace['index_route']) }}">
    <x-slot name="breadcrumb">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span>/</span>
            <a href="{{ route($workspace['index_route']) }}" class="hover:text-slate-900">Notices</a>
            <span>/</span>
            <span class="font-semibold text-slate-900">Edit Notice</span>
        </nav>
    </x-slot>

    <x-slot name="sidebar">
        <x-form-sidebar />
    </x-slot>

<form method="POST" action="{{ route($workspace['route_prefix'] . '.update', $notice) }}" enctype="multipart/form-data" class="max-w-3xl space-y-6" x-data="{ removals: [], selectedType: @js(old('type', $notice->type)) }">
    @csrf
    @method('PUT')
    <input type="hidden" name="remove_attachments" :value="removals.join(',')">

    <x-form-section :title="$workspace['detail_heading']">
        <x-form-row :cols="1">
            <x-form-field label="Title" name="title" :required="true">
                <x-input name="title" :value="$notice->title" :required="true"/>
            </x-form-field>
            <x-form-field label="Content" name="content" :required="true">
                <x-textarea name="content" rows="6" :required="true">{{ $notice->content }}</x-textarea>
            </x-form-field>

            {{-- Cover Image --}}
            <x-form-field label="Cover Image / Featured Photo" name="cover_image">
                <div x-data="{ preview: @js($notice->cover_image_url) }" class="space-y-2">
                    <label class="flex h-36 w-full cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-blue-200 bg-blue-50/30 transition-all duration-200 hover:border-blue-500 hover:bg-blue-50/60 overflow-hidden relative">
                        <template x-if="!preview">
                            <div class="flex flex-col items-center justify-center gap-1.5 px-4 text-center">
                                <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-xs font-semibold text-blue-900">Upload Cover Image (JPG, PNG, WebP, GIF)</p>
                                <p class="text-[11px] text-slate-400">Displayed at top of popups, detail pages, and cards</p>
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

            @if($notice->attachments->count() || $notice->attachment)
                <x-form-field label="Current Gallery & Files" name="_existing">
                    <div class="space-y-2">
                        @if($notice->attachment)
                            <div class="flex items-center gap-3 rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm">
                                <svg class="h-4 w-4 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                <a href="{{ asset('storage/' . $notice->attachment) }}" target="_blank" class="truncate text-[#8B0000] hover:underline">{{ basename($notice->attachment) }}</a>
                                <span class="ml-auto text-xs text-gray-400">(primary cover)</span>
                            </div>
                        @endif

                        @foreach($notice->attachments as $attachment)
                            <div class="flex items-center gap-3 rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm"
                                 x-show="!removals.includes('{{ $attachment->id }}')">
                                @if($attachment->is_image)
                                    <img src="{{ $attachment->url }}" class="h-8 w-8 rounded object-cover flex-shrink-0">
                                @else
                                    <svg class="h-4 w-4 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                @endif
                                <a href="{{ $attachment->url }}" target="_blank" class="truncate text-[#8B0000] hover:underline">{{ $attachment->file_name }}</a>
                                <span class="text-xs text-gray-400">{{ $attachment->file_type }}</span>
                                <button type="button" @click="removals.push('{{ $attachment->id }}')" class="ml-auto flex-shrink-0 text-gray-300 transition-colors hover:text-red-500" title="Remove">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </x-form-field>
            @endif

            <x-form-field label="Add More Gallery Images & Documents" name="attachments">
                <div class="space-y-2">
                    <label for="attachments"
                           class="flex h-24 w-full cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 transition-all duration-200 group hover:border-[#8B0000]/40 hover:bg-gray-100">
                        <div class="flex flex-col items-center justify-center gap-1.5 px-4 text-center">
                            <svg class="h-5 w-5 text-gray-400 transition-colors group-hover:text-[#8B0000]/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-xs text-gray-500 transition-colors group-hover:text-gray-700">Upload more images, gallery photos, PDFs, or videos</p>
                        </div>
                        <input type="file" id="attachments" name="attachments[]" accept="image/*,.pdf,video/*" multiple class="hidden">
                    </label>
                    <div id="file-preview" class="space-y-1 text-xs text-gray-500"></div>
                </div>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Targeting & Scheduling">
        <x-form-row>
            <x-form-field :label="$workspace['is_news_events'] ? 'Post Type' : 'Notice Type / Audience'" name="type" :required="true">
                <x-select name="type" :required="true" x-model="selectedType">
                    @foreach($workspace['type_options'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('type', $notice->type) === $value)>{{ $label }}</option>
                    @endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Publish Date (BS)" name="published_at">
                <x-bs-date-picker name="published_at" :value="$notice->published_at ? bsDate($notice->published_at) : ''"/>
            </x-form-field>
        </x-form-row>

        @if(! $workspace['is_news_events'])
            <div x-show="selectedType === 'department'" x-cloak>
                <x-form-field label="Department" name="department_id" :required="true">
                    <x-select name="department_id" x-bind:required="selectedType === 'department'">
                        <option value="">Select Department</option>
                        @foreach($departments as $department)
                            @php
                                $departmentLabel = $department->code
                                    ? $department->code . ' - ' . $department->name
                                    : $department->name;
                            @endphp
                            <option value="{{ $department->id }}" @selected((string) old('department_id', $notice->department_id) === (string) $department->id)>{{ $departmentLabel }}</option>
                        @endforeach
                    </x-select>
                </x-form-field>
            </div>
        @endif
    </x-form-section>

    <x-form-section title="Website Popup Modal" id="popup-settings">
        <div x-data="{ isPopup: @js((bool) old('is_popup', $notice->is_popup || request('popup') == 1)) }" class="space-y-4">
            <div class="flex items-start gap-3 rounded-xl border border-blue-100 bg-blue-50/40 p-4">
                <input type="checkbox" name="is_popup" id="is_popup" value="1" x-model="isPopup"
                       class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <label for="is_popup" class="cursor-pointer select-none">
                    <span class="block text-sm font-bold text-gray-900">Show this notice as a Popup Modal on Website</span>
                    <span class="block text-xs text-gray-500 mt-0.5">When enabled, visitors will see this notice as an interactive pop-up when they open the website during the specified Bikram Sambat interval.</span>
                </label>
            </div>

            <div x-show="isPopup" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                <x-form-field label="Popup Active From (BS)" name="popup_from_bs">
                    <x-bs-date-picker name="popup_from_bs" :value="old('popup_from_bs', $notice->popup_from_bs ?: bsDate($notice->published_at ?? now(), 'Y-m-d'))" adName="popup_from"/>
                </x-form-field>
                <x-form-field label="Popup Active To (BS)" name="popup_to_bs">
                    <x-bs-date-picker name="popup_to_bs" :value="old('popup_to_bs', $notice->popup_to_bs ?: bsDate(now()->addDays(7), 'Y-m-d'))" adName="popup_to"/>
                </x-form-field>
            </div>
        </div>
    </x-form-section>

    <div class="flex items-center gap-3">
        <x-btn type="submit">Save Changes</x-btn>
        <x-btn :href="route($workspace['index_route'])" variant="secondary">Cancel</x-btn>
    </div>
</form>
</x-form-layout>

<script>
document.getElementById('attachments')?.addEventListener('change', function() {
    const preview = document.getElementById('file-preview');
    preview.innerHTML = '';
    Array.from(this.files).forEach(f => {
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 rounded border border-gray-100 bg-gray-50 px-3 py-1.5';
        div.innerHTML = `<svg class="h-3.5 w-3.5 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg><span class="truncate">${f.name}</span><span class="ml-auto flex-shrink-0 text-gray-300">${(f.size/1024/1024).toFixed(1)} MB</span>`;
        preview.appendChild(div);
    });
});
</script>
@endsection
