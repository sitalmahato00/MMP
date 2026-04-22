@extends('layouts.app')
@section('title', 'Add Teacher')

@section('content')
<div class="space-y-5">

<div class="flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900">Add Teacher</h1>
        <p class="mt-0.5 text-sm text-slate-500">{{ $department->name }} Department</p>
    </div>
    <a href="{{ route('hod.teachers.index') }}"
       class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition shadow-sm">
        ← Back to Teachers
    </a>
</div>

<form method="POST" action="{{ route('hod.teachers.store') }}" enctype="multipart/form-data" class="space-y-5" 
      x-data="{ designation: '{{ old('designation') }}' }">
    @csrf

    @if($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 p-4">
        <p class="text-sm font-bold text-red-700 mb-2">Please fix the following errors:</p>
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
            <li class="text-sm text-red-600">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- ── SECTION 1: PERSONAL ── --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-700">Personal Information</h2>
            <p class="text-xs text-slate-400 mt-0.5">Account credentials and personal details.</p>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">

            <div class="sm:col-span-2 flex items-start gap-5">
                <div x-data="{ preview: null }">
                    <div class="relative h-20 w-20">
                        <template x-if="preview">
                            <img :src="preview" class="h-20 w-20 rounded-xl object-cover ring-2 ring-slate-200"/>
                        </template>
                        <template x-if="!preview">
                            <div class="flex h-20 w-20 items-center justify-center rounded-xl bg-gradient-to-br from-slate-100 to-slate-200">
                                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                        </template>
                    </div>
                    <label class="mt-2 block cursor-pointer text-center text-xs font-semibold text-[#1d4ed8] hover:underline">
                        Upload Photo
                        <input type="file" name="avatar" accept="image/*" class="sr-only"
                               @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"/>
                    </label>
                </div>
                <div class="flex-1 min-w-0">
                    <label class="block text-xs font-bold text-slate-600 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full rounded-xl border {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }} px-3.5 py-2.5 text-sm text-slate-700 focus:border-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-[#1d4ed8]/20 transition"
                           placeholder="Teacher's full name"/>
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full rounded-xl border {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }} px-3.5 py-2.5 text-sm text-slate-700 focus:border-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-[#1d4ed8]/20 transition"
                       placeholder="teacher@institution.edu.np"/>
                @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-700 focus:border-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-[#1d4ed8]/20 transition"
                       placeholder="98XXXXXXXX"/>
                @error('phone')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Password <span class="text-red-500">*</span></label>
                <input type="password" name="password" required
                       class="w-full rounded-xl border {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }} px-3.5 py-2.5 text-sm text-slate-700 focus:border-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-[#1d4ed8]/20 transition"
                       placeholder="Minimum 8 characters"/>
                @error('password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                <input type="password" name="password_confirmation" required
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-700 focus:border-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-[#1d4ed8]/20 transition"
                       placeholder="Re-enter password"/>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Gender <span class="text-red-500">*</span></label>
                <select name="gender" required
                        class="w-full rounded-xl border {{ $errors->has('gender') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }} px-3.5 py-2.5 text-sm text-slate-700 focus:border-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-[#1d4ed8]/20 transition">
                    <option value="">Select Gender</option>
                    <option value="male"   {{ old('gender') === 'male'   ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other"  {{ old('gender') === 'other'  ? 'selected' : '' }}>Other</option>
                </select>
                @error('gender')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Date of Birth (BS)</label>
                <x-bs-date-picker name="dob" :value="old('dob')"/>
                @error('dob')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-600 mb-1">Address</label>
                <textarea name="address" rows="2"
                          class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-700 focus:border-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-[#1d4ed8]/20 transition resize-none"
                          placeholder="Full address">{{ old('address') }}</textarea>
            </div>

        </div>
    </div>

    {{-- ── SECTION 2: EMPLOYMENT ── --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-700">Employment Details</h2>
            <p class="text-xs text-slate-400 mt-0.5">Department placement and role information.</p>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Department</label>
                <input type="text" value="{{ $department->name }}" disabled
                       class="w-full rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm text-slate-500 cursor-not-allowed"/>
                <input type="hidden" name="department_id" value="{{ $department->id }}"/>
                <p class="mt-1 text-xs text-slate-500">Teachers are automatically assigned to your department</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Employee ID <span class="text-red-500">*</span></label>
                <input type="text" name="employee_id" value="{{ old('employee_id') }}" required
                       class="w-full rounded-xl border {{ $errors->has('employee_id') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }} px-3.5 py-2.5 text-sm text-slate-700 focus:border-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-[#1d4ed8]/20 transition"
                       placeholder="e.g. EMP-001"/>
                @error('employee_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Designation <span class="text-red-500">*</span></label>
                <select name="designation" required x-model="designation"
                        class="w-full rounded-xl border {{ $errors->has('designation') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }} px-3.5 py-2.5 text-sm text-slate-700 focus:border-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-[#1d4ed8]/20 transition">
                    <option value="">Select Designation</option>
                    <option value="Teacher" {{ old('designation', 'Teacher') === 'Teacher' ? 'selected' : '' }}>Teacher</option>
                </select>
                @error('designation')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                <p class="mt-1 text-xs text-slate-500">Note: Only administrators can create HOD accounts</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Employment Type <span class="text-red-500">*</span></label>
                <select name="employment_type" required
                        class="w-full rounded-xl border {{ $errors->has('employment_type') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }} px-3.5 py-2.5 text-sm text-slate-700 focus:border-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-[#1d4ed8]/20 transition">
                    <option value="">Select Type</option>
                    <option value="permanent" {{ old('employment_type') === 'permanent' ? 'selected' : '' }}>Permanent</option>
                    <option value="contract"  {{ old('employment_type') === 'contract'  ? 'selected' : '' }}>Contract</option>
                    <option value="part-time" {{ old('employment_type') === 'part-time' ? 'selected' : '' }}>Part-time</option>
                </select>
                @error('employment_type')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Join Date (BS)</label>
                <x-bs-date-picker name="join_date" :value="old('join_date')"/>
                @error('join_date')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Status</label>
                <select name="is_active"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-700 focus:border-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-[#1d4ed8]/20 transition">
                    <option value="1" {{ old('is_active', '1') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('is_active')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

        </div>
    </div>

    {{-- ── SECTION 3: ACADEMIC ── --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-700">Academic Background</h2>
            <p class="text-xs text-slate-400 mt-0.5">Educational qualifications and expertise.</p>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Qualification</label>
                <input type="text" name="qualification" value="{{ old('qualification') }}"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-700 focus:border-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-[#1d4ed8]/20 transition"
                       placeholder="e.g. PhD Computer Science"/>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Specialization</label>
                <input type="text" name="specialization" value="{{ old('specialization') }}"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-700 focus:border-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-[#1d4ed8]/20 transition"
                       placeholder="e.g. Machine Learning, Networking"/>
            </div>

        </div>
    </div>

    {{-- ── SECTION 4: SUBJECT ASSIGNMENTS ── --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
            <h2 class="text-sm font-bold text-slate-700">Subject Assignments</h2>
            <p class="text-xs text-slate-400 mt-0.5">Assign subjects that this teacher will handle.</p>
        </div>
        <div class="p-6">
            @if($subjects->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($subjects as $subject)
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:border-[#1d4ed8] hover:bg-blue-50/30 cursor-pointer transition">
                            <input type="checkbox" name="subjects[]" value="{{ $subject->id }}" 
                                   {{ in_array($subject->id, old('subjects', [])) ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-[#1d4ed8] focus:ring-[#1d4ed8]/20">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-900">{{ $subject->name }}</p>
                                <p class="text-xs text-slate-500">{{ $subject->code }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
                <p class="mt-3 text-xs text-slate-500">Select subjects that this teacher will be responsible for teaching.</p>
            @else
                <div class="text-center py-8">
                    <div class="text-slate-400 mb-2">
                        <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <p class="text-sm text-slate-600">No subjects available</p>
                    <p class="text-xs text-slate-500">Create subjects first to assign them to teachers.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ── ACTIONS ── --}}
    <div class="flex items-center gap-3">
        <button type="submit" class="rounded-xl bg-[#1d4ed8] px-6 py-2.5 text-sm font-bold text-white hover:bg-[#1e40af] transition shadow-sm">
            Add Teacher
        </button>
        <a href="{{ route('hod.teachers.index') }}" class="rounded-xl border border-slate-200 px-6 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
            Cancel
        </a>
    </div>

</form>
</div>
@endsection