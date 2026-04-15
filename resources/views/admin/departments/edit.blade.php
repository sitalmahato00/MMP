@extends('layouts.app')
@section('title', 'Edit Department')

@section('content')
<x-page-header title="Edit Department" :subtitle="$department->name"
               back="{{ route('admin.departments.index') }}"/>

<form method="POST" action="{{ route('admin.departments.update', $department) }}" enctype="multipart/form-data" class="max-w-3xl space-y-6">
    @csrf @method('PUT')

    <x-form-section title="Department Details">
        <x-form-row>
            <x-form-field label="Department Name" name="name" :required="true" span="full">
                <x-input name="name" :value="$department->name" :required="true"/>
            </x-form-field>
            <x-form-field label="Department Code" name="code" :required="true">
                <x-input name="code" :value="$department->code" :required="true" class="uppercase"/>
            </x-form-field>
            <x-form-field label="Head of Department" name="hod_id">
                <x-select name="hod_id">
                    <option value="">— None Assigned —</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ $department->hod_id == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->name }}
                        </option>
                    @endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Description" name="description" span="full">
                <x-textarea name="description" rows="3">{{ $department->description }}</x-textarea>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Cover Image">
        <x-form-field label="Upload New Image" name="cover_image">
            <x-file-input name="cover_image" accept="image/*" :current="$department->cover_image_path"/>
        </x-form-field>
    </x-form-section>

    <div class="flex items-center gap-3">
        <x-btn type="submit">Save Changes</x-btn>
        <x-btn href="{{ route('admin.departments.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
