@extends('layouts.app')
@section('title', 'Programs')

@section('content')
<x-page-header title="Programs" subtitle="Manage all academic programs across departments.">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.programs.create') }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add Program
        </x-btn>
    </x-slot>
</x-page-header>

<x-search-filter>
    <x-input name="search" value="{{ request('search') }}" placeholder="Search program name…" class="flex-1 min-w-[200px]"/>
    <x-select name="department_id">
        <option value="">All Departments</option>
        @foreach($departments as $dept)
            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
        @endforeach
    </x-select>
</x-search-filter>

<x-data-table :paginator="$programs">
    <x-slot name="head">
        <tr>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Program</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Department</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Duration</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Semesters</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
        </tr>
    </x-slot>

    @forelse($programs as $program)
    <tr class="hover:bg-gray-50/70 transition-colors">
        <td class="px-5 py-3.5 font-semibold text-gray-900">{{ $program->name }}</td>
        <td class="px-5 py-3.5"><x-badge color="blue">{{ $program->department->code ?? '—' }}</x-badge></td>
        <td class="px-5 py-3.5 text-gray-500 text-sm">{{ $program->duration_years }} yr{{ $program->duration_years > 1 ? 's' : '' }}</td>
        <td class="px-5 py-3.5 text-gray-500 text-sm">{{ $program->total_semesters }} semesters</td>
        <td class="px-5 py-3.5">
            <x-table-actions
                :edit="route('admin.programs.edit', $program)"
                :destroy="route('admin.programs.destroy', $program)"
                name="{{ $program->name }}"
            />
        </td>
    </tr>
    @empty
    <tr><td colspan="5"><x-empty-state title="No programs found" message="Add programs under a department to get started." action="{{ route('admin.programs.create') }}" actionLabel="Add Program"/></td></tr>
    @endforelse
</x-data-table>
@endsection
