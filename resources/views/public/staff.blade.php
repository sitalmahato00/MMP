@extends('layouts.guest')
@section('title', 'Administrative Staff')
@section('meta_description', 'Meet the administrative staff of Manmohan Memorial Polytechnic.')
@section('breadcrumb', true)

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @forelse($staff as $member)
            <article class="group overflow-hidden rounded-[2rem] border border-slate-200 bg-white p-5 shadow-[0_20px_60px_rgba(15,23,42,0.08)] transition hover:-translate-y-1 hover:shadow-[0_24px_80px_rgba(15,23,42,0.12)] text-center">
                {{-- Centered Profile Picture --}}
                <div class="flex justify-center mb-3">
                    <div class="h-20 w-20 overflow-hidden rounded-full border-4 border-slate-100 bg-slate-100 shadow-md">
                        <img src="{{ $member->photo_url }}" alt="{{ $member->name }}" class="h-full w-full object-cover">
                    </div>
                </div>

                {{-- Badges --}}
                <div class="flex flex-wrap gap-2 justify-center mb-3">
                    @if($member->featured)
                        <span class="rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-amber-700">Featured</span>
                    @endif
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-600">{{ ucfirst($member->employment_status ?? 'active') }}</span>
                </div>

                {{-- Name and Info --}}
                <h3 class="text-lg font-semibold text-slate-900">{{ $member->name }}</h3>
                <p class="mt-1 text-sm text-[#003D82]">{{ $member->designation }}</p>
                <p class="mt-2 text-sm text-slate-500">{{ $member->department ?: 'General Administration' }}</p>

                {{-- Contact Info --}}
                <div class="mt-4 space-y-1 text-sm text-slate-600">
                    <div>{{ $member->show_email_public ? $member->email : 'Email hidden' }}</div>
                    <div>{{ $member->show_phone_public ? $member->phone : 'Phone hidden' }}</div>
                </div>

                {{-- Action Button --}}
                <div class="mt-5 flex flex-col items-center gap-2">
                    <a href="{{ route('public.staff.profile', $member->id) }}" class="inline-flex items-center justify-center rounded-full bg-[#003D82] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#6f0000]">View Profile</a>
                    <span class="text-xs text-slate-400">Joined {{ $member->join_date ? bsDate($member->join_date, 'Y') : '—' }}</span>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-[2rem] border border-dashed border-slate-300 bg-white p-12 text-center text-slate-500">
                <div class="text-5xl">👥</div>
                <h3 class="mt-4 text-lg font-semibold text-slate-900">No public staff profiles yet</h3>
                <p class="mt-2 text-sm text-slate-500">The administrative team is still preparing visible staff records.</p>
            </div>
        @endforelse
    </div>

    @if($staff->hasPages())
        <div class="mt-8 rounded-[2rem] border border-slate-200 bg-white p-4 shadow-[0_20px_60px_rgba(15,23,42,0.08)]">
            {{ $staff->links() }}
        </div>
    @endif
</div>
@endsection


