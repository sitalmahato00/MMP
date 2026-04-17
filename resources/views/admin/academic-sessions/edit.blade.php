@extends('layouts.app')
@section('title', 'Edit Session')

@section('content')
<x-page-header title="Edit Academic Session" :subtitle="$session->name"
               back="{{ route('admin.academic-sessions.index') }}"/>

<form method="POST" action="{{ route('admin.academic-sessions.update', $session) }}" class="max-w-2xl space-y-6">
    @csrf @method('PUT')
    <x-form-section title="Session Details">
        <x-form-row>
            <x-form-field label="Session Name" name="name" :required="true" span="full">
                <x-input name="name" :value="$session->name" :required="true"/>
            </x-form-field>
            <x-form-field label="Start Date (BS)" name="start_date" :required="true">
                <x-bs-date-picker name="start_date" :value="bsDate($session->start_date)" :required="true"/>
            </x-form-field>
            <x-form-field label="End Date (BS)" name="end_date" :required="true">
                <x-bs-date-picker name="end_date" :value="bsDate($session->end_date)" :required="true"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>
    <div class="flex items-center gap-3">
        <x-btn type="submit">Save Changes</x-btn>
        <x-btn href="{{ route('admin.academic-sessions.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
