@extends('layouts.app')
@section('title', 'Roles & Permissions')

@section('content')
<x-page-header title="Roles & Permissions" subtitle="Review portal roles and the permissions assigned to each group.">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.users.index') }}" variant="secondary">Manage Users</x-btn>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <x-card class="border-l-4 border-l-[#8B0000]">
        <p class="text-xs font-bold uppercase tracking-widest text-gray-500">Roles</p>
        <p class="mt-2 text-2xl font-black text-gray-900">{{ number_format($stats['roles']) }}</p>
    </x-card>
    <x-card class="border-l-4 border-l-blue-500">
        <p class="text-xs font-bold uppercase tracking-widest text-gray-500">Permissions</p>
        <p class="mt-2 text-2xl font-black text-gray-900">{{ number_format($stats['permissions']) }}</p>
    </x-card>
    <x-card class="border-l-4 border-l-emerald-500">
        <p class="text-xs font-bold uppercase tracking-widest text-gray-500">Role-Permission Links</p>
        <p class="mt-2 text-2xl font-black text-gray-900">{{ number_format($stats['assigned_links']) }}</p>
    </x-card>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <x-card>
        <x-slot name="header">
            <h3 class="font-bold text-gray-800">Roles</h3>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ $roles->count() }} total</span>
        </x-slot>

        <div class="space-y-4">
            @forelse($roles as $role)
                <div class="rounded-xl border border-gray-100 p-4 hover:border-[#8B0000]/20 hover:shadow-sm transition-all">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-semibold text-gray-900 capitalize">{{ $role->name }}</p>
                            <p class="text-xs text-gray-500 mt-1">Guard: {{ $role->guard_name }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-[#8B0000]/10 px-3 py-1 text-xs font-bold text-[#8B0000]">
                            {{ $role->permissions->count() }} permissions
                        </span>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        @forelse($role->permissions->take(6) as $permission)
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-600">
                                {{ $permission->name }}
                            </span>
                        @empty
                            <span class="text-xs text-gray-400">No permissions assigned</span>
                        @endforelse
                        @if($role->permissions->count() > 6)
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-500">
                                +{{ $role->permissions->count() - 6 }} more
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="py-8 text-center text-sm text-gray-400">No roles configured.</p>
            @endforelse
        </div>
    </x-card>

    <x-card>
        <x-slot name="header">
            <h3 class="font-bold text-gray-800">Permissions</h3>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ $permissions->count() }} total</span>
        </x-slot>

        <div class="flex flex-wrap gap-2">
            @forelse($permissions as $permission)
                <span class="inline-flex items-center rounded-full border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm">
                    {{ $permission->name }}
                </span>
            @empty
                <p class="py-8 text-center text-sm text-gray-400">No permissions configured.</p>
            @endforelse
        </div>
    </x-card>
</div>
@endsection