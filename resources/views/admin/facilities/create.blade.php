@extends('layouts.app')
@section('title', 'Add Facility')

@section('content')
<x-form-layout title="Submit Facility/Resource" subtitle="Create web pages for labs, workshops, and campus resources." back="{{ route('admin.facilities.index') }}">
    <x-slot name="breadcrumb">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.facilities.index') }}" class="hover:text-slate-900">Facilities</a>
            <span>/</span>
            <span class="font-semibold text-slate-900">Submit Facility</span>
        </nav>
    </x-slot>

    <x-slot name="sidebar">
        <x-form-sidebar />
    </x-slot>

<form method="POST" action="{{ route('admin.facilities.store') }}" enctype="multipart/form-data" class="max-w-4xl space-y-6">
    @csrf

    <x-form-section title="General Information">
        <x-form-row>
            <x-form-field label="Facility Name" name="name" :required="true">
                <x-input name="name" :required="true" placeholder="e.g. Advanced Physics Lab"/>
            </x-form-field>
            <x-form-field label="Category" name="category" :required="true">
                <x-select name="category" :required="true">
                    <option value="">Select Category</option>
                    <option value="classroom">Classroom</option>
                    <option value="lab">Laboratory</option>
                    <option value="workshop">Workshop</option>
                    <option value="library">Library</option>
                    <option value="transportation">Transportation</option>
                    <option value="hostel">Hostel</option>
                    <option value="other">Other / Standalone Page</option>
                </x-select>
            </x-form-field>
            
            <x-form-field label="Link to Department (Optional)" name="department_id">
                <x-select name="department_id">
                    <option value="">-- Campus Wide --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Link to Program (Optional)" name="program_id">
                <x-select name="program_id">
                    <option value="">-- Campus Wide --</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                    @endforeach
                </x-select>
            </x-form-field>

            <x-form-field label="Location (Room/Building)" name="location">
                <x-input name="location" placeholder="e.g. Block B, Room 204"/>
            </x-form-field>
            <x-form-field label="Capacity (Optional)" name="capacity">
                <x-input name="capacity" type="number" placeholder="50"/>
            </x-form-field>

            <x-form-field label="Short Description" name="description" span="full">
                <x-textarea name="description" rows="2" placeholder="Brief metadata description..."></x-textarea>
            </x-form-field>
            <x-form-field label="Detailed Content (Rich Text)" name="content" span="full">
                <x-textarea name="content" rows="6" placeholder="Full descriptive text for the webpage..."></x-textarea>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Media & Attachments (Multiple Allowed)">
        <x-form-row>
            <x-form-field label="Upload Images (Gallery)" name="images" span="full">
                <div class="space-y-2">
                    <input type="file" name="images[]" id="images" multiple accept="image/*"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-[#8B0000]/10 file:text-[#8B0000] hover:file:bg-[#8B0000]/20 transition-all border border-gray-200 rounded-xl p-2 bg-gray-50 cursor-pointer">
                    <p class="text-xs text-gray-400">Hold Ctrl or Cmd to select multiple images (.jpg, .png)</p>
                </div>
            </x-form-field>

            <x-form-field label="Upload Documents (PDFs, Docs)" name="documents" span="full">
                <div class="space-y-2">
                    <input type="file" name="documents[]" id="documents" multiple accept=".pdf,.doc,.docx"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-[#8B0000]/10 file:text-[#8B0000] hover:file:bg-[#8B0000]/20 transition-all border border-gray-200 rounded-xl p-2 bg-gray-50 cursor-pointer">
                    <p class="text-xs text-gray-400">Provide lab manuals, syllabus, guidelines, etc.</p>
                </div>
            </x-form-field>
        </x-form-row>
        
        <label class="flex items-center gap-3 mt-4 cursor-pointer p-4 bg-gray-50 rounded-xl border border-gray-100">
            <input type="checkbox" name="is_published" value="1" checked class="w-5 h-5 accent-[#8B0000] rounded border-gray-300">
            <div class="flex flex-col">
                <span class="text-sm font-bold text-gray-900">Publish Immediately</span>
                <span class="text-xs text-gray-500">Make this visible on the public website API</span>
            </div>
        </label>
    </x-form-section>

    <div class="flex items-center gap-3">
        <x-btn type="submit">Submit Facility</x-btn>
        <x-btn href="{{ route('admin.facilities.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
</x-form-layout>
@endsection
