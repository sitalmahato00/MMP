@extends('layouts.app')

@section('title', 'Executives Management')

@section('content')
<div class="space-y-6" x-data="{ drawerOpen: false, selectedExec: null, selectedType: null }">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Executives Management</h1>
            <p class="mt-1 text-sm text-slate-500">Manage college presidents and principals</p>
        </div>
        <a href="{{ route('admin.executives.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Executive
        </a>
    </div>

    {{-- Presidents Section --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-slate-900">Presidents</h2>
        </div>
        <div class="p-6">
            @if($presidents->isEmpty())
                <div class="py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <p class="mt-2 text-sm text-slate-500">No presidents added yet</p>
                </div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($presidents as $president)
                        <div class="rounded-lg border border-slate-200 p-4">
                            <div class="flex items-start gap-4">
                                @if($president->avatar)
                                    <img src="{{ Storage::disk('public')->url($president->avatar) }}" alt="{{ $president->name }}" class="h-16 w-16 rounded-lg object-cover">
                                @else
                                    <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-slate-100">
                                        <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-slate-900">{{ $president->name }}</h3>
                                    @if($president->designation)
                                        <p class="text-xs text-slate-500">{{ $president->designation }}</p>
                                    @endif
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $president->start_date_bs }}
                                        @if($president->end_date_bs)
                                            - {{ $president->end_date_bs }}
                                        @else
                                            - Present
                                        @endif
                                    </p>
                                    @if($president->is_current)
                                        <span class="mt-2 inline-block rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">Current</span>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-4 flex gap-2">
                                <button @click="drawerOpen = true; selectedExec = {{ $president->id }}; selectedType = 'president'"
                                        class="flex-1 rounded-md bg-blue-50 px-3 py-1.5 text-center text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                    Quick View
                                </button>
                                <a href="{{ route('admin.executives.edit', $president) }}" class="flex-1 rounded-md border border-slate-200 px-3 py-1.5 text-center text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                    Edit
                                </a>
                                <form action="{{ route('admin.executives.destroy', $president) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-md border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Principals Section --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-slate-900">Principals</h2>
        </div>
        <div class="p-6">
            @if($principals->isEmpty())
                <div class="py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <p class="mt-2 text-sm text-slate-500">No principals added yet</p>
                </div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($principals as $principal)
                        <div class="rounded-lg border border-slate-200 p-4">
                            <div class="flex items-start gap-4">
                                @if($principal->avatar)
                                    <img src="{{ Storage::disk('public')->url($principal->avatar) }}" alt="{{ $principal->name }}" class="h-16 w-16 rounded-lg object-cover">
                                @else
                                    <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-slate-100">
                                        <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-slate-900">{{ $principal->name }}</h3>
                                    @if($principal->designation)
                                        <p class="text-xs text-slate-500">{{ $principal->designation }}</p>
                                    @endif
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $principal->start_date_bs }}
                                        @if($principal->end_date_bs)
                                            - {{ $principal->end_date_bs }}
                                        @else
                                            - Present
                                        @endif
                                    </p>
                                    @if($principal->is_current)
                                        <span class="mt-2 inline-block rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">Current</span>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-4 flex gap-2">
                                <button @click="drawerOpen = true; selectedExec = {{ $principal->id }}; selectedType = 'principal'"
                                        class="flex-1 rounded-md bg-blue-50 px-3 py-1.5 text-center text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                    Quick View
                                </button>
                                <a href="{{ route('admin.executives.edit', $principal) }}" class="flex-1 rounded-md border border-slate-200 px-3 py-1.5 text-center text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                    Edit
                                </a>
                                <form action="{{ route('admin.executives.destroy', $principal) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-md border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

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
                        <template x-if="selectedExec">
                            <div>
                                @foreach($presidents as $exec)
                                <div x-show="selectedExec === {{ $exec->id }} && selectedType === 'president'">
                                    <div class="flex items-center gap-4 mb-6">
                                        @if($exec->avatar)
                                            <img src="{{ Storage::disk('public')->url($exec->avatar) }}" alt="{{ $exec->name }}"
                                                 class="w-20 h-20 rounded-lg object-cover ring-4 ring-white shadow-lg">
                                        @else
                                            <div class="flex h-20 w-20 items-center justify-center rounded-lg bg-slate-100 ring-4 ring-white shadow-lg">
                                                <svg class="h-10 w-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <div>
                                            <h3 class="text-xl font-bold text-gray-900">{{ $exec->name }}</h3>
                                            @if($exec->designation)
                                                <p class="text-sm text-gray-600 mt-1">{{ $exec->designation }}</p>
                                            @endif
                                            <div class="flex items-center gap-2 mt-2">
                                                <x-badge color="purple">President</x-badge>
                                                @if($exec->is_current)
                                                    <x-badge color="green" :dot="true">Current</x-badge>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-6">
                                        <div>
                                            <h4 class="text-sm font-semibold text-gray-900 mb-3">Tenure Period</h4>
                                            <div class="bg-slate-50 rounded-lg p-4">
                                                <p class="text-sm text-gray-700">
                                                    <span class="font-medium">Start:</span> {{ $exec->start_date_bs }}
                                                </p>
                                                <p class="text-sm text-gray-700 mt-2">
                                                    <span class="font-medium">End:</span> {{ $exec->end_date_bs ?? 'Present' }}
                                                </p>
                                            </div>
                                        </div>

                                        @if($exec->message)
                                        <div class="border-t border-gray-100 pt-6">
                                            <h4 class="text-sm font-semibold text-gray-900 mb-3">Message</h4>
                                            <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                                                <p class="text-sm text-gray-700 leading-relaxed">{{ $exec->message }}</p>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach

                                @foreach($principals as $exec)
                                <div x-show="selectedExec === {{ $exec->id }} && selectedType === 'principal'">
                                    <div class="flex items-center gap-4 mb-6">
                                        @if($exec->avatar)
                                            <img src="{{ Storage::disk('public')->url($exec->avatar) }}" alt="{{ $exec->name }}"
                                                 class="w-20 h-20 rounded-lg object-cover ring-4 ring-white shadow-lg">
                                        @else
                                            <div class="flex h-20 w-20 items-center justify-center rounded-lg bg-slate-100 ring-4 ring-white shadow-lg">
                                                <svg class="h-10 w-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <div>
                                            <h3 class="text-xl font-bold text-gray-900">{{ $exec->name }}</h3>
                                            @if($exec->designation)
                                                <p class="text-sm text-gray-600 mt-1">{{ $exec->designation }}</p>
                                            @endif
                                            <div class="flex items-center gap-2 mt-2">
                                                <x-badge color="blue">Principal</x-badge>
                                                @if($exec->is_current)
                                                    <x-badge color="green" :dot="true">Current</x-badge>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-6">
                                        <div>
                                            <h4 class="text-sm font-semibold text-gray-900 mb-3">Tenure Period</h4>
                                            <div class="bg-slate-50 rounded-lg p-4">
                                                <p class="text-sm text-gray-700">
                                                    <span class="font-medium">Start:</span> {{ $exec->start_date_bs }}
                                                </p>
                                                <p class="text-sm text-gray-700 mt-2">
                                                    <span class="font-medium">End:</span> {{ $exec->end_date_bs ?? 'Present' }}
                                                </p>
                                            </div>
                                        </div>

                                        @if($exec->message)
                                        <div class="border-t border-gray-100 pt-6">
                                            <h4 class="text-sm font-semibold text-gray-900 mb-3">Message</h4>
                                            <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                                                <p class="text-sm text-gray-700 leading-relaxed">{{ $exec->message }}</p>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </template>
                    </div>

                    {{-- Drawer Footer --}}
                    <div class="border-t border-gray-100 px-6 py-4">
                        <template x-if="selectedExec">
                            <div class="flex gap-3">
                                @foreach($presidents as $exec)
                                <a x-show="selectedExec === {{ $exec->id }} && selectedType === 'president'" 
                                   href="{{ route('admin.executives.edit', $exec) }}"
                                   class="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors text-center">
                                    Edit Executive
                                </a>
                                @endforeach
                                @foreach($principals as $exec)
                                <a x-show="selectedExec === {{ $exec->id }} && selectedType === 'principal'" 
                                   href="{{ route('admin.executives.edit', $exec) }}"
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
@endsection
