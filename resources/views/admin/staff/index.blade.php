@extends('layouts.app')
@section('title', 'Administrative Staff')

@section('content')
<x-page-header title="Administrative Staff" subtitle="Manage non-teaching administrative profiles for the public directory.">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.staff.create') }}">Add Staff Member</x-btn>
    </x-slot>
</x-page-header>

<x-search-filter>
    <x-input name="search" value="{{ request('search') }}" placeholder="Search staff name…" class="flex-1 min-w-[200px]"/>
</x-search-filter>

<x-data-table :paginator="$staff">
    <x-slot name="head">
        <tr>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Staff Profile</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Department</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Contact Info</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
        </tr>
    </x-slot>

    @forelse($staff as $member)
    <tr class="hover:bg-gray-50/70 transition-colors">
        <td class="px-5 py-3.5 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <x-avatar :src="$member->photo_url" :name="$member->name" size="md"/>
                <div>
                    <div class="font-semibold text-gray-900">{{ $member->name }}</div>
                    <div class="text-xs text-[#8B0000] font-medium">{{ $member->designation ?? 'Staff' }}</div>
                </div>
            </div>
        </td>
        <td class="px-5 py-3.5 border-b border-gray-100 text-sm text-gray-600">
            {{ $member->department ?? 'General Administration' }}
        </td>
        <td class="px-5 py-3.5 border-b border-gray-100">
            <div class="text-sm text-gray-900">{{ $member->phone ?? '—' }}</div>
            <div class="text-xs text-gray-500">{{ $member->email ?? '—' }}</div>
        </td>
        <td class="px-5 py-3.5 border-b border-gray-100">
            <x-badge :color="$member->is_active ? 'green' : 'red'">
                {{ $member->is_active ? 'Active' : 'Inactive' }}
            </x-badge>
        </td>
        <td class="px-5 py-3.5 border-b border-gray-100">
            <x-table-actions
                :edit="route('admin.staff.edit', $member)"
                :destroy="route('admin.staff.destroy', $member)"
                name="{{ $member->name }}"
            />
        </td>
    </tr>
    @empty
    <tr><td colspan="5"><x-empty-state title="No staff members found" message="Add administrative profiles to populate the public directory."/></td></tr>
    @endforelse
</x-data-table>
@endsection
