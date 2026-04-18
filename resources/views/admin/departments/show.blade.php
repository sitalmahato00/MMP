@extends('layouts.app')
@section('title', $department->name)

@section('content')
<x-page-header :title="$department->name" subtitle="Department profile and academic programs."
               back="{{ route('admin.departments.index') }}">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.departments.edit', $department) }}" variant="secondary" size="sm">Edit</x-btn>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 space-y-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            @if($department->photo_url)
                <img src="{{ $department->photo_url }}" alt="{{ $department->name }}" class="w-full h-56 object-cover">
            @else
                <div class="h-56 bg-gradient-to-br from-gray-100 to-gray-50 flex items-center justify-center text-5xl">🏛️</div>
            @endif
            <div class="p-6 text-center">
                <h2 class="text-lg font-bold text-gray-900">{{ $department->name }}</h2>
                <p class="text-sm text-gray-500">{{ $department->code }}</p>
                @if($department->hod)
                    <div class="mt-3 flex items-center justify-center gap-2 text-sm text-gray-700">
                        <x-avatar :src="$department->hod->avatar_url" :name="$department->hod->name" size="sm"/>
                        <span>{{ $department->hod->name }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <x-section-header title="Quick Facts"/>
            <dl class="divide-y divide-gray-50">
                <x-info-row label="Programs">{{ $department->programs->count() }}</x-info-row>
                <x-info-row label="Photo">{{ $department->photo ? 'Uploaded' : 'Not set' }}</x-info-row>
                <x-info-row label="Active">{{ $department->is_active ? 'Yes' : 'No' }}</x-info-row>
            </dl>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-4">
        @if($department->programs->count() > 0)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <x-section-header title="Programs"/>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($department->programs as $program)
                        <a href="{{ route('admin.programs.show', $program) }}" class="group rounded-lg border border-gray-100 p-4 transition hover:border-[#8B0000]/20 hover:bg-red-50/30">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="font-semibold text-gray-900 group-hover:text-[#8B0000]">{{ $program->name }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ $program->code }} · {{ $program->total_semesters }} semesters · {{ $program->duration_years }} yrs</div>
                                </div>
                                <span class="shrink-0 rounded-md {{ $program->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }} px-2 py-0.5 text-[10px] font-bold">
                                    {{ $program->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            @if($program->description)
                                <p class="mt-2 text-xs text-gray-500 line-clamp-2">{{ $program->description }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 text-center">
                <p class="text-sm text-gray-500">No programs added to this department yet.</p>
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <x-section-header title="Department Information"/>
            <dl class="divide-y divide-gray-50">
                <x-info-row label="Name">{{ $department->name }}</x-info-row>
                <x-info-row label="Code">{{ $department->code }}</x-info-row>
                <x-info-row label="HOD">{{ $department->hod?->name ?? '—' }}</x-info-row>
                <x-info-row label="Description">{{ $department->description ?? '—' }}</x-info-row>
            </dl>
        </div>
    </div>
</div>
@endsection