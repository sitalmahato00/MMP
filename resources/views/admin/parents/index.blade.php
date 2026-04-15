@extends('layouts.app')
@section('title', 'Parents')

@section('content')
<x-page-header title="Parents & Guardians" subtitle="Manage student guardians.">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.parents.create') }}">Add Parent</x-btn>
    </x-slot>
</x-page-header>

<x-search-filter>
    <x-input name="search" value="{{ request('search') }}" placeholder="Search parent name…" class="flex-1 min-w-[200px]"/>
</x-search-filter>

<x-data-table :paginator="$parents">
    <x-slot name="head">
        <tr>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Parent/Guardian</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Contact</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Linked Students</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
        </tr>
    </x-slot>

    @forelse($parents as $parent)
    <tr class="hover:bg-gray-50/70 transition-colors">
        <td class="px-5 py-3.5">
            <div class="flex items-center gap-3">
                <x-avatar :src="$parent->user?->avatar_url" :name="$parent->user?->name ?? 'Parent'" size="sm"/>
                <div class="min-w-0">
                    <p class="font-semibold text-gray-900 truncate">{{ $parent->user?->name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ $parent->relation_to_student }}</p>
                </div>
            </div>
        </td>
        <td class="px-5 py-3.5">
            <p class="text-sm text-gray-600">{{ $parent->user?->phone ?? '—' }}</p>
            <p class="text-xs text-gray-400">{{ $parent->user?->email }}</p>
        </td>
        <td class="px-5 py-3.5">
            <div class="flex flex-wrap gap-1">
                @forelse($parent->students as $student)
                    <x-badge color="blue">{{ $student->user->name }}</x-badge>
                @empty
                    <span class="text-xs text-gray-400 italic">None</span>
                @endforelse
            </div>
        </td>
        <td class="px-5 py-3.5">
            <x-table-actions
                :edit="route('admin.parents.edit', $parent)"
                :destroy="route('admin.parents.destroy', $parent)"
                name="{{ $parent->user?->name }}"
            />
        </td>
    </tr>
    @empty
    <tr><td colspan="4"><x-empty-state title="No parents found" message="Register guardians to link them to students."/></td></tr>
    @endforelse
</x-data-table>
@endsection
