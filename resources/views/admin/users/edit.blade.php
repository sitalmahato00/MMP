@extends('layouts.app')
@section('title', 'Edit User')

@section('content')
<x-page-header title="Edit User" :subtitle="$user->name"
               back="{{ route('admin.users.index') }}"/>

<form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data"
      x-data="{ tab: 'basic' }">
    @csrf @method('PUT')

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
                    <x-input name="name" :value="old('name', $user->name)" :required="true"/>
                </x-form-field>
                <x-form-field label="Email Address" name="email" :required="true">
                    <x-input name="email" type="email" :value="old('email', $user->email)" :required="true"/>
                </x-form-field>
                <x-form-field label="Phone Number" name="phone">
                    <x-input name="phone" :value="old('phone', $user->phone)"/>
                </x-form-field>
                <x-form-field label="Gender" name="gender">
                    <x-select name="gender">
                        <option value="">Select Gender</option>
                        <option value="male"   @selected(old('gender', $user->gender)==='male')>Male</option>
                        <option value="female" @selected(old('gender', $user->gender)==='female')>Female</option>
                        <option value="other"  @selected(old('gender', $user->gender)==='other')>Other</option>
                    </x-select>
                </x-form-field>
                <x-form-field label="Date of Birth (BS)" name="dob">
                    <x-bs-date-picker name="dob" :value="old('dob', $user->dob ? bsDate($user->dob) : '')"/>
                </x-form-field>
                <x-form-field label="Address" name="address" span="full">
                    <x-textarea name="address" rows="2">{{ old('address', $user->address) }}</x-textarea>
                </x-form-field>
                <x-form-field label="Profile Picture" name="avatar" span="full">
                    <x-file-input name="avatar" accept="image/*" label="Upload Profile Picture" :current="$user->avatar"/>
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
                        @foreach(['principal', 'hod', 'teacher', 'student', 'parent', 'alumni'] as $r)
                            <option value="{{ $r }}" @selected($user->hasRole($r))>{{ ucfirst($r) }}</option>
                        @endforeach
                    </x-select>
                </x-form-field>
                <x-form-field label="Account Status" name="is_active">
                    <label class="flex items-center gap-3 mt-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ $user->is_active ? 'checked' : '' }} class="w-4 h-4 accent-[#8B0000] rounded">
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
        <x-form-section title="Change Password" subtitle="Leave blank to keep the current password.">
            <x-form-row>
                <x-form-field label="New Password" name="password">
                    <x-input name="password" type="password" placeholder="Leave blank to keep current"/>
                </x-form-field>
                <x-form-field label="Confirm Password" name="password_confirmation">
                    <x-input name="password_confirmation" type="password"/>
                </x-form-field>
            </x-form-row>
        </x-form-section>
        <div class="mt-6 flex items-center gap-3">
            <button type="button" @click="tab='access'" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">← Back</button>
            <x-btn type="submit">Save Changes</x-btn>
            <x-btn href="{{ route('admin.users.index') }}" variant="secondary">Cancel</x-btn>
        </div>
    </div>

</form>
@endsection
