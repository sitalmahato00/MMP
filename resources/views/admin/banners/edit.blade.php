@extends('layouts.app')
@section('title', 'Edit Banner')

@section('content')
<x-page-header title="Edit Banner" subtitle="Update homepage hero image."
               back="{{ route('admin.banners.index') }}"/>

<form method="POST" action="{{ route('admin.banners.update', $banner) }}" enctype="multipart/form-data" class="max-w-2xl space-y-6">
    @csrf @method('PUT')
    <x-form-section title="Banner Details">
        <x-form-row>
            <x-form-field label="Title" name="title" span="full">
                <x-input name="title" :value="$banner->title"/>
            </x-form-field>
            <x-form-field label="Subtitle" name="subtitle" span="full">
                <x-input name="subtitle" :value="$banner->subtitle"/>
            </x-form-field>
            <x-form-field label="Replace Image" name="image" span="full">
                <x-file-input name="image" accept="image/*" :current="$banner->image"/>
            </x-form-field>
            <x-form-field label="Order" name="order">
                <x-input name="order" type="number" :value="$banner->order"/>
            </x-form-field>
            <x-form-field label="Active" name="is_active">
                <label class="flex items-center gap-3 cursor-pointer mt-2">
                    <input type="checkbox" name="is_active" value="1" {{ $banner->is_active ? 'checked' : '' }} class="w-4 h-4 accent-[#8B0000] rounded">
                    <span class="text-sm text-gray-600">Show on public site</span>
                </label>
            </x-form-field>
        </x-form-row>
    </x-form-section>
    <div class="flex items-center gap-3">
        <x-btn type="submit">Save Changes</x-btn>
        <x-btn href="{{ route('admin.banners.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
