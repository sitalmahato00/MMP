@extends('layouts.app')
@section('title', 'Edit Facility')

@section('content')
<x-form-layout title="Edit Facility/Resource" subtitle="Update the facility or resource page." back="{{ route('admin.facilities.index') }}">
    <x-slot name="breadcrumb">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.facilities.index') }}" class="hover:text-slate-900">Facilities</a>
            <span>/</span>
            <span class="font-semibold text-slate-900">Edit Facility</span>
        </nav>
    </x-slot>

    <x-slot name="sidebar">
        <x-form-sidebar />
    </x-slot>

<form method="POST" action="{{ route('admin.facilities.update', $facility) }}" enctype="multipart/form-data" class="max-w-4xl space-y-6">
    @csrf @method('PUT')

    <x-form-section title="General Information">
        <x-form-row>
            <x-form-field label="Facility Name" name="name" :required="true">
                <x-input name="name" :value="$facility->name" :required="true"/>
            </x-form-field>
            <x-form-field label="Category" name="category" :required="true">
                <x-select name="category" :required="true">
                    @foreach(['classroom'=>'Classroom','lab'=>'Laboratory','workshop'=>'Workshop','library'=>'Library','transportation'=>'Transportation','hostel'=>'Hostel','other'=>'Other'] as $val => $label)
                        <option value="{{ $val }}" {{ $facility->category == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </x-select>
            </x-form-field>
            
            <x-form-field label="Link to Department (Optional)" name="department_id">
                <x-select name="department_id">
                    <option value="">-- Campus Wide --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $facility->department_id == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Link to Program (Optional)" name="program_id">
                <x-select name="program_id">
                    <option value="">-- Campus Wide --</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" {{ $facility->program_id == $program->id ? 'selected' : '' }}>{{ $program->name }}</option>
                    @endforeach
                </x-select>
            </x-form-field>

            <x-form-field label="Location (Room/Building)" name="location">
                <x-input name="location" :value="$facility->location"/>
            </x-form-field>
            <x-form-field label="Capacity (Optional)" name="capacity">
                <x-input name="capacity" type="number" :value="$facility->capacity"/>
            </x-form-field>

            <x-form-field label="Short Description" name="description" span="full">
                <x-textarea name="description" rows="2">{{ $facility->description }}</x-textarea>
            </x-form-field>
            <x-form-field label="Detailed Content (Rich Text)" name="content" span="full">
                <x-textarea name="content" rows="6">{{ $facility->content }}</x-textarea>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Media & Attachments (Add New)">
        <p class="text-sm text-gray-500 mb-4 px-1">Currently attached: <strong class="text-gray-900">{{ is_array($facility->images) ? count($facility->images) : 0 }} Images</strong>, <strong class="text-gray-900">{{ is_array($facility->documents) ? count($facility->documents) : 0 }} Documents</strong>.</p>
        
        <x-form-row>
            <x-form-field label="Upload More Images" name="images" span="full">
                <div class="space-y-2">
                    <input type="file" name="images[]" id="images" multiple accept="image/*"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-[#8B0000]/10 file:text-[#8B0000] hover:file:bg-[#8B0000]/20 transition-all border border-gray-200 rounded-xl p-2 bg-gray-50 cursor-pointer">
                    <p class="text-xs text-gray-400">New uploads will be appended to the current gallery.</p>
                </div>
            </x-form-field>

            <x-form-field label="Upload More Documents (PDFs, Docs)" name="documents" span="full">
                <div class="space-y-2">
                    <input type="file" name="documents[]" id="documents" multiple accept=".pdf,.doc,.docx"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-[#8B0000]/10 file:text-[#8B0000] hover:file:bg-[#8B0000]/20 transition-all border border-gray-200 rounded-xl p-2 bg-gray-50 cursor-pointer">
                    <p class="text-xs text-gray-400">New documents will be appended.</p>
                </div>
            </x-form-field>
        </x-form-row>

        <label class="flex items-center gap-3 mt-4 cursor-pointer p-4 bg-gray-50 rounded-xl border border-gray-100">
            <input type="checkbox" name="is_published" value="1" {{ $facility->is_published ? 'checked' : '' }} class="w-5 h-5 accent-[#8B0000] rounded border-gray-300">
            <div class="flex flex-col">
                <span class="text-sm font-bold text-gray-900">Publish Immediately</span>
                <span class="text-xs text-gray-500">Make this visible on the public website API</span>
            </div>
        </label>
    </x-form-section>

    <div class="flex items-center gap-3">
        <x-btn type="submit">Save Changes</x-btn>
        <x-btn href="{{ route('admin.facilities.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
</x-form-layout>
@endsection
