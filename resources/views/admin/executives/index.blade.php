@extends('layouts.app')

@section('title', 'Executives Management')

@section('content')
<div class="space-y-6">
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
                                    <img src="{{ Storage::url($president->avatar) }}" alt="{{ $president->name }}" class="h-16 w-16 rounded-lg object-cover">
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
                                <a href="{{ route('admin.executives.edit', $president) }}" class="flex-1 rounded-md border border-slate-200 px-3 py-1.5 text-center text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                    Edit
                                </a>
                                <form action="{{ route('admin.executives.destroy', $president) }}" method="POST" class="flex-1" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full rounded-md border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50">
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
                                    <img src="{{ Storage::url($principal->avatar) }}" alt="{{ $principal->name }}" class="h-16 w-16 rounded-lg object-cover">
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
                                <a href="{{ route('admin.executives.edit', $principal) }}" class="flex-1 rounded-md border border-slate-200 px-3 py-1.5 text-center text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                    Edit
                                </a>
                                <form action="{{ route('admin.executives.destroy', $principal) }}" method="POST" class="flex-1" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full rounded-md border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50">
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
</div>
@endsection
