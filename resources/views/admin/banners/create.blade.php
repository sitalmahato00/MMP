@extends('layouts.app')
@section('title', 'Add Banner')

@section('content')
<x-page-header title="Add Banner" subtitle="Upload a new hero image for the homepage."
               back="{{ route('admin.banners.index') }}"/>

<form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data" class="max-w-2xl space-y-6">
    @csrf
    <x-form-section title="Banner Details">
        <x-form-row>
            <x-form-field label="Title" name="title" span="full">
                <x-input name="title" placeholder="Main banner heading (optional)"/>
            </x-form-field>
            <x-form-field label="Subtitle" name="subtitle" span="full">
                <x-input name="subtitle" placeholder="Secondary text (optional)"/>
            </x-form-field>
            <x-form-field label="Banner Image" name="image" :required="true" span="full">
                <x-file-input name="image" accept="image/*" label="Upload hero image (JPG, PNG)"/>
            </x-form-field>
            <x-form-field label="Order" name="order">
                <x-input name="order" type="number" value="0"/>
            </x-form-field>
            <x-form-field label="Active" name="is_active">
                <label class="flex items-center gap-3 cursor-pointer mt-2">
                    <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 accent-[#8B0000] rounded">
                    <span class="text-sm text-gray-600">Show on public site</span>
                </label>
            </x-form-field>
        </x-form-row>
    </x-form-section>
    <div class="flex items-center gap-3">
        <x-btn type="submit">Add Banner</x-btn>
        <x-btn href="{{ route('admin.banners.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
