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
            <x-form-field label="Start Date" name="start_date" :required="true">
                <x-input name="start_date" type="date" :value="$session->start_date ? \Carbon\Carbon::parse($session->start_date)->format('Y-m-d') : ''" :required="true"/>
            </x-form-field>
            <x-form-field label="End Date" name="end_date" :required="true">
                <x-input name="end_date" type="date" :value="$session->end_date ? \Carbon\Carbon::parse($session->end_date)->format('Y-m-d') : ''" :required="true"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>
    <div class="flex items-center gap-3">
        <x-btn type="submit">Save Changes</x-btn>
        <x-btn href="{{ route('admin.academic-sessions.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
