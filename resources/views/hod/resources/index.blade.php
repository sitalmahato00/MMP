@extends('layouts.app')
@section('title', 'Resources')

@section('content')
<x-page-header title="Resources" subtitle="Manage and upload resources for public, teachers, or students.">
    <x-slot name="actions">
        <x-btn href="{{ route('hod.resources.create') }}">Upload Resource</x-btn>
    </x-slot>
</x-page-header>

<x-data-table :paginator="$resources">
    <x-slot name="head">
        <tr>
            <th>Title</th>
            <th>Category</th>
            <th>Audience</th>
            <th>File</th>
            <th>Uploaded</th>
        </tr>
    </x-slot>
    @forelse($resources as $resource)
        <tr>
            <td>{{ $resource->title }}</td>
            <td>{{ $resource->category }}</td>
            <td>{{ ucfirst($resource->audience) }}</td>
            <td>
                <a href="{{ Storage::disk('public')->url($resource->file_path) }}" target="_blank" class="text-blue-600 underline">View</a>
            </td>
            <td>{{ bsDate($resource->created_at, 'Y, F d') }}</td>
        </tr>
    @empty
        <tr><td colspan="5">No resources uploaded yet.</td></tr>
    @endforelse
</x-data-table>
@endsection
