@extends('layouts.app')
@section('title', 'Banners')

@section('content')
<x-page-header title="Homepage Banners" subtitle="Manage the public site hero slideshow images.">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.banners.create') }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add Banner
        </x-btn>
    </x-slot>
</x-page-header>

<div class="space-y-3">
    @forelse($banners as $banner)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden flex items-center gap-4 pr-4">
        <div class="w-32 h-20 flex-shrink-0 bg-gray-50 overflow-hidden">
            @if($banner->image)
                <img src="{{ asset('storage/'.$banner->image) }}" class="w-full h-full object-cover"/>
            @else
                <div class="w-full h-full flex items-center justify-center text-gray-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            @endif
        </div>
        <div class="flex-1 min-w-0 py-3">
            <p class="font-semibold text-gray-900 truncate">{{ $banner->title ?? 'Untitled Banner' }}</p>
            @if($banner->subtitle)
                <p class="text-xs text-gray-400 truncate mt-0.5">{{ $banner->subtitle }}</p>
            @endif
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <x-badge :color="$banner->is_active ? 'green' : 'gray'" :dot="true">
                {{ $banner->is_active ? 'Active' : 'Hidden' }}
            </x-badge>
            <x-table-actions
                :edit="route('admin.banners.edit', $banner)"
                :destroy="route('admin.banners.destroy', $banner)"
                name="{{ $banner->title ?? 'this banner' }}"
            />
        </div>
    </div>
    @empty
        <x-empty-state title="No banners added"
                       message="Add hero banners for the public site homepage."
                       action="{{ route('admin.banners.create') }}"
                       actionLabel="Add Banner"/>
    @endforelse
</div>
@endsection
