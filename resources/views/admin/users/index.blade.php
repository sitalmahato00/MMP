@extends('layouts.app')
@section('title', 'User Management')

@section('content')

<div x-data="{
    view: localStorage.getItem('mmp_users_view') ?? 'table',
    setView(v) { this.view = v; localStorage.setItem('mmp_users_view', v); },
}" class="space-y-5">

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
            @foreach(['principal','teacher','student','parent','alumni'] as $r)
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
<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-3.5">
        <p class="text-sm text-gray-500">
            @if($users->total() > 0)
                Showing <span class="font-semibold text-gray-700">{{ $users->firstItem() }}–{{ $users->lastItem() }}</span>
                of <span class="font-semibold text-gray-700">{{ number_format($users->total()) }}</span> users
            @else
                No users match your filters
            @endif
        </p>
        <div class="flex items-center rounded-xl border border-gray-200 p-1 gap-0.5 flex-shrink-0">
            <button type="button" @click="setView('table')"
                    :class="view === 'table' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:text-gray-700'"
                    class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"/></svg>
                Table
            </button>
            <button type="button" @click="setView('cards')"
                    :class="view === 'cards' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:text-gray-700'"
                    class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 6v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Cards
            </button>
        </div>
    </div>

    {{-- TABLE VIEW --}}
    <div x-show="view === 'table'" x-cloak>
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
    </div>

    {{-- CARD VIEW --}}
    <div x-show="view === 'cards'" x-cloak class="p-5">
        @if($users->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <p class="text-sm font-medium text-gray-500">No users found.</p>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($users as $user)
            @php
                $gradients = ['from-blue-500 to-indigo-600','from-violet-500 to-purple-600','from-emerald-500 to-teal-600','from-amber-500 to-orange-600','from-rose-500 to-pink-600','from-cyan-500 to-sky-600'];
                $grad = $gradients[$user->id % 6];
            @endphp
            <div class="group relative rounded-2xl border border-gray-200 bg-white p-5 shadow-sm hover:shadow-md transition-all">
                <div class="flex flex-col items-center text-center">
                    @if($user->avatar)
                        <img src="{{ asset('storage/'.$user->avatar) }}" alt="{{ $user->name }}"
                             class="h-16 w-16 rounded-2xl object-cover ring-2 ring-white shadow"/>
                    @else
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br {{ $grad }} text-2xl font-black text-white shadow">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                    <h3 class="mt-3 text-sm font-bold text-gray-900">{{ $user->name }}</h3>
                    <p class="mt-0.5 text-xs text-gray-500">{{ $user->email }}</p>
                </div>
                <div class="mt-3 flex flex-wrap items-center justify-center gap-1.5">
                    @foreach($user->roles as $role)
                        @php $roleColor = ['principal'=>'red','hod'=>'blue','teacher'=>'green','student'=>'purple','parent'=>'yellow','alumni'=>'gray'][$role->name] ?? 'gray'; @endphp
                        <span class="rounded-lg bg-{{ $roleColor }}-50 px-2 py-0.5 text-[11px] font-bold text-{{ $roleColor }}-700">{{ $role->name }}</span>
                    @endforeach
                </div>
                <div class="mt-3 flex items-center justify-center">
                    @if($user->is_active)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Active
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>Inactive
                        </span>
                    @endif
                </div>
                <div class="mt-3 space-y-0.5 text-center">
                    <p class="text-xs text-gray-600">{{ $user->phone ?? '—' }}</p>
                    <p class="text-[11px] text-gray-400">Joined {{ bsDate($user->created_at, 'Y, F d') }}</p>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <a href="{{ route('admin.users.show', $user) }}" class="rounded-lg border border-gray-200 py-1.5 text-center text-xs font-semibold text-gray-600 hover:bg-gray-50 transition">View</a>
                    <a href="{{ route('admin.users.edit', $user) }}" class="rounded-lg bg-gray-900 py-1.5 text-center text-xs font-bold text-white hover:bg-gray-700 transition">Edit</a>
                </div>
            </div>
            @endforeach
        </div>
        <div class="border-t border-gray-100 mt-5 pt-4">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

</div>

@endsection
