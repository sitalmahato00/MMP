@extends('layouts.app')
@section('title', 'Edit Content Page')

@section('content')
<x-page-header title="Edit Content Page" subtitle="Update page information and content."
               back="{{ route('hod.content.index') }}"/>

<form method="POST" action="{{ route('hod.content.update', $content) }}" enctype="multipart/form-data"
      class="max-w-4xl space-y-6">
    @csrf
    @method('PUT')

    {{-- ── 1. BASIC INFORMATION ──────────────────────────── --}}
    <x-form-section title="Basic Information" subtitle="Page title, content, and publication status.">
        <x-form-row>
            <x-form-field label="Page Title" name="title" :required="true" span="full">
                <x-input name="title" :value="old('title', $content->title)" :required="true" 
                         placeholder="Enter page title"/>
            </x-form-field>

            <x-form-field label="Publication Status" name="is_published">
                <x-select name="is_published">
                    <option value="0" @selected(old('is_published', $content->is_published ? '1' : '0') === '0')>Save as Draft</option>
                    <option value="1" @selected(old('is_published', $content->is_published ? '1' : '0') === '1')>Publish Now</option>
                </x-select>
            </x-form-field>

            <x-form-field label="Featured Image" name="featured_image">
                @if($content->featured_image)
                    <div class="mb-3">
                        <img src="{{ asset('storage/' . $content->featured_image) }}" 
                             alt="Current featured image" 
                             class="h-20 w-32 rounded-lg object-cover border border-slate-200">
                        <p class="mt-1 text-xs text-slate-500">Current featured image</p>
                    </div>
                @endif
                <x-file-input name="featured_image" accept="image/*" 
                             label="Upload new featured image (max 2 MB)"/>
                @if($content->featured_image)
                    <p class="mt-1.5 text-xs text-slate-500">Leave empty to keep current image</p>
                @endif
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- ── 2. CONTENT ─────────────────────────────────────── --}}
    <x-form-section title="Content" subtitle="Page content and body text.">
        <x-form-row>
            <x-form-field label="Page Content" name="content" :required="true" span="full">
                <x-textarea name="content" rows="12" :required="true" 
                           placeholder="Write your page content here...">{{ old('content', $content->content) }}</x-textarea>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- ── 3. SEO SETTINGS ───────────────────────────────── --}}
    <x-form-section title="SEO Settings" subtitle="Search engine optimization settings (optional).">
        <x-form-row>
            <x-form-field label="Meta Title" name="meta_title" span="full">
                <x-input name="meta_title" :value="old('meta_title', $content->meta_title)" 
                         placeholder="Leave empty to use page title"/>
                <p class="mt-1.5 text-xs text-slate-500">Recommended: 50-60 characters</p>
            </x-form-field>

            <x-form-field label="Meta Description" name="meta_description" span="full">
                <x-textarea name="meta_description" rows="3" 
                           placeholder="Brief description for search engines">{{ old('meta_description', $content->meta_description) }}</x-textarea>
                <p class="mt-1.5 text-xs text-slate-500">Recommended: 150-160 characters</p>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <div class="flex items-center gap-3 pb-6">
        <x-btn type="submit">Update Page</x-btn>
        <x-btn href="{{ route('hod.content.index') }}" variant="secondary">Cancel</x-btn>
        <form method="POST" action="{{ route('hod.content.destroy', $content) }}" 
              onsubmit="return confirm('Are you sure you want to delete this page?')" class="ml-auto">
            @csrf
            @method('DELETE')
            <x-btn type="submit" variant="danger">Delete Page</x-btn>
        </form>
    </div>
</form>
@endsection