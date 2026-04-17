@extends('layouts.app')
@section('title', 'Edit Student')

@section('content')
<x-page-header title="Edit Student" :subtitle="$student->user->name"
               back="{{ route('admin.students.index') }}"/>

<form method="POST" action="{{ route('admin.students.update', $student) }}" enctype="multipart/form-data" class="max-w-3xl space-y-6">
    @csrf @method('PUT')

    <x-form-section title="Account Details">
        <x-form-row>
            <x-form-field label="Full Name" name="name" :required="true">
                <x-input name="name" :value="$student->user->name" :required="true"/>
            </x-form-field>
            <x-form-field label="Email" name="email" :required="true">
                <x-input name="email" type="email" :value="$student->user->email" :required="true"/>
            </x-form-field>
            <x-form-field label="Phone" name="phone">
                <x-input name="phone" :value="$student->user->phone"/>
            </x-form-field>
            <x-form-field label="Gender" name="gender">
                <x-select name="gender">
                    <option value="">Select Gender</option>
                    <option value="male" {{ $student->user->gender == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ $student->user->gender == 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ $student->user->gender == 'other' ? 'selected' : '' }}>Other</option>
                </x-select>
            </x-form-field>
            <x-form-field label="Date of Birth (BS)" name="dob">
                <x-bs-date-picker name="dob" :value="$student->user->dob ? bsDate($student->user->dob) : ''"/>
            </x-form-field>
            <x-form-field label="Address" name="address" span="full">
                <x-textarea name="address" rows="2">{{ $student->user->address }}</x-textarea>
            </x-form-field>
            <x-form-field label="Profile Picture (Avatar)" name="avatar" span="full">
                <x-file-input name="avatar" accept="image/*" label="Upload Profile Picture" :current="$student->user->avatar"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Enrollment Details">
        <x-form-row>
            <x-form-field label="Admission Number" name="admission_number" :required="true">
                <x-input name="admission_number" :value="$student->admission_number" :required="true"/>
            </x-form-field>
            <x-form-field label="Program" name="program_id" :required="true">
                <x-select name="program_id" :required="true">
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" {{ $student->program_id == $program->id ? 'selected' : '' }}>
                            {{ $program->name }}
                        </option>
                    @endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Current Semester" name="current_semester" :required="true">
                <x-select name="current_semester" :required="true">
                    @for($i = 1; $i <= 8; $i++)
                        <option value="{{ $i }}" {{ $student->current_semester == $i ? 'selected' : '' }}>
                            Semester {{ $i }}
                        </option>
                    @endfor
                </x-select>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <div class="flex items-center gap-3">
        <x-btn type="submit">Save Changes</x-btn>
        <x-btn href="{{ route('admin.students.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
