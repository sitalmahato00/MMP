@extends('layouts.app')
@section('title', 'Edit Notice')

@section('content')
<x-page-header title="Edit Notice" :subtitle="$notice->title"
               back="{{ route('admin.notices.index') }}"/>

<form method="POST" action="{{ route('admin.notices.update', $notice) }}" enctype="multipart/form-data" class="max-w-3xl space-y-6" x-data="{ removals: [] }">
    @csrf @method('PUT')
    <input type="hidden" name="remove_attachments" :value="removals.join(',')">

    <x-form-section title="Notice Details">
        <x-form-row :cols="1">
            <x-form-field label="Title" name="title" :required="true">
                <x-input name="title" :value="$notice->title" :required="true"/>
            </x-form-field>
            <x-form-field label="Content" name="content" :required="true">
                <x-textarea name="content" rows="6" :required="true">{{ $notice->content }}</x-textarea>
            </x-form-field>

            {{-- Existing attachments --}}
            @if($notice->attachments->count() || $notice->attachment)
            <x-form-field label="Current Attachments" name="_existing">
                <div class="space-y-2">
                    {{-- Legacy single attachment --}}
                    @if($notice->attachment)
                    <div class="flex items-center gap-3 bg-gray-50 px-3 py-2 rounded-lg border border-gray-100 text-sm">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        <a href="{{ asset('storage/'.$notice->attachment) }}" target="_blank" class="text-[#8B0000] hover:underline truncate">{{ basename($notice->attachment) }}</a>
                        <span class="text-xs text-gray-400 ml-auto">(legacy)</span>
                    </div>
                    @endif
                    {{-- Multi attachments --}}
                    @foreach($notice->attachments as $att)
                    <div class="flex items-center gap-3 bg-gray-50 px-3 py-2 rounded-lg border border-gray-100 text-sm"
                         x-show="!removals.includes('{{ $att->id }}')">
                        @if($att->is_image)
                            <img src="{{ $att->url }}" class="w-8 h-8 rounded object-cover flex-shrink-0">
                        @else
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        @endif
                        <a href="{{ $att->url }}" target="_blank" class="text-[#8B0000] hover:underline truncate">{{ $att->file_name }}</a>
                        <span class="text-xs text-gray-400">{{ $att->file_type }}</span>
                        <button type="button" @click="removals.push('{{ $att->id }}')" class="ml-auto text-gray-300 hover:text-red-500 transition-colors flex-shrink-0" title="Remove">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    @endforeach
                </div>
            </x-form-field>
            @endif

            <x-form-field label="Add More Attachments" name="attachments">
                <div class="space-y-2">
                    <label for="attachments"
                           class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-gray-200 rounded-xl bg-gray-50 hover:bg-gray-100 hover:border-[#8B0000]/40 cursor-pointer transition-all duration-200 group">
                        <div class="flex flex-col items-center justify-center gap-2 text-center px-4">
                            <svg class="w-6 h-6 text-gray-300 group-hover:text-[#8B0000]/60 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-xs text-gray-400 group-hover:text-gray-600 transition-colors">Upload more images, PDFs, or videos (multiple allowed)</p>
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
            <x-form-field label="Notice Type / Audience" name="type" :required="true">
                <x-select name="type" :required="true">
                    @foreach(['general'=>'Notice Board','exam'=>'Exam Schedules & Results','news'=>'News','event'=>'Event','department'=>'Specific Department','class'=>'Specific Class','teachers'=>'Teachers Only'] as $val => $label)
                        <option value="{{ $val }}" {{ $notice->type === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Publish Date" name="published_at">
                <x-input name="published_at" type="datetime-local" :value="$notice->published_at ? \Carbon\Carbon::parse($notice->published_at)->format('Y-m-d\TH:i') : ''"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <div class="flex items-center gap-3">
        <x-btn type="submit">Save Changes</x-btn>
        <x-btn href="{{ route('admin.notices.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>

<script>
document.getElementById('attachments')?.addEventListener('change', function() {
    const preview = document.getElementById('file-preview');
    preview.innerHTML = '';
    Array.from(this.files).forEach(f => {
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 bg-gray-50 px-3 py-1.5 rounded border border-gray-100';
        div.innerHTML = `<svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg><span class="truncate">${f.name}</span><span class="text-gray-300 ml-auto flex-shrink-0">${(f.size/1024/1024).toFixed(1)} MB</span>`;
        preview.appendChild(div);
    });
});
</script>
@endsection
