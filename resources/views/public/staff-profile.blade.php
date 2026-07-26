@extends('layouts.guest')
@section('title', $staff->name)
@section('meta_description', $staff->designation . ' profile at Manmohan Memorial Polytechnic.')
@section('breadcrumb', true)

@section('content')
@php
    $workingSchedule = $staff->working_schedule ?? [];
    $publicDocuments = $staff->documents->where('is_public', true);
@endphp

<div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg">
        <div class="p-8">
            <div class="flex flex-col sm:flex-row items-center gap-6">
                <div class="h-32 w-32 overflow-hidden rounded-2xl border-2 border-slate-200 bg-slate-100 shadow-md">
                    <img src="{{ $staff->photo_url }}" alt="{{ $staff->name }}" class="h-full w-full object-cover">
                </div>
                <div class="text-center sm:text-left flex-1">
                    <p class="text-sm font-semibold uppercase tracking-wider text-slate-500">Administrative Staff</p>
                    <h1 class="mt-2 text-3xl font-bold text-slate-900">{{ $staff->name }}</h1>
                    <p class="mt-1 text-lg text-slate-600">{{ $staff->designation }}</p>
                    <div class="mt-3 flex flex-wrap justify-center sm:justify-start gap-2">
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-sm font-semibold text-blue-700">{{ $staff->department ?: 'General Administration' }}</span>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">{{ $staff->staff_code }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Contact Information</div>
                    <div class="mt-3 space-y-2 text-sm text-slate-700">
                        <div class="flex items-center gap-2">
                            <span class="font-medium">Email:</span>
                            <span>{{ $staff->show_email_public && $staff->email ? $staff->email : 'Private' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-medium">Phone:</span>
                            <span>{{ $staff->show_phone_public && $staff->phone ? $staff->phone : 'Private' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-medium">Address:</span>
                            <span>{{ $staff->address ?: 'Not shared' }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Employment Details</div>
                    <div class="mt-3 space-y-2 text-sm text-slate-700">
                        <div class="flex items-center gap-2">
                            <span class="font-medium">Type:</span>
                            <span>{{ ucfirst(str_replace('_', ' ', $staff->employment_type ?? 'Not specified')) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-medium">Status:</span>
                            <span>{{ ucfirst($staff->employment_status ?? 'Active') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-medium">Joined:</span>
                            <span>{{ $staff->join_date ? bsDate($staff->join_date, 'Y F d') : '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <a href="{{ route('public.staff') }}" class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">Back to Staff</a>
                <a href="{{ route('public.people') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">People Directory</a>
            </div>
        </div>
    </div>
</div>
@endsection
