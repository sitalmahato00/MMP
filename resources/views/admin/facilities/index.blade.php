@extends('layouts.app')
@section('title', 'Facilities & Resources')

@section('content')
<x-page-header title="Facilities & Resources" subtitle="Manage classrooms, labs, workshops, and multi-resource web pages.">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.facilities.create') }}">Add Facility</x-btn>
    </x-slot>
</x-page-header>

<x-search-filter>
    <x-input name="search" value="{{ request('search') }}" placeholder="Search by name..." class="flex-1 min-w-[200px]"/>
</x-search-filter>

<x-data-table :paginator="$facilities">
    <x-slot name="head">
        <tr>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Name</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Category</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Associated With</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Files Attached</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
        </tr>
    </x-slot>

    @forelse($facilities as $facility)
    <tr class="hover:bg-gray-50/70 transition-colors">
        <td class="px-5 py-3.5 border-b border-gray-100">
            <span class="font-semibold text-gray-900">{{ $facility->name }}</span>
            <div class="text-xs text-gray-400 mt-0.5 truncate max-w-[200px]">{{ $facility->location ?? 'No location specified' }}</div>
        </td>
        <td class="px-5 py-3.5 border-b border-gray-100">
            <x-badge color="purple">{{ ucfirst($facility->category) }}</x-badge>
        </td>
        <td class="px-5 py-3.5 border-b border-gray-100 text-sm text-gray-600">
            @if($facility->department)
                {{ $facility->department->name }}
            @elseif($facility->program)
                {{ $facility->program->name }}
            @else
                <span class="text-gray-400 italic">General/Campus</span>
            @endif
        </td>
        <td class="px-5 py-3.5 border-b border-gray-100">
            <div class="text-sm font-medium text-gray-600 space-x-2">
                <span>{{ is_array($facility->images) ? count($facility->images) : 0 }} Images</span>
                <span class="text-gray-300">|</span>
                <span>{{ is_array($facility->documents) ? count($facility->documents) : 0 }} Docs</span>
            </div>
        </td>
        <td class="px-5 py-3.5 border-b border-gray-100">
            <x-badge :color="$facility->is_published ? 'green' : 'red'">
                {{ $facility->is_published ? 'Published' : 'Hidden' }}
            </x-badge>
        </td>
        <td class="px-5 py-3.5 border-b border-gray-100">
            <x-table-actions
                :edit="route('admin.facilities.edit', $facility)"
                :destroy="route('admin.facilities.destroy', $facility)"
                name="{{ $facility->name }}"
            />
        </td>
    </tr>
    @empty
    <tr><td colspan="6"><x-empty-state title="No facilities found" message="Create a lab, workshop, or resource page."/></td></tr>
    @endforelse
</x-data-table>
@endsection
