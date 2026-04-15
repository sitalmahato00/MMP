@extends('layouts.app')
@section('title', 'Notices')

@section('content')
<x-page-header title="Notice Board" subtitle="Publish and manage institutional notices.">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.notices.create') }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Post Notice
        </x-btn>
    </x-slot>
</x-page-header>

<x-search-filter>
    <x-input name="search" value="{{ request('search') }}" placeholder="Search notices…" class="flex-1 min-w-[200px]"/>
    <x-select name="type">
        <option value="">All Types</option>
        @foreach(['general','exam','holiday','event'] as $t)
            <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
        @endforeach
    </x-select>
    <x-select name="target">
        <option value="">All Audiences</option>
        @foreach(['all','students','teachers','parents'] as $a)
            <option value="{{ $a }}" {{ request('target') === $a ? 'selected' : '' }}>{{ ucfirst($a) }}</option>
        @endforeach
    </x-select>
</x-search-filter>

<x-data-table :paginator="$notices">
    <x-slot name="head">
        <tr>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Title</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Type</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Audience</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Published</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Expires</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
        </tr>
    </x-slot>

    @forelse($notices as $notice)
    @php
        $typeColors = ['general'=>'blue','exam'=>'red','holiday'=>'green','event'=>'purple'];
        $isExpired = $notice->expires_at && $notice->expires_at->isPast();
    @endphp
    <tr class="hover:bg-gray-50/70 transition-colors {{ $isExpired ? 'opacity-60' : '' }}">
        <td class="px-5 py-3.5">
            <p class="font-semibold text-gray-900 truncate max-w-xs">{{ $notice->title }}</p>
            <p class="text-xs text-gray-400 mt-0.5 truncate max-w-xs">{{ Str::limit(strip_tags($notice->content), 60) }}</p>
        </td>
        <td class="px-5 py-3.5">
            <x-badge :color="$typeColors[$notice->type] ?? 'gray'">{{ $notice->type }}</x-badge>
        </td>
        <td class="px-5 py-3.5">
            <x-badge color="gray">{{ $notice->target ?? 'all' }}</x-badge>
        </td>
        <td class="px-5 py-3.5 text-gray-400 text-xs whitespace-nowrap">
            {{ $notice->published_at?->format('d M Y') ?? $notice->created_at->format('d M Y') }}
        </td>
        <td class="px-5 py-3.5">
            @if($notice->expires_at)
                <x-badge :color="$isExpired ? 'red' : 'yellow'" :dot="true">
                    {{ $isExpired ? 'Expired' : $notice->expires_at->format('d M Y') }}
                </x-badge>
            @else
                <span class="text-xs text-gray-300">—</span>
            @endif
        </td>
        <td class="px-5 py-3.5">
            <x-table-actions
                :edit="route('admin.notices.edit', $notice)"
                :destroy="route('admin.notices.destroy', $notice)"
                name="{{ $notice->title }}"
            />
        </td>
    </tr>
    @empty
    <tr><td colspan="6"><x-empty-state title="No notices published" message="Post your first institutional notice." action="{{ route('admin.notices.create') }}" actionLabel="Post Notice"/></td></tr>
    @endforelse
</x-data-table>
@endsection
