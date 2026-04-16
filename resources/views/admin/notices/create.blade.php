@extends('layouts.app')
@php
    $noticeTypes = [
        'general' => 'Notice Board',
        'exam' => 'Exam Schedules & Results',
        'news' => 'News',
        'event' => 'Event',
        'department' => 'Specific Department',
        'class' => 'Specific Class',
        'teachers' => 'Teachers Only',
    ];
    $defaultType = old('type', request('type', 'general'));
    $pageTitle = $defaultType === 'news' ? 'Post News' : ($defaultType === 'event' ? 'Post Event' : 'Post Notice');
    $pageSubtitle = $defaultType === 'news'
        ? 'Publish a news update to the system.'
        : ($defaultType === 'event'
            ? 'Publish an event update to the system.'
            : 'Publish a new notice to the system.');
@endphp
@section('title', $pageTitle)

@section('content')
<x-page-header :title="$pageTitle" :subtitle="$pageSubtitle"
               back="{{ route('admin.notices.index') }}"/>

<form method="POST" action="{{ route('admin.notices.store') }}" enctype="multipart/form-data" class="max-w-3xl space-y-6">
    @csrf
    <x-form-section title="Notice Details">
        <x-form-row :cols="1">
            <x-form-field label="Title" name="title" :required="true">
                <x-input name="title" :required="true" placeholder="Notice title…"/>
            </x-form-field>
            <x-form-field label="Content" name="content" :required="true">
                <x-textarea name="content" rows="6" placeholder="Full notice content…"/>
            </x-form-field>
            <x-form-field label="Attachments" name="attachments">
                <div class="space-y-2">
                    <label for="attachments"
                           class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-gray-200 rounded-xl bg-gray-50 hover:bg-gray-100 hover:border-[#8B0000]/40 cursor-pointer transition-all duration-200 group">
                        <div class="flex flex-col items-center justify-center gap-2 text-center px-4">
                            <svg class="w-6 h-6 text-gray-300 group-hover:text-[#8B0000]/60 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-xs text-gray-400 group-hover:text-gray-600 transition-colors">Upload images, PDFs, or videos (multiple allowed, max 10 files)</p>
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
                    @foreach($noticeTypes as $val => $label)
                        <option value="{{ $val }}" {{ $defaultType === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Publish Date" name="published_at">
                <x-input name="published_at" type="datetime-local" :value="now()->format('Y-m-d\TH:i')"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <div class="flex items-center gap-3">
        <x-btn type="submit">Publish Notice</x-btn>
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
