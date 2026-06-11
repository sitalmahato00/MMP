@extends('layouts.app')
@section('title', 'Add HOD')

@section('content')
<x-form-layout title="Add HOD" subtitle="Create a new Head of Department account." back="{{ route('admin.hods.index') }}">
    <x-slot name="breadcrumb">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.hods.index') }}" class="hover:text-slate-900">HODs</a>
            <span>/</span>
            <span class="font-semibold text-slate-900">Add HOD</span>
        </nav>
    </x-slot>

    <x-slot name="sidebar">
        <x-form-sidebar />
    </x-slot>

    <form action="{{ route('admin.hods.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        @csrf

        <div class="space-y-6">
            {{-- Basic Information --}}
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <x-label for="name" required>Full Name</x-label>
                        <x-input id="name" name="name" value="{{ old('name') }}" required/>
                        <x-error field="name"/>
                    </div>

                    <div>
                        <x-label for="email" required>Email</x-label>
                        <x-input type="email" id="email" name="email" value="{{ old('email') }}" required/>
                        <x-error field="email"/>
                    </div>

                    <div>
                        <x-label for="phone">Phone</x-label>
                        <x-input id="phone" name="phone" value="{{ old('phone') }}"/>
                        <x-error field="phone"/>
                    </div>

                    <div>
                        <x-label for="gender">Gender</x-label>
                        <x-select id="gender" name="gender">
                            <option value="">Select Gender</option>
                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                        </x-select>
                        <x-error field="gender"/>
                    </div>

                    <div>
                        <x-label for="dob">Date of Birth (BS)</x-label>
                        <x-input id="dob" name="dob" value="{{ old('dob') }}" placeholder="YYYY-MM-DD"/>
                        <x-error field="dob"/>
                    </div>

                    <div class="md:col-span-2">
                        <x-label for="address">Address</x-label>
                        <x-input id="address" name="address" value="{{ old('address') }}"/>
                        <x-error field="address"/>
                    </div>

                    <div class="md:col-span-2">
                        <x-label for="avatar">Profile Photo</x-label>
                        <x-file-input name="avatar" accept="image/*" label="Upload Profile Photo"/>
                        <x-error field="avatar"/>
                    </div>
                </div>
            </div>

            {{-- Department Assignment --}}
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Department Assignment</h3>
                <div>
                    <x-label for="department_id">Assign Department</x-label>
                    <x-select id="department_id" name="department_id">
                        <option value="">No Department (Assign Later)</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }} ({{ $dept->code }})
                            </option>
                        @endforeach
                    </x-select>
                    <p class="mt-1 text-xs text-gray-500">Only departments without an assigned HOD are shown</p>
                    <x-error field="department_id"/>
                </div>
            </div>

            {{-- Account Settings --}}
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Settings</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <p class="text-sm text-slate-600">A password reset link will be sent to the HOD after account creation.</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"/>
                            <span class="text-sm font-medium text-gray-700">Active Account</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-100">
            <x-btn href="{{ route('admin.hods.index') }}" variant="ghost">Cancel</x-btn>
            <x-btn type="submit">Create HOD</x-btn>
        </div>
    </form>
</x-form-layout>

@endsection
