@extends('layouts.app')
@section('title', 'Edit Parent')

@section('content')
<x-page-header title="Edit Parent" subtitle="Update parent/guardian information and linked children."
               back="{{ route('admin.parents.show', $parent) }}"/>

<form method="POST" action="{{ route('admin.parents.update', $parent) }}" enctype="multipart/form-data"
      class="max-w-4xl space-y-6">
    @csrf @method('PUT')

    {{-- 1. PERSONAL INFORMATION --}}
    <x-form-section title="Personal Information" subtitle="Parent/guardian identity and contact details.">
        <x-form-row>
            <x-form-field label="Full Name" name="name" :required="true">
                <x-input name="name" :value="old('name', $parent->user?->name)" :required="true"/>
            </x-form-field>
            <x-form-field label="Email Address" name="email" :required="true">
                <x-input name="email" type="email" :value="old('email', $parent->user?->email)" :required="true"/>
            </x-form-field>
            <x-form-field label="Phone Number" name="phone">
                <x-input name="phone" :value="old('phone', $parent->user?->phone)"/>
            </x-form-field>
            <x-form-field label="Address" name="address" span="full">
                <x-textarea name="address" rows="2">{{ old('address', $parent->user?->address) }}</x-textarea>
            </x-form-field>
            <x-form-field label="Profile Photo" name="avatar" span="full">
                @if($parent->user?->avatar)
                    <div class="mb-2 flex items-center gap-3">
                        <img src="{{ asset('storage/'.$parent->user->avatar) }}" class="h-12 w-12 rounded-xl object-cover ring-2 ring-slate-100"/>
                        <span class="text-xs text-slate-500">Current photo</span>
                    </div>
                @endif
                <x-file-input name="avatar" accept="image/*" label="Upload new photo (max 2 MB)"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- 2. PARENT DETAILS --}}
    <x-form-section title="Parent Details" subtitle="Relation to student and occupation.">
        <x-form-row>
            <x-form-field label="Relation to Student" name="relation_to_student">
                <x-select name="relation_to_student">
                    <option value="parent" @selected(old('relation_to_student', $parent->relation_to_student) === 'parent')>Parent</option>
                    <option value="father" @selected(old('relation_to_student', $parent->relation_to_student) === 'father')>Father</option>
                    <option value="mother" @selected(old('relation_to_student', $parent->relation_to_student) === 'mother')>Mother</option>
                    <option value="guardian" @selected(old('relation_to_student', $parent->relation_to_student) === 'guardian')>Guardian</option>
                </x-select>
            </x-form-field>
            <x-form-field label="Occupation" name="occupation">
                <x-input name="occupation" :value="old('occupation', $parent->occupation)"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- 3. ACCOUNT STATUS --}}
    <x-form-section title="Account Status" subtitle="Enable or disable this parent account.">
        <x-form-row>
            <x-form-field label="Active" name="is_active">
                <label class="inline-flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="is_active" value="0"/>
                    <input type="checkbox" name="is_active" value="1"
                           @checked(old('is_active', $parent->user?->is_active))
                           class="rounded border-slate-300 text-[#8B0000] focus:ring-red-200"/>
                    <span class="text-sm text-slate-700">Account is active and can log in</span>
                </label>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- 4. LINK CHILDREN --}}
    <x-form-section title="Link Children" subtitle="Select students linked to this parent account.">
        <div class="space-y-3">
            @php $linkedIds = old('student_ids', $parent->students->pluck('id')->toArray()); @endphp
            @if($students->count())
            <div x-data="{ search: '' }" class="space-y-3">
                <input type="text" x-model="search" placeholder="Search students by name…"
                       class="w-full rounded-xl border border-slate-200 bg-white py-2.5 px-4 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100"/>
                <div class="max-h-64 overflow-y-auto rounded-xl border border-slate-200 divide-y divide-slate-50">
                    @foreach($students as $student)
                    <label x-show="!search || '{{ strtolower($student->user?->name) }}'.includes(search.toLowerCase())"
                           class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 cursor-pointer transition">
                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                               @checked(in_array($student->id, $linkedIds))
                               class="rounded border-slate-300 text-[#8B0000] focus:ring-red-200"/>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-900">{{ $student->user?->name }}</p>
                            <p class="text-xs text-slate-500">{{ $student->student_no }} · {{ $student->department?->name }} · {{ $student->program?->name }} · Sem {{ $student->current_semester }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            @else
            <p class="text-sm text-slate-500 italic">No active students available to link.</p>
            @endif
        </div>
    </x-form-section>

    {{-- SUBMIT --}}
    <div class="flex items-center gap-3">
        <x-btn type="submit">Update Parent</x-btn>
        <a href="{{ route('admin.parents.show', $parent) }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
    </div>
</form>
@endsection
