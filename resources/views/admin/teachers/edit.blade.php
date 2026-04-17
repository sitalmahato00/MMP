@extends('layouts.app')
@section('title', 'Edit Teacher')

@section('content')
<x-page-header title="Edit Teacher" :subtitle="$teacher->user->name"
               back="{{ route('admin.teachers.index') }}"/>

<form method="POST" action="{{ route('admin.teachers.update', $teacher) }}" enctype="multipart/form-data" class="max-w-3xl space-y-6">
    @csrf @method('PUT')

    <x-form-section title="Account Details">
        <x-form-row>
            <x-form-field label="Full Name" name="name" :required="true">
                <x-input name="name" :value="$teacher->user->name" :required="true"/>
            </x-form-field>
            <x-form-field label="Email" name="email" :required="true">
                <x-input name="email" type="email" :value="$teacher->user->email" :required="true"/>
            </x-form-field>
            <x-form-field label="Phone" name="phone">
                <x-input name="phone" :value="$teacher->user->phone"/>
            </x-form-field>
            <x-form-field label="Gender" name="gender">
                <x-select name="gender">
                    <option value="">Select Gender</option>
                    <option value="male" {{ $teacher->user->gender == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ $teacher->user->gender == 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ $teacher->user->gender == 'other' ? 'selected' : '' }}>Other</option>
                </x-select>
            </x-form-field>
            <x-form-field label="Date of Birth (BS)" name="dob">
                <x-bs-date-picker name="dob" :value="$teacher->user->dob ? bsDate($teacher->user->dob) : ''"/>
            </x-form-field>
            <x-form-field label="Address" name="address" span="full">
                <x-textarea name="address" rows="2">{{ $teacher->user->address }}</x-textarea>
            </x-form-field>
            <x-form-field label="Profile Picture (Avatar)" name="avatar" span="full">
                <x-file-input name="avatar" accept="image/*" label="Upload Profile Picture" :current="$teacher->user->avatar"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Professional Details">
        <x-form-row>
            <x-form-field label="Department" name="department_id" :required="true">
                <x-select name="department_id" :required="true">
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $teacher->department_id == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Qualification" name="qualification">
                <x-input name="qualification" :value="$teacher->qualification"/>
            </x-form-field>
            <x-form-field label="Specialization" name="specialization">
                <x-input name="specialization" :value="$teacher->specialization"/>
            </x-form-field>
            <x-form-field label="Join Date (BS)" name="join_date">
                <x-bs-date-picker name="join_date" :value="$teacher->join_date ? bsDate($teacher->join_date) : ''"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <div class="flex items-center gap-3">
        <x-btn type="submit">Save Changes</x-btn>
        <x-btn href="{{ route('admin.teachers.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
