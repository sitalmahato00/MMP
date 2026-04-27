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
    $profileCompletion = max(0, min(100, (int) ($alumnus->profile_completion ?? 0)));
    $skills = collect($alumnus->skills ?? [])->filter()->values();
    $visibleProjects = $alumnus->projects ? $alumnus->projects->where('is_visible', true)->values() : collect();
    $achievementRecords = $alumnus->achievementRecords ?? collect();
    $employmentHistory = $alumnus->employmentHistory ?? collect();
    $student = $alumnus->student;
    $visibilityLabel = ucfirst((string) ($alumnus->visibility ?? 'public'));
    $phoneLink = $alumnus->user?->phone ? 'tel:' . preg_replace('/\s+/', '', $alumnus->user->phone) : null;
    $emailLink = $alumnus->user?->email ? 'mailto:' . $alumnus->user->email : null;
    $achievementSummary = trim((string) $alumnus->achievements);
    $stats = [
        ['label' => 'Projects', 'value' => $alumnus->visible_projects_count ?? $visibleProjects->count()],
        ['label' => 'Achievements', 'value' => $alumnus->achievement_records_count ?? $achievementRecords->count()],
        ['label' => 'Career entries', 'value' => $alumnus->employment_history_count ?? $employmentHistory->count()],
        ['label' => 'Completion', 'value' => $profileCompletion . '%'],
    ];
    $detailSections = [
        [
            'title' => 'Contact & Identity',
            'items' => [
                ['label' => 'Email', 'value' => $alumnus->user?->email, 'url' => $emailLink],
                ['label' => 'Phone', 'value' => $alumnus->user?->phone, 'url' => $phoneLink],
                ['label' => 'Address', 'value' => $alumnus->user?->address],
                ['label' => 'Gender', 'value' => $alumnus->user?->gender],
                ['label' => 'Birth Date', 'value' => $alumnus->user?->dob ? bsDate($alumnus->user->dob, 'd M Y') : null],
            ],
        ],
        [
            'title' => 'Academic Record',
            'items' => [
                ['label' => 'Department', 'value' => $alumnus->department?->name],
                ['label' => 'Program', 'value' => $alumnus->program?->name],
                ['label' => 'Roll Number', 'value' => $alumnus->roll_number],
                ['label' => 'Admission Year', 'value' => $alumnus->admission_year],
                ['label' => 'Graduation Year', 'value' => $alumnus->graduation_year],
                ['label' => 'Student No.', 'value' => $student?->student_no],
                ['label' => 'Registration No.', 'value' => $student?->registration_number],
                ['label' => 'Student Batch', 'value' => $student?->batch],
                ['label' => 'Section', 'value' => $student?->section],
                ['label' => 'Current Semester', 'value' => $student?->current_semester],
                ['label' => 'Student Status', 'value' => $student?->status],
                ['label' => 'Admission Date', 'value' => $student?->admission_date ? bsDate($student->admission_date, 'd M Y') : null],
            ],
        ],
        [
            'title' => 'Professional Record',
            'items' => [
                ['label' => 'Current Job', 'value' => $alumnus->current_job],
                ['label' => 'Company', 'value' => $alumnus->company_name],
                ['label' => 'Work Location', 'value' => $alumnus->work_location],
                ['label' => 'Employment Status', 'value' => $st['label']],
            ],
        ],
        [
            'title' => 'Public Status',
            'items' => [
                ['label' => 'Visibility', 'value' => $visibilityLabel],
                ['label' => 'Profile Completion', 'value' => $profileCompletion . '%'],
                ['label' => 'Verified', 'value' => $alumnus->is_verified ? 'Yes' : 'No'],
                ['label' => 'Featured', 'value' => $alumnus->is_featured ? 'Yes' : 'No'],
            ],
        ],
    ];
@endphp

<section class="relative py-12 lg:py-16">
    <div class="absolute inset-x-0 top-0 -z-10 h-64 bg-gradient-to-b from-slate-50 to-transparent"></div>
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

        {{-- Back --}}
        <a href="{{ route('public.alumni') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Directory
        </a>

        {{-- Hero Header --}}
        <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm mb-8">
            <div class="h-32 bg-gradient-to-br {{ $grad }} relative"></div>
            <div class="px-6 pb-6 -mt-10 relative">
                <div class="flex flex-wrap items-end gap-5">
                    @if($alumnus->user?->avatar)
                        <img src="{{ asset('storage/'.$alumnus->user->avatar) }}" alt="" class="h-24 w-24 rounded-2xl object-cover ring-4 ring-white shadow-lg"/>
                    @else
                        <div class="flex h-24 w-24 items-center justify-center rounded-2xl bg-gradient-to-br {{ $grad }} text-4xl font-bold text-white ring-4 ring-white shadow-lg">
                            {{ strtoupper(substr($alumnus->user?->name ?? 'A', 0, 1)) }}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0 pt-12">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-3xl font-bold text-slate-900">{{ $alumnus->user?->name }}</h1>
                            @if($alumnus->is_featured)
                                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 ring-1 ring-amber-200">Featured</span>
                            @endif
                            @if($alumnus->is_verified)
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">Verified</span>
                            @endif
                            <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $st['cls'] }}">{{ $st['label'] }}</span>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">
                            @if($alumnus->current_job){{ $alumnus->current_job }}@endif
                            @if($alumnus->company_name) at {{ $alumnus->company_name }}@endif
                            @if(! $alumnus->current_job && ! $alumnus->company_name)
                                Alumni
                            @endif
                            � {{ $alumnus->department?->name }}
                            � {{ $alumnus->program?->name }}
                            � Batch {{ $alumnus->graduation_year }}
                        </p>
                        @if($alumnus->work_location)
                            <p class="mt-1 text-sm text-slate-400">{{ $alumnus->work_location }}</p>
                        @endif
                        <div class="mt-4 flex flex-wrap gap-2">
                            @if($alumnus->linkedin_url)
                                <a href="{{ $alumnus->linkedin_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">LinkedIn</a>
                            @endif
                            @if($alumnus->github_url)
                                <a href="{{ $alumnus->github_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">GitHub</a>
                            @endif
                            @if($alumnus->portfolio_url)
                                <a href="{{ $alumnus->portfolio_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">Portfolio</a>
                            @endif
                            @if($alumnus->cv_path)
                                <a href="{{ asset('storage/'.$alumnus->cv_path) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl bg-[#003D82] px-3 py-2 text-xs font-semibold text-white hover:bg-[#720000] transition">Download CV</a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
                    @foreach($stats as $stat)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3">
                            <div class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">{{ $stat['label'] }}</div>
                            <div class="mt-1 text-lg font-bold text-slate-900">{{ $stat['value'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">

                {{-- Bio --}}
                @if($alumnus->bio)
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6">
                    <h3 class="font-bold text-slate-900 mb-3">About</h3>
                    <p class="text-sm leading-relaxed text-slate-600">{!! nl2br(e($alumnus->bio)) !!}</p>
                </div>
                @endif

                {{-- Achievements Summary --}}
                @if($achievementSummary !== '')
                <div class="rounded-2xl border border-amber-200 bg-amber-50/70 shadow-sm p-6">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-amber-700">Summary</span>
                        <h3 class="font-bold text-slate-900">Achievements</h3>
                    </div>
                    <p class="text-sm leading-relaxed text-slate-700">{!! nl2br(e($achievementSummary)) !!}</p>
                </div>
                @endif

                {{-- Projects --}}
                @if($visibleProjects->isNotEmpty())
                <div class="space-y-4">
                    @foreach($visibleProjects as $project)
                    @php
                        $projectTypeBadge = $project->type === 'minor'
                            ? ['label' => 'Minor Project', 'cls' => 'bg-cyan-100 text-cyan-700']
                            : ['label' => 'Major Project', 'cls' => 'bg-violet-100 text-violet-700'];
                        $projectStatusBadge = $project->status === 'in_progress'
                            ? ['label' => 'In Progress', 'cls' => 'bg-amber-50 text-amber-700 ring-amber-200']
                            : ['label' => 'Completed', 'cls' => 'bg-emerald-50 text-emerald-700 ring-emerald-200'];
                        $technologies = collect($project->technologies ?? [])->filter()->values();
                        $teamMembers = collect($project->team_members ?? [])->filter()->values();
                        $screenshots = collect($project->screenshots ?? [])->filter()->values();
                    @endphp
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        @if($project->cover_image)
                            <img src="{{ asset('storage/'.$project->cover_image) }}" alt="{{ $project->title }}" class="h-52 w-full object-cover">
                        @endif
                        <div class="border-b border-slate-100 px-5 py-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.2em] {{ $projectTypeBadge['cls'] }}">{{ $projectTypeBadge['label'] }}</span>
                                        <span class="rounded-full px-2.5 py-1 text-[10px] font-bold ring-1 {{ $projectStatusBadge['cls'] }}">{{ $projectStatusBadge['label'] }}</span>
                                    </div>
                                    <h3 class="text-xl font-bold text-slate-900">{{ $project->title }}</h3>
                                </div>
                                @if($project->year)
                                    <span class="text-xs font-semibold text-slate-500">{{ $project->year }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="p-5">
                            @if($project->description)
                                <p class="text-sm leading-relaxed text-slate-600">{{ $project->description }}</p>
                            @endif
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                @if($project->supervisor)
                                    <div>
                                        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">Supervisor</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-800">{{ $project->supervisor }}</p>
                                    </div>
                                @endif
                                @if($project->status)
                                    <div>
                                        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">Status</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-800">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</p>
                                    </div>
                                @endif
                            </div>
                            @if($technologies->isNotEmpty())
                                <div class="mt-4 flex flex-wrap gap-1.5">
                                    @foreach($technologies as $tech)
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold text-slate-700">{{ $tech }}</span>
                                    @endforeach
                                </div>
                            @endif
                            @if($teamMembers->isNotEmpty())
                                <p class="mt-4 text-xs text-slate-500">Team: {{ $teamMembers->implode(', ') }}</p>
                            @endif
                            <div class="mt-4 flex flex-wrap gap-2">
                                @if($project->report_path)
                                    <a href="{{ asset('storage/'.$project->report_path) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">Report</a>
                                @endif
                                @if($project->github_url)
                                    <a href="{{ $project->github_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">GitHub</a>
                                @endif
                                @if($project->demo_url)
                                    <a href="{{ $project->demo_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">Live Demo</a>
                                @endif
                            </div>
                            @if($screenshots->isNotEmpty())
                                <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3">
                                    @foreach($screenshots->take(6) as $screenshot)
                                        <img src="{{ asset('storage/'.$screenshot) }}" class="h-24 w-full rounded-xl object-cover" alt="Project screenshot"/>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm text-sm text-slate-500">No public projects have been shared yet.</div>
                @endif

                {{-- Achievements --}}
                @if($achievementRecords->isNotEmpty())
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h3 class="font-bold text-slate-900">Awards & Certifications</h3>
                    </div>
                    <div class="p-5">
                        @foreach($achievementRecords as $ach)
                        <div class="flex gap-3 {{ !$loop->last ? 'mb-4 pb-4 border-b border-slate-100' : '' }}">
                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-amber-50">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="text-sm font-bold text-slate-900">{{ $ach->title }}</p>
                                    @if($ach->year)<p class="text-xs text-slate-500">{{ $ach->year }}</p>@endif
                                </div>
                                @if($ach->description)<p class="mt-1 text-xs leading-relaxed text-slate-600">{{ $ach->description }}</p>@endif
                                @if($ach->certificate_path)
                                    <a href="{{ asset('storage/'.$ach->certificate_path) }}" target="_blank" rel="noopener" class="mt-2 inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">Certificate</a>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm text-sm text-slate-500">No public awards or certifications have been shared yet.</div>
                @endif

                {{-- Career Timeline --}}
                @if($employmentHistory->isNotEmpty())
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h3 class="font-bold text-slate-900">Career Timeline</h3>
                    </div>
                    <div class="p-5">
                        @foreach($employmentHistory as $job)
                        <div class="flex gap-3 {{ !$loop->last ? 'mb-4 pb-4 border-b border-slate-100' : '' }}">
                            <div class="flex-shrink-0 mt-1">
                                <div class="h-2.5 w-2.5 rounded-full {{ $job->is_current ? 'bg-emerald-500' : 'bg-slate-300' }}"></div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="text-sm font-bold text-slate-900">{{ $job->job_title }}</p>
                                    <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400">{{ $job->is_current ? 'Current' : 'Past' }}</p>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">{{ $job->company_name }}@if($job->location) � {{ $job->location }}@endif</p>
                                <p class="mt-1 text-[10px] text-slate-500">{{ $job->start_date ? bsDate($job->start_date, 'M Y') : '—' }} — {{ $job->is_current ? 'Present' : ($job->end_date ? bsDate($job->end_date, 'M Y') : '—') }}</p>
                                @if($job->description)
                                    <p class="mt-2 text-xs leading-relaxed text-slate-600">{{ $job->description }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Contact Card --}}
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h3 class="font-bold text-slate-900">Contact & Identity</h3>
                    </div>
                    <div class="p-5 space-y-3">
                        @foreach([
                            ['label' => 'Email', 'value' => $alumnus->user?->email, 'url' => $emailLink],
                            ['label' => 'Phone', 'value' => $alumnus->user?->phone, 'url' => $phoneLink],
                            ['label' => 'Address', 'value' => $alumnus->user?->address],
                            ['label' => 'Gender', 'value' => $alumnus->user?->gender],
                            ['label' => 'Birth Date', 'value' => $alumnus->user?->dob ? bsDate($alumnus->user->dob, 'd M Y') : null],
                        ] as $f)
                        @if($f['value'])
                        <div class="flex items-start justify-between gap-4 py-1.5">
                            <span class="text-xs font-semibold text-slate-500">{{ $f['label'] }}</span>
                            @if(!empty($f['url']))
                                <a href="{{ $f['url'] }}" class="text-sm font-medium text-slate-900 hover:text-[#003D82] transition">{{ $f['value'] }}</a>
                            @else
                                <span class="text-sm text-right text-slate-900">{{ $f['value'] }}</span>
                            @endif
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>

                {{-- Academic Card --}}
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h3 class="font-bold text-slate-900">Academic Record</h3>
                    </div>
                    <div class="p-5 space-y-3">
                        @foreach([
                            ['label' => 'Department', 'value' => $alumnus->department?->name],
                            ['label' => 'Program', 'value' => $alumnus->program?->name],
                            ['label' => 'Roll Number', 'value' => $alumnus->roll_number],
                            ['label' => 'Admission Year', 'value' => $alumnus->admission_year],
                            ['label' => 'Graduation Year', 'value' => $alumnus->graduation_year],
                            ['label' => 'Student No.', 'value' => $student?->student_no],
                            ['label' => 'Registration No.', 'value' => $student?->registration_number],
                            ['label' => 'Student Batch', 'value' => $student?->batch],
                            ['label' => 'Section', 'value' => $student?->section],
                            ['label' => 'Semester', 'value' => $student?->current_semester],
                            ['label' => 'Admission Date', 'value' => $student?->admission_date ? bsDate($student->admission_date, 'd M Y') : null],
                        ] as $f)
                        @if($f['value'])
                        <div class="flex justify-between gap-4 py-1.5">
                            <span class="text-xs font-semibold text-slate-500">{{ $f['label'] }}</span>
                            <span class="text-sm text-right text-slate-900">{{ $f['value'] }}</span>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>

                {{-- Professional Card --}}
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h3 class="font-bold text-slate-900">Professional Record</h3>
                    </div>
                    <div class="p-5 space-y-3">
                        @foreach([
                            ['label' => 'Current Job', 'value' => $alumnus->current_job],
                            ['label' => 'Company', 'value' => $alumnus->company_name],
                            ['label' => 'Work Location', 'value' => $alumnus->work_location],
                            ['label' => 'Employment Status', 'value' => $st['label']],
                            ['label' => 'Visibility', 'value' => $visibilityLabel],
                            ['label' => 'Profile Completion', 'value' => $profileCompletion . '%'],
                        ] as $f)
                        @if($f['value'])
                        <div class="flex justify-between gap-4 py-1.5">
                            <span class="text-xs font-semibold text-slate-500">{{ $f['label'] }}</span>
                            <span class="text-sm text-right text-slate-900">{{ $f['value'] }}</span>
                        </div>
                        @endif
                        @endforeach

                        <div class="pt-2">
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-gradient-to-r from-[#003D82] to-amber-500" style="width: {{ $profileCompletion }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Skills --}}
                @if($skills->isNotEmpty())
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h3 class="font-bold text-slate-900">Skills</h3>
                    </div>
                    <div class="p-5 flex flex-wrap gap-1.5">
                        @foreach($skills as $skill)
                            <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $skill }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Public Links --}}
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h3 class="font-bold text-slate-900">Resources</h3>
                    </div>
                    <div class="p-5 flex flex-wrap gap-2">
                        @if($alumnus->linkedin_url)
                            <a href="{{ $alumnus->linkedin_url }}" target="_blank" rel="noopener" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">LinkedIn</a>
                        @endif
                        @if($alumnus->github_url)
                            <a href="{{ $alumnus->github_url }}" target="_blank" rel="noopener" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">GitHub</a>
                        @endif
                        @if($alumnus->portfolio_url)
                            <a href="{{ $alumnus->portfolio_url }}" target="_blank" rel="noopener" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">Portfolio</a>
                        @endif
                        @if($alumnus->cv_path)
                            <a href="{{ asset('storage/'.$alumnus->cv_path) }}" target="_blank" rel="noopener" class="rounded-xl bg-[#003D82] px-3 py-2 text-xs font-semibold text-white hover:bg-[#720000] transition">CV</a>
                        @endif
                    </div>
                </div>

            </div>
        </div>

    </div>
</section>
@endsection

