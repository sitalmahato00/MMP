@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="relative overflow-hidden rounded-xl lg:rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-indigo-50/40"></div>
        <div class="relative px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
            <div class="flex flex-col gap-3 lg:gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-slate-400">Profile</p>
                    <h1 class="mt-1 text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-slate-900">
                        Edit Profile
                    </h1>
                </div>
                <a href="{{ route('teacher.profile.show') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back
                </a>
            </div>
        </div>
    </section>

    {{-- Form --}}
    <form action="{{ route('teacher.profile.update') }}" method="POST" class="rounded-xl border border-slate-200/80 bg-white p-4 sm:p-6 shadow-sm space-y-6">
        @csrf
        @method('PUT')

        {{-- Personal Information --}}
        <div>
            <h2 class="text-sm font-semibold text-slate-900 mb-4">Personal Information</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-900 mb-2">Full Name *</label>
                    <input type="text" name="name" required value="{{ old('name', $user->name) }}" 
                        class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('name') border-rose-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-900 mb-2">Email *</label>
                    <input type="email" name="email" required value="{{ old('email', $user->email) }}" 
                        class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('email') border-rose-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-900 mb-2">Phone</label>
                    <input type="tel" name="phone" value="{{ old('phone', $teacher->phone ?? '') }}" 
                        class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('phone') border-rose-500 @enderror">
                    @error('phone')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Professional Information --}}
        <div class="border-t border-slate-100 pt-6">
            <h2 class="text-sm font-semibold text-slate-900 mb-4">Professional Information</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-900 mb-2">Qualification</label>
                    <input type="text" name="qualification" value="{{ old('qualification', $teacher->qualification ?? '') }}" placeholder="e.g., B.Sc, M.Sc, Ph.D"
                        class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('qualification') border-rose-500 @enderror">
                    @error('qualification')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-900 mb-2">Specialization</label>
                    <input type="text" name="specialization" value="{{ old('specialization', $teacher->specialization ?? '') }}" placeholder="e.g., Mathematics, Physics"
                        class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('specialization') border-rose-500 @enderror">
                    @error('specialization')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex gap-3 border-t border-slate-100 pt-6">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Save Changes
            </button>
            <a href="{{ route('teacher.profile.show') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-6 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
