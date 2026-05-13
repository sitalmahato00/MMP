@extends('layouts.app')
@section('title', 'Add User')

@section('content')
<x-page-header title="Add User" subtitle="Create a new system user."
               back="{{ route('admin.users.index') }}"/>

<form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data"
      x-data="{ tab: 'basic' }">
    @csrf

    {{-- Tab Navigation --}}
    <div class="mb-6 flex gap-1 rounded-xl bg-slate-100 p-1">
        <button type="button" @click="tab='basic'"
                :class="tab==='basic' ? 'bg-white shadow-sm text-slate-900 font-semibold' : 'text-slate-500 hover:text-slate-700'"
                class="flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition">
            Basic Info
        </button>
        <button type="button" @click="tab='access'"
                :class="tab==='access' ? 'bg-white shadow-sm text-slate-900 font-semibold' : 'text-slate-500 hover:text-slate-700'"
                class="flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition">
            Role &amp; Access
        </button>
        <button type="button" @click="tab='security'"
                :class="tab==='security' ? 'bg-white shadow-sm text-slate-900 font-semibold' : 'text-slate-500 hover:text-slate-700'"
                class="flex-1 rounded-lg px-4 py-2.5 text-sm font-medium transition">
            Security
        </button>
    </div>

    {{-- Tab: Basic Info --}}
    <div x-show="tab==='basic'" x-transition>
        <x-form-section title="Basic Information" subtitle="Identity, contact details, and profile photo.">
            <x-form-row>
                <x-form-field label="Full Name" name="name" :required="true">
                    <x-input name="name" :value="old('name')" :required="true" placeholder="John Doe"/>
                </x-form-field>
                <x-form-field label="Email Address" name="email" :required="true">
                    <x-input name="email" type="email" :value="old('email')" :required="true" placeholder="john@example.com"/>
                </x-form-field>
                <x-form-field label="Phone Number" name="phone">
                    <x-input name="phone" :value="old('phone')" placeholder="+977-9800000000"/>
                </x-form-field>
                <x-form-field label="Gender" name="gender">
                    <x-select name="gender">
                        <option value="">Select Gender</option>
                        <option value="male"   @selected(old('gender')==='male')>Male</option>
                        <option value="female" @selected(old('gender')==='female')>Female</option>
                        <option value="other"  @selected(old('gender')==='other')>Other</option>
                    </x-select>
                </x-form-field>
                <x-form-field label="Date of Birth (BS)" name="dob">
                    <x-bs-date-picker name="dob" :value="old('dob')"/>
                </x-form-field>
                <x-form-field label="Address" name="address" span="full">
                    <x-textarea name="address" rows="2" placeholder="Full address">{{ old('address') }}</x-textarea>
                </x-form-field>
                <x-form-field label="Profile Picture" name="avatar" span="full">
                    <x-file-input name="avatar" accept="image/*" label="Upload Profile Picture"/>
                </x-form-field>
            </x-form-row>
        </x-form-section>
        <div class="mt-4 flex gap-3">
            <button type="button" @click="tab='access'" class="rounded-xl bg-[#8B0000] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#7a0000] transition">Next: Role &amp; Access →</button>
        </div>
    </div>

    {{-- Tab: Role & Access --}}
    <div x-show="tab==='access'" x-transition>
        <x-form-section title="Role &amp; Access" subtitle="Assign a system role and set account status.">
            <x-form-row>
                <x-form-field label="Role" name="role" :required="true">
                    <x-select name="role" :required="true">
                        <option value="">— Select Role —</option>
                        @foreach(['principal', 'hod', 'teacher', 'student', 'parent', 'alumni'] as $r)
                            <option value="{{ $r }}" @selected(old('role', $defaultRole ?? '') === $r)>{{ ucfirst($r) }}</option>
                        @endforeach
                    </x-select>
                </x-form-field>
                <x-form-field label="Account Status" name="is_active">
                    <label class="flex items-center gap-3 mt-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-4 h-4 accent-[#8B0000] rounded">
                        <span class="text-sm text-gray-600">Active (can login)</span>
                    </label>
                </x-form-field>
            </x-form-row>
        </x-form-section>
        <div class="mt-4 flex gap-3">
            <button type="button" @click="tab='basic'" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">← Back</button>
            <button type="button" @click="tab='security'" class="rounded-xl bg-[#8B0000] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#7a0000] transition">Next: Security →</button>
        </div>
    </div>

    {{-- Tab: Security --}}
    <div x-show="tab==='security'" x-transition>
        <x-form-section title="Security" subtitle="Password is auto-generated and emailed to the user.">
            <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4 text-sm text-blue-700 dark:text-blue-300">
                <svg class="inline w-4 h-4 mr-1 -mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                A secure random password will be auto-generated and emailed to the user upon account creation.
            </div>
        </x-form-section>
        <div class="mt-6 flex items-center gap-3">
            <button type="button" @click="tab='access'" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">← Back</button>
            <x-btn type="submit">Create User</x-btn>
            <x-btn href="{{ route('admin.users.index') }}" variant="secondary">Cancel</x-btn>
        </div>
    </div>

</form>
@endsection
