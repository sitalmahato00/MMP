@extends('layouts.app')
@section('title', 'Departments')

@section('content')
<div class="space-y-6">
    <section class="rounded-[1.6rem] border border-slate-200 bg-white p-6 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-slate-950">Departments</h1>
                <p class="mt-2 text-sm text-slate-600">Manage faculties, assign HODs, and keep academic programs organized.</p>
            </div>

            <a href="{{ route('admin.departments.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#8B0000] px-4 py-2.5 text-sm font-bold text-white transition-colors hover:bg-[#6e0000]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
                </svg>
                Add Department
            </a>
        </div>

        <form method="GET" action="{{ route('admin.departments.index') }}" class="mt-5">
            <div class="relative max-w-xl">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 106.05 6.05a7.5 7.5 0 0010.6 10.6z"/>
                </svg>
                <input
                    type="text"
                    name="search"
                    value="{{ $search ?? '' }}"
                    placeholder="Search by department, code, description, or HOD"
                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-[#8B0000] focus:ring-2 focus:ring-red-100"
                >
            </div>
        </form>
    </section>

    @if($departments->isEmpty())
        <section class="rounded-[1.6rem] border border-slate-200 bg-white p-8 text-center shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
            <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 21h18M5 21V7a2 2 0 012-2h3l2-2h5a2 2 0 012 2v16"/>
            </svg>
            <h2 class="mt-3 text-lg font-bold text-slate-900">No departments found</h2>
            @if(!empty($search))
                <p class="mt-1 text-sm text-slate-500">Try a different keyword or clear the search.</p>
            @else
                <p class="mt-1 text-sm text-slate-500">Create your first department to get started.</p>
            @endif
        </section>
    @else
        <section class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            @php
                $gradients = [
                    'bg-gradient-to-br from-slate-900 via-slate-800 to-slate-700',
                    'bg-gradient-to-br from-[#8B0000] via-red-800 to-rose-700',
                    'bg-gradient-to-br from-blue-900 via-blue-800 to-sky-700',
                    'bg-gradient-to-br from-emerald-900 via-emerald-800 to-teal-700',
                ];
            @endphp

            @foreach($departments as $index => $dept)
                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_12px_30px_rgba(15,23,42,0.06)] transition duration-200 hover:-translate-y-1 hover:shadow-[0_20px_40px_rgba(15,23,42,0.12)]">
                    <div class="relative h-28">
                        @if($dept->photo)
                            <img src="{{ asset('storage/' . $dept->photo) }}" alt="{{ $dept->name }}" loading="lazy" decoding="async" class="absolute inset-0 h-full w-full object-cover">
                        @else
                            <div class="absolute inset-0 {{ $gradients[$index % count($gradients)] }}"></div>
                        @endif

                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/75 via-slate-900/30 to-transparent"></div>

                        <div class="relative z-10 flex h-full items-end justify-between p-4">
                            <span class="rounded-md bg-white/20 px-2 py-1 text-xs font-bold tracking-[0.08em] text-white backdrop-blur-sm">{{ $dept->code }}</span>
                            <span class="rounded-md px-2 py-1 text-[11px] font-bold {{ $dept->is_active ? 'bg-emerald-400/20 text-emerald-100' : 'bg-slate-400/20 text-slate-100' }}">{{ $dept->is_active ? 'Active' : 'Inactive' }}</span>
                        </div>
                    </div>

                    <div class="space-y-4 p-5">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">{{ $dept->name }}</h2>
                            <p class="mt-1 truncate text-sm text-slate-500">{{ $dept->description ?: 'No description available' }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3 rounded-xl bg-slate-50 p-3 text-sm">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-slate-400">Head of Department</p>
                                <p class="mt-1 truncate font-semibold text-slate-700">{{ $dept->hod?->name ?? 'Not assigned' }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-slate-400">Programs</p>
                                <p class="mt-1 font-semibold text-slate-700">{{ $dept->programs_count }} total</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                            <a href="{{ route('admin.departments.show', $dept) }}" title="View" aria-label="View {{ $dept->name }}" class="rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:border-blue-200 hover:text-blue-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z"/>
                                </svg>
                            </a>

                            <a href="{{ route('admin.departments.edit', $dept) }}" title="Edit" aria-label="Edit {{ $dept->name }}" class="rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:border-amber-200 hover:text-amber-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-8.586a2 2 0 112.828 2.828L12 15l-4 1 1-4 8.586-8.586z"/>
                                </svg>
                            </a>

                            <form method="POST" action="{{ route('admin.departments.destroy', $dept) }}" onsubmit="return confirm('Delete {{ addslashes($dept->name) }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Delete" aria-label="Delete {{ $dept->name }}" class="rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:border-rose-200 hover:text-rose-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-6 0l1-2h4l1 2"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        <div>
            {{ $departments->onEachSide(1)->links() }}
        </div>
    @endif
</div>
@endsection
