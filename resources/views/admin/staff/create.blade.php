@extends('layouts.app')
@section('title', 'Add Staff')

@section('content')
<x-page-header title="Add Staff Member" subtitle="Create a new administrative profile."
               back="{{ route('admin.staff.index') }}"/>

<form method="POST" action="{{ route('admin.staff.store') }}" enctype="multipart/form-data" class="max-w-3xl space-y-6">
    @csrf

    <x-form-section title="Staff Details">
        <x-form-row>
            <x-form-field label="Full Name" name="name" :required="true">
                <x-input name="name" :required="true" placeholder="Jane Doe"/>
            </x-form-field>
            <x-form-field label="Designation" name="designation" :required="true">
                <x-input name="designation" :required="true" placeholder="e.g. Account Head"/>
            </x-form-field>
            <x-form-field label="Department/Office" name="department">
                <x-input name="department" placeholder="e.g. Administration"/>
            </x-form-field>
            <x-form-field label="Email" name="email">
                <x-input name="email" type="email"/>
            </x-form-field>
            <x-form-field label="Phone" name="phone">
                <x-input name="phone"/>
            </x-form-field>
            <x-form-field label="Display Order" name="order">
                <x-input name="order" type="number" value="0"/>
            </x-form-field>
            <x-form-field label="Profile Photo" name="photo" span="full">
                <x-file-input name="photo" accept="image/*" label="Upload profile photo"/>
            </x-form-field>
            <x-form-field label="Status" name="is_active" span="full">
                <label class="flex items-center gap-3 mt-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 accent-[#8B0000] rounded">
                    <span class="text-sm text-gray-600">Active (Visible on Directory)</span>
                </label>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <div class="flex items-center gap-3">
        <x-btn type="submit">Add Staff Member</x-btn>
        <x-btn href="{{ route('admin.staff.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
