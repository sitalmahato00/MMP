@extends('layouts.app')
@section('title', $student->user->name)

@section('content')
<x-page-header :title="$student->user->name" subtitle="Student record, alumni conversion status, and related profile data."
               back="{{ route('admin.students.index') }}">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.students.edit', $student) }}" variant="secondary" size="sm">Edit</x-btn>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 space-y-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 flex flex-col items-center text-center">
            <x-avatar :src="$student->user->avatar_url" :name="$student->user->name" size="xl" class="mb-3"/>
            <h2 class="text-lg font-bold text-gray-900">{{ $student->user->name }}</h2>
            <p class="text-sm text-gray-400">{{ $student->user->email }}</p>
            <div class="mt-3 flex flex-wrap gap-2 justify-center">
                <x-badge color="blue">{{ $student->program?->name ?? 'Program not set' }}</x-badge>
                <x-badge color="purple">Sem {{ $student->current_semester }}</x-badge>
                <x-badge :color="$student->status === 'graduated' ? 'green' : ($student->is_archived ? 'gray' : 'blue')">
                    {{ $student->status === 'graduated' ? 'Alumni' : ucfirst($student->status ?? 'active') }}
                </x-badge>
            </div>
            <p class="mt-3 text-xs text-gray-500 leading-relaxed">
                This record remains the source of truth. When the active academic session ends and this student is in the final semester, the system promotes the linked user to alumni automatically.
            </p>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <x-section-header title="Parent / Guardian"/>
            <div class="space-y-3">
                @forelse($student->parents as $parent)
                    <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                        <div class="font-semibold text-gray-900">{{ $parent->user?->name ?? 'Unnamed parent' }}</div>
                        <div class="text-sm text-gray-500">{{ $parent->relation_to_student ? ucfirst($parent->relation_to_student) : 'Parent' }}</div>
                        <div class="text-xs text-gray-400">{{ $parent->user?->email ?? '—' }}</div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No parent or guardian linked yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <x-section-header title="Account Information"/>
            <dl class="divide-y divide-gray-50">
                <x-info-row label="Student ID">{{ $student->student_no }}</x-info-row>
                <x-info-row label="Full Name">{{ $student->user->name }}</x-info-row>
                <x-info-row label="Email">{{ $student->user->email }}</x-info-row>
                <x-info-row label="Phone">{{ $student->user->phone ?? '—' }}</x-info-row>
                <x-info-row label="Gender">{{ ucfirst($student->user->gender ?? '—') }}</x-info-row>
                <x-info-row label="Date of Birth">{{ $student->user->dob ? bsDate($student->user->dob, 'd F Y') : '—' }}</x-info-row>
                <x-info-row label="Address">{{ $student->user->address ?? '—' }}</x-info-row>
                <x-info-row label="Status">
                    <x-badge :color="$student->status === 'graduated' ? 'green' : ($student->is_archived ? 'gray' : 'blue')" :dot="true">
                        {{ $student->status === 'graduated' ? 'Alumni' : ucfirst($student->status ?? 'active') }}
                    </x-badge>
                </x-info-row>
                <x-info-row label="Member Since">{{ bsDate($student->created_at, 'd F Y') }}</x-info-row>
            </dl>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <x-section-header title="Enrollment Information"/>
            <dl class="divide-y divide-gray-50">
                <x-info-row label="Academic Session">{{ $student->academicSession?->name ?? '—' }}</x-info-row>
                <x-info-row label="Department">{{ $student->department?->name ?? $student->program?->department?->name ?? '—' }}</x-info-row>
                <x-info-row label="Program">{{ $student->program?->name ?? '—' }}</x-info-row>
                <x-info-row label="Current Semester">Semester {{ $student->current_semester }}</x-info-row>
                <x-info-row label="Roll Number">{{ $student->roll_number ?? '—' }}</x-info-row>
                <x-info-row label="Registration Number">{{ $student->registration_number ?? '—' }}</x-info-row>
                <x-info-row label="Section">{{ $student->section ?? '—' }}</x-info-row>
                <x-info-row label="Batch">{{ $student->batch ?? '—' }}</x-info-row>
                <x-info-row label="Archived">
                    <x-badge :color="$student->is_archived ? 'green' : 'gray'" :dot="true">
                        {{ $student->is_archived ? 'Yes' : 'No' }}
                    </x-badge>
                </x-info-row>
            </dl>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <x-section-header title="Alumni Record"/>
            @if($student->alumnus)
                <dl class="divide-y divide-gray-50">
                    <x-info-row label="Graduation Year">{{ $student->alumnus->graduation_year }}</x-info-row>
                    <x-info-row label="Verified">
                        <x-badge :color="$student->alumnus->is_verified ? 'green' : 'yellow'" :dot="true">
                            {{ $student->alumnus->is_verified ? 'Verified' : 'Pending' }}
                        </x-badge>
                    </x-info-row>
                    <x-info-row label="Featured">
                        <x-badge :color="$student->alumnus->is_featured ? 'green' : 'gray'" :dot="true">
                            {{ $student->alumnus->is_featured ? 'Featured' : 'No' }}
                        </x-badge>
                    </x-info-row>
                    <x-info-row label="Current Job">{{ $student->alumnus->current_job ?? '—' }}</x-info-row>
                    <x-info-row label="Company">{{ $student->alumnus->company_name ?? '—' }}</x-info-row>
                    <x-info-row label="Achievements">{{ $student->alumnus->achievements ?? '—' }}</x-info-row>
                </dl>
            @else
                <p class="text-sm text-gray-500">
                    This student has not been promoted to alumni yet. The record will convert automatically when the active session ends and the student has completed the final semester.
                </p>
            @endif
        </div>
    </div>
</div>
@endsection