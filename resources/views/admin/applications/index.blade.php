@extends('layouts.app')
@section('title', 'Applications')

@section('content')
<x-page-header title="Admission Applications" subtitle="View and manage online admission applications.">
</x-page-header>

<x-search-filter>
    <x-input name="search" value="{{ request('search') }}" placeholder="Search by name, email, phone…" class="flex-1 min-w-[200px]"/>
    <x-select name="status">
        <option value="">All Status</option>
        @foreach(['pending' => 'Pending', 'reviewed' => 'Reviewed', 'contacted' => 'Contacted', 'accepted' => 'Accepted', 'rejected' => 'Rejected'] as $value => $label)
            <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </x-select>
    <x-select name="department_id">
        <option value="">All Departments</option>
        @foreach($departments as $dept)
            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
        @endforeach
    </x-select>
</x-search-filter>

<x-data-table :paginator="$applications">
    <x-slot name="head">
        <tr>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Applicant</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Contact</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Department</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Applied</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
        </tr>
    </x-slot>

    @forelse($applications as $app)
    @php
        $statusColors = ['pending'=>'amber','reviewed'=>'blue','contacted'=>'purple','accepted'=>'green','rejected'=>'red'];
    @endphp
    <tr class="hover:bg-gray-50/70 transition-colors">
        <td class="px-5 py-3.5">
            <p class="font-semibold text-gray-900">{{ $app->full_name }}</p>
            @if($app->gender)
                <p class="text-xs text-gray-400 mt-0.5 capitalize">{{ $app->gender }}</p>
            @endif
        </td>
        <td class="px-5 py-3.5">
            <p class="text-sm text-gray-700">{{ $app->email }}</p>
            <p class="text-xs text-gray-400">{{ $app->phone }}</p>
        </td>
        <td class="px-5 py-3.5">
            <span class="text-sm text-gray-600">{{ $app->department?->name ?? '—' }}</span>
        </td>
        <td class="px-5 py-3.5">
            <x-badge :color="$statusColors[$app->status] ?? 'gray'">{{ ucfirst($app->status) }}</x-badge>
        </td>
        <td class="px-5 py-3.5 text-gray-400 text-xs whitespace-nowrap">
            {{ bsDate($app->created_at, 'Y, F d') }}
        </td>
        <td class="px-5 py-3.5 text-right">
            <div class="flex items-center justify-end gap-1">
                <a href="{{ route('admin.applications.show', $app) }}" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-700 transition-colors" title="View">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>
                <form method="POST" action="{{ route('admin.applications.destroy', $app) }}" onsubmit="return confirm('Delete this application?')">
                    @csrf @method('DELETE')
                    <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600 transition-colors" title="Delete">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            </div>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="6" class="px-5 py-12 text-center">
            <div class="flex flex-col items-center gap-2">
                <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-gray-400 font-medium">No applications yet</p>
            </div>
        </td>
    </tr>
    @endforelse
</x-data-table>
@endsection
