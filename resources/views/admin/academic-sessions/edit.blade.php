@extends('layouts.app')
@section('title', 'Edit Session')

@section('content')
<x-form-layout title="Edit Academic Session" subtitle="Edit details for {{ $session->name }}." back="{{ route('admin.academic-sessions.index') }}">
    <x-slot name="breadcrumb">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.academic-sessions.index') }}" class="hover:text-slate-900">Academic Sessions</a>
            <span>/</span>
            <span class="font-semibold text-slate-900">Edit Session</span>
        </nav>
    </x-slot>

    <x-slot name="sidebar">
        <x-form-sidebar />
    </x-slot>

    <form method="POST" action="{{ route('admin.academic-sessions.update', $session) }}" class="max-w-2xl space-y-6">
    @csrf @method('PUT')
    <x-form-section title="Session Details">
        <x-form-row>
            <x-form-field label="Session Name" name="name" :required="true">
                <x-input name="name" :value="$session->name" :required="true"/>
            </x-form-field>
            <x-form-field label="Session Name (BS)" name="name_bs">
                <x-input name="name_bs" :value="$session->name_bs" placeholder="e.g. २०८१/२०८२"/>
            </x-form-field>
            <x-form-field label="Start Date (BS)" name="start_date" :required="true">
                <x-bs-date-picker name="start_date" :value="bsDate($session->start_date)" :required="true"/>
            </x-form-field>
            <x-form-field label="End Date (BS)" name="end_date" :required="true">
                <x-bs-date-picker name="end_date" :value="bsDate($session->end_date)" :required="true"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Status & Options">
        <x-form-row>
            <x-form-field label="Status" name="status">
                <select name="status" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                    <option value="upcoming" {{ old('status', $session->status) === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    <option value="active" {{ old('status', $session->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="ended" {{ old('status', $session->status) === 'ended' ? 'selected' : '' }} disabled>Ended (locked)</option>
                </select>
            </x-form-field>
            <x-form-field label="Notes" name="notes" span="full">
                <textarea name="notes" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100" placeholder="Optional session notes">{{ old('notes', $session->notes) }}</textarea>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    @if($session->is_active)
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
            <p class="text-xs font-bold text-amber-800">This is the currently active session.</p>
            <p class="mt-0.5 text-xs text-amber-700">Changing the status to "Upcoming" will deactivate it. Use "End Session" from the sessions page to properly end with student promotion.</p>
        </div>
    @endif

    <div class="flex items-center gap-3">
        <x-btn type="submit">Save Changes</x-btn>
        <x-btn href="{{ route('admin.academic-sessions.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
</x-form-layout>
@endsection
