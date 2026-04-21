@extends('layouts.app')

@section('title', 'Create Timetable')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <x-page-header 
        title="Create Timetable" 
        :subtitle="$department->name . ' — Set up a new class routine for your department'"
        back="{{ route('hod.timetable.index') }}"/>

    {{-- Create Form --}}
    <form method="POST" action="{{ route('hod.timetable.store') }}">
        @csrf
        
        <x-form-section 
            title="Timetable Information" 
            subtitle="Enter the basic details of the timetable">
            
            <x-form-row>
                <x-form-field label="Program" name="program_id" required>
                    <x-select name="program_id" required>
                        <option value="">Select Program</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" @selected(old('program_id') == $program->id)>
                                {{ $program->name }}
                            </option>
                        @endforeach
                    </x-select>
                </x-form-field>

                <x-form-field label="Semester" name="semester" required>
                    <x-select name="semester" required>
                        <option value="">Select Semester</option>
                        @for($i = 1; $i <= 8; $i++)
                            <option value="{{ $i }}" @selected(old('semester') == $i)>Semester {{ $i }}</option>
                        @endfor
                    </x-select>
                </x-form-field>
            </x-form-row>

            <x-form-row>
                <x-form-field label="Section (Optional)" name="section">
                    <x-input 
                        type="text" 
                        name="section" 
                        :value="old('section')" 
                        placeholder="e.g., A, B, Morning, Evening"/>
                    <p class="mt-1 text-xs text-slate-500">Leave empty if not applicable</p>
                </x-form-field>

                <x-form-field label="Academic Session" name="academic_session_id" required>
                    <x-select name="academic_session_id" required>
                        <option value="">Select Academic Session</option>
                        @foreach($academicSessions as $session)
                            <option value="{{ $session->id }}" @selected(old('academic_session_id') == $session->id)>
                                {{ $session->name }}
                            </option>
                        @endforeach
                    </x-select>
                </x-form-field>
            </x-form-row>

            <x-form-row>
                <x-form-field label="Effective From (BS Date)" name="effective_from" required>
                    <x-bs-date-picker 
                        name="effective_from" 
                        :value="old('effective_from', bsDate(now(), 'Y-m-d'))"
                        adName="effective_from_ad"
                        required />
                    <p class="mt-1 text-xs text-slate-500">Date when this timetable becomes active</p>
                </x-form-field>

                <x-form-field label="Status" name="is_active">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" 
                               @checked(old('is_active', true))
                               class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="text-sm font-medium text-slate-700">Set as Active Timetable</span>
                            <p class="text-xs text-slate-500">This will deactivate other timetables for the same program/semester</p>
                        </div>
                    </label>
                </x-form-field>
            </x-form-row>

            <div class="rounded-lg bg-blue-50 border border-blue-200 p-4">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm text-blue-800">
                        <p class="font-semibold mb-1">Next Steps</p>
                        <p>After creating the timetable, you'll be able to add time slots, assign subjects and teachers, and configure the weekly schedule.</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 mt-6">
                <x-btn type="submit">Create Timetable</x-btn>
                <a href="{{ route('hod.timetable.index') }}" 
                   class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Cancel
                </a>
            </div>
        </x-form-section>
    </form>
</div>
@endsection
