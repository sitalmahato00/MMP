@extends('layouts.app')
@section('title', 'Add Parent')

@section('content')
<x-page-header title="Add Parent" subtitle="Create a new parent/guardian account and link to students."
               back="{{ route('admin.parents.index') }}"/>

<form method="POST" action="{{ route('admin.parents.store') }}" enctype="multipart/form-data"
      class="max-w-4xl space-y-6">
    @csrf

    {{-- 1. PERSONAL INFORMATION --}}
    <x-form-section title="Personal Information" subtitle="Parent/guardian identity and contact details.">
        <x-form-row>
            <x-form-field label="Full Name" name="name" :required="true">
                <x-input name="name" :value="old('name')" :required="true" placeholder="Full legal name"/>
            </x-form-field>
            <x-form-field label="Email Address" name="email" :required="true">
                <x-input name="email" type="email" :value="old('email')" :required="true" placeholder="parent@example.com"/>
            </x-form-field>
            <x-form-field label="Phone Number" name="phone">
                <x-input name="phone" :value="old('phone')" placeholder="98XXXXXXXX"/>
            </x-form-field>
            <x-form-field label="Password" name="password" :required="true">
                <x-input name="password" type="password" :required="true" placeholder="Min. 8 characters"/>
            </x-form-field>
            <x-form-field label="Address" name="address" span="full">
                <x-textarea name="address" rows="2" placeholder="District, Province, Country">{{ old('address') }}</x-textarea>
            </x-form-field>
            <x-form-field label="Profile Photo" name="avatar" span="full">
                <x-file-input name="avatar" accept="image/*" label="Upload photo (max 2 MB)"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- 2. PARENT DETAILS --}}
    <x-form-section title="Parent Details" subtitle="Relation to student and occupation.">
        <x-form-row>
            <x-form-field label="Relation to Student" name="relation_to_student">
                <x-select name="relation_to_student">
                    <option value="parent" @selected(old('relation_to_student', 'parent') === 'parent')>Parent</option>
                    <option value="father" @selected(old('relation_to_student') === 'father')>Father</option>
                    <option value="mother" @selected(old('relation_to_student') === 'mother')>Mother</option>
                    <option value="guardian" @selected(old('relation_to_student') === 'guardian')>Guardian</option>
                </x-select>
            </x-form-field>
            <x-form-field label="Occupation" name="occupation">
                <x-input name="occupation" :value="old('occupation')" placeholder="e.g. Teacher, Business, Farmer"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    {{-- 3. LINK CHILDREN --}}
    <x-form-section title="Link Children" subtitle="Select students to link to this parent account.">
        <div class="space-y-3">
            @if($students->count())
            <div x-data="{ search: '' }" class="space-y-3">
                <input type="text" x-model="search" placeholder="Search students by name…"
                       class="w-full rounded-xl border border-slate-200 bg-white py-2.5 px-4 text-sm text-slate-700 outline-none focus:border-[#8B0000] focus:ring-2 focus:ring-red-100"/>
                <div class="max-h-64 overflow-y-auto rounded-xl border border-slate-200 divide-y divide-slate-50">
                    @foreach($students as $student)
                    <label x-show="!search || '{{ strtolower($student->user?->name) }}'.includes(search.toLowerCase())"
                           class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 cursor-pointer transition">
                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                               @checked(in_array($student->id, old('student_ids', [])))
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
        <x-btn type="submit">Create Parent Account</x-btn>
        <a href="{{ route('admin.parents.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
    </div>
</form>
@endsection
