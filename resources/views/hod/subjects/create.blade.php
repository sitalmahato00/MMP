@extends('layouts.app')

@section('title', 'Create Subject')

@section('content')
<div x-data="subjectForm()" class="space-y-6">
    {{-- Header --}}
    <x-page-header 
        title="Create Subject" 
        subtitle="Add a new subject to your department"
        back="{{ route('hod.subjects.index') }}"/>

    {{-- Create Form --}}
    <form method="POST" action="{{ route('hod.subjects.store') }}" enctype="multipart/form-data">
        @csrf

        <x-form-section 
            title="Subject Information" 
            subtitle="Enter the basic details of the subject">
            
            <x-form-row>
                <x-form-field label="Program" name="program_id" required>
                    <x-select name="program_id" required>
                        <option value="">Select Program</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" @selected(old('program_id') == $program->id)>
                                {{ $program->code }} - {{ $program->name }}
                            </option>
                        @endforeach
                    </x-select>
                </x-form-field>

                <x-form-field label="Semester" name="semester">
                    <x-select name="semester">
                        <option value="">Select Semester (Optional)</option>
                        @for($i = 1; $i <= 8; $i++)
                            <option value="{{ $i }}" @selected(old('semester') == $i)>Semester {{ $i }}</option>
                        @endfor
                    </x-select>
                </x-form-field>
            </x-form-row>

            <x-form-row>
                <x-form-field label="Subject Name" name="name" required>
                    <x-input 
                        type="text" 
                        name="name" 
                        :value="old('name')" 
                        required
                        placeholder="e.g., Computer Programming"/>
                </x-form-field>

                <x-form-field label="Subject Code" name="code" required>
                    <x-input 
                        type="text" 
                        name="code" 
                        :value="old('code')" 
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
                        :value="old('credit_hours')" 
                        min="0"
                        placeholder="e.g., 3"/>
                </x-form-field>
            </x-form-row>

            <x-form-row>
                <x-form-field label="Status" name="is_active">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" 
                               @checked(old('is_active', true))
                               class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-slate-700">Active</span>
                    </label>
                </x-form-field>
            </x-form-row>

            <x-form-row>
                <x-form-field label="Subject Details" name="details">
                    <x-textarea
                        name="details"
                        rows="5"
                        placeholder="Add topic outline, module notes, learning outcomes, or other subject details...">{{ old('details') }}</x-textarea>
                </x-form-field>

                <x-form-field label="Subject Syllabus (PDF)" name="syllabus">
                    <input
                        type="file"
                        name="syllabus"
                        accept=".pdf"
                        class="w-full rounded-lg border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800 file:mr-3 file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100">
                    <p class="mt-2 text-xs text-slate-500">Optional. Upload a subject-specific syllabus PDF up to 10 MB.</p>
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
                            :value="old('full_marks_internal_theory', 0)" 
                            step="0.01" 
                            min="0"
                            placeholder="e.g., 20"/>
                    </x-form-field>

                    <x-form-field label="Internal Theory Pass Marks" name="pass_marks_internal_theory">
                        <x-input 
                            type="number" 
                            name="pass_marks_internal_theory" 
                            :value="old('pass_marks_internal_theory', 0)" 
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
                            :value="old('full_marks_external_theory', 0)" 
                            step="0.01" 
                            min="0"
                            placeholder="e.g., 80"/>
                    </x-form-field>

                    <x-form-field label="External Theory Pass Marks" name="pass_marks_external_theory">
                        <x-input 
                            type="number" 
                            name="pass_marks_external_theory" 
                            :value="old('pass_marks_external_theory', 0)" 
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
                            :value="old('full_marks_internal_practical', 0)" 
                            step="0.01" 
                            min="0"
                            placeholder="e.g., 25"/>
                    </x-form-field>

                    <x-form-field label="Internal Practical Pass Marks" name="pass_marks_internal_practical">
                        <x-input 
                            type="number" 
                            name="pass_marks_internal_practical" 
                            :value="old('pass_marks_internal_practical', 0)" 
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
                            :value="old('full_marks_external_practical', 0)" 
                            step="0.01" 
                            min="0"
                            placeholder="e.g., 75"/>
                    </x-form-field>

                    <x-form-field label="External Practical Pass Marks" name="pass_marks_external_practical">
                        <x-input 
                            type="number" 
                            name="pass_marks_external_practical" 
                            :value="old('pass_marks_external_practical', 0)" 
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
                    <li>Ensure pass marks are less than or equal to full marks</li>
                </ul>
            </div>
        </x-form-section>

        {{-- Teacher Assignment Section --}}
        <x-form-section 
            title="Assign Teachers" 
            subtitle="Assign teachers to this subject with their roles">
            
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-600">Add teachers who will teach this subject</p>
                    <button type="button" @click="addTeacher()" 
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 transition-colors">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Teacher
                    </button>
                </div>

                <template x-if="teachers.length === 0">
                    <div class="rounded-lg border-2 border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <p class="mt-2 text-sm text-slate-500">No teachers assigned yet. Click "Add Teacher" to assign.</p>
                    </div>
                </template>

                <div class="space-y-3">
                    <template x-for="(teacher, index) in teachers" :key="index">
                        <div class="rounded-xl border border-slate-200 bg-white p-4">
                            <div class="grid gap-4 md:grid-cols-3">
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">Teacher *</label>
                                    <select x-model="teacher.teacher_id" 
                                            :name="'teachers[' + index + '][teacher_id]'"
                                            required
                                            class="w-full rounded-lg border-slate-300 text-sm">
                                        <option value="">Select Teacher</option>
                                        @foreach($teachers as $t)
                                            <option value="{{ $t->id }}">{{ $t->user->name }} ({{ $t->employee_id ?? 'N/A' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">Role *</label>
                                    <select x-model="teacher.role"
                                            :name="'teachers[' + index + '][role]'"
                                            required
                                            class="w-full rounded-lg border-slate-300 text-sm">
                                        <option value="">Select Role</option>
                                        <option value="Theory Teacher">Theory Teacher</option>
                                        <option value="Lab Tech">Lab Technician</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">Section (Optional)</label>
                                    <div class="flex gap-2">
                                        <input type="text" 
                                               x-model="teacher.section"
                                               :name="'teachers[' + index + '][section]'"
                                               placeholder="e.g., A, B, Morning"
                                               class="w-full rounded-lg border-slate-300 text-sm">
                                        <button type="button" @click="removeTeacher(index)"
                                                class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100 transition-colors">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="rounded-lg bg-amber-50 p-4 text-sm text-amber-800">
                    <p class="font-medium">Teacher Role Examples:</p>
                    <ul class="mt-2 list-disc list-inside space-y-1">
                        <li><strong>Theory Teacher</strong> - For theory classes</li>
                        <li><strong>Lab Tech</strong> - For practical/lab sessions</li>
                        <li><strong>Project Supervisor</strong> - For project-based subjects</li>
                        <li><strong>Tutorial Instructor</strong> - For tutorial sessions</li>
                        <li>You can use any custom role name that fits your needs</li>
                    </ul>
                </div>
            </div>

            <div class="flex items-center gap-3 mt-6">
                <x-btn type="submit">Create Subject</x-btn>
                <a href="{{ route('hod.subjects.index') }}" 
                   class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Cancel
                </a>
            </div>
        </x-form-section>
    </form>
</div>

<script>
function subjectForm() {
    return {
        subjectType: '{{ old("type", "") }}',
        teachers: @json(old('teachers', [])),
        
        addTeacher() {
            this.teachers.push({
                teacher_id: '',
                role: '',
                section: ''
            });
        },
        
        removeTeacher(index) {
            if (confirm('Remove this teacher assignment?')) {
                this.teachers.splice(index, 1);
            }
        }
    }
}
</script>
@endsection
