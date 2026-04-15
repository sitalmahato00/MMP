@extends('layouts.app')
@section('title', 'Update Executive Record')

@section('content')
<x-page-header title="Update Leadership Record" :subtitle="$executive->name"
               back="{{ route('admin.web-control.index') }}"/>

<form method="POST" action="{{ route('admin.executives.update', $executive) }}" enctype="multipart/form-data" class="max-w-4xl space-y-6">
    @csrf @method('PUT')

    <x-form-section title="Identity & Title">
        <x-form-row>
            <x-form-field label="Full Name" name="name" :required="true">
                <x-input name="name" :value="$executive->name" :required="true" />
            </x-form-field>
            
            <x-form-field label="Leadership Type" name="type" :required="true">
                <x-select name="type" :required="true">
                    <option value="principal" {{ $executive->type == 'principal' ? 'selected' : '' }}>Principal / Director Type</option>
                    <option value="president" {{ $executive->type == 'president' ? 'selected' : '' }}>President / Chairman Type</option>
                </x-select>
            </x-form-field>

            <x-form-field label="Specific Designation" name="designation">
                <x-input name="designation" :value="$executive->designation" />
            </x-form-field>
            
            <x-form-field label="Numerical Display Order" name="order" :required="true">
                <x-input name="order" type="number" :value="$executive->order" :required="true" />
            </x-form-field>

            <x-form-field label="Official Profile Portrait" name="avatar" span="full">
                @if($executive->avatar)
                    <div class="mb-3">
                        <x-avatar :src="url('storage/'.$executive->avatar)" :name="$executive->name" size="xl" />
                    </div>
                @endif
                <x-file-input name="avatar" accept="image/*" />
                <p class="text-xs text-gray-400 mt-1">Leave blank to keep existing photo.</p>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Tenure (Bikram Sambat Dates)">
        <p class="text-sm text-gray-500 mb-4 px-1">Please enter all tenure dates using the BS Calendar (YYYY-MM-DD).</p>
        <x-form-row>
            <x-form-field label="Start Date (BS)" name="start_date_bs" :required="true">
                <x-input name="start_date_bs" type="text" class="nepali-datepicker" :value="$executive->start_date_bs" :required="true"/>
            </x-form-field>
            
            <x-form-field label="End Date (BS) - Leave empty if present" name="end_date_bs">
                <x-input name="end_date_bs" type="text" class="nepali-datepicker" :value="$executive->end_date_bs"/>
            </x-form-field>

            <label class="flex items-center gap-3 mt-4 cursor-pointer p-4 bg-gray-50 rounded-xl border border-gray-100 col-span-full">
                <input type="checkbox" name="is_current" value="1" {{ $executive->is_current ? 'checked' : '' }} class="w-5 h-5 accent-[#8B0000] rounded border-gray-300">
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
                <x-textarea name="message" rows="8">{{ $executive->message }}</x-textarea>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <div class="flex justify-between items-center mt-6">
        <div class="flex items-center gap-3">
            <x-btn type="submit">Save Changes</x-btn>
            <x-btn href="{{ route('admin.web-control.index') }}" variant="secondary">Cancel</x-btn>
        </div>
    </div>
</form>

<div class="mt-8 border-t pt-8">
    <form method="POST" action="{{ route('admin.executives.destroy', $executive) }}" class="flex items-center justify-between">
        @csrf @method('DELETE')
        <div>
            <h4 class="text-sm font-bold text-red-600">Danger Zone</h4>
            <p class="text-xs text-gray-500">Permanently delete this executive record from the history books.</p>
        </div>
        <x-btn type="submit" variant="danger" onclick="return confirm('Are you sure you want to permanently delete this historical record?')">Delete History Record</x-btn>
    </form>
</div>
@endsection
