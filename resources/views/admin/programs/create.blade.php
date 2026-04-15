@extends('layouts.app')
@section('title', 'Add Program')

@section('content')
<x-page-header title="Add Program" subtitle="Create a new academic program under a department."
               back="{{ route('admin.programs.index') }}"/>

<form method="POST" action="{{ route('admin.programs.store') }}" class="max-w-2xl space-y-6">
    @csrf
    <x-form-section title="Program Details">
        <x-form-row>
            <x-form-field label="Program Name" name="name" :required="true" span="full">
                <x-input name="name" :required="true" placeholder="e.g. Bachelor of Computer Application"/>
            </x-form-field>
            <x-form-field label="Department" name="department_id" :required="true">
                <x-select name="department_id" :required="true">
                    <option value="">— Select Department —</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Duration (Years)" name="duration_years" :required="true">
                <x-input name="duration_years" type="number" :required="true" placeholder="e.g. 4" class="[appearance:textfield]"/>
            </x-form-field>
            <x-form-field label="Total Semesters" name="total_semesters" :required="true">
                <x-input name="total_semesters" type="number" :required="true" placeholder="e.g. 8" class="[appearance:textfield]"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>
    <div class="flex items-center gap-3">
        <x-btn type="submit">Create Program</x-btn>
        <x-btn href="{{ route('admin.programs.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
