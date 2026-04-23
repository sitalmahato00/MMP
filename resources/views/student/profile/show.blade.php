@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Account Information</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        My Profile
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">Manage your personal information and account settings</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('student.profile.edit') }}" 
                       class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                        Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Profile Photo & Basic Info --}}
        <div class="lg:col-span-1">
            <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm p-6">
                <div class="text-center">
                    <div class="mx-auto h-24 w-24 rounded-full bg-slate-100 flex items-center justify-center overflow-hidden">
                        @if($student->profile_photo)
                            <img src="{{ Storage::url($student->profile_photo) }}" 
                                 alt="{{ $student->user->name }}" 
                                 class="h-full w-full object-cover">
                        @else
                            <svg class="h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        @endif
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ $student->user->name }}</h3>
                    <p class="text-sm text-slate-600">Student ID: {{ $student->student_id }}</p>
                    <p class="text-sm text-slate-600">{{ $student->program->name ?? 'N/A' }}</p>
                </div>
            </section>
        </div>

        {{-- Profile Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Personal Information --}}
            <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-sm font-semibold text-slate-900">Personal Information</h2>
                </div>
                <div class="p-6">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Full Name</label>
                            <p class="text-sm text-slate-900">{{ $student->user->name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Email Address</label>
                            <p class="text-sm text-slate-900">{{ $student->user->email }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Phone Number</label>
                            <p class="text-sm text-slate-900">{{ $student->phone ?: 'Not provided' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Date of Birth</label>
                            <p class="text-sm text-slate-900">
                                {{ $student->date_of_birth ? bsDate($student->date_of_birth, 'F d, Y') : 'Not provided' }}
                            </p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-slate-700 mb-1">Address</label>
                            <p class="text-sm text-slate-900">{{ $student->address ?: 'Not provided' }}</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Academic Information --}}
            <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-sm font-semibold text-slate-900">Academic Information</h2>
                </div>
                <div class="p-6">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Student ID</label>
                            <p class="text-sm text-slate-900">{{ $student->student_id }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Program</label>
                            <p class="text-sm text-slate-900">{{ $student->program->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Department</label>
                            <p class="text-sm text-slate-900">{{ $student->program->department->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Current Semester</label>
                            <p class="text-sm text-slate-900">{{ $student->semester }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Admission Date</label>
                            <p class="text-sm text-slate-900">
                                {{ $student->admission_date ? bsDate($student->admission_date, 'F d, Y') : 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Status</label>
                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-emerald-50 text-emerald-700">
                                {{ ucfirst($student->status ?? 'active') }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Account Security --}}
            <section class="rounded-xl border border-slate-200/80 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-sm font-semibold text-slate-900">Account Security</h2>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-slate-900">Password</h3>
                            <p class="text-xs text-slate-600">Last updated: {{ $student->user->updated_at->diffForHumans() }}</p>
                        </div>
                        <a href="{{ route('student.profile.change-password') }}" 
                           class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v-2H7v-2H4a1 1 0 01-1-1v-1m0-4h4.764a6 6 0 0110.236-3.236A6 6 0 0117 8a1 1 0 01-1 1h-1v1a1 1 0 01-1 1h-1v1H9z"/>
                            </svg>
                            Change Password
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection