@extends('layouts.app')
@section('title', 'Departments')

@section('content')
<x-page-header title="Departments" subtitle="Manage faculties and assign Heads of Department.">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.departments.create') }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add Department
        </x-btn>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <x-stat-card title="Total Departments" value="{{ \App\Models\Department::count() }}" color="blue" icon="<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'/></svg>"/>
    <x-stat-card title="Total Programs" value="{{ \App\Models\Program::count() }}" color="purple" icon="<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path d='M12 14l9-5-9-5-9 5 9 5z'/><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222'/></svg>"/>
    <x-stat-card title="HODs Assigned" value="{{ \App\Models\Department::whereNotNull('hod_id')->count() }}" color="green" icon="<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'/></svg>"/>
</div>

<x-data-table :paginator="null">
    <x-slot name="head">
        <tr>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Department</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Code</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Head of Dept</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Programs</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
        </tr>
    </x-slot>

    @forelse($departments as $dept)
    <tr class="hover:bg-gray-50/70 transition-colors">
        <td class="px-5 py-3.5">
            <p class="font-semibold text-gray-900">{{ $dept->name }}</p>
            @if($dept->description)
                <p class="text-xs text-gray-400 truncate max-w-xs mt-0.5">{{ Str::limit($dept->description, 60) }}</p>
            @endif
        </td>
        <td class="px-5 py-3.5">
            <x-badge color="blue">{{ $dept->code }}</x-badge>
        </td>
        <td class="px-5 py-3.5">
            @if($dept->hod)
                <div class="flex items-center gap-2">
                    <img src="{{ $dept->hod->avatar_url }}" class="w-6 h-6 rounded-full object-cover">
                    <span class="text-sm text-gray-700">{{ $dept->hod->name }}</span>
                </div>
            @else
                <span class="text-xs text-gray-400 italic">Not assigned</span>
            @endif
        </td>
        <td class="px-5 py-3.5">
            <x-badge color="gray">{{ $dept->programs_count }} programs</x-badge>
        </td>
        <td class="px-5 py-3.5">
            <x-table-actions
                :show="route('admin.departments.show', $dept)"
                :edit="route('admin.departments.edit', $dept)"
                :destroy="route('admin.departments.destroy', $dept)"
                name="{{ $dept->name }}"
            />
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="5" class="px-5 py-14 text-center">
            <svg class="mx-auto w-10 h-10 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <p class="text-sm font-semibold text-gray-400">No departments yet</p>
            <p class="text-xs text-gray-300 mt-1">Add your first department to get started.</p>
        </td>
    </tr>
    @endforelse
</x-data-table>
@endsection
