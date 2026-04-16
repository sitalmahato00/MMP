@extends('layouts.guest')
@section('title', 'Alumni Directory')
@section('meta_description', 'Meet the successful alumni of Manmohan Memorial Polytechnic — graduates making an impact across industry and community.')
@section('breadcrumb', true)

@section('content')
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8"
     x-data="{ activeDept: 'all' }">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Main Alumni Grid --}}
        <div class="lg:col-span-2">
            <div class="section-header flex items-center justify-between pr-3" style="background-color: #8B0000;">
                <span>🎓 Alumni Directory</span>
                <span class="text-yellow-300 text-xs font-normal">{{ $alumni->count() }} alumni</span>
            </div>

            {{-- Department Filter --}}
            @if($departments->count() > 0)
            <div class="bg-white border-x border-b border-gray-200 px-4 py-3 flex flex-wrap gap-2">
                <button @click="activeDept = 'all'"
                    :class="activeDept === 'all' ? 'bg-[#8B0000] text-white border-[#8B0000]' : 'bg-white text-gray-600 border-gray-300 hover:border-[#8B0000] hover:text-[#8B0000]'"
                    class="px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors">
                    All Departments
                </button>
                @foreach($departments as $dept)
                <button @click="activeDept = '{{ $dept->id }}'"
                    :class="activeDept === '{{ $dept->id }}' ? 'bg-[#8B0000] text-white border-[#8B0000]' : 'bg-white text-gray-600 border-gray-300 hover:border-[#8B0000] hover:text-[#8B0000]'"
                    class="px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors">
                    {{ $dept->name }}
                </button>
                @endforeach
            </div>
            @endif

            <div class="bg-white border border-gray-200 border-t-0 p-5">
                @if($alumni->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        @foreach($alumni as $alumnus)
                        <div class="group p-4 border border-gray-100 rounded-lg hover:border-[#8B0000]/30 hover:shadow-md transition-all flex gap-4"
                             x-show="activeDept === 'all' || activeDept === '{{ $alumnus->department_id }}'"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100">
                            <div class="w-16 h-16 flex-shrink-0 rounded-full overflow-hidden border-2 border-gray-100 group-hover:border-[#8B0000]/20 transition-colors bg-gray-100">
                                @if($alumnus->user && $alumnus->user->avatar)
                                    <img src="{{ asset('storage/' . $alumnus->user->avatar) }}" alt="{{ $alumnus->user->name ?? 'Alumni' }}" class="w-full h-full object-cover" loading="lazy">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-xl font-bold text-gray-400" style="background: linear-gradient(135deg, #FEF2F2, #fee2e2);">
                                        {{ strtoupper(substr($alumnus->user->name ?? 'A', 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-bold text-gray-900 group-hover:text-[#8B0000] transition-colors text-sm">
                                    <a href="{{ route('public.alumni.profile', $alumnus->id) }}">{{ $alumnus->user->name ?? 'Alumni' }}</a>
                                </h3>
                                @if($alumnus->current_job || $alumnus->company_name)
                                    <p class="text-xs text-gray-600 mt-0.5">
                                        {{ $alumnus->current_job }}
                                        @if($alumnus->company_name) <span class="text-gray-400">@ {{ $alumnus->company_name }}</span> @endif
                                    </p>
                                @endif
                                <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                    @if($alumnus->department)
                                        <span class="text-[10px] text-red-700 bg-red-50 px-2 py-0.5 rounded border border-red-100">{{ $alumnus->department->name }}</span>
                                    @endif
                                    @if($alumnus->graduation_year)
                                        <span class="text-[10px] text-gray-500 bg-gray-50 px-2 py-0.5 rounded border border-gray-100">Batch {{ $alumnus->graduation_year }}</span>
                                    @endif
                                </div>
                                <a href="{{ route('public.alumni.profile', $alumnus->id) }}" class="inline-flex items-center gap-1 text-[10px] font-semibold text-[#8B0000] hover:text-[#5c0000] mt-2 transition-colors">
                                    View Profile →
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-16 text-center text-gray-400">
                        <p class="text-5xl mb-4">🎓</p>
                        <p class="font-semibold text-gray-500">Alumni profiles coming soon.</p>
                        <p class="text-sm text-gray-400 mt-2">Verified alumni will appear here once their profiles are activated.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            <div class="rounded-xl p-5 text-white" style="background: linear-gradient(135deg, #8B0000, #5B0000);">
                <svg class="w-10 h-10 mx-auto mb-3 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M12 14l9-5-9-5-9 5 9 5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                </svg>
                <h3 class="font-bold font-serif text-center text-lg mb-2">Are You an Alumnus?</h3>
                <p class="text-red-200 text-sm text-center mb-4">Login to update your profile and connect with the MMP community.</p>
                <a href="{{ route('login') }}" class="block text-center bg-yellow-500 hover:bg-yellow-400 text-gray-900 font-bold px-5 py-2 rounded text-sm transition-colors">
                    Login to Portal
                </a>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="section-header rounded-t-xl">Quick Links</div>
                <div class="p-4">
                    <ul class="space-y-2.5 text-sm">
                        @foreach([
                            ['href' => route('public.staff'), 'label' => 'Administrative Staff'],
                            ['href' => route('public.leadership'), 'label' => 'Presidents & Principals'],
                            ['href' => route('public.departments'), 'label' => 'Our Programs'],
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
