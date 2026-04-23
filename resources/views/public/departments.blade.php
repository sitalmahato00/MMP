@extends('layouts.guest')
@section('title', 'Our Departments')
@section('breadcrumb', true)

@section('content')
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
    <div class="section-header mb-6" style="background-color: #003D82;">🏛️ Our Departments</div>
    <p class="text-gray-600 text-sm mb-6 -mt-3">Explore the CTEVT-affiliated departments at Manmohan Memorial Polytechnic. Each department offers diploma programs with hands-on training and industry-ready skills.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @php
            $deptIcons = ['IT' => '💻', 'CE' => '🏗️', 'EL' => '⚡', 'EE' => '📡', 'ME' => '⚙️', 'AR' => '📐'];
        @endphp

        @forelse($departments as $dept)
            <div class="group bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-lg hover:border-blue-200 transition-all">
                <div class="h-2" style="background-color: #003D82;"></div>
                <div class="aspect-[16/9] bg-gray-100 overflow-hidden relative">
                    @if($dept->photo_url)
                        <img src="{{ $dept->photo_url }}" alt="{{ $dept->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-50 flex items-center justify-center text-6xl">{{ $deptIcons[$dept->code] ?? '🏛️' }}</div>
                    @endif
                    @if($dept->is_active)
                        <span class="absolute top-3 right-3 rounded-md bg-emerald-500 px-2 py-0.5 text-[10px] font-bold text-white shadow">Active</span>
                    @endif
                </div>
                <div class="p-5">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-2xl">{{ $deptIcons[$dept->code] ?? '🏛️' }}</span>
                        <span class="rounded-md bg-blue-50 border border-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-800 uppercase tracking-wide">{{ $dept->code }}</span>
                    </div>
                    <h3 class="font-bold text-lg text-gray-900 font-serif mb-2 leading-tight">{{ $dept->name }}</h3>
                    <p class="text-sm text-gray-500 mb-4 leading-relaxed line-clamp-2">{{ $dept->description ?? 'CTEVT approved diploma engineering program.' }}</p>

                    <div class="grid grid-cols-3 gap-2 mb-4 text-center">
                        <div class="rounded-lg bg-gray-50 border border-gray-100 py-2">
                            <div class="text-sm font-bold text-gray-900">{{ $dept->programs_count ?? 0 }}</div>
                            <div class="text-[10px] text-gray-400">Programs</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 border border-gray-100 py-2">
                            <div class="text-sm font-bold text-gray-900">{{ $dept->students_count ?? 0 }}</div>
                            <div class="text-[10px] text-gray-400">Students</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 border border-gray-100 py-2">
                            <div class="text-sm font-bold text-gray-900">{{ $dept->teachers_count ?? 0 }}</div>
                            <div class="text-[10px] text-gray-400">Teachers</div>
                        </div>
                    </div>

                    <a href="{{ route('public.department.show', $dept->slug) }}" class="block w-full text-center rounded-lg border-2 border-[#003D82] bg-white px-4 py-2 text-sm font-bold text-[#003D82] transition-colors hover:bg-[#003D82] hover:text-white">
                        View Department →
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white border border-gray-200 rounded-xl p-10 text-center text-gray-500">
                <div class="text-4xl mb-3">🏛️</div>
                <p class="font-semibold text-gray-700">No departments published yet.</p>
                <p class="text-sm mt-1">Departments will appear here automatically once added in the admin panel.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection

