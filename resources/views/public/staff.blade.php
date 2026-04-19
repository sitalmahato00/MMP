@extends('layouts.guest')
@section('title', 'Administrative Staff')
@section('meta_description', 'Meet the administrative staff of Manmohan Memorial Polytechnic.')
@section('breadcrumb', true)

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-[2rem] bg-gradient-to-br from-slate-950 via-slate-900 to-[#8B0000] text-white shadow-[0_30px_80px_rgba(15,23,42,0.35)]">
        <div class="grid gap-6 p-6 lg:grid-cols-[1.2fr_0.8fr] lg:p-10">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-white/65">Public Directory</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight lg:text-4xl">Administrative Staff</h1>
                <p class="mt-3 max-w-2xl text-sm text-white/75">Browse the staff who have been made public, filter by department or designation, and open full profiles when contact visibility is allowed.</p>

                <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                        <div class="text-xs uppercase tracking-[0.25em] text-white/55">Visible Staff</div>
                        <div class="mt-2 text-3xl font-semibold">{{ $totalVisible }}</div>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                        <div class="text-xs uppercase tracking-[0.25em] text-white/55">Active</div>
                        <div class="mt-2 text-3xl font-semibold">{{ $activeVisible }}</div>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                        <div class="text-xs uppercase tracking-[0.25em] text-white/55">Featured</div>
                        <div class="mt-2 text-3xl font-semibold">{{ $featuredVisible }}</div>
                    </div>
                    <div class="rounded-2xl bg-white/10 p-4 backdrop-blur">
                        <div class="text-xs uppercase tracking-[0.25em] text-white/55">This Year</div>
                        <div class="mt-2 text-3xl font-semibold">{{ $addedThisYear }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-[1.75rem] border border-white/15 bg-white/10 p-5 backdrop-blur">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-white/65">Featured Department</p>
                <h2 class="mt-2 text-lg font-semibold">{{ $topDepartment?->department ?? 'No department yet' }}</h2>
                <p class="mt-2 text-sm text-white/70">{{ $topDepartment?->total ? $topDepartment->total . ' staff members' : 'Public staff data is still being populated.' }}</p>

                <div class="mt-5 space-y-3 text-sm">
                    <a href="{{ route('public.departments') }}" class="flex items-center justify-between rounded-2xl bg-white/10 px-4 py-3 text-white transition hover:bg-white/15">
                        <span>Explore programs</span>
                        <span class="text-white/60">→</span>
                    </a>
                    <a href="{{ route('public.people') }}" class="flex items-center justify-between rounded-2xl bg-white/10 px-4 py-3 text-white transition hover:bg-white/15">
                        <span>Browse all people</span>
                        <span class="text-white/60">→</span>
                    </a>
                    <a href="{{ route('public.contact') }}" class="flex items-center justify-between rounded-2xl bg-white/10 px-4 py-3 text-white transition hover:bg-white/15">
                        <span>Contact the college</span>
                        <span class="text-white/60">→</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form method="GET" class="mt-8 rounded-[2rem] border border-slate-200 bg-white p-5 shadow-[0_20px_60px_rgba(15,23,42,0.08)] lg:p-6">
        <div class="grid gap-4 xl:grid-cols-6">
            <div class="space-y-2 xl:col-span-2">
                <label class="text-sm font-medium text-slate-700">Search</label>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Search staff name or code" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Department</label>
                <select name="department" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                    <option value="">All</option>
                    @foreach($departments as $department)
                        <option value="{{ $department }}" @selected(request('department') === $department)>{{ $department }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Designation</label>
                <select name="designation" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                    <option value="">All</option>
                    @foreach($designations as $designation)
                        <option value="{{ $designation }}" @selected(request('designation') === $designation)>{{ $designation }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Status</label>
                <select name="employment_status" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                    <option value="">All</option>
                    @foreach(['active' => 'Active', 'leave' => 'Leave', 'resigned' => 'Resigned'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('employment_status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Joined Year</label>
                <select name="joined_year" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                    <option value="">All</option>
                    @foreach($joinedYears as $year)
                        <option value="{{ $year }}" @selected(request('joined_year') === $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-700">Featured</label>
                <select name="featured" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-[#8B0000] focus:ring-4 focus:ring-[#8B0000]/10">
                    <option value="">All</option>
                    <option value="1" @selected(request('featured') === '1')>Featured only</option>
                    <option value="0" @selected(request('featured') === '0')>Not featured</option>
                </select>
            </div>
        </div>

        <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
            <div class="text-sm text-slate-500">{{ $staff->total() }} public staff profiles</div>
            <button type="submit" class="inline-flex items-center justify-center rounded-full bg-[#8B0000] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#6f0000]">Apply Filters</button>
        </div>
    </form>

    <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @forelse($staff as $member)
            <article class="group overflow-hidden rounded-[2rem] border border-slate-200 bg-white p-5 shadow-[0_20px_60px_rgba(15,23,42,0.08)] transition hover:-translate-y-1 hover:shadow-[0_24px_80px_rgba(15,23,42,0.12)]">
                <div class="flex items-start gap-4">
                    <div class="h-20 w-20 overflow-hidden rounded-3xl border border-slate-200 bg-slate-100">
                        <img src="{{ $member->photo_url }}" alt="{{ $member->name }}" class="h-full w-full object-cover">
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap gap-2">
                            @if($member->featured)
                                <span class="rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-amber-700">Featured</span>
                            @endif
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-600">{{ ucfirst($member->employment_status ?? 'active') }}</span>
                        </div>
                        <h3 class="mt-3 text-lg font-semibold text-slate-900">{{ $member->name }}</h3>
                        <p class="mt-1 text-sm text-[#8B0000]">{{ $member->designation }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ $member->department ?: 'General Administration' }}</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-3 text-sm text-slate-600 sm:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 p-3">
                        <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Staff Code</div>
                        <div class="mt-1 font-semibold text-slate-900">{{ $member->staff_code }}</div>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3">
                        <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Public Docs</div>
                        <div class="mt-1 font-semibold text-slate-900">{{ $member->public_documents_count ?? 0 }}</div>
                    </div>
                </div>

                <div class="mt-5 space-y-2 text-sm text-slate-600">
                    <div>{{ $member->show_email_public ? $member->email : 'Email hidden' }}</div>
                    <div>{{ $member->show_phone_public ? $member->phone : 'Phone hidden' }}</div>
                </div>

                <div class="mt-6 flex items-center justify-between gap-3">
                    <a href="{{ route('public.staff.profile', $member->id) }}" class="inline-flex items-center justify-center rounded-full bg-[#8B0000] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#6f0000]">View Profile</a>
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

    <div class="mt-8 rounded-[2rem] border border-slate-200 bg-white p-4 shadow-[0_20px_60px_rgba(15,23,42,0.08)]">
        {{ $staff->links() }}
    </div>
</div>
@endsection

