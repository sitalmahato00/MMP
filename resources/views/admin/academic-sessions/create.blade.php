@extends('layouts.app')
@section('title', 'New Academic Session')

@section('content')
<x-page-header title="New Academic Session" subtitle="Define a new academic year period."
               back="{{ route('admin.academic-sessions.index') }}"/>

<form method="POST" action="{{ route('admin.academic-sessions.store') }}" class="max-w-2xl space-y-6">
    @csrf
    <x-form-section title="Session Details">
        <x-form-row>
            <x-form-field label="Session Name" name="name" :required="true" span="full">
                <x-input name="name" :required="true" placeholder="e.g. 2081/2082"/>
            </x-form-field>
            <x-form-field label="Start Date (BS)" name="start_date" :required="true">
                <x-bs-date-picker name="start_date" :required="true"/>
            </x-form-field>
            <x-form-field label="End Date (BS)" name="end_date" :required="true">
                <x-bs-date-picker name="end_date" :required="true"/>
            </x-form-field>
            <x-form-field label="Set as Current Session" name="is_current">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_current" value="1" {{ old('is_current') ? 'checked' : '' }}
                           class="w-4 h-4 accent-[#8B0000] rounded">
                    <span class="text-sm text-gray-600">Make this the active session immediately</span>
                </label>
            </x-form-field>
        </x-form-row>
    </x-form-section>
    <div class="flex items-center gap-3">
        <x-btn type="submit">Create Session</x-btn>
        <x-btn href="{{ route('admin.academic-sessions.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
