@extends('layouts.app')
@section('title', 'Edit Program')

@section('content')
<x-page-header title="Edit Program" :subtitle="$program->name"
               back="{{ route('admin.programs.index') }}"/>

<form method="POST" action="{{ route('admin.programs.update', $program) }}" class="max-w-2xl space-y-6">
    @csrf @method('PUT')
    <x-form-section title="Program Details">
        <x-form-row>
            <x-form-field label="Program Name" name="name" :required="true" span="full">
                <x-input name="name" :value="$program->name" :required="true"/>
            </x-form-field>
            <x-form-field label="Department" name="department_id" :required="true">
                <x-select name="department_id" :required="true">
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $program->department_id == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Duration (Years)" name="duration_years" :required="true">
                <x-input name="duration_years" type="number" :value="$program->duration_years" :required="true" class="[appearance:textfield]"/>
            </x-form-field>
            <x-form-field label="Total Semesters" name="total_semesters" :required="true">
                <x-input name="total_semesters" type="number" :value="$program->total_semesters" :required="true" class="[appearance:textfield]"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>
    <div class="flex items-center gap-3">
        <x-btn type="submit">Save Changes</x-btn>
        <x-btn href="{{ route('admin.programs.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
