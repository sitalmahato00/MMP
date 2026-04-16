@extends('layouts.app')
@section('title', 'Downloads')

@section('content')
<x-page-header title="Downloads" subtitle="Manage public downloadable forms and resources.">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.downloads.create') }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add Download
        </x-btn>
    </x-slot>
</x-page-header>

<x-data-table :paginator="$downloads">
    <x-slot name="head">
        <tr>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Title</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Category</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">File</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Added</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
        </tr>
    </x-slot>

    @forelse($downloads as $download)
    <tr class="hover:bg-gray-50/70 transition-colors">
        <td class="px-5 py-3.5 font-semibold text-gray-900">{{ $download->title }}</td>
        <td class="px-5 py-3.5">
            <x-badge color="gray">{{ $download->category ?? 'general' }}</x-badge>
        </td>
        <td class="px-5 py-3.5">
            @if($download->file_path)
                <x-file-preview
                    :url="asset('storage/'.$download->file_path)"
                    :filename="$download->file_name ?? basename($download->file_path)"
                    :type="$download->file_type ?? pathinfo($download->file_path, PATHINFO_EXTENSION)"
                />
            @else
                <span class="text-xs text-gray-300">No file</span>
            @endif
        </td>
        <td class="px-5 py-3.5 text-gray-400 text-xs whitespace-nowrap">{{ $download->created_at->format('d M Y') }}</td>
        <td class="px-5 py-3.5">
            <x-table-actions
                :edit="route('admin.downloads.edit', $download)"
                :destroy="route('admin.downloads.destroy', $download)"
                name="{{ $download->title }}"
            />
        </td>
    </tr>
    @empty
    <tr><td colspan="5">
        <x-empty-state title="No downloads added"
                       message="Upload forms, syllabi, or question banks for public access."
                       action="{{ route('admin.downloads.create') }}"
                       actionLabel="Add Download"/>
    </td></tr>
    @endforelse
</x-data-table>
@endsection
