@extends('layouts.guest')
@section('title', $alumnus->user?->name ?? 'Alumni Profile')

@section('content')
@php
    $gradients = ['from-blue-500 to-indigo-600','from-violet-500 to-purple-600','from-emerald-500 to-teal-600','from-amber-500 to-orange-600','from-rose-500 to-pink-600','from-cyan-500 to-sky-600'];
    $grad = $gradients[$alumnus->id % 6];
    $statusMap = [
        'employed'   => ['label' => 'Employed',   'cls' => 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
        'studying'   => ['label' => 'Studying',   'cls' => 'bg-blue-50 text-blue-700 ring-blue-200'],
        'freelancing'=> ['label' => 'Freelancing', 'cls' => 'bg-violet-50 text-violet-700 ring-violet-200'],
        'unemployed' => ['label' => 'Unemployed', 'cls' => 'bg-amber-50 text-amber-700 ring-amber-200'],
        'unknown'    => ['label' => 'Unknown',    'cls' => 'bg-slate-100 text-slate-600 ring-slate-200'],
    ];
    $st = $statusMap[$alumnus->employment_status] ?? $statusMap['unknown'];
@endphp

<section class="py-12 lg:py-16">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

        {{-- Back --}}
        <a href="{{ route('public.alumni') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Directory
        </a>

        {{-- Hero Header --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden mb-8">
            <div class="h-28 bg-gradient-to-br {{ $grad }} relative"></div>
            <div class="px-6 pb-6 -mt-10 relative">
                <div class="flex flex-wrap items-end gap-5">
                    @if($alumnus->user?->avatar)
                        <img src="{{ asset('storage/'.$alumnus->user->avatar) }}" alt="" class="h-20 w-20 rounded-2xl object-cover ring-4 ring-white shadow-lg"/>
                    @else
                        <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br {{ $grad }} text-3xl font-black text-white ring-4 ring-white shadow-lg">
                            {{ strtoupper(substr($alumnus->user?->name ?? 'A', 0, 1)) }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0 pt-10">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl font-black text-slate-900">{{ $alumnus->user?->name }}</h1>
                            @if($alumnus->is_featured)
                                <span class="rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 ring-1 ring-amber-200">★ Featured</span>
                            @endif
                            <span class="rounded-lg px-2.5 py-1 text-xs font-bold ring-1 {{ $st['cls'] }}">{{ $st['label'] }}</span>
                        </div>
                        <p class="text-sm text-slate-500 mt-1">
                            @if($alumnus->current_job){{ $alumnus->current_job }}@endif
                            @if($alumnus->company_name) at {{ $alumnus->company_name }}@endif
                            @if(!$alumnus->current_job && !$alumnus->company_name)Alumni @endif
                            · {{ $alumnus->department?->name }} · Batch {{ $alumnus->graduation_year }}
                        </p>
                    </div>
                </div>
                {{-- Social Links --}}
                @if($alumnus->linkedin_url || $alumnus->github_url || $alumnus->portfolio_url)
                <div class="mt-4 flex flex-wrap gap-2">
                    @if($alumnus->linkedin_url)
                    <a href="{{ $alumnus->linkedin_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">LinkedIn</a>
                    @endif
                    @if($alumnus->github_url)
                    <a href="{{ $alumnus->github_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">GitHub</a>
                    @endif
                    @if($alumnus->portfolio_url)
                    <a href="{{ $alumnus->portfolio_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">Portfolio</a>
                    @endif
                </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">

                {{-- Bio --}}
                @if($alumnus->bio)
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5">
                    <h3 class="font-bold text-slate-900 mb-3">About</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">{!! nl2br(e($alumnus->bio)) !!}</p>
                </div>
                @endif

                {{-- Projects --}}
                @if($alumnus->projects?->count())
                <div class="space-y-4">
                    @foreach($alumnus->projects->where('is_visible', true) as $project)
                    <div class="rounded-2xl border {{ $project->type === 'minor' ? 'border-cyan-200' : 'border-violet-200' }} bg-white shadow-sm overflow-hidden">
                        <div class="border-b {{ $project->type === 'minor' ? 'border-cyan-100 bg-cyan-50/50' : 'border-violet-100 bg-violet-50/50' }} px-5 py-3 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="rounded-lg {{ $project->type === 'minor' ? 'bg-cyan-100 text-cyan-700' : 'bg-violet-100 text-violet-700' }} px-2 py-0.5 text-[10px] font-bold uppercase">{{ $project->type }}</span>
                                <h3 class="font-bold text-slate-900">{{ $project->title }}</h3>
                            </div>
                            @if($project->year)<span class="text-xs text-slate-500">{{ $project->year }}</span>@endif
                        </div>
                        <div class="p-5">
                            @if($project->description)
                                <p class="text-sm text-slate-600 mb-3">{{ $project->description }}</p>
                            @endif
                            @if($project->supervisor)
                                <p class="text-xs text-slate-500 mb-2">Supervisor: <strong>{{ $project->supervisor }}</strong></p>
                            @endif
                            @if($project->technologies && count($project->technologies))
                                <div class="flex flex-wrap gap-1 mb-3">
                                    @foreach($project->technologies as $tech)
                                        <span class="rounded {{ $project->type === 'minor' ? 'bg-cyan-50 text-cyan-700' : 'bg-violet-50 text-violet-700' }} px-2 py-0.5 text-[10px] font-bold">{{ $tech }}</span>
                                    @endforeach
                                </div>
                            @endif
                            @if($project->team_members && count($project->team_members))
                                <p class="text-xs text-slate-500 mb-2">Team: {{ implode(', ', $project->team_members) }}</p>
                            @endif
                            <div class="flex flex-wrap gap-2 mt-3">
                                @if($project->report_path)
                                    <a href="{{ asset('storage/'.$project->report_path) }}" target="_blank" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">Report (PDF)</a>
                                @endif
                                @if($project->github_url)
                                    <a href="{{ $project->github_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">GitHub</a>
                                @endif
                                @if($project->demo_url)
                                    <a href="{{ $project->demo_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">Live Demo</a>
                                @endif
                            </div>
                            @if($project->screenshots && count($project->screenshots))
                                <div class="mt-4 grid grid-cols-3 gap-2">
                                    @foreach(array_slice($project->screenshots, 0, 6) as $ss)
                                        <img src="{{ asset('storage/'.$ss) }}" class="rounded-lg object-cover h-24 w-full" alt="Screenshot"/>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Achievements --}}
                @if($alumnus->achievementRecords && $alumnus->achievementRecords->count())
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h3 class="font-bold text-slate-900">Achievements</h3>
                    </div>
                    <div class="p-5">
                        @foreach($alumnus->achievementRecords as $ach)
                        <div class="flex gap-3 {{ !$loop->last ? 'mb-3 pb-3 border-b border-slate-50' : '' }}">
                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-amber-50">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-900">{{ $ach->title }}</p>
                                @if($ach->year)<p class="text-xs text-slate-500">{{ $ach->year }}</p>@endif
                                @if($ach->description)<p class="text-xs text-slate-600 mt-1">{{ $ach->description }}</p>@endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Info Card --}}
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h3 class="font-bold text-slate-900">Details</h3>
                    </div>
                    <div class="p-5 space-y-3">
                        @foreach([
                            ['label' => 'Department', 'value' => $alumnus->department?->name],
                            ['label' => 'Program',    'value' => $alumnus->program?->name],
                            ['label' => 'Batch',      'value' => $alumnus->graduation_year],
                            ['label' => 'Location',   'value' => $alumnus->work_location],
                        ] as $f)
                        @if($f['value'])
                        <div class="flex justify-between py-1.5">
                            <span class="text-xs font-semibold text-slate-500">{{ $f['label'] }}</span>
                            <span class="text-sm text-slate-900">{{ $f['value'] }}</span>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>

                {{-- Skills --}}
                @if($alumnus->skills && count($alumnus->skills))
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h3 class="font-bold text-slate-900">Skills</h3>
                    </div>
                    <div class="p-5 flex flex-wrap gap-1.5">
                        @foreach($alumnus->skills as $skill)
                            <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $skill }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Career Timeline --}}
                @if($alumnus->employmentHistory?->count())
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h3 class="font-bold text-slate-900">Career</h3>
                    </div>
                    <div class="p-5">
                        @foreach($alumnus->employmentHistory as $job)
                        <div class="flex gap-3 {{ !$loop->last ? 'mb-3 pb-3 border-b border-slate-50' : '' }}">
                            <div class="flex-shrink-0 mt-1">
                                <div class="h-2.5 w-2.5 rounded-full {{ $job->is_current ? 'bg-emerald-500' : 'bg-slate-300' }}"></div>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900">{{ $job->job_title }}</p>
                                <p class="text-[10px] text-slate-500">{{ $job->company_name }} · {{ $job->start_date?->format('M Y') ?? '—' }}–{{ $job->is_current ? 'Present' : ($job->end_date?->format('M Y') ?? '—') }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

    </div>
</section>
@endsection
