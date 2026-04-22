@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl lg:rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-indigo-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}" 
                        alt="{{ $user->name }}" class="h-16 w-16 rounded-full border-4 border-white shadow-md">
                    <div>
                        <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Teacher Profile</p>
                        <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                            {{ $user->name }}
                        </h1>
                        <p class="mt-1 text-sm text-slate-600">{{ $teacher->designation ?? 'Teacher' }}</p>
                    </div>
                </div>
                <a href="{{ route('teacher.profile.edit') }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Profile
                </a>
            </div>
        </div>
    </section>

    {{-- Profile Information --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Personal Information --}}
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold text-slate-900">Personal Information</h2>
            <div class="space-y-4">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Full Name</p>
                    <p class="mt-1 text-sm text-slate-900">{{ $user->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Email</p>
                    <p class="mt-1 text-sm text-slate-900">{{ $user->email }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Department</p>
                    <p class="mt-1 text-sm text-slate-900">{{ $teacher->department->name }}</p>
                </div>
            </div>
        </div>

        {{-- Professional Information --}}
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold text-slate-900">Professional Information</h2>
            <div class="space-y-4">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Designation</p>
                    <p class="mt-1 text-sm text-slate-900">{{ $teacher->designation ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Qualification</p>
                    <p class="mt-1 text-sm text-slate-900">{{ $teacher->qualification ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Specialization</p>
                    <p class="mt-1 text-sm text-slate-900">{{ $teacher->specialization ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Employment Details --}}
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold text-slate-900">Employment Details</h2>
            <div class="space-y-4">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Employee ID</p>
                    <p class="mt-1 text-sm text-slate-900">{{ $teacher->employee_id ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Join Date</p>
                    <p class="mt-1 text-sm text-slate-900">{{ $teacher->join_date ? bsDate($teacher->join_date, 'M d, Y') : '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Employment Type</p>
                    <p class="mt-1 text-sm text-slate-900 capitalize">{{ $teacher->employment_type ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Account Settings --}}
        <div class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold text-slate-900">Account Settings</h2>
            <div class="space-y-3">
                <a href="{{ route('teacher.profile.change-password') }}" class="flex items-center justify-between rounded-lg border border-slate-200 p-3 transition hover:bg-slate-50">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Change Password</p>
                        <p class="text-xs text-slate-500">Update your account password</p>
                    </div>
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                <form action="{{ route('logout') }}" method="POST" class="inline w-full">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-between rounded-lg border border-rose-200 p-3 transition hover:bg-rose-50">
                        <div class="text-left">
                            <p class="text-sm font-semibold text-rose-900">Logout</p>
                            <p class="text-xs text-rose-600">Sign out from your account</p>
                        </div>
                        <svg class="h-5 w-5 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
