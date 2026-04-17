@extends('layouts.app')
@section('title', 'Add Department')

@section('content')
<x-page-header title="Add Department" subtitle="Create a new faculty department."
               back="{{ route('admin.departments.index') }}"/>

<form method="POST" action="{{ route('admin.departments.store') }}" enctype="multipart/form-data" class="max-w-3xl space-y-6">
    @csrf

    <x-form-section title="Department Details">
        <x-form-row>
            <x-form-field label="Department Name" name="name" :required="true" span="full">
                <x-input name="name" :required="true" placeholder="e.g. Computer Science & IT"/>
            </x-form-field>
            <x-form-field label="Department Code" name="code" :required="true">
                <x-input name="code" :required="true" placeholder="e.g. CSIT" class="uppercase"/>
            </x-form-field>
            <x-form-field label="Head of Department" name="hod_id">
                <x-select name="hod_id">
                    <option value="">— None Assigned —</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ old('hod_id') == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->name }}
                        </option>
                    @endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Description" name="description" span="full">
                <x-textarea name="description" rows="3" placeholder="Brief description of the department…"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Media">
        <x-form-row>
            <x-form-field label="Department Photo" name="photo">
                <x-file-input name="photo" accept="image/*" label="Upload department photo (JPG, PNG)"/>
            </x-form-field>
            <x-form-field label="Syllabus PDF" name="syllabus">
                <x-file-input name="syllabus" accept="application/pdf" label="Upload syllabus document (PDF)"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <div class="flex items-center gap-3">
        <x-btn type="submit">Create Department</x-btn>
        <x-btn href="{{ route('admin.departments.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
