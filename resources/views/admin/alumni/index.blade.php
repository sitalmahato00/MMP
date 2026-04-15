@extends('layouts.app')
@section('title', 'Alumni')

@section('content')
<x-page-header title="Alumni Network" subtitle="Manage graduated students database.">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.alumni.create') }}">Add Alumni</x-btn>
    </x-slot>
</x-page-header>

<x-search-filter>
    <x-input name="search" value="{{ request('search') }}" placeholder="Search alumni name…" class="flex-1 min-w-[200px]"/>
</x-search-filter>

<x-data-table :paginator="$alumni">
    <x-slot name="head">
        <tr>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Alumnus</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Program</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Graduation Year</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Current Job/Company</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
        </tr>
    </x-slot>

    @forelse($alumni as $alum)
    <tr class="hover:bg-gray-50/70 transition-colors">
        <td class="px-5 py-3.5">
            <div class="flex items-center gap-3">
                <x-avatar :src="$alum->user?->avatar_url" :name="$alum->user?->name ?? 'Alumnus'" size="sm"/>
                <div class="min-w-0">
                    <p class="font-semibold text-gray-900 truncate">{{ $alum->user?->name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ $alum->user?->email }}</p>
                </div>
            </div>
        </td>
        <td class="px-5 py-3.5 text-sm text-gray-600">
            <x-badge color="blue">{{ $alum->program?->name ?? '—' }}</x-badge>
        </td>
        <td class="px-5 py-3.5 text-sm font-semibold text-gray-700">
            {{ $alum->graduation_year ?? '—' }}
        </td>
        <td class="px-5 py-3.5">
            <p class="text-sm text-gray-900">{{ $alum->current_job_title ?? '—' }}</p>
            <p class="text-xs text-gray-400">{{ $alum->company_name }}</p>
        </td>
        <td class="px-5 py-3.5">
            <x-table-actions
                :edit="route('admin.alumni.edit', $alum)"
                :destroy="route('admin.alumni.destroy', $alum)"
                name="{{ $alum->user?->name }}"
            />
        </td>
    </tr>
    @empty
    <tr><td colspan="5"><x-empty-state title="No alumni found" message="Register graduated students to build the network."/></td></tr>
    @endforelse
</x-data-table>
@endsection
