@extends('layouts.app')
@section('title', 'HOD Details')

@section('content')

<x-page-header title="HOD Details" subtitle="View Head of Department information.">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.hods.edit', $hod) }}" variant="secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit
        </x-btn>
        <x-btn href="{{ route('admin.hods.index') }}" variant="ghost">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to List
        </x-btn>
    </x-slot>
</x-page-header>

<div class="max-w-4xl">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        {{-- Header Section --}}
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-8">
            <div class="flex items-center gap-6">
                <img src="{{ $hod->avatar_url }}" alt="{{ $hod->name }}"
                     class="w-24 h-24 rounded-full object-cover ring-4 ring-white shadow-lg">
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $hod->name }}</h2>
                    <p class="text-gray-600 mt-1">{{ $hod->email }}</p>
                    <div class="flex items-center gap-3 mt-3">
                        <x-badge color="blue">HOD</x-badge>
                        <x-badge :color="$hod->is_active ? 'green' : 'red'" :dot="true">
                            {{ $hod->is_active ? 'Active' : 'Inactive' }}
                        </x-badge>
                    </div>
                </div>
            </div>
        </div>

        {{-- Details Section --}}
        <div class="p-6 space-y-6">
            {{-- Personal Information --}}
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Personal Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</p>
                        <p class="mt-1 text-sm text-gray-900">{{ $hod->phone ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</p>
                        <p class="mt-1 text-sm text-gray-900">{{ $hod->gender ? ucfirst($hod->gender) : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Date of Birth</p>
                        <p class="mt-1 text-sm text-gray-900">{{ $hod->dob ? bsDate($hod->dob, 'Y F d') : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Address</p>
                        <p class="mt-1 text-sm text-gray-900">{{ $hod->address ?? '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- Department Assignment --}}
            <div class="border-t border-gray-100 pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Department Assignment
                </h3>
                @if($hod->hodDepartment)
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900">{{ $hod->hodDepartment->name }}</h4>
                                <p class="text-sm text-gray-600 mt-1">Code: {{ $hod->hodDepartment->code }}</p>
                                @if($hod->hodDepartment->description)
                                    <p class="text-sm text-gray-500 mt-2">{{ $hod->hodDepartment->description }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-amber-50 rounded-lg p-4 border border-amber-100">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <p class="text-sm text-amber-800">No department assigned yet</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Account Information --}}
            <div class="border-t border-gray-100 pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Account Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Account Created</p>
                        <p class="mt-1 text-sm text-gray-900">{{ bsDate($hod->created_at, 'Y F d, l') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</p>
                        <p class="mt-1 text-sm text-gray-900">{{ bsDate($hod->updated_at, 'Y F d, l') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
