@extends('layouts.app')
@section('title', 'Add Teacher')

@section('content')
<x-form-layout title="Add Teacher" subtitle="Onboard a new faculty member to the system." back="{{ route('admin.teachers.index') }}">
    <x-slot name="breadcrumb">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.teachers.index') }}" class="hover:text-slate-900">Teachers</a>
            <span>/</span>
            <span class="font-semibold text-slate-900">Add Teacher</span>
        </nav>
    </x-slot>

    <x-slot name="sidebar">
        <x-form-sidebar />
    </x-slot>

    <form method="POST" action="{{ route('admin.teachers.store') }}" enctype="multipart/form-data" class="max-w-full space-y-5">
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
                    <label class="mt-2 block cursor-pointer text-center text-xs font-semibold text-[#8B0000] hover:underline">
                        Upload Photo
                        <input type="file" name="avatar" accept="image/*" class="sr-only"
                               @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"/>
                    </label>
                </div>
                <div class="flex-1 min-w-0">
                    <label class="block text-xs font-bold text-slate-600 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full rounded-xl border {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }} px-3.5 py-2.5 text-sm text-slate-700 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20 transition"
                           placeholder="Teacher's full name"/>
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full rounded-xl border {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }} px-3.5 py-2.5 text-sm text-slate-700 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20 transition"
                       placeholder="teacher@institution.edu.np"/>
                @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-700 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20 transition"
                       placeholder="98XXXXXXXX"/>
                @error('phone')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <p class="text-sm text-slate-600">A password reset link will be sent to the teacher after account creation.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Gender</label>
                <select name="gender"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-700 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20 transition">
                    <option value="">Select Gender</option>
                    <option value="male"   {{ old('gender') === 'male'   ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other"  {{ old('gender') === 'other'  ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Date of Birth (BS)</label>
                <x-bs-date-picker name="dob" :value="old('dob')"/>
                @error('dob')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-600 mb-1">Address</label>
                <textarea name="address" rows="2"
                          class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-700 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20 transition resize-none"
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
                <label class="block text-xs font-bold text-slate-600 mb-1">Department <span class="text-red-500">*</span></label>
                <select name="department_id" required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-700 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20 transition">
                    <option value="">Select Department</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
                @error('department_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>


            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Employee ID</label>
                <input type="text" name="employee_id" value="{{ old('employee_id') }}"
                       class="w-full rounded-xl border {{ $errors->has('employee_id') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }} px-3.5 py-2.5 text-sm text-slate-700 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20 transition"
                       placeholder="e.g. EMP-001"/>
                @error('employee_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Designation <span class="text-red-500">*</span></label>
                <input type="text" name="designation" value="Teacher" readonly
                       class="w-full rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm text-slate-600 cursor-not-allowed"
                       placeholder="Teacher"/>
                <p class="mt-1 text-xs text-slate-500">HODs are managed separately through the HOD management interface</p>
                @error('designation')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Employment Type <span class="text-red-500">*</span></label>
                <select name="employment_type" required
                        class="w-full rounded-xl border {{ $errors->has('employment_type') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }} px-3.5 py-2.5 text-sm text-slate-700 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20 transition">
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
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-700 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20 transition"
                       placeholder="e.g. PhD Computer Science"/>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Specialization</label>
                <input type="text" name="specialization" value="{{ old('specialization') }}"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-700 focus:border-[#8B0000] focus:outline-none focus:ring-2 focus:ring-[#8B0000]/20 transition"
                       placeholder="e.g. Machine Learning, Networking"/>
            </div>

        </div>
    </div>

    {{-- ── ACTIONS ── --}}
    <div class="flex items-center gap-3">
        <x-btn type="submit" variant="success">Add Teacher</x-btn>
        <x-btn href="{{ route('admin.teachers.index') }}" variant="secondary">Cancel</x-btn>
    </div>

</form>
</x-form-layout>
@endsection

