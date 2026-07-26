@extends('layouts.guest')
@section('title', 'Programs — ' . ($department->name ?? 'Department') . ' — MMP')
@section('breadcrumb', true)

@section('content')
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-6">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

        {{-- MAIN CONTENT --}}
        <div class="lg:col-span-3 space-y-6">

            {{-- Header --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h1 class="text-xl font-black text-gray-900 font-serif">{{ $department->name }} — Programs</h1>
                        <p class="text-sm text-gray-500 mt-0.5">Academic programs offered by this department</p>
                    </div>
                    <a href="{{ route('public.department.show', $department->slug) }}"
                       class="text-sm font-semibold text-[#003D82] hover:underline flex-shrink-0">← Back</a>
                </div>
            </div>

            {{-- Programs Grid --}}
            @if($programs->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                @foreach($programs as $program)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-200 overflow-hidden group">
                    <div class="h-1.5" style="background-color: #003D82;"></div>
                    <div class="p-6">
                        <div class="flex items-start gap-4 mb-4">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform duration-200"
                                 style="background-color: #003D82;">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h2 class="font-bold text-gray-900 text-base leading-tight group-hover:text-[#003D82] transition-colors">
                                    {{ $program->name }}
                                </h2>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $program->code }}
                                    @if($program->ctevt_code) · CTEVT: {{ $program->ctevt_code }}@endif
                                </p>
                            </div>
                        </div>

                        {{-- Tags --}}
                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="inline-flex items-center gap-1 text-xs text-gray-600 bg-gray-50 border border-gray-200 px-2.5 py-1 rounded-full font-medium">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $program->duration_years }} Year{{ $program->duration_years != 1 ? 's' : '' }}
                            </span>
                            <span class="inline-flex items-center gap-1 text-xs text-gray-600 bg-gray-50 border border-gray-200 px-2.5 py-1 rounded-full font-medium">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                {{ $program->total_semesters }} Semesters
                            </span>
                            @if($program->affiliation_type)
                            <span class="inline-flex items-center gap-1 text-xs text-[#003D82] bg-blue-50 border border-blue-200 px-2.5 py-1 rounded-full font-bold">
                                {{ $program->affiliation_type }}
                            </span>
                            @endif
                        </div>

                        @if($program->description)
                        <p class="text-sm text-gray-600 leading-relaxed line-clamp-3 mb-4">{{ $program->description }}</p>
                        @endif

                        <a href="{{ route('public.program.show', [$department->slug, $program->slug ?: \Illuminate\Support\Str::slug($program->name)]) }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold text-white transition-colors"
                           style="background-color: #003D82;">
                            Learn More
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="bg-white rounded-xl border border-gray-200 p-12 text-center shadow-sm">
                <svg class="w-14 h-14 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <p class="font-semibold text-gray-700">No programs listed yet.</p>
                <p class="text-sm text-gray-500 mt-1">Program details will appear here once published.</p>
            </div>
            @endif

        </div>

        {{-- SIDEBAR --}}
        <div class="lg:col-span-1 self-start">
            @include('partials.department-sidebar', [
                'department' => $department,
                'activePage' => 'programs',
                'downloads'  => collect([]),
                'events'     => collect([]),
            ])
        </div>
    </div>
</div>
@endsection
