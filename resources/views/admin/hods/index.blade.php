@extends('layouts.app')
@section('title', 'HOD Management')

@section('content')

<x-page-header title="HOD Management" subtitle="Manage Heads of Department and their assignments.">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.hods.create') }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add HOD
        </x-btn>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <x-stat-card title="Total HODs" value="{{ \App\Models\User::role('hod')->count() }}" color="blue" icon="<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'/></svg>"/>
    <x-stat-card title="Active HODs" value="{{ \App\Models\User::role('hod')->active()->count() }}" color="green" icon="<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'/></svg>"/>
    <x-stat-card title="Assigned Departments" value="{{ \App\Models\Department::whereNotNull('hod_id')->count() }}" color="purple" icon="<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'/></svg>"/>
</div>

<div x-data="{
    view: localStorage.getItem('mmp_hods_view') ?? 'table',
    setView(v) { this.view = v; localStorage.setItem('mmp_hods_view', v); },
}" class="space-y-4">

{{-- Search / Filter Bar --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-3">
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <x-input name="search" value="{{ request('search') }}" placeholder="Search name or email…" class="flex-1 min-w-[200px]"/>
        <x-select name="status">
            <option value="">All Status</option>
            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
        </x-select>
        <x-btn type="submit" variant="secondary">Filter</x-btn>
        @if(request()->anyFilled(['search','status']))
            <x-btn href="{{ route('admin.hods.index') }}" variant="ghost" size="sm">Clear</x-btn>
        @endif
    </form>
</div>

{{-- Main Content Panel --}}
<div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    {{-- Panel header: result count + view toggle --}}
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-3.5">
        <p class="text-sm text-slate-500">
            @if($hods->total() > 0)
                Showing <span class="font-semibold text-slate-700">{{ $hods->firstItem() }}–{{ $hods->lastItem() }}</span>
                of <span class="font-semibold text-slate-700">{{ number_format($hods->total()) }}</span> HODs
            @else
                No HODs match your filters
            @endif
        </p>

        {{-- View toggle --}}
        <div class="flex items-center rounded-xl border border-slate-200 p-1 gap-0.5 flex-shrink-0">
            <button type="button" @click="setView('table')"
                    :class="view === 'table' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-700'"
                    class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"/></svg>
                Table
            </button>
            <button type="button" @click="setView('cards')"
                    :class="view === 'cards' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-700'"
                    class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Cards
            </button>
        </div>
    </div>

{{-- Card View --}}
<div x-show="view === 'cards'" x-cloak>
    {{-- Card View --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" x-data="{ drawerOpen: false, selectedHod: null }">
        @forelse($hods as $hod)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
            <div class="p-5">
                <div class="flex items-start gap-4">
                    <img src="{{ $hod->avatar_url }}" alt="{{ $hod->name }}"
                         class="w-16 h-16 rounded-full object-cover ring-2 ring-gray-100 flex-shrink-0">
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-900 truncate">{{ $hod->name }}</h3>
                        <p class="text-xs text-gray-500 truncate mt-0.5">{{ $hod->email }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <x-badge :color="$hod->is_active ? 'green' : 'red'" :dot="true">
                                {{ $hod->is_active ? 'Active' : 'Inactive' }}
                            </x-badge>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100 space-y-2">
                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        @if($hod->hodDepartment)
                            <span class="text-gray-700 font-medium truncate">{{ $hod->hodDepartment->name }}</span>
                        @else
                            <span class="text-gray-400 italic">Not assigned</span>
                        @endif
                    </div>
                    
                    @if($hod->phone)
                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span class="text-gray-600">{{ $hod->phone }}</span>
                    </div>
                    @endif
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <button @click="drawerOpen = true; selectedHod = {{ $hod->id }}"
                            class="flex-1 px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                        Quick View
                    </button>
                    <a href="{{ route('admin.hods.edit', $hod) }}"
                       class="px-3 py-2 text-sm font-medium text-gray-600 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>
                    @if($hod->id !== auth()->id())
                    <form action="{{ route('admin.hods.destroy', $hod) }}" method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete {{ $hod->name }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-14 text-center">
                <svg class="mx-auto w-10 h-10 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <p class="text-sm font-semibold text-gray-400">No HODs found</p>
                <p class="text-xs text-gray-300 mt-1">Try adjusting your search or filters.</p>
            </div>
        </div>
        @endforelse

        {{-- Drawer Component --}}
        <div x-show="drawerOpen" 
             x-cloak
             @keydown.escape.window="drawerOpen = false"
             class="fixed inset-0 z-50 overflow-hidden">
            {{-- Backdrop --}}
            <div x-show="drawerOpen"
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="drawerOpen = false"
                 class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"></div>

            {{-- Drawer Panel --}}
            <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
                <div x-show="drawerOpen"
                     x-transition:enter="transform transition ease-in-out duration-300"
                     x-transition:enter-start="translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transform transition ease-in-out duration-300"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="translate-x-full"
                     class="w-screen max-w-md">
                    <div class="flex h-full flex-col bg-white shadow-xl">
                        {{-- Drawer Header --}}
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-6">
                            <div class="flex items-start justify-between">
                                <h2 class="text-lg font-semibold text-gray-900">HOD Details</h2>
                                <button @click="drawerOpen = false" class="text-gray-400 hover:text-gray-500">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Drawer Content --}}
                        <div class="flex-1 overflow-y-auto px-6 py-6">
                            <template x-if="selectedHod">
                                <div>
                                    @foreach($hods as $hod)
                                    <div x-show="selectedHod === {{ $hod->id }}">
                                        <div class="flex items-center gap-4 mb-6">
                                            <img src="{{ $hod->avatar_url }}" alt="{{ $hod->name }}"
                                                 class="w-20 h-20 rounded-full object-cover ring-4 ring-white shadow-lg">
                                            <div>
                                                <h3 class="text-xl font-bold text-gray-900">{{ $hod->name }}</h3>
                                                <p class="text-sm text-gray-600 mt-1">{{ $hod->email }}</p>
                                                <div class="flex items-center gap-2 mt-2">
                                                    <x-badge color="blue">HOD</x-badge>
                                                    <x-badge :color="$hod->is_active ? 'green' : 'red'" :dot="true">
                                                        {{ $hod->is_active ? 'Active' : 'Inactive' }}
                                                    </x-badge>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="space-y-6">
                                            {{-- Personal Info --}}
                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-900 mb-3">Personal Information</h4>
                                                <div class="space-y-3">
                                                    <div>
                                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</p>
                                                        <p class="mt-1 text-sm text-gray-900">{{ $hod->phone ?? '—' }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</p>
                                                        <p class="mt-1 text-sm text-gray-900">{{ $hod->gender ? ucfirst($hod->gender) : '—' }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Date of Birth</p>
                                                        <p class="mt-1 text-sm text-gray-900">{{ $hod->dob ? bsDate($hod->dob, 'Y F d') : '—' }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Address</p>
                                                        <p class="mt-1 text-sm text-gray-900">{{ $hod->address ?? '—' }}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Department --}}
                                            <div class="border-t border-gray-100 pt-6">
                                                <h4 class="text-sm font-semibold text-gray-900 mb-3">Department Assignment</h4>
                                                @if($hod->hodDepartment)
                                                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                                                        <div class="flex items-start gap-3">
                                                            <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                                </svg>
                                                            </div>
                                                            <div>
                                                                <h5 class="font-semibold text-gray-900">{{ $hod->hodDepartment->name }}</h5>
                                                                <p class="text-xs text-gray-600 mt-1">Code: {{ $hod->hodDepartment->code }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="bg-amber-50 rounded-lg p-4 border border-amber-100">
                                                        <p class="text-sm text-amber-800">No department assigned</p>
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Account Info --}}
                                            <div class="border-t border-gray-100 pt-6">
                                                <h4 class="text-sm font-semibold text-gray-900 mb-3">Account Information</h4>
                                                <div class="space-y-3">
                                                    <div>
                                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</p>
                                                        <p class="mt-1 text-sm text-gray-900">{{ bsDate($hod->created_at, 'Y F d') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </template>
                        </div>

                        {{-- Drawer Footer --}}
                        <div class="border-t border-gray-100 px-6 py-4">
                            <template x-if="selectedHod">
                                <div class="flex gap-3">
                                    @foreach($hods as $hod)
                                    <a x-show="selectedHod === {{ $hod->id }}" 
                                       href="{{ route('admin.hods.edit', $hod) }}"
                                       class="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors text-center">
                                        Edit HOD
                                    </a>
                                    @endforeach
                                    <button @click="drawerOpen = false"
                                            class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                                        Close
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pagination for Card View --}}
    @if($hods->hasPages())
    <div class="border-t border-gray-100 px-5 py-4">
        {{ $hods->links() }}
    </div>
    @endif
</div>{{-- /card view --}}

    {{-- Table View --}}
    <div x-show="view === 'table'" x-cloak>
        <x-data-table :paginator="$hods">
            <x-slot name="head">
                <tr>
                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider w-10">#</th>
                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">HOD</th>
                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Department</th>
                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Phone</th>
                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Joined</th>
                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                </tr>
            </x-slot>

            @forelse($hods as $hod)
            <tr class="hover:bg-gray-50/70 transition-colors">
                <td class="px-5 py-3.5 text-gray-400 font-mono text-xs">{{ $hod->id }}</td>
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-3">
                        <img src="{{ $hod->avatar_url }}" alt="{{ $hod->name }}"
                             class="w-8 h-8 rounded-full object-cover ring-2 ring-gray-100 flex-shrink-0">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900 truncate">{{ $hod->name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $hod->email }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3.5">
                    @if($hod->hodDepartment)
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-900">{{ $hod->hodDepartment->name }}</span>
                        </div>
                    @else
                        <span class="text-xs text-gray-400 italic">Not assigned</span>
                    @endif
                </td>
                <td class="px-5 py-3.5 text-gray-500 text-xs">{{ $hod->phone ?? '—' }}</td>
                <td class="px-5 py-3.5">
                    <x-badge :color="$hod->is_active ? 'green' : 'red'" :dot="true">
                        {{ $hod->is_active ? 'Active' : 'Inactive' }}
                    </x-badge>
                </td>
                <td class="px-5 py-3.5 text-gray-400 text-xs whitespace-nowrap">{{ bsDate($hod->created_at, 'Y, F d') }}</td>
                <td class="px-5 py-3.5">
                    <x-table-actions
                        :show="route('admin.hods.show', $hod)"
                        :edit="route('admin.hods.edit', $hod)"
                        :destroy="$hod->id !== auth()->id() ? route('admin.hods.destroy', $hod) : null"
                        name="{{ $hod->name }}"
                    />
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-5 py-14 text-center">
                    <svg class="mx-auto w-10 h-10 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <p class="text-sm font-semibold text-gray-400">No HODs found</p>
                    <p class="text-xs text-gray-300 mt-1">Try adjusting your search or filters.</p>
                </td>
            </tr>
            @endforelse
        </x-data-table>
    </div>

</div>{{-- /panel --}}

</div>{{-- /Alpine component --}}

@endsection
