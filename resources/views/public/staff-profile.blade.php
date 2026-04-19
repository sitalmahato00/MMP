@extends('layouts.guest')
@section('title', $staff->name)
@section('meta_description', $staff->designation . ' profile at Manmohan Memorial Polytechnic.')
@section('breadcrumb', true)

@section('content')
@php
    $workingSchedule = $staff->working_schedule ?? [];
    $publicDocuments = $staff->documents->where('is_public', true);
@endphp

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_20px_60px_rgba(15,23,42,0.08)]">
        <div class="grid gap-0 lg:grid-cols-[0.95fr_1.05fr]">
            <div class="bg-gradient-to-br from-slate-950 via-slate-900 to-[#8B0000] p-8 text-white lg:p-10">
                <div class="flex items-start gap-5">
                    <div class="h-28 w-28 overflow-hidden rounded-[1.75rem] border border-white/20 bg-white/10 shadow-lg">
                        <img src="{{ $staff->photo_url }}" alt="{{ $staff->name }}" class="h-full w-full object-cover">
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-white/65">Administrative Staff</p>
                        <h1 class="mt-3 text-3xl font-semibold tracking-tight">{{ $staff->name }}</h1>
                        <p class="mt-2 text-lg text-white/80">{{ $staff->designation }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white">{{ $staff->department ?: 'General Administration' }}</span>
                            <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white">{{ $staff->staff_code }}</span>
                            @if($staff->featured)
                                <span class="rounded-full bg-amber-400/20 px-3 py-1 text-xs font-semibold text-white">Featured</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-8 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                        <div class="text-xs uppercase tracking-[0.25em] text-white/60">Public Profile</div>
                        <div class="mt-2 text-lg font-semibold">{{ $staff->public_visible ? 'Visible' : 'Hidden' }}</div>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                        <div class="text-xs uppercase tracking-[0.25em] text-white/60">Documents</div>
                        <div class="mt-2 text-lg font-semibold">{{ $publicDocuments->count() }}</div>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                        <div class="text-xs uppercase tracking-[0.25em] text-white/60">Status</div>
                        <div class="mt-2 text-lg font-semibold">{{ ucfirst($staff->employment_status ?? 'active') }}</div>
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('public.staff') }}" class="inline-flex items-center justify-center rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">Back to Staff</a>
                    <a href="{{ route('public.people') }}" class="inline-flex items-center justify-center rounded-full border border-white/20 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-white/15">People Directory</a>
                </div>
            </div>

            <div class="p-8 lg:p-10">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Contact</div>
                        <div class="mt-3 space-y-2 text-sm text-slate-700">
                            <div>{{ $staff->show_email_public && $staff->email ? $staff->email : 'Email private' }}</div>
                            <div>{{ $staff->show_phone_public && $staff->phone ? $staff->phone : 'Phone private' }}</div>
                            <div>{{ $staff->address ?: 'Address not shared' }}</div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Employment</div>
                        <div class="mt-3 space-y-2 text-sm text-slate-700">
                            <div>{{ ucfirst(str_replace('_', ' ', $staff->employment_type ?? 'unspecified')) }}</div>
                            <div>Joined {{ $staff->join_date ? bsDate($staff->join_date, 'Y F d') : '—' }}</div>
                            <div>{{ $staff->end_date ? 'Ended ' . bsDate($staff->end_date, 'Y F d') : 'Currently serving' }}</div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Working Schedule</div>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3 text-sm text-slate-700">
                        <div>Label: {{ data_get($workingSchedule, 'label') ?: 'Not set' }}</div>
                        <div>Hours: {{ data_get($workingSchedule, 'start') ?: '—' }} - {{ data_get($workingSchedule, 'end') ?: '—' }}</div>
                        <div>Days: {{ implode(', ', data_get($workingSchedule, 'days', [])) ?: 'Not set' }}</div>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Responsibilities</div>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @forelse($staff->responsibilities ?? [] as $item)
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700">{{ $item }}</span>
                        @empty
                            <span class="text-sm text-slate-500">No responsibilities listed.</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)]">
            <h2 class="text-lg font-semibold text-slate-900">About</h2>
            <p class="mt-4 text-sm leading-7 text-slate-600">{{ $staff->bio ?: 'This staff profile has not yet included a biography.' }}</p>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl bg-slate-50 p-4">
                    <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Gender</div>
                    <div class="mt-2 text-sm font-semibold text-slate-900">{{ $staff->gender ?: 'Not shared' }}</div>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Salary Range</div>
                    <div class="mt-2 text-sm font-semibold text-slate-900">{{ $staff->salary_amount ? 'Configured' : 'Not shared' }}</div>
                </div>
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-[0_20px_60px_rgba(15,23,42,0.08)]">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">Public Documents</h2>
                <span class="text-sm text-slate-500">{{ $publicDocuments->count() }} files</span>
            </div>

            <div class="mt-4 space-y-3">
                @forelse($publicDocuments as $document)
                    <a href="{{ asset('storage/' . ltrim($document->file_path, '/')) }}" target="_blank" class="flex items-start justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 transition hover:border-[#8B0000] hover:bg-white">
                        <div>
                            <div class="text-sm font-semibold text-slate-900">{{ $document->label }}</div>
                            <div class="mt-1 text-xs text-slate-500">{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</div>
                        </div>
                        <span class="text-sm font-semibold text-[#8B0000]">Open</span>
                    </a>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-10 text-center text-sm text-slate-500">No public documents are available for this profile.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection