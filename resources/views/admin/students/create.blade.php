@extends('layouts.app')
@section('title', 'Add Student')

@section('content')
<x-page-header title="Add Student" subtitle="Enroll a new student to a program."
               back="{{ route('admin.students.index') }}"/>

<form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data" class="max-w-3xl space-y-6">
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
            <x-form-field label="Date of Birth (BS)" name="dob">
                <x-bs-date-picker name="dob"/>
            </x-form-field>
            <x-form-field label="Address" name="address" span="full">
                <x-textarea name="address" rows="2" placeholder="Full address"></x-textarea>
            </x-form-field>
            <x-form-field label="Profile Picture (Avatar)" name="avatar" span="full">
                <x-file-input name="avatar" accept="image/*" label="Upload Profile Picture"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Enrollment Details">
        <x-form-row>
            <x-form-field label="Admission Number" name="admission_number" :required="true">
                <x-input name="admission_number" :required="true"/>
            </x-form-field>
            <x-form-field label="Program" name="program_id" :required="true">
                <x-select name="program_id" :required="true">
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                    @endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Current Semester" name="current_semester" :required="true">
                <x-select name="current_semester" :required="true">
                    @for($i = 1; $i <= 8; $i++)
                        <option value="{{ $i }}">Semester {{ $i }}</option>
                    @endfor
                </x-select>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <div class="flex items-center gap-3">
        <x-btn type="submit">Enroll Student</x-btn>
        <x-btn href="{{ route('admin.students.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
