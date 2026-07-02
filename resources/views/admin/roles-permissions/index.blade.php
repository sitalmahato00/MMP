@extends('layouts.app')
@section('title', 'Roles & Permissions')

@section('content')
<x-page-header title="Roles & Permissions" subtitle="Review portal roles and the permissions assigned to each group.">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.users.index') }}" variant="secondary">Manage Users</x-btn>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
    <div class="kpi-card relative overflow-hidden rounded-2xl p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
         style="background: linear-gradient(135deg,#0F2E6E,#2563EB);">
        <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute -bottom-3 -left-3 h-14 w-14 rounded-full bg-white/5"></div>
        <div class="relative flex items-center gap-3">
            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xl font-black leading-tight text-white">{{ number_format($stats['roles']) }}</p>
                <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-wider text-white/80">Roles</p>
            </div>
        </div>
    </div>
    <div class="kpi-card relative overflow-hidden rounded-2xl p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
         style="background: linear-gradient(135deg,#2563EB,#3B82F6);">
        <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute -bottom-3 -left-3 h-14 w-14 rounded-full bg-white/5"></div>
        <div class="relative flex items-center gap-3">
            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xl font-black leading-tight text-white">{{ number_format($stats['permissions']) }}</p>
                <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-wider text-white/80">Permissions</p>
            </div>
        </div>
    </div>
    <div class="kpi-card relative overflow-hidden rounded-2xl p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
         style="background: linear-gradient(135deg,#10B981,#22C55E);">
        <div class="pointer-events-none absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute -bottom-3 -left-3 h-14 w-14 rounded-full bg-white/5"></div>
        <div class="relative flex items-center gap-3">
            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xl font-black leading-tight text-white">{{ number_format($stats['assigned_links']) }}</p>
                <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-wider text-white/80">Role-Permission Links</p>
            </div>
        </div>
    </div>
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