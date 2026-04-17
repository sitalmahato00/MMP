@extends('layouts.app')
@section('title', 'Edit Exam')

@section('content')
<x-page-header title="Edit Exam" :subtitle="$exam->name"
               back="{{ route('admin.exams.index') }}"/>

<form method="POST" action="{{ route('admin.exams.update', $exam) }}" class="max-w-2xl space-y-6">
    @csrf @method('PUT')
    <x-form-section title="Exam Details">
        <x-form-row>
            <x-form-field label="Exam Name" name="name" :required="true" span="full">
                <x-input name="name" :value="$exam->name" :required="true"/>
            </x-form-field>
            <x-form-field label="Academic Session" name="academic_session_id" :required="true">
                <x-select name="academic_session_id" :required="true">
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}" {{ $exam->academic_session_id == $session->id ? 'selected' : '' }}>
                            {{ $session->name }}
                        </option>
                    @endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Start Date (BS)" name="start_date">
                <x-bs-date-picker name="start_date" :value="$exam->start_date ? bsDate($exam->start_date) : ''"/>
            </x-form-field>
            <x-form-field label="End Date (BS)" name="end_date">
                <x-bs-date-picker name="end_date" :value="$exam->end_date ? bsDate($exam->end_date) : ''"/>
            </x-form-field>
            <x-form-field label="Open Mark Entry" name="marks_open" span="full">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="marks_open" value="1" {{ $exam->marks_open ? 'checked' : '' }}
                           class="w-4 h-4 accent-[#8B0000] rounded">
                    <span class="text-sm text-gray-600">Allow teachers to enter marks</span>
                </label>
            </x-form-field>
        </x-form-row>
    </x-form-section>
    <div class="flex items-center gap-3">
        <x-btn type="submit">Save Changes</x-btn>
        <x-btn href="{{ route('admin.exams.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
