@extends('layouts.guest')
@section('title', 'People — ' . ($department->name ?? 'Department') . ' — MMP')
@section('breadcrumb', true)

@section('content')
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-6">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

        {{-- MAIN CONTENT --}}
        <div class="lg:col-span-3 space-y-8">

            {{-- Header --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h1 class="text-xl font-black text-gray-900 font-serif">{{ $department->name }} — People</h1>
                        <p class="text-sm text-gray-500 mt-0.5">Faculty and staff of the department</p>
                    </div>
                    <a href="{{ route('public.department.show', $department->slug) }}"
                       class="text-sm font-semibold text-[#003D82] hover:underline">← Back</a>
                </div>
            </div>

            {{-- Head of Department --}}
            @if($hod)
            <div>
                <div class="rounded-t-xl px-4 py-2.5" style="background-color: #003D82;">
                    <span class="text-white font-bold text-sm">Head of Department</span>
                </div>
                <div class="bg-white border border-t-0 border-gray-200 rounded-b-xl shadow-sm p-6">
                    <div class="flex flex-col sm:flex-row gap-6">
                        <div class="flex-shrink-0">
                            <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-full overflow-hidden border-4 border-[#003D82] shadow-lg mx-auto sm:mx-0">
                                <img src="{{ $hod->user?->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($hod->user?->name ?? 'HOD') . '&background=003D82&color=fff&size=200' }}"
                                     alt="{{ $hod->user?->name }}" class="w-full h-full object-cover">
                            </div>
                        </div>
                        <div class="flex-1 text-center sm:text-left">
                            <h2 class="text-xl font-black text-gray-900">{{ $hod->user?->name }}</h2>
                            <p class="text-[#003D82] font-semibold text-sm mt-0.5">Head of Department</p>
                            <p class="text-gray-500 text-sm">{{ $department->name }}</p>
                            @if($hod->qualification)
                            <p class="text-gray-600 text-sm mt-2">🎓 {{ $hod->qualification }}</p>
                            @endif
                            @if($hod->specialization)
                            <p class="text-gray-600 text-sm mt-1">🔬 {{ $hod->specialization }}</p>
                            @endif
                            <div class="flex flex-wrap gap-3 mt-4 justify-center sm:justify-start">
                                @if($hod->user?->email)
                                <a href="mailto:{{ $hod->user->email }}"
                                   class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-[#003D82] border-2 border-[#003D82] rounded-lg hover:bg-[#003D82] hover:text-white transition-all duration-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    Email
                                </a>
                                @endif
                                @if($hod->user?->phone)
                                <a href="tel:{{ $hod->user->phone }}"
                                   class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-gray-600 border-2 border-gray-300 rounded-lg hover:border-[#003D82] hover:text-[#003D82] transition-all duration-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    Call
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Faculty Members --}}
            @if($faculty->count())
            <div>
                <div class="flex items-center gap-3 rounded-t-xl px-4 py-2.5" style="background-color: #003D82;">
                    <span class="text-white font-bold text-sm">Faculty Members</span>
                    <span class="ml-auto text-blue-200 text-xs">({{ $faculty->count() }})</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 p-4 bg-white border border-t-0 border-gray-200 rounded-b-xl shadow-sm">
                    @foreach($faculty as $teacher)
                    <div class="bg-gray-50 rounded-xl border border-gray-200 hover:shadow-md hover:border-blue-200 transition-all duration-200 p-5 group">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-gray-200 group-hover:border-[#003D82] transition-colors mb-3">
                                <img src="{{ $teacher->user?->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($teacher->user?->name ?? 'T') . '&background=003D82&color=fff&size=128' }}"
                                     alt="{{ $teacher->user?->name }}" class="w-full h-full object-cover">
                            </div>
                            <h3 class="font-bold text-gray-900 text-sm leading-tight group-hover:text-[#003D82] transition-colors">
                                {{ $teacher->user?->name }}
                            </h3>
                            <p class="text-xs text-[#003D82] font-semibold mt-0.5">{{ $teacher->designation }}</p>
                            @if($teacher->qualification)
                            <p class="text-[10px] text-gray-500 mt-1">{{ $teacher->qualification }}</p>
                            @endif
                            @if($teacher->specialization)
                            <p class="text-[10px] text-gray-400 mt-0.5 italic">{{ $teacher->specialization }}</p>
                            @endif
                            @if($teacher->user?->email)
                            <a href="mailto:{{ $teacher->user->email }}"
                               class="mt-3 text-[10px] text-blue-600 hover:underline truncate max-w-full">
                                {{ $teacher->user->email }}
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if(!$hod && !$faculty->count())
            <div class="bg-white rounded-xl border border-gray-200 p-12 text-center shadow-sm">
                <svg class="w-12 h-12 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="font-semibold text-gray-700">Faculty details will be available soon.</p>
            </div>
            @endif

        </div>

        {{-- SIDEBAR --}}
        <div class="lg:col-span-1 self-start">
            @include('partials.department-sidebar', [
                'department' => $department,
                'activePage' => 'people',
                'downloads'  => collect([]),
                'events'     => collect([]),
            ])
        </div>
    </div>
</div>
@endsection
