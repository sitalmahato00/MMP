@extends('layouts.app')
@section('title', 'Edit Department')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <x-page-header title="Edit Department" :subtitle="$department->name"
                   back="{{ route('admin.departments.index') }}"/>

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
            <x-form-field label="Syllabus PDF" name="syllabus">
                <x-file-input name="syllabus" accept="application/pdf" :current="$department->syllabus" label="Replace syllabus document"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

        <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
            <button type="submit" class="inline-flex items-center rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#6e0000]">
                Save Changes
            </button>
            <a href="{{ route('admin.departments.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-100">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
