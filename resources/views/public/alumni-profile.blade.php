@extends('layouts.guest')
@section('title', ($alumnus->user->name ?? 'Alumni') . ' — Alumni Profile')
@section('meta_description', 'View the alumni profile of ' . ($alumnus->user->name ?? 'MMP Graduate') . ' — ' . ($alumnus->current_job ?? 'MMP Alumni') . '.')
@section('breadcrumb', true)

@section('content')
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Profile Main --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Header Card --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="h-24" style="background: linear-gradient(135deg, #8B0000, #5B0000);"></div>
                <div class="px-6 pb-6 -mt-12">
                    <div class="flex items-end gap-4 mb-4">
                        <div class="w-20 h-20 rounded-full border-4 border-white shadow-md overflow-hidden bg-gray-100 flex-shrink-0">
                            @if($alumnus->user && $alumnus->user->avatar)
                                <img src="{{ asset('storage/' . $alumnus->user->avatar) }}" alt="{{ $alumnus->user->name ?? 'Alumni' }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-2xl font-bold" style="background: #FEF2F2; color: #8B0000;">
                                    {{ strtoupper(substr($alumnus->user->name ?? 'A', 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="pt-14">
                            <h1 class="text-2xl font-bold font-serif text-gray-900">{{ $alumnus->user->name ?? 'MMP Alumni' }}</h1>
                            @if($alumnus->current_job)
                                <p class="text-gray-600 text-sm mt-0.5">{{ $alumnus->current_job }}
                                    @if($alumnus->company_name) <span class="text-gray-400">at {{ $alumnus->company_name }}</span> @endif
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Badges --}}
                    <div class="flex flex-wrap gap-2 mb-4">
                        @if($alumnus->department)
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-red-700 bg-red-50 px-3 py-1 rounded-full border border-red-100">
                                🏛️ {{ $alumnus->department->name }}
                            </span>
                        @endif
                        @if($alumnus->program)
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 bg-blue-50 px-3 py-1 rounded-full border border-blue-100">
                                📘 {{ $alumnus->program->name }}
                            </span>
                        @endif
                        @if($alumnus->graduation_year)
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-700 bg-green-50 px-3 py-1 rounded-full border border-green-100">
                                🎓 Batch {{ $alumnus->graduation_year }}
                            </span>
                        @endif
                        @if($alumnus->is_verified)
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-yellow-700 bg-yellow-50 px-3 py-1 rounded-full border border-yellow-100">
                                ✓ Verified Alumni
                            </span>
                        @endif
                    </div>

                    {{-- Achievements --}}
                    @if($alumnus->achievements)
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                            <h3 class="font-bold text-sm text-gray-700 mb-2">Achievements & Highlights</h3>
                            <div class="text-sm text-gray-600 leading-relaxed prose prose-sm max-w-none">
                                {!! nl2br(e($alumnus->achievements)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- CTA --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center">
                <p class="font-semibold text-gray-800 mb-1">Are you {{ $alumnus->user->name ?? 'this alumnus' }}?</p>
                <p class="text-sm text-gray-500 mb-3">Login to update your profile and stay connected.</p>
                <a href="{{ route('login') }}" class="inline-block bg-[#8B0000] hover:bg-[#5c0000] text-white font-bold px-5 py-2 rounded text-sm transition-colors w-full text-center">
                    Login to Portal
                </a>
            </div>

            {{-- Back to directory --}}
            <a href="{{ route('public.alumni') }}" class="flex items-center gap-2 text-sm text-[#8B0000] hover:text-[#5c0000] font-semibold transition-colors">
                ← Back to Alumni Directory
            </a>

            {{-- Quick Links --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="section-header rounded-t-xl">Quick Links</div>
                <div class="p-4">
                    <ul class="space-y-2.5 text-sm">
                        @foreach([
                            ['href' => route('public.departments'), 'label' => 'Our Programs'],
                            ['href' => route('public.leadership'), 'label' => 'Presidents & Principals'],
                            ['href' => route('public.contact'), 'label' => 'Contact Us'],
                        ] as $link)
                        <li>
                            <a href="{{ $link['href'] }}" class="flex items-center gap-2 text-gray-700 hover:text-[#8B0000] transition-colors">
                                <span class="text-[#8B0000] font-bold">›</span> {{ $link['label'] }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
