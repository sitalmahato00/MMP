@extends('layouts.app')
@section('title', 'Enroll Student')

@section('content')
<x-page-header title="Enroll Student" subtitle="Add a new student to the college system."
               back="{{ route('admin.students.index') }}"/>

<form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data"
      x-data="{
          createParent: {{ old('create_parent') ? 'true' : 'false' }},
          selectedProgram: '{{ old('program_id') }}',
          programs: {{ $programs->map(fn($p) => ['id'=>$p->id,'name'=>$p->name,'dept'=>$p->department?->name,'semesters'=>$p->total_semesters])->toJson() }},
          programInfo() { return this.programs.find(p => p.id == this.selectedProgram) ?? null; }
      }"
      class="w-full mx-auto space-y-6">
    @csrf

    {{-- ── 1. PERSONAL INFORMATION ──────────────────────── --}}
    <x-form-section title="Personal Information" subtitle="Student's identity, contact details, and login credentials.">
        <x-form-row>
            <x-form-field label="Full Name" name="name" :required="true">
                <x-input name="name" :value="old('name')" :required="true" placeholder="Full legal name"/>
            </x-form-field>
            <x-form-field label="Email Address" name="email" :required="true">
                <x-input name="email" type="email" :value="old('email')" :required="true" placeholder="student@example.com"/>
            </x-form-field>
            <x-form-field label="Phone Number" name="phone">
                <x-input name="phone" :value="old('phone')" placeholder="98XXXXXXXX"/>
            </x-form-field>
            <x-form-field label="Password" name="password" :required="true">
                <x-input name="password" type="password" :required="true" placeholder="Min. 8 characters"/>
            </x-form-field>
            <x-form-field label="Gender" name="gender">
                <x-select name="gender">
                    <option value="">Select Gender</option>
                    <option value="male"   @selected(old('gender') === 'male')>Male</option>
                    <option value="female" @selected(old('gender') === 'female')>Female</option>
                    <option value="other"  @selected(old('gender') === 'other')>Other</option>
                </x-select>
            </x-form-field>
            <x-form-field label="Date of Birth (BS)" name="dob">
                <x-bs-date-picker name="dob" :value="old('dob')"/>
            </x-form-field>
            <x-form-field label="Permanent Address" name="address" span="full">
                <x-textarea name="address" rows="2" placeholder="District, Province, Country">{{ old('address') }}</x-textarea>
            </x-form-field>
            <x-form-field label="Profile Photo" name="avatar" span="full">
                <x-file-input name="avatar" accept="image/*" label="Upload photo (max 2 MB)"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- ── 2. ENROLLMENT DETAILS ────────────────────────── --}}
    <x-form-section title="Enrollment Details" subtitle="Academic program, semester, and session assignment.">
        @if($currentSession)
            <div class="mb-4 flex items-center gap-3 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><span class="font-semibold">Active session:</span> {{ $currentSession->name }} — assigned automatically on enrolment.</span>
            </div>
        @else
            <div class="mb-4 flex items-center gap-3 rounded-xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span><span class="font-semibold">No active session.</span> Activate an academic session before enrolling students.</span>
            </div>
        @endif
        <x-form-row>
            <x-form-field label="Student ID" name="student_no" :required="true">
                <x-input name="student_no" :value="old('student_no')" :required="true" placeholder="e.g. S-2081-001"/>
            </x-form-field>
            <x-form-field label="Registration Number" name="registration_number">
                <x-input name="registration_number" :value="old('registration_number')" placeholder="CTEVT reg. number (optional)"/>
            </x-form-field>
            <x-form-field label="Program" name="program_id" :required="true">
                <x-select name="program_id" :required="true" x-model="selectedProgram">
                    <option value="">Select Program</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" @selected(old('program_id') == $program->id)>
                            {{ $program->name }}
                        </option>
                    @endforeach
                </x-select>
                <template x-if="programInfo()">
                    <p class="mt-1.5 text-xs text-slate-500"
                       x-text="programInfo().semesters + '-semester program · ' + programInfo().dept"></p>
                </template>
            </x-form-field>
            <x-form-field label="Current Semester" name="current_semester" :required="true">
                <x-select name="current_semester" :required="true">
                    <option value="">Select Semester</option>
                    @for($i = 1; $i <= 6; $i++)
                        <option value="{{ $i }}" @selected(old('current_semester', 1) == $i)>Semester {{ $i }}</option>
                    @endfor
                </x-select>
            </x-form-field>
            <x-form-field label="Section" name="section">
                <x-input name="section" :value="old('section')" placeholder="e.g. A, B"/>
            </x-form-field>
            <x-form-field label="Batch / Year" name="batch">
                <x-input name="batch" :value="old('batch')" placeholder="e.g. 2081"/>
            </x-form-field>
            <x-form-field label="Admission Date (BS)" name="admission_date">
                <x-bs-date-picker name="admission_date" :value="old('admission_date')"/>
            </x-form-field>
            <x-form-field label="Initial Status" name="status">
                <x-select name="status">
                    <option value="active"   @selected(old('status', 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                    <option value="suspended" @selected(old('status') === 'suspended')>Suspended</option>
                </x-select>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- ── 3. HEALTH & EMERGENCY CONTACT ───────────────── --}}
    <x-form-section title="Health & Emergency Contact" subtitle="Stored on the student record for quick access.">
        <x-form-row>
            <x-form-field label="Blood Group" name="blood_group">
                <x-select name="blood_group">
                    <option value="">Not specified</option>
                    @foreach(['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg)
                        <option value="{{ $bg }}" @selected(old('blood_group') === $bg)>{{ $bg }}</option>
                    @endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Emergency Contact Name" name="guardian_name">
                <x-input name="guardian_name" :value="old('guardian_name')" placeholder="Parent or guardian name"/>
            </x-form-field>
            <x-form-field label="Emergency Contact Phone" name="guardian_phone">
                <x-input name="guardian_phone" :value="old('guardian_phone')" placeholder="98XXXXXXXX"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- ── 4. PARENT / GUARDIAN PORTAL ACCOUNT ─────────── --}}
    <x-form-section title="Parent / Guardian Account" subtitle="Optionally create a parent portal account and link it to this student.">

        {{-- Toggle checkbox --}}
        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4 transition hover:bg-slate-50">
            <input type="checkbox" name="create_parent" value="1"
                   x-model="createParent"
                   class="mt-0.5 h-4 w-4 flex-shrink-0 rounded border-slate-300 text-[#8B0000] focus:ring-[#8B0000]"/>
            <div>
                <span class="font-semibold text-slate-800 text-sm">Auto-create a parent / guardian account</span>
                <p class="mt-0.5 text-xs text-slate-500 leading-relaxed">
                    A login account will be created for the parent and linked to this student.
                    They can view attendance, marks, and notices via the parent portal.
                    The student's password is used as the initial parent password.
                </p>
            </div>
        </label>

        {{-- Parent fields — shown when checkbox is checked --}}
        <div x-show="createParent"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-1"
             class="mt-5">
            <x-form-row>
                <x-form-field label="Parent Full Name" name="parent_name" :required="true">
                    <x-input name="parent_name" :value="old('parent_name')" placeholder="Full name"/>
                </x-form-field>
                <x-form-field label="Parent Email" name="parent_email" :required="true">
                    <x-input name="parent_email" type="email" :value="old('parent_email')" placeholder="parent@example.com"/>
                </x-form-field>
                <x-form-field label="Parent Phone" name="parent_phone">
                    <x-input name="parent_phone" :value="old('parent_phone')" placeholder="98XXXXXXXX"/>
                </x-form-field>
                <x-form-field label="Relation to Student" name="parent_relation">
                    <x-select name="parent_relation">
                        <option value="father"   @selected(old('parent_relation') === 'father')>Father</option>
                        <option value="mother"   @selected(old('parent_relation') === 'mother')>Mother</option>
                        <option value="guardian" @selected(old('parent_relation') === 'guardian')>Guardian</option>
                        <option value="uncle"    @selected(old('parent_relation') === 'uncle')>Uncle / Aunt</option>
                        <option value="sibling"  @selected(old('parent_relation') === 'sibling')>Sibling</option>
                        <option value="other"    @selected(old('parent_relation') === 'other')>Other</option>
                    </x-select>
                </x-form-field>
                <x-form-field label="Occupation" name="parent_occupation">
                    <x-input name="parent_occupation" :value="old('parent_occupation')" placeholder="e.g. Teacher, Farmer, Business"/>
                </x-form-field>
            </x-form-row>
        </div>
    </x-form-section>

    <div class="flex items-center gap-3 pb-6">
        <x-btn type="submit">Enroll Student</x-btn>
        <x-btn href="{{ route('admin.students.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection

