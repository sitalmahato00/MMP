@extends('layouts.app')
@section('title', 'Alumni Records')

@section('content')
<x-page-header title="Alumni Records" subtitle="View and manage department alumni information."
               back="{{ route('hod.alumni.index') }}"/>

{{-- Stats Cards --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Total Alumni</p>
                <p class="mt-1 text-2xl font-bold text-slate-800">{{ $totalAlumni }}</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100">
                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Recent Graduates</p>
                <p class="mt-1 text-2xl font-bold text-emerald-600">{{ $recentGraduates }}</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100">
                <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Employed</p>
                <p class="mt-1 text-2xl font-bold text-violet-600">{{ $employedAlumni }}</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-100">
                <svg class="h-6 w-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Entrepreneurs</p>
                <p class="mt-1 text-2xl font-bold text-amber-600">{{ $entrepreneurAlumni }}</p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100">
                <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="mb-6 rounded-2xl border border-slate-200 bg-white p-4">
    <form method="GET" class="flex flex-wrap items-end gap-4">
        <div class="relative flex-1 min-w-[200px]">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Search alumni..." 
                   class="w-full rounded-xl border-slate-300 pl-10 pr-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
            <svg class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <div class="min-w-[150px]">
            <select name="graduation_year" class="block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All Years</option>
                @foreach($graduationYears as $year)
                    <option value="{{ $year }}" @selected(request('graduation_year') == $year)>{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[200px]">
            <select name="program_id" class="block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All Programs</option>
                @foreach($programs as $program)
                    <option value="{{ $program->id }}" @selected(request('program_id') == $program->id)>
                        {{ $program->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[150px]">
            <select name="status" class="block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="recent_graduate" @selected(request('status') === 'recent_graduate')>Recent Graduate</option>
                <option value="employed" @selected(request('status') === 'employed')>Employed</option>
                <option value="entrepreneur" @selected(request('status') === 'entrepreneur')>Entrepreneur</option>
                <option value="further_study" @selected(request('status') === 'further_study')>Further Study</option>
            </select>
        </div>
        <div>
            <x-btn type="submit">Filter</x-btn>
        </div>
    </form>
</div>

{{-- Alumni List --}}
@if($alumni->count() > 0)
    <div class="space-y-3">
        @foreach($alumni as $alumnus)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 transition hover:shadow-md">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-4">
                        @if($alumnus->student->user?->avatar)
                            <img src="{{ asset('storage/'.$alumnus->student->user->avatar) }}" alt="" 
                                 class="h-12 w-12 rounded-xl object-cover ring-2 ring-slate-200">
                        @else
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-lg font-bold text-white">
                                {{ strtoupper(substr($alumnus->student->user?->name ?? 'A', 0, 1)) }}
                            </div>
                        @endif

                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-slate-800">{{ $alumnus->student->user?->name }}</h3>
                            <div class="flex flex-wrap items-center gap-3 text-sm text-slate-600">
                                <span>{{ $alumnus->student->program?->name }}</span>
                                <span>•</span>
                                <span>Graduated {{ $alumnus->graduation_year }}</span>
                                <span>•</span>
                                <span>{{ $alumnus->student->user?->email }}</span>
                            </div>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-700">
                                    {{ ucfirst(str_replace('_', ' ', $alumnus->current_status)) }}
                                </span>
                                @if($alumnus->achievements_count > 0)
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                        {{ $alumnus->achievements_count }} Achievement{{ $alumnus->achievements_count > 1 ? 's' : '' }}
                                    </span>
                                @endif
                                @if($alumnus->employments_count > 0)
                                    <span class="inline-flex rounded-full bg-violet-100 px-2.5 py-0.5 text-xs font-semibold text-violet-700">
                                        {{ $alumnus->employments_count }} Employment{{ $alumnus->employments_count > 1 ? 's' : '' }}
                                    </span>
                                @endif
                                @if($alumnus->projects_count > 0)
                                    <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">
                                        {{ $alumnus->projects_count }} Project{{ $alumnus->projects_count > 1 ? 's' : '' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button onclick="openAlumniDrawer({{ $alumnus->id }})"
                                class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-300 text-slate-600 transition hover:bg-slate-50">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $alumni->links() }}
    </div>
@else
    <div class="rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
        </svg>
        <h3 class="mt-4 text-lg font-semibold text-slate-800">No alumni records found</h3>
        <p class="mt-1 text-sm text-slate-500">Alumni records will appear here once students are prepared.</p>
    </div>
@endif

{{-- Alumni Drawer (placeholder for future implementation) --}}
<script>
function openAlumniDrawer(alumniId) {
    // This would open a drawer with detailed alumni information
    // For now, just show an alert
    alert('Alumni drawer functionality would be implemented here for alumni ID: ' + alumniId);
}
</script>
@endsection