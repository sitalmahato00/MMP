@extends('layouts.guest')
@section('title', 'People Directory')
@section('meta_description', 'Meet the HODs, teachers, staff, and lab techs assigned to each department at Manmohan Memorial Polytechnic.')
@section('breadcrumb', true)

@section('content')
@php
    $departmentOptions = $departments->sortBy('name')->values();
    $visibleSections = $visibleDepartmentSections->values();
@endphp
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="section-header flex items-center justify-between pr-3" style="background-color: #8B0000;">
                <span>👥 People Directory</span>
                <span class="text-yellow-300 text-xs font-normal">{{ $totalCount }} members</span>
            </div>

            <div class="bg-white border border-gray-200 p-4 shadow-sm space-y-3" x-data="{ deptMenuOpen: false }">
                <div>
                    <p class="text-sm font-semibold text-gray-700">Department dropdown</p>
                    <p class="text-xs text-gray-500">Choose a department to view its HOD, teachers, staff, and lab techs.</p>
                </div>
                <div class="relative max-w-xl">
                    <button type="button"
                        @click="deptMenuOpen = !deptMenuOpen"
                        class="flex w-full items-center justify-between gap-3 rounded-md border border-gray-300 bg-white px-4 py-3 text-left text-sm font-semibold text-gray-700 shadow-sm transition hover:border-[#8B0000] hover:text-[#8B0000]">
                        <span class="flex min-w-0 flex-col">
                            <span class="text-[11px] font-medium uppercase tracking-[0.2em] text-gray-400">Selected Department</span>
                            <span class="truncate">{{ $activeDepartmentLabel }}</span>
                        </span>
                        <svg class="h-4 w-4 shrink-0 transition-transform" :class="deptMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="deptMenuOpen" x-cloak @click.outside="deptMenuOpen = false"
                        class="absolute left-0 top-full z-20 mt-2 w-full overflow-hidden rounded-md border border-gray-200 bg-white shadow-lg">
                        <a href="{{ route('public.people') }}"
                            class="block px-4 py-3 text-sm text-gray-700 hover:bg-red-50 hover:text-[#8B0000] transition-colors {{ $selectedDepartmentSlug === '' ? 'bg-red-50 text-[#8B0000]' : '' }}">
                            All Departments
                        </a>
                        @foreach($departmentOptions as $department)
                            <a href="{{ route('public.people', ['department' => $department->slug]) }}"
                                class="block px-4 py-3 text-sm text-gray-700 hover:bg-red-50 hover:text-[#8B0000] transition-colors {{ $selectedDepartmentSlug === $department->slug ? 'bg-red-50 text-[#8B0000]' : '' }}">
                                {{ $department->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            @if($visibleSections->count() > 0)
                <div class="space-y-6">
                    @foreach($visibleSections as $section)
                        <section class="bg-white border border-gray-200 shadow-sm overflow-hidden">
                            <div class="bg-[#8B0000] px-5 py-3 text-white flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 class="font-serif text-lg font-bold">{{ $section->department->name }}</h2>
                                    <p class="text-xs text-red-100">Department-wise people list</p>
                                </div>
                                <a href="{{ route('public.department.show', $section->department->slug) }}" class="text-xs font-semibold uppercase tracking-wide text-yellow-300 hover:text-yellow-200 transition-colors">View Department</a>
                            </div>

                            <div class="p-5 space-y-6">
                                @if($section->count > 0)
                                    @php
                                        $categoryGroups = [
                                            ['label' => 'Head of Department', 'members' => $section->hod ? collect([$section->hod]) : collect()],
                                            ['label' => 'Teachers', 'members' => $section->teachers],
                                            ['label' => 'Staff', 'members' => $section->staff],
                                            ['label' => 'Lab Techs', 'members' => $section->labtechs],
                                        ];
                                    @endphp

                                    @foreach($categoryGroups as $category)
                                        @if($category['members']->count() > 0)
                                            <div>
                                                <div class="mb-3 flex items-center justify-between gap-3">
                                                    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-700">{{ $category['label'] }}</h3>
                                                    <span class="text-xs text-gray-400">{{ $category['members']->count() }} people</span>
                                                </div>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
                                                    @foreach($category['members'] as $member)
                                                        <div class="group text-center p-4 border border-gray-100 rounded hover:border-[#8B0000]/30 hover:shadow-md transition-all">
                                                            <div class="w-24 h-24 mx-auto mb-3 rounded-full overflow-hidden border-4 border-gray-100 group-hover:border-[#8B0000]/20 transition-colors bg-gray-200 shadow-sm">
                                                                <img src="{{ $member->photo_url }}" alt="{{ $member->name }}" class="w-full h-full object-cover" loading="lazy">
                                                            </div>
                                                            <h4 class="font-bold text-sm text-gray-900 group-hover:text-[#8B0000] transition-colors">{{ $member->name }}</h4>
                                                            <p class="text-xs text-gray-500 mt-1">{{ $member->designation }}</p>
                                                            @if($member->department)
                                                                @if(!empty($member->department_slug))
                                                                    <a href="{{ route('public.department.show', $member->department_slug) }}" class="text-[10px] text-red-700 bg-red-50 px-2 py-0.5 rounded inline-block mt-2 border border-red-100 hover:bg-red-100 transition-colors">
                                                                        {{ $member->department }}
                                                                    </a>
                                                                @else
                                                                    <span class="text-[10px] text-red-700 bg-red-50 px-2 py-0.5 rounded inline-block mt-2 border border-red-100">{{ $member->department }}</span>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @else
                                    <div class="rounded border border-dashed border-gray-300 bg-gray-50 px-4 py-10 text-center text-gray-500">
                                        <p class="font-semibold">No people have been assigned to this department yet.</p>
                                        <p class="text-sm mt-2">Try another department from the dropdown.</p>
                                    </div>
                                @endif
                            </div>
                        </section>
                    @endforeach
                </div>
            @else
                <div class="bg-white border border-gray-200 p-10 text-center text-gray-500 shadow-sm">
                    <p class="text-5xl mb-4">👥</p>
                    <p class="font-semibold">No people have been assigned to this department yet.</p>
                    <p class="text-sm mt-2">Choose another department or check back later.</p>
                </div>
            @endif

            @if($selectedDepartmentSlug === '' && $otherCount > 0)
                <section class="bg-white border border-gray-200 shadow-sm overflow-hidden">
                    <div class="bg-gray-800 px-5 py-3 text-white flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="font-serif text-lg font-bold">Other / Unassigned</h2>
                            <p class="text-xs text-gray-300">People not matched to a department</p>
                        </div>
                        <span class="text-xs text-gray-300">{{ $otherCount }} people</span>
                    </div>

                    <div class="p-5 space-y-6">
                        @php
                            $otherCategoryGroups = [
                                ['label' => 'Lab Techs', 'members' => $otherLabTechs],
                                ['label' => 'Staff', 'members' => $otherStaff],
                            ];
                        @endphp

                        @foreach($otherCategoryGroups as $category)
                            @if($category['members']->count() > 0)
                                <div>
                                    <div class="mb-3 flex items-center justify-between gap-3">
                                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-700">{{ $category['label'] }}</h3>
                                        <span class="text-xs text-gray-400">{{ $category['members']->count() }} people</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
                                        @foreach($category['members'] as $member)
                                            <div class="group text-center p-4 border border-gray-100 rounded hover:border-[#8B0000]/30 hover:shadow-md transition-all">
                                                <div class="w-24 h-24 mx-auto mb-3 rounded-full overflow-hidden border-4 border-gray-100 group-hover:border-[#8B0000]/20 transition-colors bg-gray-200 shadow-sm">
                                                    <img src="{{ $member->photo_url }}" alt="{{ $member->name }}" class="w-full h-full object-cover" loading="lazy">
                                                </div>
                                                <h4 class="font-bold text-sm text-gray-900 group-hover:text-[#8B0000] transition-colors">{{ $member->name }}</h4>
                                                <p class="text-xs text-gray-500 mt-1">{{ $member->designation }}</p>
                                                @if($member->department)
                                                    <span class="text-[10px] text-red-700 bg-red-50 px-2 py-0.5 rounded inline-block mt-2 border border-red-100">{{ $member->department }}</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <div class="space-y-6">
            <div>
                <div class="section-header" style="background-color: #8B0000;">🔗 Quick Links</div>
                <div class="bg-white border border-gray-200 border-t-0">
                    @foreach([
                        ['label' => 'Administrative Staff', 'href' => route('public.staff')],
                        ['label' => 'Presidents & Principals', 'href' => route('public.leadership')],
                        ['label' => 'Our Programs', 'href' => route('public.departments')],
                        ['label' => 'Notice Board', 'href' => route('public.notices')],
                        ['label' => 'Contact Us', 'href' => route('public.contact')],
                    ] as $link)
                        <a href="{{ $link['href'] }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 last:border-0 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 transition-colors">
                            <span class="text-red-600">›</span>{{ $link['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            @if($departments->count() > 0)
                <div>
                    <div class="section-header" style="background-color: #8B0000;">🏛️ Departments</div>
                    <div class="bg-white border border-gray-200 border-t-0">
                        @foreach($departmentOptions as $department)
                            <a href="{{ route('public.department.show', $department->slug) }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 last:border-0 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 transition-colors">
                                <span class="text-red-600">›</span>{{ $department->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
