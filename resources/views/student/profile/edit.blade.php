@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex items-center gap-3 mb-3">
                <a href="{{ route('student.profile.show') }}" 
                   class="inline-flex items-center gap-1 text-sm text-slate-600 hover:text-slate-900">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Profile
                </a>
            </div>
            <div>
                <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Account Settings</p>
                <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                    Edit Profile
                </h1>
                <p class="mt-1 text-sm text-slate-600">Update your personal information</p>
            </div>
        </div>
    </section>

    <div class="max-w-2xl">
        <form action="{{ route('student.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Profile Photo --}}
            <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm p-6 mb-6">
                <h2 class="text-sm font-semibold text-slate-900 mb-4">Profile Photo</h2>
                
                <div class="flex items-center gap-6">
                    <div class="h-20 w-20 rounded-full bg-slate-100 flex items-center justify-center overflow-hidden">
                        @if($student->profile_photo)
                            <img src="{{ asset('storage/' . ltrim($student->profile_photo, '/')) }}" 
                                 alt="{{ $student->user->name }}" 
                                 class="h-full w-full object-cover">
                        @else
                            <svg class="h-10 w-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        @endif
                    </div>
                    <div>
                        <input type="file" name="profile_photo" accept="image/*" 
                               class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-1 text-xs text-slate-500">JPG, PNG up to 2MB</p>
                        @error('profile_photo')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- Personal Information --}}
            <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm p-6 mb-6">
                <h2 class="text-sm font-semibold text-slate-900 mb-4">Personal Information</h2>
                
                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Full Name *</label>
                        <input type="text" name="name" value="{{ old('name', $student->user->name) }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Email Address *</label>
                        <input type="email" name="email" value="{{ old('email', $student->user->email) }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $student->phone) }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('phone')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Address</label>
                        <textarea name="address" rows="3"
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">{{ old('address', $student->address) }}</textarea>
                        @error('address')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- Academic Information (Read-only) --}}
            <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm p-6 mb-6">
                <h2 class="text-sm font-semibold text-slate-900 mb-4">Academic Information</h2>
                <p class="text-xs text-slate-500 mb-4">This information cannot be changed. Contact administration if corrections are needed.</p>
                
                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Student ID</label>
                        <input type="text" value="{{ $student->student_id }}" disabled
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-slate-50 text-slate-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Program</label>
                        <input type="text" value="{{ $student->program->name ?? 'N/A' }}" disabled
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-slate-50 text-slate-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Department</label>
                        <input type="text" value="{{ $student->program->department->name ?? 'N/A' }}" disabled
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-slate-50 text-slate-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Current Semester</label>
                        <input type="text" value="{{ $student->semester }}" disabled
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-slate-50 text-slate-500">
                    </div>
                </div>
            </section>

            {{-- Form Actions --}}
            <div class="flex items-center gap-3">
                <button type="submit" 
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Changes
                </button>
                <a href="{{ route('student.profile.show') }}" 
                   class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection