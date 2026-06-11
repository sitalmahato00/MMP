@extends('layouts.app')
@section('title', 'Edit Department')

@section('content')
<x-form-layout title="Edit Department" subtitle="Update department settings." back="{{ route('admin.departments.index') }}">
    <x-slot name="breadcrumb">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.departments.index') }}" class="hover:text-slate-900">Departments</a>
            <span>/</span>
            <span class="font-semibold text-slate-900">Edit Department</span>
        </nav>
    </x-slot>

    <x-slot name="sidebar">
        <x-form-sidebar />
    </x-slot>

    <form method="POST" action="{{ route('admin.departments.update', $department) }}" enctype="multipart/form-data" class="mx-auto w-full max-w-3xl space-y-6">
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

    <x-form-section title="Media">
        <x-form-row>
            <x-form-field label="Department Photo" name="photo">
                <x-file-input name="photo" accept="image/*" :current="$department->photo" label="Replace department photo"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

        <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
            <x-btn type="submit" variant="success">Save Changes</x-btn>
            <x-btn href="{{ route('admin.departments.index') }}" variant="secondary">Cancel</x-btn>
        </div>
    </form>
</x-form-layout>
@endsection
