@extends('layouts.app')

@section('title', 'Edit Subject')

@section('content')
<div x-data="subjectEditForm()" class="space-y-6">
    {{-- Header --}}
    <x-page-header 
        title="Edit Subject" 
        :subtitle="$subject->code . ' - ' . $subject->name"
        back="{{ route('hod.subjects.show', $subject) }}"/>

    {{-- Edit Form --}}
    <form method="POST" action="{{ route('hod.subjects.update', $subject) }}">
        @csrf
        @method('PUT')

        <x-form-section 
            title="Subject Information" 
            subtitle="Update the basic details of the subject">
            
            <x-form-row>
                <x-form-field label="Program" name="program_id" required>
                    <x-select name="program_id" required>
                        <option value="">Select Program</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" @selected(old('program_id', $subject->program_id) == $program->id)>
                                {{ $program->code }} - {{ $program->name }}
                            </option>
                        @endforeach
                    </x-select>
                </x-form-field>

                <x-form-field label="Semester" name="semester" required>
                    <x-select name="semester" required>
                        <option value="">Select Semester</option>
                        @for($i = 1; $i <= 8; $i++)
                            <option value="{{ $i }}" @selected(old('semester', $subject->semester) == $i)>Semester {{ $i }}</option>
                        @endfor
                    </x-select>
                </x-form-field>
            </x-form-row>

            <x-form-row>
                <x-form-field label="Subject Name" name="name" required>
                    <x-input 
                        type="text" 
                        name="name" 
                        :value="old('name', $subject->name)" 
                        required
                        placeholder="e.g., Computer Programming"/>
                </x-form-field>

                <x-form-field label="Subject Code" name="code" required>
                    <x-input 
                        type="text" 
                        name="code" 
                        :value="old('code', $subject->code)" 
                        required
                        placeholder="e.g., CSC101"/>
                </x-form-field>
            </x-form-row>

            <x-form-row>
                <x-form-field label="Subject Type" name="type" required>
                    <x-select name="type" x-model="subjectType" required>
                        <option value="">Select Type</option>
                        <option value="theory">Theory Only</option>
                        <option value="practical">Practical Only</option>
                        <option value="both">Both Theory & Practical</option>
                    </x-select>
                </x-form-field>

                <x-form-field label="Credit Hours" name="credit_hours">
                    <x-input 
                        type="number" 
                        name="credit_hours" 
                        :value="old('credit_hours', $subject->credit_hours)" 
                        min="0"
                        placeholder="e.g., 3"/>
                </x-form-field>
            </x-form-row>

            <x-form-row>
                <x-form-field label="Status" name="is_active">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" 
                               @checked(old('is_active', $subject->is_active))
                               class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-slate-700">Active</span>
                    </label>
                </x-form-field>
            </x-form-row>
        </x-form-section>

        {{-- CTEVT Marking Scheme - Dynamic based on subject type --}}
        <x-form-section 
            title="CTEVT Marking Scheme" 
            subtitle="Set the full marks and pass marks for theory and practical components">
            
            {{-- Theory Marks - Show when type is 'theory' or 'both' --}}
            <div x-show="subjectType === 'theory' || subjectType === 'both'" class="space-y-4">
                <h3 class="text-sm font-semibold text-slate-900 border-b border-slate-200 pb-2">Theory Component</h3>
                
                <x-form-row>
                    <x-form-field label="Internal Theory Full Marks" name="full_marks_internal_theory">
                        <x-input 
                            type="number" 
                            name="full_marks_internal_theory" 
                            :value="old('full_marks_internal_theory', $subject->full_marks_internal_theory)" 
                            step="0.01" 
                            min="0"
                            placeholder="e.g., 20"/>
                    </x-form-field>

                    <x-form-field label="Internal Theory Pass Marks" name="pass_marks_internal_theory">
                        <x-input 
                            type="number" 
                            name="pass_marks_internal_theory" 
                            :value="old('pass_marks_internal_theory', $subject->pass_marks_internal_theory)" 
                            step="0.01" 
                            min="0"
                            placeholder="e.g., 8"/>
                    </x-form-field>
                </x-form-row>

                <x-form-row>
                    <x-form-field label="External Theory Full Marks" name="full_marks_external_theory">
                        <x-input 
                            type="number" 
                            name="full_marks_external_theory" 
                            :value="old('full_marks_external_theory', $subject->full_marks_external_theory)" 
                            step="0.01" 
                            min="0"
                            placeholder="e.g., 80"/>
                    </x-form-field>

                    <x-form-field label="External Theory Pass Marks" name="pass_marks_external_theory">
                        <x-input 
                            type="number" 
                            name="pass_marks_external_theory" 
                            :value="old('pass_marks_external_theory', $subject->pass_marks_external_theory)" 
                            step="0.01" 
                            min="0"
                            placeholder="e.g., 32"/>
                    </x-form-field>
                </x-form-row>
            </div>

            {{-- Practical Marks - Show when type is 'practical' or 'both' --}}
            <div x-show="subjectType === 'practical' || subjectType === 'both'" class="space-y-4" :class="{'mt-6': subjectType === 'both'}">
                <h3 class="text-sm font-semibold text-slate-900 border-b border-slate-200 pb-2">Practical Component</h3>
                
                <x-form-row>
                    <x-form-field label="Internal Practical Full Marks" name="full_marks_internal_practical">
                        <x-input 
                            type="number" 
                            name="full_marks_internal_practical" 
                            :value="old('full_marks_internal_practical', $subject->full_marks_internal_practical)" 
                            step="0.01" 
                            min="0"
                            placeholder="e.g., 25"/>
                    </x-form-field>

                    <x-form-field label="Internal Practical Pass Marks" name="pass_marks_internal_practical">
                        <x-input 
                            type="number" 
                            name="pass_marks_internal_practical" 
                            :value="old('pass_marks_internal_practical', $subject->pass_marks_internal_practical)" 
                            step="0.01" 
                            min="0"
                            placeholder="e.g., 10"/>
                    </x-form-field>
                </x-form-row>

                <x-form-row>
                    <x-form-field label="External Practical Full Marks" name="full_marks_external_practical">
                        <x-input 
                            type="number" 
                            name="full_marks_external_practical" 
                            :value="old('full_marks_external_practical', $subject->full_marks_external_practical)" 
                            step="0.01" 
                            min="0"
                            placeholder="e.g., 75"/>
                    </x-form-field>

                    <x-form-field label="External Practical Pass Marks" name="pass_marks_external_practical">
                        <x-input 
                            type="number" 
                            name="pass_marks_external_practical" 
                            :value="old('pass_marks_external_practical', $subject->pass_marks_external_practical)" 
                            step="0.01" 
                            min="0"
                            placeholder="e.g., 30"/>
                    </x-form-field>
                </x-form-row>
            </div>

            <div x-show="subjectType" class="rounded-lg bg-blue-50 p-4 text-sm text-blue-800 mt-4">
                <p class="font-medium">Note:</p>
                <ul class="mt-2 list-disc list-inside space-y-1">
                    <li>These marks will be used as defaults for all CTEVT exams</li>
                    <li>Individual exam marking schemes can override these defaults</li>
                    <li>Changes will affect future mark validations immediately</li>
                </ul>
            </div>

            <div class="flex items-center gap-3 mt-6">
                <x-btn type="submit">Update Subject</x-btn>
                <a href="{{ route('hod.subjects.show', $subject) }}" 
                   class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Cancel
                </a>
            </div>
        </x-form-section>
    </form>

    {{-- Teacher Assignment Section --}}
    <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Assigned Teachers</h2>
                    <p class="text-xs text-slate-500">
                        @if($currentSession)
                            Academic Session: {{ $currentSession->name }}
                        @else
                            No active academic session
                        @endif
                    </p>
                </div>
                @if($currentSession)
                    <button type="button" 
                            onclick="document.getElementById('assignTeacherForm').classList.toggle('hidden')"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 transition-colors">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Assign Teacher
                    </button>
                @endif
            </div>
        </div>

        {{-- Assign Teacher Form --}}
        @if($currentSession)
            <div id="assignTeacherForm" class="hidden border-b border-slate-100 bg-slate-50 p-5">
                <form method="POST" action="{{ route('hod.subjects.assign-teacher', $subject) }}">
                    @csrf
                    <div class="grid gap-4 md:grid-cols-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Teacher *</label>
                            <select name="teacher_id" required class="w-full rounded-lg border-slate-300 text-sm">
                                <option value="">Select Teacher</option>
                                @foreach($availableTeachers as $teacher)
                                    <option value="{{ $teacher->id }}">
                                        {{ $teacher->user->name }} ({{ $teacher->employee_id ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Role *</label>
                            <input type="text" name="role" required placeholder="e.g., Theory Teacher, Lab Tech" 
                                   class="w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Section (Optional)</label>
                            <input type="text" name="section" placeholder="e.g., A, B, Morning" 
                                   class="w-full rounded-lg border-slate-300 text-sm">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" 
                                    class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                Assign
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @endif

        {{-- Assigned Teachers List --}}
        <div class="p-5">
            @if($assignedTeachers->isEmpty())
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <p class="mt-2 text-sm text-slate-500">No teachers assigned yet</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($assignedTeachers as $teacher)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 p-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $teacher->user->name }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ $teacher->employee_id ?? 'N/A' }}
                                        @if($teacher->pivot->role)
                                            • <span class="font-medium text-blue-600">{{ $teacher->pivot->role }}</span>
                                        @endif
                                        @if($teacher->pivot->section)
                                            • Section: {{ $teacher->pivot->section }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('hod.subjects.remove-teacher', [$subject, $teacher]) }}" 
                                  onsubmit="return confirm('Remove this teacher assignment?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="inline-flex items-center gap-1 rounded-md bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100 transition-colors">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Remove
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>

<script>
function subjectEditForm() {
    return {
        subjectType: '{{ old("type", $subject->type) }}'
    }
}
</script>
@endsection
