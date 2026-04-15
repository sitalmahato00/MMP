@extends('layouts.app')
@section('title', 'Edit Staff')

@section('content')
<x-page-header title="Edit Staff Member" :subtitle="$staff->name"
               back="{{ route('admin.staff.index') }}"/>

<form method="POST" action="{{ route('admin.staff.update', $staff) }}" enctype="multipart/form-data" class="max-w-3xl space-y-6">
    @csrf @method('PUT')

    <x-form-section title="Staff Details">
        <x-form-row>
            <x-form-field label="Full Name" name="name" :required="true">
                <x-input name="name" :value="$staff->name" :required="true"/>
            </x-form-field>
            <x-form-field label="Designation" name="designation" :required="true">
                <x-input name="designation" :value="$staff->designation" :required="true"/>
            </x-form-field>
            <x-form-field label="Department/Office" name="department">
                <x-input name="department" :value="$staff->department"/>
            </x-form-field>
            <x-form-field label="Email" name="email">
                <x-input name="email" type="email" :value="$staff->email"/>
            </x-form-field>
            <x-form-field label="Phone" name="phone">
                <x-input name="phone" :value="$staff->phone"/>
            </x-form-field>
            <x-form-field label="Display Order" name="order">
                <x-input name="order" type="number" :value="$staff->order"/>
            </x-form-field>
            <x-form-field label="Profile Photo" name="photo" span="full">
                <x-file-input name="photo" accept="image/*" label="Upload replacement photo" :current="$staff->photo"/>
            </x-form-field>
            <x-form-field label="Status" name="is_active" span="full">
                <label class="flex items-center gap-3 mt-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ $staff->is_active ? 'checked' : '' }} class="w-4 h-4 accent-[#8B0000] rounded">
                    <span class="text-sm text-gray-600">Active (Visible on Directory)</span>
                </label>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <div class="flex items-center gap-3">
        <x-btn type="submit">Save Changes</x-btn>
        <x-btn href="{{ route('admin.staff.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
