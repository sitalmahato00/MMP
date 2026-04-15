@extends('layouts.app')
@section('title', 'Students')

@section('content')
<x-page-header title="Students" subtitle="View and manage all enrolled students.">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.students.create') }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add Student
        </x-btn>
    </x-slot>
</x-page-header>

<x-search-filter>
    <x-input name="search" value="{{ request('search') }}" placeholder="Search name or admission no…" class="flex-1 min-w-[200px]"/>
    <x-select name="program_id">
        <option value="">All Programs</option>
        @foreach($programs as $program)
            <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                {{ $program->name }}
            </option>
        @endforeach
    </x-select>
    <x-select name="semester">
        <option value="">All Semesters</option>
        @for($i = 1; $i <= 8; $i++)
            <option value="{{ $i }}" {{ request('semester') == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
        @endfor
    </x-select>
</x-search-filter>

<x-data-table :paginator="$students">
    <x-slot name="head">
        <tr>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Student</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Admission No.</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Program</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Semester</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Guardian</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
        </tr>
    </x-slot>

    @forelse($students as $student)
    <tr class="hover:bg-gray-50/70 transition-colors">
        <td class="px-5 py-3.5">
            <div class="flex items-center gap-3">
                <x-avatar :src="$student->user?->avatar_url" :name="$student->user?->name ?? 'Student'" size="sm"/>
                <div class="min-w-0">
                    <p class="font-semibold text-gray-900 truncate">{{ $student->user?->name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ $student->user?->email }}</p>
                </div>
            </div>
        </td>
        <td class="px-5 py-3.5 font-mono text-xs text-gray-600">{{ $student->admission_number }}</td>
        <td class="px-5 py-3.5">
            <x-badge color="blue">{{ $student->program?->name ?? '—' }}</x-badge>
        </td>
        <td class="px-5 py-3.5">
            <x-badge color="purple">Sem {{ $student->current_semester }}</x-badge>
        </td>
        <td class="px-5 py-3.5 text-gray-500 text-sm">
            {{ $student->guardian?->user?->name ?? '—' }}
        </td>
        <td class="px-5 py-3.5">
            <x-table-actions
                :show="route('admin.students.show', $student)"
                :edit="route('admin.students.edit', $student)"
                :destroy="route('admin.students.destroy', $student)"
                name="{{ $student->user?->name }}"
            />
        </td>
    </tr>
    @empty
    <tr><td colspan="6">
        <x-empty-state title="No students found"
                       message="Enroll your first student or adjust your filters."
                       action="{{ route('admin.students.create') }}"
                       actionLabel="Add Student"/>
    </td></tr>
    @endforelse
</x-data-table>
@endsection
