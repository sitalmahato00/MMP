@extends('layouts.guest')
@section('title', $profile['name'] . ' — ' . $profile['type_label'] . ' Profile')
@section('meta_description', 'View the full profile of ' . $profile['name'] . ' at Manmohan Memorial Polytechnic.')
@section('breadcrumb', true)

@section('content')
@php
    $department = $profile['department'] ?? [];
    $sections = collect($profile['sections'] ?? []);
    $highlights = collect($profile['highlights'] ?? []);
    $subjects = collect($profile['subjects'] ?? []);
    $actionLinks = collect($profile['action_links'] ?? []);
    $contactSection = $sections->firstWhere('title', 'Contact Details');
    $departmentPrograms = collect(data_get($department, 'programs', []));
@endphp
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="h-24" style="background: linear-gradient(135deg, #003D82, #001F4D);"></div>
                <div class="px-6 pb-6 -mt-12">
                    <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                        <div class="w-24 h-24 rounded-full border-4 border-white shadow-md overflow-hidden bg-gray-100 flex-shrink-0">
                            <img src="{{ $profile['avatar_url'] }}" alt="{{ $profile['name'] }}" class="w-full h-full object-cover">
                        </div>
                        <div class="pt-12 sm:pt-14 flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-yellow-800 bg-yellow-50 px-3 py-1 rounded-full border border-yellow-100">
                                    {{ $profile['type_label'] }}
                                </span>
                                @if(!empty(data_get($department, 'name')))
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 bg-blue-50 px-3 py-1 rounded-full border border-blue-100">
                                        {{ data_get($department, 'name') }}
                                    </span>
                                @endif
                                @if(!empty(data_get($department, 'code')))
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 bg-blue-50 px-3 py-1 rounded-full border border-blue-100">
                                        Code: {{ data_get($department, 'code') }}
                                    </span>
                                @endif
                            </div>
                            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $profile['name'] }}</h1>
                            <p class="text-gray-600 text-sm sm:text-base mt-1">{{ $profile['designation'] }}</p>
                            @if(!empty($profile['summary']))
                                <p class="mt-4 text-sm leading-relaxed text-gray-600 max-w-3xl">{{ $profile['summary'] }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if($highlights->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                    @foreach($highlights as $highlight)
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400">{{ $highlight['label'] }}</p>
                            <p class="mt-2 text-sm font-bold text-gray-900">{{ $highlight['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            @endif

            @foreach($sections as $section)
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="section-header rounded-t-xl">{{ $section['title'] }}</div>
                    <div class="p-5">
                        @if(!empty($section['rows']))
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($section['rows'] as $row)
                                    <div class="rounded-lg bg-gray-50 border border-gray-100 px-4 py-3">
                                        <dt class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500">{{ $row['label'] }}</dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $row['value'] }}</dd>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-lg border border-dashed border-gray-200 bg-gray-50 px-4 py-8 text-center text-sm text-gray-500">
                                No additional details available.
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            @if($subjects->count() > 0)
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="section-header rounded-t-xl">Subjects</div>
                    <div class="p-5">
                        <div class="flex flex-wrap gap-2">
                            @foreach($subjects as $subject)
                                <span class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-semibold text-gray-700">
                                    @if(!empty($subject['code']))
                                        <span class="text-blue-700">{{ $subject['code'] }}</span>
                                        <span class="text-gray-300">|</span>
                                    @endif
                                    <span>{{ $subject['name'] }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if(!empty(data_get($department, 'description')) || $departmentPrograms->count() > 0)
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="section-header rounded-t-xl">Department Snapshot</div>
                    <div class="p-5 space-y-5">
                        @if(!empty(data_get($department, 'description')))
                            <p class="text-sm leading-relaxed text-gray-600">{{ data_get($department, 'description') }}</p>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            @if(!empty(data_get($department, 'seat_capacity')))
                                <div class="rounded-lg bg-blue-50 border border-blue-100 px-4 py-3">
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-blue-500">Seat Capacity</div>
                                    <div class="mt-1 text-sm font-bold text-blue-900">{{ data_get($department, 'seat_capacity') }}</div>
                                </div>
                            @endif
                            @if(!empty(data_get($department, 'programs_count')))
                                <div class="rounded-lg bg-blue-50 border border-blue-100 px-4 py-3">
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-blue-500">Programs</div>
                                    <div class="mt-1 text-sm font-bold text-blue-900">{{ data_get($department, 'programs_count') }}</div>
                                </div>
                            @endif
                            @if(!empty(data_get($department, 'teachers_count')))
                                <div class="rounded-lg bg-emerald-50 border border-emerald-100 px-4 py-3">
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-500">Teachers</div>
                                    <div class="mt-1 text-sm font-bold text-emerald-900">{{ data_get($department, 'teachers_count') }}</div>
                                </div>
                            @endif
                        </div>

                        @if($departmentPrograms->count() > 0)
                            <div>
                                <div class="mb-2 text-sm font-bold uppercase tracking-[0.18em] text-gray-600">Programs</div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($departmentPrograms as $program)
                                        <span class="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-semibold text-gray-700">
                                            {{ $program['code'] ? $program['code'] . ' - ' : '' }}{{ $program['name'] }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <h3 class="font-bold text-lg text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-2">
                    @foreach($actionLinks as $link)
                        <a href="{{ $link['href'] }}" class="flex items-center justify-between gap-3 rounded-lg border border-gray-100 px-4 py-3 text-sm text-gray-700 hover:border-[#003D82]/20 hover:bg-blue-50 hover:text-[#003D82] transition-colors">
                            <span>{{ $link['label'] }}</span>
                            <span class="text-[#003D82] font-bold">›</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <h3 class="font-bold text-lg text-gray-900 mb-4">Contact</h3>
                @if($contactSection && !empty($contactSection['rows']))
                    <div class="space-y-3">
                        @foreach($contactSection['rows'] as $row)
                            <div class="rounded-lg bg-gray-50 border border-gray-100 px-4 py-3">
                                <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500">{{ $row['label'] }}</div>
                                <div class="mt-1 text-sm font-semibold text-gray-900">{{ $row['value'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">No contact details are available for this profile.</p>
                @endif
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <h3 class="font-bold text-lg text-gray-900 mb-4">Directory Links</h3>
                <div class="space-y-2 text-sm">
                    <a href="{{ route('public.people') }}" class="flex items-center gap-2 text-gray-700 hover:text-[#003D82] transition-colors">
                        <span class="text-[#003D82] font-bold">›</span> Department Wise People
                    </a>
                    <a href="{{ route('public.staff') }}" class="flex items-center gap-2 text-gray-700 hover:text-[#003D82] transition-colors">
                        <span class="text-[#003D82] font-bold">›</span> Administrative Staff
                    </a>
                    <a href="{{ route('public.leadership') }}" class="flex items-center gap-2 text-gray-700 hover:text-[#003D82] transition-colors">
                        <span class="text-[#003D82] font-bold">›</span> Presidents & Principals
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

