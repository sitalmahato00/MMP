@extends('layouts.app')
@section('title', 'Add Executive Record')

@section('content')
<x-page-header title="Record Leadership" subtitle="Add institutional presidents, principals, and key directors."
               back="{{ route('admin.web-control.index') }}"/>

<form method="POST" action="{{ route('admin.executives.store') }}" enctype="multipart/form-data" class="max-w-4xl space-y-6">
    @csrf

    <x-form-section title="Identity & Title">
        <x-form-row>
            <x-form-field label="Full Name" name="name" :required="true">
                <x-input name="name" :required="true" placeholder="e.g. Mr. Sambhoo Bahadur Shrestha"/>
            </x-form-field>
            
            <x-form-field label="Leadership Type" name="type" :required="true">
                <x-select name="type" :required="true">
                    <option value="principal">Principal / Director Type</option>
                    <option value="president">President / Chairman Type</option>
                </x-select>
            </x-form-field>

            <x-form-field label="Specific Designation" name="designation">
                <x-input name="designation" placeholder="e.g. Executive Director, Hon. President"/>
            </x-form-field>
            
            <x-form-field label="Numerical Display Order" name="order" :required="true">
                <x-input name="order" type="number" value="0" :required="true" />
            </x-form-field>

            <x-form-field label="Official Profile Portrait" name="avatar" span="full">
                <x-file-input name="avatar" accept="image/*" />
                <p class="text-xs text-gray-400 mt-1">Recommended: 400x400px studio portrait.</p>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Tenure (Bikram Sambat Dates)">
        <p class="text-sm text-gray-500 mb-4 px-1">Please enter all tenure dates using the BS Calendar (YYYY-MM-DD).</p>
        <x-form-row>
            <x-form-field label="Start Date (BS)" name="start_date_bs" :required="true">
                <x-input name="start_date_bs" type="text" class="nepali-datepicker" placeholder="e.g. 2062-03-21" :required="true"/>
            </x-form-field>
            
            <x-form-field label="End Date (BS) - Leave empty if present" name="end_date_bs">
                <x-input name="end_date_bs" type="text" class="nepali-datepicker" placeholder="e.g. 2070-12-15"/>
            </x-form-field>

            <label class="flex items-center gap-3 mt-4 cursor-pointer p-4 bg-gray-50 rounded-xl border border-gray-100 col-span-full">
                <input type="checkbox" name="is_current" value="1" class="w-5 h-5 accent-[#8B0000] rounded border-gray-300">
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-gray-900">Current Incumbent</span>
                    <span class="text-xs text-gray-500">Flags this person securely as the currently serving leader in this role.</span>
                </div>
            </label>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Institutional Message (Optional)">
        <x-form-row>
            <x-form-field label="Message from the Desk" name="message" span="full">
                <x-textarea name="message" rows="8" placeholder="Enter their primary address or welcome message here..."></x-textarea>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <div class="flex items-center gap-3">
        <x-btn type="submit">Publish Record</x-btn>
        <x-btn href="{{ route('admin.web-control.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
