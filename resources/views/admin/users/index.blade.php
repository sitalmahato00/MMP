@extends('layouts.app')
@section('title', 'User Management')

@section('content')

<x-page-header title="User Management" subtitle="Manage all system accounts and role assignments.">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.users.create') }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add User
        </x-btn>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <x-stat-card title="Total Users" value="{{ \App\Models\User::count() }}" color="blue" icon="<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'/></svg>"/>
    <x-stat-card title="Active Students" value="{{ \App\Models\User::role('student')->active()->count() }}" color="purple" icon="<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 14l9-5-9-5-9 5 9 5z'/><path d='M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z'/></svg>"/>
    <x-stat-card title="Active Teachers" value="{{ \App\Models\User::role('teacher')->active()->count() }}" color="green" icon="<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'/></svg>"/>
    <x-stat-card title="Inactive Users" value="{{ \App\Models\User::where('is_active', false)->count() }}" color="red" icon="<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'/></svg>"/>
</div>

{{-- Search / Filter Bar --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4 px-4 py-3">
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <x-input name="search" value="{{ request('search') }}" placeholder="Search name or email…" class="flex-1 min-w-[200px]"/>
        <x-select name="role">
            <option value="">All Roles</option>
            @foreach(['principal','hod','teacher','student','parent','alumni'] as $r)
                <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
            @endforeach
        </x-select>
        <x-select name="status">
            <option value="">All Status</option>
            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
        </x-select>
        <x-btn type="submit" variant="secondary">Filter</x-btn>
        @if(request()->anyFilled(['search','role','status']))
            <x-btn href="{{ route('admin.users.index') }}" variant="ghost" size="sm">Clear</x-btn>
        @endif
    </form>
</div>

{{-- Table --}}
<x-data-table :paginator="$users">
    <x-slot name="head">
        <tr>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider w-10">#</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">User</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Role</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Phone</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Joined</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
        </tr>
    </x-slot>

    @forelse($users as $user)
    <tr class="hover:bg-gray-50/70 transition-colors">
        <td class="px-5 py-3.5 text-gray-400 font-mono text-xs">{{ $user->id }}</td>
        <td class="px-5 py-3.5">
            <div class="flex items-center gap-3">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                     class="w-8 h-8 rounded-full object-cover ring-2 ring-gray-100 flex-shrink-0">
                <div class="min-w-0">
                    <p class="font-semibold text-gray-900 truncate">{{ $user->name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ $user->email }}</p>
                </div>
            </div>
        </td>
        <td class="px-5 py-3.5">
            @foreach($user->roles as $role)
                @php $roleColor = ['principal'=>'red','hod'=>'blue','teacher'=>'green','student'=>'purple','parent'=>'yellow','alumni'=>'gray'][$role->name] ?? 'gray'; @endphp
                <x-badge :color="$roleColor">{{ $role->name }}</x-badge>
            @endforeach
        </td>
        <td class="px-5 py-3.5 text-gray-500 text-xs">{{ $user->phone ?? '—' }}</td>
        <td class="px-5 py-3.5">
            <x-badge :color="$user->is_active ? 'green' : 'red'" :dot="true">
                {{ $user->is_active ? 'Active' : 'Inactive' }}
            </x-badge>
        </td>
        <td class="px-5 py-3.5 text-gray-400 text-xs whitespace-nowrap">{{ bsDate($user->created_at, 'Y, F d') }}</td>
        <td class="px-5 py-3.5">
            <x-table-actions
                :show="route('admin.users.show', $user)"
                :edit="route('admin.users.edit', $user)"
                :destroy="$user->id !== auth()->id() ? route('admin.users.destroy', $user) : null"
                name="{{ $user->name }}"
            />
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="7" class="px-5 py-14 text-center">
            <svg class="mx-auto w-10 h-10 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <p class="text-sm font-semibold text-gray-400">No users found</p>
            <p class="text-xs text-gray-300 mt-1">Try adjusting your search or filters.</p>
        </td>
    </tr>
    @endforelse
</x-data-table>

@endsection
