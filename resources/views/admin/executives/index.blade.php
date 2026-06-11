@extends('layouts.app')
@section('title', 'Executive Management')

@section('content')

<x-page-header title="Executive Management" subtitle="Manage Presidents and Principals of the institution.">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.executives.create') }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add Executive
        </x-btn>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <x-stat-card title="Total Executives" value="{{ \App\Models\Executive::count() }}" color="blue" icon="<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'/></svg>"/>
    <x-stat-card title="Current" value="{{ \App\Models\Executive::where('is_current', true)->count() }}" color="green" icon="<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'/></svg>"/>
    <x-stat-card title="Presidents" value="{{ \App\Models\Executive::where('type', 'president')->count() }}" color="purple" icon="<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'/></svg>"/>
    <x-stat-card title="Principals" value="{{ \App\Models\Executive::where('type', 'principal')->count() }}" color="indigo" icon="<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'/></svg>"/>
</div>

<div x-data="{
    view: localStorage.getItem('mmp_executives_view') ?? 'table',
    setView(v) { this.view = v; localStorage.setItem('mmp_executives_view', v); },
}" class="space-y-4">

{{-- Search / Filter Bar --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm px-4 py-3">
    <form method="GET">
        <div class="grid gap-3 lg:grid-cols-5 items-end">
            <x-input name="search" value="{{ request('search') }}" placeholder="Search name or designation…" class="w-full lg:col-span-2"/>
            <x-select name="type" class="w-full">
                <option value="">All Types</option>
                <option value="president" {{ request('type') === 'president' ? 'selected' : '' }}>President</option>
                <option value="principal" {{ request('type') === 'principal' ? 'selected' : '' }}>Principal</option>
            </x-select>
            <x-select name="status" class="w-full">
                <option value="">All Status</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Current</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Past</option>
            </x-select>
            <div class="flex flex-wrap gap-2 lg:col-span-1">
                <x-btn type="submit" variant="secondary" class="w-full">Filter</x-btn>
                @if(request()->anyFilled(['search','type','status']))
                    <x-btn href="{{ route('admin.executives.index') }}" variant="ghost" size="sm" class="w-full">Clear</x-btn>
                @endif
            </div>
        </div>
    </form>
</div>

{{-- Main Content Panel --}}
<div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    {{-- Panel header: result count + view toggle --}}
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-3.5">
        <p class="text-sm text-slate-500">
            @if($executives->total() > 0)
                Showing <span class="font-semibold text-slate-700">{{ $executives->firstItem() }}–{{ $executives->lastItem() }}</span>
                of <span class="font-semibold text-slate-700">{{ number_format($executives->total()) }}</span> Executives
            @else
                No executives match your filters
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
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 6v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Cards
            </button>
        </div>
    </div>

{{-- Card View --}}
<div x-show="view === 'cards'" x-cloak>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-5" x-data="{ drawerOpen: false, selectedExecutive: null }">
        @forelse($executives as $executive)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow overflow-hidden">
            <div class="p-5">
                <div class="flex items-start gap-4">
                    @if($executive->avatar_url)
                        <img src="{{ $executive->avatar_url }}" alt="{{ $executive->name }}"
                             class="w-16 h-16 rounded-full object-cover ring-2 ring-gray-100 flex-shrink-0">
                    @else
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center ring-2 ring-gray-100 flex-shrink-0">
                            <span class="text-white font-bold text-xl">{{ substr($executive->name, 0, 1) }}</span>
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-900 truncate">{{ $executive->name }}</h3>
                        <p class="text-xs text-gray-500 truncate mt-0.5">{{ $executive->designation ?? ucfirst($executive->type) }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <x-badge :color="$executive->type === 'president' ? 'purple' : 'indigo'">
                                {{ ucfirst($executive->type) }}
                            </x-badge>
                            <x-badge :color="$executive->is_current ? 'green' : 'gray'" :dot="true">
                                {{ $executive->is_current ? 'Current' : 'Past' }}
                            </x-badge>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100 space-y-2">
                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-gray-700 text-xs">
                            {{ $executive->start_date_bs }}
                            @if($executive->end_date_bs)
                                - {{ $executive->end_date_bs }}
                            @else
                                - Present
                            @endif
                        </span>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <x-btn type="button" variant="view" size="sm" class="flex-1" @click="drawerOpen = true; selectedExecutive = {{ $executive->id }}">View</x-btn>
                    <x-btn href="{{ route('admin.executives.edit', $executive) }}" variant="edit" size="sm">Edit</x-btn>
                    <form action="{{ route('admin.executives.destroy', $executive) }}" method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete {{ $executive->name }}?')">
                        @csrf
                        @method('DELETE')
                        <x-btn type="submit" variant="danger" size="sm">Delete</x-btn>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-14 text-center">
                <svg class="mx-auto w-10 h-10 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <p class="text-sm font-semibold text-gray-400">No executives found</p>
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
                                <h2 class="text-lg font-semibold text-gray-900">Executive Details</h2>
                                <button @click="drawerOpen = false" class="text-gray-400 hover:text-gray-500">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Drawer Content --}}
                        <div class="flex-1 overflow-y-auto px-6 py-6">
                            <template x-if="selectedExecutive">
                                <div>
                                    @foreach($executives as $executive)
                                    <div x-show="selectedExecutive === {{ $executive->id }}">
                                        <div class="flex items-center gap-4 mb-6">
                                            @if($executive->avatar_url)
                                                <img src="{{ $executive->avatar_url }}" alt="{{ $executive->name }}"
                                                     class="w-20 h-20 rounded-full object-cover ring-4 ring-white shadow-lg">
                                            @else
                                                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center ring-4 ring-white shadow-lg">
                                                    <span class="text-white font-bold text-2xl">{{ substr($executive->name, 0, 1) }}</span>
                                                </div>
                                            @endif
                                            <div>
                                                <h3 class="text-xl font-bold text-gray-900">{{ $executive->name }}</h3>
                                                <p class="text-sm text-gray-600 mt-1">{{ $executive->designation ?? ucfirst($executive->type) }}</p>
                                                <div class="flex items-center gap-2 mt-2">
                                                    <x-badge :color="$executive->type === 'president' ? 'purple' : 'indigo'">
                                                        {{ ucfirst($executive->type) }}
                                                    </x-badge>
                                                    <x-badge :color="$executive->is_current ? 'green' : 'gray'" :dot="true">
                                                        {{ $executive->is_current ? 'Current' : 'Past' }}
                                                    </x-badge>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="space-y-6">
                                            {{-- Tenure Period --}}
                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-900 mb-3">Tenure Period</h4>
                                                <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                                                    <div class="flex items-center gap-3">
                                                        <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-semibold text-gray-900">
                                                                {{ $executive->start_date_bs }}
                                                                @if($executive->end_date_bs)
                                                                    - {{ $executive->end_date_bs }}
                                                                @else
                                                                    - Present
                                                                @endif
                                                            </p>
                                                            <p class="text-xs text-gray-600 mt-1">
                                                                @if($executive->is_current)
                                                                    Currently serving
                                                                @else
                                                                    Past tenure
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Message --}}
                                            @if($executive->message)
                                            <div class="border-t border-gray-100 pt-6">
                                                <h4 class="text-sm font-semibold text-gray-900 mb-3">Message</h4>
                                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                                    <p class="text-sm text-gray-700 leading-relaxed">{{ $executive->message }}</p>
                                                </div>
                                            </div>
                                            @endif

                                            {{-- Additional Info --}}
                                            <div class="border-t border-gray-100 pt-6">
                                                <h4 class="text-sm font-semibold text-gray-900 mb-3">Additional Information</h4>
                                                <div class="space-y-3">
                                                    <div>
                                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Display Order</p>
                                                        <p class="mt-1 text-sm text-gray-900">{{ $executive->order }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Added</p>
                                                        <p class="mt-1 text-sm text-gray-900">{{ bsDate($executive->created_at, 'Y F d') }}</p>
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
                            <template x-if="selectedExecutive">
                                <div class="flex gap-3">
                                    @foreach($executives as $executive)
                                    <a x-show="selectedExecutive === {{ $executive->id }}" 
                                       href="{{ route('admin.executives.edit', $executive) }}"
                                       class="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors text-center">
                                        Edit Executive
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
    @if($executives->hasPages())
    <div class="border-t border-gray-100 px-5 py-4">
        {{ $executives->links() }}
    </div>
    @endif
</div>{{-- /card view --}}

    {{-- Table View --}}
    <div x-show="view === 'table'" x-cloak>
        <x-data-table :paginator="$executives">
            <x-slot name="head">
                <tr>
                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider w-10">#</th>
                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Executive</th>
                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Tenure Period</th>
                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                </tr>
            </x-slot>

            @forelse($executives as $executive)
            <tr class="hover:bg-gray-50/70 transition-colors">
                <td class="px-5 py-3.5 text-gray-400 font-mono text-xs">{{ $executive->id }}</td>
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-3">
                        @if($executive->avatar_url)
                            <img src="{{ $executive->avatar_url }}" alt="{{ $executive->name }}"
                                 class="w-8 h-8 rounded-full object-cover ring-2 ring-gray-100 flex-shrink-0">
                        @else
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center ring-2 ring-gray-100 flex-shrink-0">
                                <span class="text-white font-bold text-xs">{{ substr($executive->name, 0, 1) }}</span>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900 truncate">{{ $executive->name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $executive->designation ?? ucfirst($executive->type) }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3.5">
                    <x-badge :color="$executive->type === 'president' ? 'purple' : 'indigo'">
                        {{ ucfirst($executive->type) }}
                    </x-badge>
                </td>
                <td class="px-5 py-3.5 text-gray-500 text-xs whitespace-nowrap">
                    {{ $executive->start_date_bs }}
                    @if($executive->end_date_bs)
                        - {{ $executive->end_date_bs }}
                    @else
                        - Present
                    @endif
                </td>
                <td class="px-5 py-3.5">
                    <x-badge :color="$executive->is_current ? 'green' : 'gray'" :dot="true">
                        {{ $executive->is_current ? 'Current' : 'Past' }}
                    </x-badge>
                </td>
                <td class="px-5 py-3.5">
                    <x-table-actions
                        :edit="route('admin.executives.edit', $executive)"
                        :destroy="route('admin.executives.destroy', $executive)"
                        name="{{ $executive->name }}"
                    />
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-5 py-14 text-center">
                    <svg class="mx-auto w-10 h-10 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <p class="text-sm font-semibold text-gray-400">No executives found</p>
                    <p class="text-xs text-gray-300 mt-1">Try adjusting your search or filters.</p>
                </td>
            </tr>
            @endforelse
        </x-data-table>
    </div>

</div>{{-- /panel --}}

</div>{{-- /Alpine component --}}

@endsection
