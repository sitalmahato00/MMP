@extends('layouts.app')
@section('title', 'Add Teacher')

@section('content')
<x-page-header title="Add Teacher" subtitle="Onboard a new faculty member."
               back="{{ route('admin.teachers.index') }}"/>

<form method="POST" action="{{ route('admin.teachers.store') }}" enctype="multipart/form-data" class="max-w-3xl space-y-6">
    @csrf

    <x-form-section title="Account Details">
        <x-form-row>
            <x-form-field label="Full Name" name="name" :required="true">
                <x-input name="name" :required="true"/>
            </x-form-field>
            <x-form-field label="Email" name="email" :required="true">
                <x-input name="email" type="email" :required="true"/>
            </x-form-field>
            <x-form-field label="Phone" name="phone">
                <x-input name="phone"/>
            </x-form-field>
            <x-form-field label="Password" name="password" :required="true">
                <x-input name="password" type="password" :required="true"/>
            </x-form-field>
            <x-form-field label="Gender" name="gender">
                <x-select name="gender">
                    <option value="">Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </x-select>
            </x-form-field>
            <x-form-field label="Date of Birth" name="dob">
                <x-input name="dob" type="date"/>
            </x-form-field>
            <x-form-field label="Address" name="address" span="full">
                <x-textarea name="address" rows="2" placeholder="Full address"></x-textarea>
            </x-form-field>
            <x-form-field label="Profile Picture (Avatar)" name="avatar" span="full">
                <x-file-input name="avatar" accept="image/*" label="Upload Profile Picture"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Professional Details">
        <x-form-row>
            <x-form-field label="Department" name="department_id" :required="true">
                <x-select name="department_id" :required="true">
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Qualification" name="qualification">
                <x-input name="qualification" placeholder="e.g. PhD, MSc"/>
            </x-form-field>
            <x-form-field label="Specialization" name="specialization">
                <x-input name="specialization"/>
            </x-form-field>
            <x-form-field label="Hire Date" name="hire_date">
                <x-input name="hire_date" type="date"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <div class="flex items-center gap-3">
        <x-btn type="submit">Add Teacher</x-btn>
        <x-btn href="{{ route('admin.teachers.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
