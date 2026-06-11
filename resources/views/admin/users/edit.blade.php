@extends('layouts.app')
@section('title', 'Edit User')

@section('content')
<x-form-layout title="Edit User" subtitle="Update system user details." back="{{ route('admin.users.index') }}">
    <x-slot name="breadcrumb">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.users.index') }}" class="hover:text-slate-900">User Management</a>
            <span>/</span>
            <span class="font-semibold text-slate-900">Edit User</span>
        </nav>
    </x-slot>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data" class="space-y-6">
    @csrf @method('PUT')
    
    <x-form-section title="Basic Information">
        <x-form-row>
            <x-form-field label="Profile Picture (Avatar)" name="avatar" span="full">
                <x-file-input name="avatar" accept="image/*" label="Upload Profile Picture" :current="$user->avatar"/>
            </x-form-field>
            <x-form-field label="Full Name" name="name" :required="true">
                <x-input name="name" :value="$user->name" :required="true"/>
            </x-form-field>
            <x-form-field label="Phone Number" name="phone">
                <x-input name="phone" :value="$user->phone"/>
            </x-form-field>
            <x-form-field label="Email Address" name="email" :required="true">
                <x-input name="email" type="email" :value="$user->email" :required="true"/>
            </x-form-field>
            <x-form-field label="Gender" name="gender">
                <x-select name="gender">
                    <option value="">Select Gender</option>
                    <option value="male" {{ $user->gender == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ $user->gender == 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ $user->gender == 'other' ? 'selected' : '' }}>Other</option>
                </x-select>
            </x-form-field>
            <x-form-field label="Date of Birth (BS)" name="dob">
                <x-bs-date-picker name="dob" :value="$user->dob ? bsDate($user->dob) : ''"/>
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

    <x-slot name="sidebar">
        <x-form-sidebar>
            <div class="rounded-[8px] border border-slate-200 bg-white p-4 shadow-sm">
                <div class="text-sm font-semibold text-slate-900 mb-3">Action Notes</div>
                <p class="text-sm leading-6 text-slate-600">Use this page to update user roles, status, and login credentials in a single centralized form. Leave password fields blank if you do not wish to change the password.</p>
            </div>
        </x-form-sidebar>
    </x-slot>

    <x-slot name="footer">
        <div class="flex flex-wrap items-center gap-3">
            <x-btn type="submit" variant="success">Save Changes</x-btn>
            <x-btn type="reset" variant="ghost">Reset</x-btn>
            <x-btn href="{{ route('admin.users.index') }}" variant="secondary">Cancel</x-btn>
        </div>
    </x-slot>
</form>
</x-form-layout>
@endsection
