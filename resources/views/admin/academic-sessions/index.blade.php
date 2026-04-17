@extends('layouts.app')
@section('title', 'Academic Sessions')

@section('content')
<x-page-header title="Academic Sessions" subtitle="Manage and activate academic year sessions.">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.academic-sessions.create') }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            New Session
        </x-btn>
    </x-slot>
</x-page-header>

<x-data-table>
    <x-slot name="head">
        <tr>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Session Name</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Start Date</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">End Date</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
        </tr>
    </x-slot>

    @forelse($sessions as $session)
    <tr class="hover:bg-gray-50/70 transition-colors">
        <td class="px-5 py-3.5 font-semibold text-gray-900">{{ $session->name }}</td>
        <td class="px-5 py-3.5 text-gray-500 text-sm">{{ bsDate($session->start_date, 'd F Y') }}</td>
        <td class="px-5 py-3.5 text-gray-500 text-sm">{{ bsDate($session->end_date, 'd F Y') }}</td>
        <td class="px-5 py-3.5">
            @if($session->is_current)
                <x-badge color="green" :dot="true">Active</x-badge>
            @else
                <x-badge color="gray">Inactive</x-badge>
            @endif
        </td>
        <td class="px-5 py-3.5">
            <div class="flex items-center justify-end gap-2">
                @if(!$session->is_current)
                <form method="POST" action="{{ route('admin.academic-sessions.set-current', $session) }}">
                    @csrf @method('PATCH')
                    <x-btn type="submit" variant="secondary" size="sm">Set Active</x-btn>
                </form>
                @endif
                <x-table-actions
                    :edit="route('admin.academic-sessions.edit', $session)"
                    :destroy="!$session->is_current ? route('admin.academic-sessions.destroy', $session) : null"
                    name="{{ $session->name }}"
                />
            </div>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="5">
            <x-empty-state title="No sessions created yet"
                           message="Create your first academic session to begin."
                           action="{{ route('admin.academic-sessions.create') }}"
                           actionLabel="New Session"/>
        </td>
    </tr>
    @endforelse
</x-data-table>
@endsection
