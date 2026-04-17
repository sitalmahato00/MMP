@extends('layouts.app')
@section('title', 'Add User')

@section('content')
<x-page-header title="Add User" subtitle="Create a new system user."
               back="{{ route('admin.users.index') }}"/>

<form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data" class="max-w-3xl space-y-6">
    @csrf
    <x-form-section title="Basic Information">
        <x-form-row>
            <x-form-field label="Full Name" name="name" :required="true">
                <x-input name="name" :required="true" placeholder="John Doe"/>
            </x-form-field>
            <x-form-field label="Email Address" name="email" :required="true">
                <x-input name="email" type="email" :required="true" placeholder="john@example.com"/>
            </x-form-field>
            <x-form-field label="Phone Number" name="phone">
                <x-input name="phone" placeholder="+977-9800000000"/>
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

    <x-form-section title="Role & Access">
        <x-form-row>
            <x-form-field label="Role" name="role" :required="true">
                <x-select name="role" :required="true">
                    @foreach(['principal', 'hod', 'teacher', 'student', 'parent', 'alumni'] as $role)
                        <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                    @endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Account Status" name="is_active">
                <label class="flex items-center gap-3 mt-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 accent-[#8B0000] rounded">
                    <span class="text-sm text-gray-600">Active (can login)</span>
                </label>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Security">
        <x-form-row>
            <x-form-field label="Password" name="password" :required="true">
                <x-input name="password" type="password" :required="true"/>
            </x-form-field>
            <x-form-field label="Confirm Password" name="password_confirmation" :required="true">
                <x-input name="password_confirmation" type="password" :required="true"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <div class="flex items-center gap-3">
        <x-btn type="submit">Create User</x-btn>
        <x-btn href="{{ route('admin.users.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
