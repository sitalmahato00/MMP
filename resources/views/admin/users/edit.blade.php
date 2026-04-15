@extends('layouts.app')
@section('title', 'Edit User')

@section('content')
<x-page-header title="Edit User" :subtitle="$user->name"
               back="{{ route('admin.users.index') }}"/>

<form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data" class="max-w-3xl space-y-6">
    @csrf @method('PUT')
    
    <x-form-section title="Basic Information">
        <x-form-row>
            <x-form-field label="Full Name" name="name" :required="true">
                <x-input name="name" :value="$user->name" :required="true"/>
            </x-form-field>
            <x-form-field label="Email Address" name="email" :required="true">
                <x-input name="email" type="email" :value="$user->email" :required="true"/>
            </x-form-field>
            <x-form-field label="Phone Number" name="phone">
                <x-input name="phone" :value="$user->phone"/>
            </x-form-field>
            <x-form-field label="Gender" name="gender">
                <x-select name="gender">
                    <option value="">Select Gender</option>
                    <option value="male" {{ $user->gender == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ $user->gender == 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ $user->gender == 'other' ? 'selected' : '' }}>Other</option>
                </x-select>
            </x-form-field>
            <x-form-field label="Date of Birth" name="dob">
                <x-input name="dob" type="date" :value="$user->dob ? \Carbon\Carbon::parse($user->dob)->format('Y-m-d') : ''"/>
            </x-form-field>
            <x-form-field label="Address" name="address" span="full">
                <x-textarea name="address" rows="2">{{ $user->address }}</x-textarea>
            </x-form-field>
            <x-form-field label="Profile Picture (Avatar)" name="avatar" span="full">
                <x-file-input name="avatar" accept="image/*" label="Upload Profile Picture" :current="$user->avatar"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Role & Access">
        <x-form-row>
            <x-form-field label="Role" name="role" :required="true">
                <x-select name="role" :required="true">
                    @foreach(['principal', 'hod', 'teacher', 'student', 'parent', 'alumni'] as $role)
                        <option value="{{ $role }}" {{ $user->hasRole($role) ? 'selected' : '' }}>
                            {{ ucfirst($role) }}
                        </option>
                    @endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Account Status" name="is_active">
                <label class="flex items-center gap-3 mt-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }} class="w-4 h-4 accent-[#8B0000] rounded">
                    <span class="text-sm text-gray-600">Active (can login)</span>
                </label>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Change Password (leave blank to keep current)">
        <x-form-row>
            <x-form-field label="New Password" name="password">
                <x-input name="password" type="password"/>
            </x-form-field>
            <x-form-field label="Confirm Password" name="password_confirmation">
                <x-input name="password_confirmation" type="password"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <div class="flex items-center gap-3">
        <x-btn type="submit">Save Changes</x-btn>
        <x-btn href="{{ route('admin.users.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
