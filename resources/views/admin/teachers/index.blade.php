@extends('layouts.app')
@section('title', 'Teachers')

@section('content')
<x-page-header title="Teachers" subtitle="Manage all faculty members.">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.teachers.create') }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add Teacher
        </x-btn>
    </x-slot>
</x-page-header>

<x-search-filter>
    <x-input name="search" value="{{ request('search') }}" placeholder="Search teacher name…" class="flex-1 min-w-[200px]"/>
    <x-select name="department_id">
        <option value="">All Departments</option>
        @foreach($departments as $dept)
            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                {{ $dept->name }}
            </option>
        @endforeach
    </x-select>
</x-search-filter>

<x-data-table :paginator="$teachers">
    <x-slot name="head">
        <tr>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Teacher</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Department</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Qualification</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Hire Date</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
        </tr>
    </x-slot>

    @forelse($teachers as $teacher)
    <tr class="hover:bg-gray-50/70 transition-colors">
        <td class="px-5 py-3.5">
            <div class="flex items-center gap-3">
                <x-avatar :src="$teacher->user?->avatar_url" :name="$teacher->user?->name ?? 'Teacher'" size="sm"/>
                <div class="min-w-0">
                    <p class="font-semibold text-gray-900 truncate">{{ $teacher->user?->name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ $teacher->user?->email }}</p>
                </div>
            </div>
        </td>
        <td class="px-5 py-3.5">
            <x-badge color="blue">{{ $teacher->department?->code ?? '—' }}</x-badge>
        </td>
        <td class="px-5 py-3.5 text-gray-500 text-sm">{{ $teacher->qualification ?? '—' }}</td>
        <td class="px-5 py-3.5 text-gray-400 text-xs whitespace-nowrap">
            {{ $teacher->hire_date ? \Carbon\Carbon::parse($teacher->hire_date)->format('d M Y') : '—' }}
        </td>
        <td class="px-5 py-3.5">
            <x-table-actions
                :show="route('admin.teachers.show', $teacher)"
                :edit="route('admin.teachers.edit', $teacher)"
                :destroy="route('admin.teachers.destroy', $teacher)"
                name="{{ $teacher->user?->name }}"
            />
        </td>
    </tr>
    @empty
    <tr><td colspan="5">
        <x-empty-state title="No teachers found"
                       message="Add your first faculty member."
                       action="{{ route('admin.teachers.create') }}"
                       actionLabel="Add Teacher"/>
    </td></tr>
    @endforelse
</x-data-table>
@endsection
