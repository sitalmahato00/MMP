@extends('layouts.app')

@section('title', 'Edit Assessment Exam')

@section('content')
<x-page-header title="Edit Assessment Exam" subtitle="Update assessment exam details for your department."
               back="{{ route('hod.exams.index') }}"/>

<form method="POST" action="{{ route('hod.exams.update', $exam) }}" 
      class="max-w-4xl space-y-6"
      x-data="{
            programs: {{ json_encode($existingPrograms) }},
            addProgram() {
                this.programs.push({ program_id: '', semester: 1 });
            },
            removeProgram(index) {
                if (this.programs.length > 1) {
                    this.programs.splice(index, 1);
                }
            }
        }">
    @csrf
    @method('PUT')

    {{-- ── 1. BASIC INFORMATION ──────────────────────────── --}}
    <x-form-section title="Basic Information" subtitle="Academic session, assessment number, and exam name.">
        <x-form-row>
            <x-form-field label="Academic Session" name="academic_session_id" :required="true">
                <x-select name="academic_session_id" :required="true">
                    <option value="">Select Session</option>
                    @foreach($activeSessions as $session)
                        <option value="{{ $session->id }}" @selected(old('academic_session_id', $exam->academic_session_id) == $session->id)>
                            {{ $session->name }}
                        </option>
                    @endforeach
                </x-select>
            </x-form-field>

            <x-form-field label="Assessment Number" name="assessment_number" :required="true">
                <x-select name="assessment_number" :required="true">
                    <option value="">Select Assessment</option>
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" @selected(old('assessment_number', $exam->assessment_number) == $i)>
                            Assessment {{ $i }}
                        </option>
                    @endfor
                </x-select>
            </x-form-field>

            <x-form-field label="Exam Name" name="name" :required="true" span="full">
                <x-input name="name" :value="old('name', $exam->name)" :required="true" 
                         placeholder="e.g., Monthly Assessment 1 - Kartik 2081"/>
            </x-form-field>

            <x-form-field label="Full Marks" name="assessment_full_marks" :required="true">
                <x-input type="number" name="assessment_full_marks" :value="old('assessment_full_marks', $exam->assessment_full_marks ?? 100)" 
                         :required="true" step="0.01" min="0" placeholder="100"/>
            </x-form-field>

            <x-form-field label="Pass Marks" name="assessment_pass_marks" :required="true">
                <x-input type="number" name="assessment_pass_marks" :value="old('assessment_pass_marks', $exam->assessment_pass_marks ?? 40)" 
                         :required="true" step="0.01" min="0" placeholder="40"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- ── 2. SCHEDULE ───────────────────────────────────── --}}
    <x-form-section title="Schedule" subtitle="Exam start and end dates in Bikram Sambat.">
        <x-form-row>
            <x-form-field label="Start Date (BS)" name="start_date_bs" :required="true">
                <x-bs-date-picker name="start_date_bs" :value="old('start_date_bs', bsDate($exam->start_date))" :required="true"/>
            </x-form-field>

            <x-form-field label="End Date (BS)" name="end_date_bs">
                <x-bs-date-picker name="end_date_bs" :value="old('end_date_bs', $exam->end_date ? bsDate($exam->end_date) : '')"/>
                <p class="mt-1.5 text-xs text-slate-500">Leave empty if single day exam</p>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- ── 3. PROGRAMS & SEMESTERS ──────────────────────── --}}
    <x-form-section title="Programs & Semesters" subtitle="Select programs and their respective semesters for this exam.">
        <div class="space-y-3">
            <template x-for="(program, index) in programs" :key="index">
                <div class="flex gap-3 items-start">
                    <div class="flex-1">
                        <select :name="`programs[${index}]`" required x-model="program.program_id"
                                class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Select Program</option>
                            @foreach($programs as $prog)
                                <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-40">
                        <select :name="`semesters[${index}]`" required x-model="program.semester"
                                class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="all">All Semesters</option>
                            @for($i = 1; $i <= 6; $i++)
                                <option value="{{ $i }}">Semester {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <button type="button" @click="removeProgram(index)" x-show="programs.length > 1"
                            class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg border border-red-200 text-red-600 transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        <button type="button" @click="addProgram()"
                class="mt-3 inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Another Program
        </button>

        @error('programs')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </x-form-section>

    <div class="flex items-center gap-3 pb-6">
        <x-btn type="submit">Update Assessment Exam</x-btn>
        <x-btn href="{{ route('hod.exams.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
