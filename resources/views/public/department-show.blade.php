@extends('layouts.guest')
@section('title', $department->name ?? 'Department')
@section('breadcrumb', true)

@section('content')
@php
    $programIcons = [
        '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
        '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
        '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>',
        '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
        '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',
    ];
@endphp
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            {{-- Department Hero --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                <div class="h-2" style="background-color: #003D82;"></div>
                @if($department->photo_url)
                    <img src="{{ $department->photo_url }}" alt="{{ $department->name }}" class="w-full h-72 object-cover">
                @endif
                <div class="p-8">
                    <div class="flex items-center gap-3 mb-4">
                        <h2 class="text-2xl font-bold text-gray-900">{{ $department->name }}</h2>
                        <span class="rounded-md bg-blue-50 border border-blue-100 px-2 py-0.5 text-xs font-bold text-blue-800 uppercase">{{ $department->code }}</span>
                    </div>
                    <p class="text-gray-600 leading-relaxed mb-6">{{ $department->description ?? 'This department offers a comprehensive CTEVT-approved 3-year diploma program designed to equip students with hands-on technical skills required in today\'s industry.' }}</p>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-blue-800">{{ $department->programs->count() }}</div>
                            <div class="text-xs text-gray-500 font-medium mt-1">Programs</div>
                        </div>
                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-blue-800">3</div>
                            <div class="text-xs text-gray-500 font-medium mt-1">Years Duration</div>
                        </div>
                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-blue-800">6</div>
                            <div class="text-xs text-gray-500 font-medium mt-1">Semesters</div>
                        </div>
                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-blue-800">CTEVT</div>
                            <div class="text-xs text-gray-500 font-medium mt-1">Affiliated</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Programs Section --}}
            <div class="section-header" style="background-color: #003D82;">📚 Programs Offered</div>

            @forelse($department->programs as $program)
                @php $icon = $programIcons[$loop->index % count($programIcons)]; @endphp
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-blue-50 border border-blue-100">
                                {!! $icon !!}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900">{{ $program->name }}</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            {{ $program->code }}
                                            @if($program->ctevt_code) • CTEVT: {{ $program->ctevt_code }} @endif
                                            @if($program->affiliation_type) • {{ $program->affiliation_type }} @endif
                                        </p>
                                    </div>
                                    @if($program->is_active)
                                        <span class="shrink-0 rounded-md bg-emerald-50 border border-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">Active</span>
                                    @endif
                                </div>

                                @if($program->description)
                                    <p class="text-sm text-gray-600 mt-3 line-clamp-2">{{ $program->description }}</p>
                                @endif

                                <div class="flex flex-wrap gap-3 mt-4">
                                    <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $program->duration_years }} Years
                                    </div>
                                    <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        {{ $program->total_semesters }} Semesters
                                    </div>
                                    <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                        {{ $program->subjects->count() }} Subjects
                                    </div>
                                    @if($program->subjects->sum('credit_hours'))
                                        <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            {{ $program->subjects->sum('credit_hours') }} Credit Hrs
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-4">
                                    <a href="{{ route('public.program.show', [$department->slug, $program->slug ?: \Illuminate\Support\Str::slug($program->name)]) }}" class="inline-flex items-center gap-2 rounded-lg border-2 border-[#003D82] bg-white px-5 py-2 text-sm font-bold text-[#003D82] transition-colors hover:bg-[#003D82] hover:text-white">
                                        View Full Details
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl border border-gray-200 p-8 text-center shadow-sm">
                    <div class="text-4xl mb-3">📚</div>
                    <p class="font-semibold text-gray-700">No programs listed yet.</p>
                    <p class="text-sm text-gray-500 mt-1">Program details will appear here once published.</p>
                </div>
            @endforelse
        </div>

        <div class="space-y-6">
            @if($department->hod)
                <div>
                    <div class="section-header" style="background-color: #003D82;">👨‍💼 Head of Department</div>
                    <div class="bg-white border border-gray-200 border-t-0 p-5">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-full bg-blue-50 border-2 flex items-center justify-center overflow-hidden flex-shrink-0" style="border-color: #003D82;">
                                <img src="{{ $department->hod->avatar_url }}" alt="{{ $department->hod->name }}" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <div class="font-bold text-gray-900">{{ $department->hod->name }}</div>
                                <div class="text-sm text-blue-700">Head of Department</div>
                                <div class="text-xs text-gray-500">{{ $department->name }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($department->programs->count())
                <div>
                    <div class="section-header" style="background-color: #003D82;">📚 Programs at a Glance</div>
                    <div class="bg-white border border-gray-200 border-t-0 divide-y divide-gray-100">
                        @foreach($department->programs as $program)
                            <a href="{{ route('public.program.show', [$department->slug, $program->slug ?: \Illuminate\Support\Str::slug($program->name)]) }}" class="block px-4 py-3 hover:bg-blue-50 transition-colors">
                                <div class="font-bold text-sm text-gray-900">{{ $program->name }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $program->code }} • {{ $program->duration_years }} yrs • {{ $program->total_semesters }} sem • {{ $program->subjects->count() }} subjects</div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div>
                <div class="section-header" style="background-color: #003D82;">🔗 Quick Links</div>
                <div class="bg-white border border-gray-200 border-t-0 rounded-b-lg shadow-md">
                    <a href="{{ route('public.departments') }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm font-bold text-blue-800 hover:bg-blue-50 transition-colors"><span class="text-blue-600">›</span> All Departments</a>
                    <a href="{{ route('public.notices') }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition-colors"><span class="text-blue-600">›</span> Notices</a>
                    <a href="{{ route('public.downloads') }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition-colors"><span class="text-blue-600">›</span> Downloads</a>
                    <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-blue-800 hover:bg-blue-50 transition-colors"><span class="text-blue-600">›</span> Student Portal</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

