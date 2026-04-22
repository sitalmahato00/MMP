@extends('layouts.app')
@section('title', 'Create Facility/Resource')

@section('content')
<x-page-header title="Create Facility/Resource" subtitle="Create a new department facility or resource page."
               back="{{ route('hod.facilities.index') }}"/>

<form method="POST" action="{{ route('hod.facilities.store') }}" enctype="multipart/form-data"
      class="max-w-4xl space-y-6">
    @csrf

    {{-- ── 1. BASIC INFORMATION ──────────────────────────── --}}
    <x-form-section title="Basic Information" subtitle="Facility/resource name, type, and status.">
        <x-form-row>
            <x-form-field label="Facility/Resource Name" name="title" :required="true" span="full">
                <x-input name="title" :value="old('title')" :required="true" 
                         placeholder="e.g., Computer Lab, Library, Workshop, Study Materials"/>
            </x-form-field>

            <x-form-field label="Type/Category" name="category">
                <x-select name="category">
                    <option value="">Select Type</option>
                    <option value="lab" @selected(old('category') === 'lab')>Laboratory</option>
                    <option value="library" @selected(old('category') === 'library')>Library</option>
                    <option value="workshop" @selected(old('category') === 'workshop')>Workshop</option>
                    <option value="classroom" @selected(old('category') === 'classroom')>Classroom</option>
                    <option value="equipment" @selected(old('category') === 'equipment')>Equipment</option>
                    <option value="software" @selected(old('category') === 'software')>Software/Tools</option>
                    <option value="study_material" @selected(old('category') === 'study_material')>Study Material</option>
                    <option value="other" @selected(old('category') === 'other')>Other Resource</option>
                </x-select>
                <p class="mt-1.5 text-xs text-slate-500">Helps organize resources on the public page</p>
            </x-form-field>

            <x-form-field label="Publication Status" name="is_published">
                <x-select name="is_published">
                    <option value="0" @selected(old('is_published', '0') === '0')>Save as Draft</option>
                    <option value="1" @selected(old('is_published') === '1')>Publish Now</option>
                </x-select>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- ── 2. FACILITY DETAILS ───────────────────────────── --}}
    <x-form-section title="Facility Details" subtitle="Location, capacity, and availability information.">
        <x-form-row>
            <x-form-field label="Location" name="location">
                <x-input name="location" :value="old('location')" 
                         placeholder="e.g., Building A, 2nd Floor, Room 201"/>
            </x-form-field>

            <x-form-field label="Capacity" name="capacity">
                <x-input name="capacity" type="number" :value="old('capacity')" 
                         placeholder="e.g., 30 (for labs/classrooms)"/>
                <p class="mt-1.5 text-xs text-slate-500">Number of seats or users (if applicable)</p>
            </x-form-field>

            <x-form-field label="Availability" name="availability" span="full">
                <x-input name="availability" :value="old('availability')" 
                         placeholder="e.g., Mon-Fri 8AM-5PM, Available for students"/>
                <p class="mt-1.5 text-xs text-slate-500">When is this facility/resource available?</p>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- ── 3. DESCRIPTION & FEATURES ─────────────────────── --}}
    <x-form-section title="Description & Features" subtitle="Detailed information about the facility or resource.">
        <x-form-row>
            <x-form-field label="Description" name="content" :required="true" span="full">
                <x-textarea name="content" rows="8" :required="true" 
                           placeholder="Describe the facility/resource, its features, equipment, specifications, or how students can use it...">{{ old('content') }}</x-textarea>
            </x-form-field>

            <x-form-field label="Key Features (Optional)" name="features" span="full">
                <x-textarea name="features" rows="4" 
                           placeholder="List key features, equipment, or specifications (one per line)">{{ old('features') }}</x-textarea>
                <p class="mt-1.5 text-xs text-slate-500">Example: High-speed computers, Latest software, Air-conditioned</p>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- ── 4. MEDIA ───────────────────────────────────────── --}}
    <x-form-section title="Images & Media" subtitle="Upload photos or images of the facility.">
        <x-form-row>
            <x-form-field label="Featured Image" name="featured_image" span="full">
                <x-file-input name="featured_image" accept="image/*" 
                             label="Upload facility image (max 2 MB)"/>
                <p class="mt-1.5 text-xs text-slate-500">This image will be displayed on the public facilities page</p>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- ── 5. SEO SETTINGS ───────────────────────────────── --}}
    <x-form-section title="SEO Settings" subtitle="Search engine optimization settings (optional).">
        <x-form-row>
            <x-form-field label="Meta Title" name="meta_title" span="full">
                <x-input name="meta_title" :value="old('meta_title')" 
                         placeholder="Leave empty to use facility name"/>
                <p class="mt-1.5 text-xs text-slate-500">Recommended: 50-60 characters</p>
            </x-form-field>

            <x-form-field label="Meta Description" name="meta_description" span="full">
                <x-textarea name="meta_description" rows="3" 
                           placeholder="Brief description for search engines">{{ old('meta_description') }}</x-textarea>
                <p class="mt-1.5 text-xs text-slate-500">Recommended: 150-160 characters</p>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <div class="flex items-center gap-3 pb-6">
        <x-btn type="submit">Create Facility/Resource</x-btn>
        <x-btn href="{{ route('hod.facilities.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection