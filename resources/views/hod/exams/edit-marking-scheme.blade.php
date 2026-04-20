@extends('layouts.app')

@section('title', 'Edit Marking Scheme - ' . $exam->name)

@section('content')
<x-page-header 
    :title="'Edit Marking Scheme - ' . $exam->name" 
    :subtitle="$exam->category_label . ' • ' . bsDate($exam->start_date, 'F d, Y')"
    back="{{ route('hod.exams.marks', ['exam_id' => $exam->id]) }}"/>

<form method="POST" action="{{ route('hod.exams.update-marking-scheme', $exam) }}" class="max-w-6xl space-y-6">
    @csrf
    @method('PUT')

    {{-- ── EXAM INFO ─────────────────────────────────────── --}}
    <x-form-section title="Exam Information" subtitle="CTEVT final exam marking scheme configuration.">
        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-blue-900">{{ $exam->name }}</p>
                    <p class="text-xs text-blue-700 mt-1">Configure full marks and pass marks for each subject component. These will override the default subject marks for this exam.</p>
                </div>
            </div>
        </div>
    </x-form-section>

    {{-- ── SUBJECTS MARKING SCHEME ──────────────────────── --}}
    <x-form-section title="Subjects Marking Scheme" subtitle="Set full marks and pass marks for each subject component.">
        <div class="space-y-6">
            @foreach($subjects as $index => $subject)
                @php
                    $scheme = $subject->markingScheme;
                @endphp
                <div class="rounded-lg border border-slate-200 p-4">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600 flex-shrink-0">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">{{ $subject->name }}</h3>
                            <p class="text-xs text-slate-500">{{ $subject->code }} • {{ ucfirst($subject->type) }}</p>
                        </div>
                    </div>

                    <input type="hidden" name="subjects[{{ $index }}][subject_id]" value="{{ $subject->id }}">

                    <div class="grid grid-cols-2 gap-6">
                        {{-- Theory Section --}}
                        <div class="space-y-4">
                            <h4 class="text-sm font-medium text-slate-700 border-b border-slate-200 pb-2">Theory Component</h4>
                            
                            <x-form-row>
                                <x-form-field label="Internal Theory Full" name="subjects[{{ $index }}][full_marks_internal_theory]" :required="true">
                                    <x-input type="number" 
                                             name="subjects[{{ $index }}][full_marks_internal_theory]" 
                                             :value="old('subjects.'.$index.'.full_marks_internal_theory', $scheme->full_marks_internal_theory ?? $subject->full_marks_internal_theory ?? 0)" 
                                             :required="true" step="0.01" min="0" placeholder="0"/>
                                </x-form-field>

                                <x-form-field label="Internal Theory Pass" name="subjects[{{ $index }}][pass_marks_internal_theory]" :required="true">
                                    <x-input type="number" 
                                             name="subjects[{{ $index }}][pass_marks_internal_theory]" 
                                             :value="old('subjects.'.$index.'.pass_marks_internal_theory', $scheme->pass_marks_internal_theory ?? $subject->pass_marks_internal_theory ?? 0)" 
                                             :required="true" step="0.01" min="0" placeholder="0"/>
                                </x-form-field>
                            </x-form-row>

                            <x-form-row>
                                <x-form-field label="External Theory Full" name="subjects[{{ $index }}][full_marks_external_theory]" :required="true">
                                    <x-input type="number" 
                                             name="subjects[{{ $index }}][full_marks_external_theory]" 
                                             :value="old('subjects.'.$index.'.full_marks_external_theory', $scheme->full_marks_external_theory ?? $subject->full_marks_external_theory ?? 0)" 
                                             :required="true" step="0.01" min="0" placeholder="0"/>
                                </x-form-field>

                                <x-form-field label="External Theory Pass" name="subjects[{{ $index }}][pass_marks_external_theory]" :required="true">
                                    <x-input type="number" 
                                             name="subjects[{{ $index }}][pass_marks_external_theory]" 
                                             :value="old('subjects.'.$index.'.pass_marks_external_theory', $scheme->pass_marks_external_theory ?? $subject->pass_marks_external_theory ?? 0)" 
                                             :required="true" step="0.01" min="0" placeholder="0"/>
                                </x-form-field>
                            </x-form-row>
                        </div>

                        {{-- Practical Section --}}
                        <div class="space-y-4">
                            <h4 class="text-sm font-medium text-slate-700 border-b border-slate-200 pb-2">Practical Component</h4>
                            
                            <x-form-row>
                                <x-form-field label="Internal Practical Full" name="subjects[{{ $index }}][full_marks_internal_practical]">
                                    <x-input type="number" 
                                             name="subjects[{{ $index }}][full_marks_internal_practical]" 
                                             :value="old('subjects.'.$index.'.full_marks_internal_practical', $scheme->full_marks_internal_practical ?? $subject->full_marks_internal_practical ?? 0)" 
                                             step="0.01" min="0" placeholder="0"/>
                                </x-form-field>

                                <x-form-field label="Internal Practical Pass" name="subjects[{{ $index }}][pass_marks_internal_practical]">
                                    <x-input type="number" 
                                             name="subjects[{{ $index }}][pass_marks_internal_practical]" 
                                             :value="old('subjects.'.$index.'.pass_marks_internal_practical', $scheme->pass_marks_internal_practical ?? $subject->pass_marks_internal_practical ?? 0)" 
                                             step="0.01" min="0" placeholder="0"/>
                                </x-form-field>
                            </x-form-row>

                            <x-form-row>
                                <x-form-field label="External Practical Full" name="subjects[{{ $index }}][full_marks_external_practical]">
                                    <x-input type="number" 
                                             name="subjects[{{ $index }}][full_marks_external_practical]" 
                                             :value="old('subjects.'.$index.'.full_marks_external_practical', $scheme->full_marks_external_practical ?? $subject->full_marks_external_practical ?? 0)" 
                                             step="0.01" min="0" placeholder="0"/>
                                </x-form-field>

                                <x-form-field label="External Practical Pass" name="subjects[{{ $index }}][pass_marks_external_practical]">
                                    <x-input type="number" 
                                             name="subjects[{{ $index }}][pass_marks_external_practical]" 
                                             :value="old('subjects.'.$index.'.pass_marks_external_practical', $scheme->pass_marks_external_practical ?? $subject->pass_marks_external_practical ?? 0)" 
                                             step="0.01" min="0" placeholder="0"/>
                                </x-form-field>
                            </x-form-row>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-form-section>

    <div class="flex items-center gap-3 pb-6">
        <x-btn type="submit">Update Marking Scheme</x-btn>
        <x-btn href="{{ route('hod.exams.marks', ['exam_id' => $exam->id]) }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection