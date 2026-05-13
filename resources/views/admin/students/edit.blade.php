@extends('layouts.app')
@section('title', 'Edit Student')

@section('content')
<x-page-header title="Edit Student" :subtitle="$student->user->name"
               back="{{ route('admin.students.index') }}"/>

<form method="POST" action="{{ route('admin.students.update', $student) }}" enctype="multipart/form-data"
      x-data="{
          tab: 'personal',
          selectedProgram: '{{ $student->program_id }}',
          programs: {{ $programs->map(fn($p) => ['id'=>$p->id,'name'=>$p->name,'dept'=>$p->department?->name,'semesters'=>$p->total_semesters])->toJson() }},
          programInfo() { return this.programs.find(p => p.id == this.selectedProgram) ?? null; }
      }">
    @csrf @method('PUT')

    {{-- Tab Navigation --}}
    <div class="mb-6 flex gap-1 rounded-xl bg-slate-100 p-1">
        <button type="button" @click="tab='personal'"
                :class="tab==='personal' ? 'bg-white shadow-sm text-slate-900 font-semibold' : 'text-slate-500 hover:text-slate-700'"
                class="flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition">Personal</button>
        <button type="button" @click="tab='enrollment'"
                :class="tab==='enrollment' ? 'bg-white shadow-sm text-slate-900 font-semibold' : 'text-slate-500 hover:text-slate-700'"
                class="flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition">Enrollment</button>
        <button type="button" @click="tab='health'"
                :class="tab==='health' ? 'bg-white shadow-sm text-slate-900 font-semibold' : 'text-slate-500 hover:text-slate-700'"
                class="flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition">Health &amp; Emergency</button>
        @if($student->parents->isNotEmpty())
        <button type="button" @click="tab='parents'"
                :class="tab==='parents' ? 'bg-white shadow-sm text-slate-900 font-semibold' : 'text-slate-500 hover:text-slate-700'"
                class="flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition">Linked Parents</button>
        @endif
    </div>

    {{-- Tab: Personal --}}
    <div x-show="tab==='personal'" x-transition>
        <x-form-section title="Personal Information" subtitle="Student's identity, contact details, and login credentials.">
            <x-form-row>
                <x-form-field label="Full Name" name="name" :required="true">
                    <x-input name="name" :value="old('name', $student->user->name)" :required="true"/>
                </x-form-field>
                <x-form-field label="Email Address" name="email" :required="true">
                    <x-input name="email" type="email" :value="old('email', $student->user->email)" :required="true"/>
                </x-form-field>
                <x-form-field label="Phone Number" name="phone">
                    <x-input name="phone" :value="old('phone', $student->user->phone)" placeholder="98XXXXXXXX"/>
                </x-form-field>
                <x-form-field label="Gender" name="gender">
                    <x-select name="gender">
                        <option value="">Select Gender</option>
                        <option value="male"   @selected(old('gender', $student->user->gender) === 'male')>Male</option>
                        <option value="female" @selected(old('gender', $student->user->gender) === 'female')>Female</option>
                        <option value="other"  @selected(old('gender', $student->user->gender) === 'other')>Other</option>
                    </x-select>
                </x-form-field>
                <x-form-field label="Date of Birth (BS)" name="dob">
                    <x-bs-date-picker name="dob" :value="old('dob', $student->user->dob ? bsDate($student->user->dob) : '')"/>
                </x-form-field>
                <x-form-field label="Permanent Address" name="address" span="full">
                    <x-textarea name="address" rows="2">{{ old('address', $student->user->address) }}</x-textarea>
                </x-form-field>
                <x-form-field label="Profile Photo" name="avatar" span="full">
                    <x-file-input name="avatar" accept="image/*" label="Upload new photo (leave blank to keep current)" :current="$student->user->avatar"/>
                </x-form-field>
            </x-form-row>
        </x-form-section>
        <div class="mt-4 flex gap-3">
            <button type="button" @click="tab='enrollment'" class="rounded-xl bg-[#8B0000] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#7a0000] transition">Next: Enrollment →</button>
        </div>
    </div>

    {{-- Tab: Enrollment --}}
    <div x-show="tab==='enrollment'" x-transition>
        <x-form-section title="Enrollment Details" subtitle="Academic program, semester, and administrative codes.">
            <p class="text-xs text-slate-500 -mt-1 mb-4">
                Session: <span class="font-semibold text-slate-700">{{ $student->academicSession?->name ?? '—' }}</span>
                &nbsp;·&nbsp; Department derived from selected program.
            </p>
            <x-form-row>
                <x-form-field label="Student ID" name="student_no" :required="true">
                    <x-input name="student_no" :value="old('student_no', $student->student_no)" :required="true" placeholder="e.g. S-2081-001"/>
                </x-form-field>
                <x-form-field label="Registration Number" name="registration_number">
                    <x-input name="registration_number" :value="old('registration_number', $student->registration_number)" placeholder="CTEVT reg. number"/>
                </x-form-field>
                <x-form-field label="Program" name="program_id" :required="true">
                    <x-select name="program_id" :required="true" x-model="selectedProgram">
                        <option value="">Select Program</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" @selected(old('program_id', $student->program_id) == $program->id)>
                                {{ $program->name }} — {{ $program->department?->name }}
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
                        @for($i = 1; $i <= 6; $i++)
                            <option value="{{ $i }}" @selected(old('current_semester', $student->current_semester) == $i)>Semester {{ $i }}</option>
                        @endfor
                    </x-select>
                </x-form-field>
                <x-form-field label="Section" name="section">
                    <x-input name="section" :value="old('section', $student->section)" placeholder="e.g. A, B"/>
                </x-form-field>
                <x-form-field label="Batch / Year" name="batch">
                    <x-input name="batch" :value="old('batch', $student->batch)" placeholder="e.g. 2081"/>
                </x-form-field>
                <x-form-field label="Admission Date (BS)" name="admission_date">
                    <x-bs-date-picker name="admission_date" :value="old('admission_date', $student->admission_date ? bsDate($student->admission_date) : '')"/>
                </x-form-field>
                <x-form-field label="Status" name="status">
                    <x-select name="status">
                        <option value="active"    @selected(old('status', $student->status) === 'active')>Active</option>
                        <option value="inactive"  @selected(old('status', $student->status) === 'inactive')>Inactive</option>
                        <option value="suspended" @selected(old('status', $student->status) === 'suspended')>Suspended</option>
                        <option value="dropped"   @selected(old('status', $student->status) === 'dropped')>Dropped</option>
                        <option value="graduated" @selected(old('status', $student->status) === 'graduated')>Graduated / Alumni</option>
                    </x-select>
                </x-form-field>
            </x-form-row>
        </x-form-section>
        <div class="mt-4 flex gap-3">
            <button type="button" @click="tab='personal'" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">← Back</button>
            <button type="button" @click="tab='health'" class="rounded-xl bg-[#8B0000] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#7a0000] transition">Next: Health &amp; Emergency →</button>
        </div>
    </div>

    {{-- Tab: Health & Emergency --}}
    <div x-show="tab==='health'" x-transition>
        <x-form-section title="Health &amp; Emergency Contact">
            <x-form-row>
                <x-form-field label="Blood Group" name="blood_group">
                    <x-select name="blood_group">
                        <option value="">Not specified</option>
                        @foreach(['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg)
                            <option value="{{ $bg }}" @selected(old('blood_group', $student->blood_group) === $bg)>{{ $bg }}</option>
                        @endforeach
                    </x-select>
                </x-form-field>
                <x-form-field label="Emergency Contact Name" name="guardian_name">
                    <x-input name="guardian_name" :value="old('guardian_name', $student->guardian_name)" placeholder="Parent or guardian name"/>
                </x-form-field>
                <x-form-field label="Emergency Contact Phone" name="guardian_phone">
                    <x-input name="guardian_phone" :value="old('guardian_phone', $student->guardian_phone)" placeholder="98XXXXXXXX"/>
                </x-form-field>
            </x-form-row>
        </x-form-section>
        <div class="mt-6 flex items-center gap-3">
            <button type="button" @click="tab='enrollment'" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">← Back</button>
            <x-btn type="submit">Save Changes</x-btn>
            <x-btn href="{{ route('admin.students.index') }}" variant="secondary">Cancel</x-btn>
        </div>
    </div>

    {{-- Tab: Linked Parents (read-only) --}}
    @if($student->parents->isNotEmpty())
    <div x-show="tab==='parents'" x-transition>
        <x-form-section title="Linked Parent Accounts" subtitle="Manage parent links from the Parents section.">
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach($student->parents as $parent)
                <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-sm font-black text-white">
                        {{ strtoupper(substr($parent->user?->name ?? 'P', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $parent->user?->name }}</p>
                        <p class="text-xs text-slate-500">{{ ucfirst($parent->relation_to_student ?? 'Parent') }} · {{ $parent->user?->email }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </x-form-section>
        <div class="mt-4 flex gap-3">
            <button type="button" @click="tab='health'" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">← Back</button>
            <x-btn type="submit">Save Changes</x-btn>
        </div>
    </div>
    @endif

</form>
@endsection
