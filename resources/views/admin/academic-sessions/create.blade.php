@extends('layouts.app')
@section('title', 'New Academic Session')

@section('content')
<x-form-layout title="New Academic Session" subtitle="Define a new academic year period. Activating it will close the current session and move final-semester students to alumni automatically." back="{{ route('admin.academic-sessions.index') }}">
    <x-slot name="breadcrumb">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.academic-sessions.index') }}" class="hover:text-slate-900">Academic Sessions</a>
            <span>/</span>
            <span class="font-semibold text-slate-900">New Session</span>
        </nav>
    </x-slot>

    <x-slot name="sidebar">
        <x-form-sidebar />
    </x-slot>

    <form method="POST" action="{{ route('admin.academic-sessions.store') }}" class="max-w-2xl space-y-6">
    @csrf
    <x-form-section title="Session Details">
        <x-form-row>
            <x-form-field label="Session Name" name="name" :required="true">
                <x-input name="name" :required="true" placeholder="e.g. 2081/2082"/>
            </x-form-field>
            <x-form-field label="Session Name (BS)" name="name_bs">
                <x-input name="name_bs" placeholder="e.g. २०८१/२०८२"/>
            </x-form-field>
            <x-form-field label="Start Date (BS)" name="start_date" :required="true">
                <x-bs-date-picker name="start_date" :required="true"/>
            </x-form-field>
            <x-form-field label="End Date (BS)" name="end_date" :required="true">
                <x-bs-date-picker name="end_date" :required="true"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Status & Options">
        <x-form-row>
            <x-form-field label="Initial Status" name="status">
                <select name="status" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100">
                    <option value="upcoming" {{ old('status', 'upcoming') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                </select>
                <p class="mt-1 text-xs text-slate-500">Active sessions will close the current active session and promote students.</p>
            </x-form-field>
            <x-form-field label="Notes" name="notes" span="full">
                <textarea name="notes" rows="3" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100" placeholder="Optional session notes, e.g. special circumstances or policy changes">{{ old('notes') }}</textarea>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <div class="flex items-center gap-3">
        <x-btn type="submit">Create Session</x-btn>
        <x-btn href="{{ route('admin.academic-sessions.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
</x-form-layout>
@endsection
