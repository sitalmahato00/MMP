@extends('layouts.guest')
@section('title', 'Administrative Staff')
@section('meta_description', 'Meet the administrative staff of Manmohan Memorial Polytechnic.')
@section('breadcrumb', true)

@section('content')
@php
    $staffByDept = $staff->groupBy(fn($s) => $s->department ?: 'General');
    $deptNames = $staffByDept->keys()->sort()->values();
@endphp
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8"
     x-data="{ activeDept: 'all' }">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <div class="section-header flex items-center justify-between pr-3" style="background-color: #8B0000;">
                <span>👥 Administrative Staff</span>
                <span class="text-yellow-300 text-xs font-normal" x-text="activeDept === 'all' ? '{{ $staff->count() }} members' : ''"></span>
            </div>

            {{-- Department Filter --}}
            @if($deptNames->count() > 1)
            <div class="bg-white border-x border-b border-gray-200 px-4 py-3 flex flex-wrap gap-2">
                <button @click="activeDept = 'all'"
                    :class="activeDept === 'all' ? 'bg-[#8B0000] text-white border-[#8B0000]' : 'bg-white text-gray-600 border-gray-300 hover:border-[#8B0000] hover:text-[#8B0000]'"
                    class="px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors">
                    All Departments
                </button>
                @foreach($deptNames as $dept)
                <button @click="activeDept = '{{ addslashes($dept) }}'"
                    :class="activeDept === '{{ addslashes($dept) }}' ? 'bg-[#8B0000] text-white border-[#8B0000]' : 'bg-white text-gray-600 border-gray-300 hover:border-[#8B0000] hover:text-[#8B0000]'"
                    class="px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors">
                    {{ $dept }}
                </button>
                @endforeach
            </div>
            @endif

            <div class="bg-white border border-gray-200 border-t-0 p-5">
                @if($staff->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
                        @foreach($staff as $member)
                            <div class="group text-center p-4 border border-gray-100 rounded hover:border-[#8B0000]/30 hover:shadow-md transition-all"
                                 x-show="activeDept === 'all' || activeDept === '{{ addslashes($member->department ?: 'General') }}'"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100">
                                <div class="w-24 h-24 mx-auto mb-3 rounded-full overflow-hidden border-4 border-gray-100 group-hover:border-[#8B0000]/20 transition-colors bg-gray-200 shadow-sm">
                                    <img src="{{ $member->photo_url }}" alt="{{ $member->name }}" class="w-full h-full object-cover" loading="lazy">
                                </div>
                                <h3 class="font-bold text-sm text-gray-900 group-hover:text-[#8B0000] transition-colors">{{ $member->name }}</h3>
                                <p class="text-xs text-gray-500 mt-1">{{ $member->designation }}</p>
                                @if($member->department)
                                    <span class="text-[10px] text-red-700 bg-red-50 px-2 py-0.5 rounded inline-block mt-2 border border-red-100">{{ $member->department }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-16 text-center text-gray-400">
                        <p class="text-5xl mb-4">👥</p>
                        <p class="font-semibold text-gray-500">Staff directory is being updated.</p>
                        <p class="text-sm text-gray-400 mt-2">Please check back later.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            <div>
                <div class="section-header" style="background-color: #8B0000;">🔗 Quick Links</div>
                <div class="bg-white border border-gray-200 border-t-0">
                    @foreach([
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
                        @foreach($departments as $dept)
                            <a href="{{ route('public.department.show', $dept->slug) }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 last:border-0 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 transition-colors">
                                <span class="text-red-600">›</span>{{ $dept->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

