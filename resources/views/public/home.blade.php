@extends('layouts.guest')

@section('title', 'Manmohan Memorial Polytechnic')
@section('meta_description', 'Best Technical College in Koshi Province, Nepal. CTEVT Diploma programs in IT, Civil, Electrical, Mechanical & Electronics Engineering.')
@section('no_breadcrumb', true)

@section('content')

{{-- ── HERO SLIDER SECTION ───────────────────────────────────── --}}
<section class="relative w-full h-[500px] overflow-hidden bg-gray-900 group">
    {{-- Background Image --}}
    <div class="absolute inset-0">
        <img src="{{ asset('assets/image.png') }}" alt="MMP Campus" class="w-full h-full object-cover mix-blend-overlay opacity-90">
        <div class="absolute inset-0 bg-gradient-to-r from-black/80 to-transparent"></div>
    </div>

    {{-- Slider Arrows (Decorative) --}}
    <button class="absolute left-6 top-1/2 -translate-y-1/2 text-white hover:text-gray-300 opacity-60 hover:opacity-100 transition-opacity z-10 hidden md:block text-4xl">
        <i class="ri-arrow-left-s-line"></i>
    </button>
    <button class="absolute right-6 top-1/2 -translate-y-1/2 text-white hover:text-gray-300 opacity-60 hover:opacity-100 transition-opacity z-10 hidden md:block text-4xl">
        <i class="ri-arrow-right-s-line"></i>
    </button>

    <div class="absolute inset-0 flex flex-col justify-center">
        <div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto text-white">
            <div class="max-w-3xl pl-4 md:pl-10">
                <span class="bg-[#e74c3c] bg-opacity-90 text-[10px] font-bold px-2 py-1 mb-3 inline-block uppercase text-white shadow">
                    New Admission
                </span>
                <h1 class="text-3xl md:text-5xl lg:text-[50px] font-bold font-serif leading-[1.15] mb-4 text-white drop-shadow-lg">
                    ADMISSION OPEN FOR DIPLOMA<br>COURSES
                </h1>
                <div class="text-sm md:text-[15px] mb-8 text-gray-200 drop-shadow flex flex-wrap items-center gap-1 md:gap-2 tracking-wide font-light">
                    <span>Information Technology</span> <span class="text-red-400">|</span> 
                    <span>Civil</span> <span class="text-red-400">|</span> 
                    <span>Electrical</span> <span class="text-red-400">|</span> 
                    <span>Mechanical</span> <span class="text-red-400">|</span> 
                    <span>Electronics Engineering</span>
                </div>
                <a href="{{ route('public.departments') }}" class="bg-[#d35400] hover:bg-[#e67e22] text-white px-5 py-2.5 text-sm font-bold shadow-lg transition-colors inline-flex items-center gap-2 rounded-sm leading-none">
                    Apply Now <i class="ri-arrow-right-line text-lg"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Slider Dots --}}
    <div class="absolute bottom-6 left-0 w-full flex justify-center gap-2">
        <div class="w-2 h-2 bg-white rounded-full"></div>
        <div class="w-2 h-2 bg-white/40 rounded-full"></div>
        <div class="w-2 h-2 bg-white/40 rounded-full"></div>
    </div>
</section>

{{-- ── MAIN CONTENT TOP (3 COLUMNS) ────────────────────────────── --}}
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-10 bg-[#f9f9f9]">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- LEFT COLUMN (Quick Links & Officials) --}}
        <div class="lg:col-span-3 space-y-6">
            {{-- Quick Links Card --}}
            <div class="bg-white border text-sm shadow-sm">
                <div class="bg-[#8B0000] text-white font-bold p-3.5 flex items-center gap-2 border-b-2 border-yellow-500">
                    <i class="ri-links-line text-lg"></i>
                    Quick Links
                </div>
                <ul class="divide-y divide-gray-100 p-1">
                    @foreach(['MMP AT A GLANCE', 'Courses/Department - PDF', 'Academic System', 'Student Life at MMP', 'e-Learning Portal (LMS)', 'Constituent Schools', 'Alumni Portal', 'Internships & Placements'] as $link)
                    <li><a href="#" class="block px-4 py-2.5 text-gray-700 hover:text-[#8B0000] hover:bg-red-50 text-[13px] transition-colors"><span class="text-red-500 font-bold mr-2">›</span> {{ $link }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- People/Officials (Dynamic) --}}
            <div class="bg-white border text-sm shadow-sm">
                <div class="bg-[#8B0000] text-white font-bold p-3.5 flex items-center gap-2 border-b-2 border-yellow-500">
                    <i class="ri-user-star-line text-lg"></i>
                    Managements
                </div>
                <div class="p-4 space-y-5">
                    @php
                        $currentPrincipal = $leadership['principals']->firstWhere('is_current', true);
                        $currentPresident = $leadership['presidents']->firstWhere('is_current', true);
                    @endphp
                    @foreach(array_filter([$currentPresident, $currentPrincipal]) as $exec)
                    <div class="flex gap-4 items-center">
                        <div class="w-14 h-16 bg-gray-200 border shadow-sm flex-shrink-0 overflow-hidden -ml-1">
                            @if($exec->avatar)
                                <img src="{{ asset('storage/'.$exec->avatar) }}" class="w-full h-full object-cover">
                            @else
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($exec->name) }}&background=fff" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div>
                            <div class="font-bold text-[#8B0000] text-[13px]">{{ $exec->name }}</div>
                            <div class="text-[11px] text-gray-500 mt-0.5">{{ $exec->designation ?: ucfirst($exec->type) }}</div>
                        </div>
                    </div>
                    @endforeach
                    @if(empty(array_filter([$currentPresident ?? null, $currentPrincipal ?? null])))
                        <p class="text-xs text-gray-400 text-center">Management details coming soon.</p>
                    @endif
                </div>
                <a href="{{ route('public.leadership') }}" class="block p-2.5 bg-gray-50 border-t text-xs font-bold text-[#8B0000] hover:underline text-center">View All Presidents & Principals »</a>
            </div>
        </div>

        {{-- CENTER COLUMN (Welcome & Nav Tabs) --}}
        <div class="lg:col-span-6 space-y-6">
            {{-- Welcome Box --}}
            <div class="bg-[#8B0000] text-white p-8 text-center rounded-sm relative overflow-hidden shadow-sm">
                <div class="absolute inset-0 opacity-10 bg-gradient-to-tr from-black to-transparent"></div>
                <div class="relative z-10">
                    <h2 class="font-serif text-2xl font-bold mb-3">Welcome to MMP</h2>
                    <p class="text-[13px] leading-relaxed mb-4 text-gray-100 px-4 whitespace-pre-line text-left">
                        {{ optional($siteSettings->get('what_is_mmp'))->value ?? 'Manmohan Memorial Polytechnic (MMP) is a constituent college of Manmohan Technical University — the first technical university in Nepal.' }}
                    </p>
                    <a href="{{ route('public.page', 'what-is-mmp') }}" class="inline-block border border-white text-white px-6 py-2 text-xs font-bold hover:bg-white hover:text-[#8B0000] transition-colors uppercase tracking-wide">
                        About MMP
                    </a>
                </div>
            </div>

            {{-- Notice Board Tabs --}}
            <div class="bg-white border shadow-sm flex flex-col h-[400px]" x-data="{ activeNoticeTab: 'general' }">
                <div class="flex">
                    <button type="button" @click="activeNoticeTab = 'general'" :class="activeNoticeTab === 'general' ? 'bg-[#8B0000] text-white border-yellow-500' : 'bg-[#f5f5f5] text-gray-700 border-transparent hover:bg-[#e9e9e9]'" class="flex-1 py-3.5 font-bold text-sm flex items-center justify-center gap-2 transition-colors border-t-[3px] relative">
                        <i class="ri-pushpin-line text-lg"></i>
                        Notice Board
                    </button>
                    <button type="button" @click="activeNoticeTab = 'exam'" :class="activeNoticeTab === 'exam' ? 'bg-[#8B0000] text-white border-yellow-500' : 'bg-[#f5f5f5] text-gray-700 border-transparent hover:bg-[#e9e9e9]'" class="flex-1 py-3.5 font-bold text-sm flex items-center justify-center gap-2 transition-colors border-t-[3px]">
                        <i class="ri-file-text-line text-lg"></i>
                        Exam Schedules & Results
                    </button>
                </div>
                <div class="p-0 overflow-y-auto flex-1">
                    <ul class="divide-y divide-gray-100" x-show="activeNoticeTab === 'general'" x-cloak>
                        @forelse(($notices ?? collect())->take(6) as $notice)
                        <li>
                            <a href="{{ route('public.notices', ['type' => 'general']) }}" class="flex items-start gap-4 px-4 py-3 hover:bg-red-50 group transition-colors">
                                @php $noticeDate = $notice->published_at ?? $notice->created_at; @endphp
                                <div class="text-[11px] text-gray-500 font-medium whitespace-nowrap pt-0.5 w-[75px]">{{ optional($noticeDate)->format('Y-m-d') }}</div>
                                <div class="flex-1 text-[13px] text-gray-700 group-hover:text-[#8B0000] font-medium leading-snug">{{ $notice->title }}</div>
                                <div class="text-gray-300 group-hover:text-[#8B0000]"><i class="ri-arrow-right-s-line text-lg"></i></div>
                            </a>
                        </li>
                        @empty
                        <li class="px-4 py-8 text-center text-gray-500 text-sm">No recent notices found.</li>
                        @endforelse
                    </ul>
                    <ul class="divide-y divide-gray-100" x-show="activeNoticeTab === 'exam'" x-cloak>
                        @forelse(($examNotices ?? collect())->take(6) as $notice)
                        <li>
                            <a href="{{ route('public.notices', ['type' => 'exam']) }}" class="flex items-start gap-4 px-4 py-3 hover:bg-red-50 group transition-colors">
                                @php $noticeDate = $notice->published_at ?? $notice->created_at; @endphp
                                <div class="text-[11px] text-gray-500 font-medium whitespace-nowrap pt-0.5 w-[75px]">{{ optional($noticeDate)->format('Y-m-d') }}</div>
                                <div class="flex-1 text-[13px] text-gray-700 group-hover:text-[#8B0000] font-medium leading-snug">{{ $notice->title }}</div>
                                <div class="text-gray-300 group-hover:text-[#8B0000]"><i class="ri-arrow-right-s-line text-lg"></i></div>
                            </a>
                        </li>
                        @empty
                        <li class="px-4 py-8 text-center text-gray-500 text-sm">No exam schedules or result notices found.</li>
                        @endforelse
                    </ul>
                </div>
                <div class="px-4 py-2 border-t bg-white">
                    <a x-show="activeNoticeTab === 'general'" x-cloak href="{{ route('public.notices', ['type' => 'general']) }}" class="text-[#8B0000] text-xs font-bold hover:underline flex items-center gap-1">View More »</a>
                    <a x-show="activeNoticeTab === 'exam'" x-cloak href="{{ route('public.notices', ['type' => 'exam']) }}" class="text-[#8B0000] text-xs font-bold hover:underline flex items-center gap-1">View More »</a>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN (Events) --}}
        <div class="lg:col-span-3 space-y-6 flex flex-col">
            <div class="bg-white border shadow-sm flex-1 flex flex-col">
                <div class="bg-[#8B0000] text-white font-bold p-3.5 flex items-center gap-2 border-b-2 border-yellow-500">
                    <i class="ri-calendar-event-line text-lg"></i>
                    Events
                </div>
                <div class="p-4 space-y-4 flex-1">
                    @foreach(['Annual Tech Fest & Exhibition 2080', 'Industry Training Seminar for Faculty', 'Quality Assurance in Tech Education', 'World Environment Day Campaign', 'Staff Management Training'] as $index => $eventTitle)
                    <div class="flex gap-3">
                        <div class="w-[60px] h-12 bg-gray-200 border shadow-sm flex-shrink-0 overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 w-full overflow-hidden">
                            <div class="text-[10px] font-bold text-[#8B0000] mb-0.5">Apr 1{{ $index }}, 2026</div>
                            <a href="#" class="font-medium text-gray-800 text-[12px] leading-tight hover:text-[#8B0000] block truncate whitespace-normal" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">{{ $eventTitle }}</a>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="p-2.5 bg-[#8B0000] text-white text-xs font-bold text-left hover:bg-red-900 transition-colors cursor-pointer px-4">
                    View More »
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ── DIPLOMA PROGRAMS (5 COLUMNS GRID) ───────────────────────── --}}
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-12 bg-white border-t border-[#f9f9f9]">
    <div class="flex justify-between items-center mb-8 pb-3 border-b border-gray-100">
        <h2 class="text-2xl font-bold font-serif text-[#8B0000] border-l-[3px] border-[#8B0000] pl-3 leading-none">
            Our Diploma Programs
        </h2>
        <a href="{{ route('public.departments') }}" class="text-xs font-bold text-gray-500 hover:text-[#8B0000] flex items-center gap-1 border border-gray-200 px-3 py-1.5 rounded-sm hover:border-[#8B0000] transition-colors"><i class="ri-function-line"></i> VIEW ALL PROGRAMS</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-5">
        @php
            $programIcons = [
                'Information Technology' => 'ri-computer-line',
                'Civil Engineering' => 'ri-building-2-line',
                'Electrical Engineering' => 'ri-flashlight-line',
                'Mechanical Engineering' => 'ri-settings-3-line',
                'Electronics Engineering' => 'ri-cpu-line',
            ];
            $programData = $departments->take(5);
            if ($programData->isEmpty()) {
                $programData = collect(array_keys($programIcons))->map(fn($n) => (object)['name'=>$n, 'slug'=>Str::slug($n)]);
            }
        @endphp

        @foreach($programData as $prog)
            <a href="{{ route('public.department.show', $prog->slug) }}" class="group border border-gray-100 shadow-[0_2px_8px_rgba(0,0,0,0.04)] rounded-sm p-6 text-center flex flex-col items-center hover:bg-[#8B0000] hover:text-white transition-all h-full">
                <div class="w-14 h-14 bg-white border border-gray-100 rounded-full shadow-sm flex items-center justify-center text-blue-600 group-hover:text-[#8B0000] text-2xl mb-4 group-hover:bg-white transition-colors relative">
                    <i class="{{ $programIcons[$prog->name] ?? 'ri-book-read-line' }}"></i>
                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-yellow-400 rounded-full flex items-center justify-center text-[8px] text-white font-black opacity-0 group-hover:opacity-100 transition-opacity">!</div>
                </div>
                <h3 class="font-bold text-[13px] leading-snug mb-1.5 text-gray-900 group-hover:text-white transition-colors">Diploma in<br>{{ str_replace('Diploma in ', '', $prog->name) }}</h3>
                <p class="text-[11px] text-gray-400 font-medium uppercase tracking-wide group-hover:text-red-200 mb-3">( 3 Years / 6 Semesters )</p>
                <div class="text-[11px] text-gray-500 mt-auto leading-relaxed group-hover:text-gray-100">Preparing skilled professionals for the modern industry.</div>
                <div class="mt-4 text-[#8B0000] font-bold text-xs group-hover:text-yellow-400 transition-colors">3 Years</div>
            </a>
        @endforeach
    </div>
    
    <div class="text-center mt-10">
        <a href="{{ route('public.departments') }}" class="bg-[#8B0000] text-white px-6 py-2.5 rounded-sm font-bold shadow hover:bg-red-900 transition-colors inline-flex items-center gap-2 text-sm drop-shadow-md">
            <i class="ri-eye-line"></i> View All Programs
        </a>
    </div>
</div>

{{-- ── PRINCIPAL'S MESSAGE ─────────────────────────────────────── --}}
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-12 bg-gray-50 border-t border-gray-100 relative overflow-hidden">
    <div class="absolute top-0 right-0 opacity-5 -translate-y-1/4 translate-x-1/4 pointer-events-none">
        <i class="ri-double-quotes-r text-[300px]"></i>
    </div>
    
    <div class="flex justify-between items-center mb-8 pb-3 border-b border-gray-200">
        <h2 class="text-2xl font-bold font-serif text-[#8B0000] border-l-[3px] border-[#8B0000] pl-3 leading-none">
            Principal's Message
        </h2>
    </div>

    <div class="flex flex-col lg:flex-row gap-8 items-start">
        @php $currentPrincipal = $leadership['principals']->firstWhere('is_current', true); @endphp
        <div class="text-center w-full lg:w-48 flex-shrink-0">
            <div class="w-32 h-[140px] mx-auto bg-gray-200 border-[6px] border-white shadow-md overflow-hidden mb-3">
                @if($currentPrincipal?->avatar)
                    <img src="{{ asset('storage/'.$currentPrincipal->avatar) }}" alt="Principal" class="w-full h-full object-cover">
                @else
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($currentPrincipal?->name ?? 'Principal') }}&background=fff&size=200" alt="Principal" class="w-full h-full object-cover">
                @endif
            </div>
            <div class="font-bold text-[#8B0000] text-[15px]">{{ $currentPrincipal?->name ?? 'Principal' }}</div>
            <div class="text-xs text-gray-500 font-medium">{{ $currentPrincipal?->designation ?? 'Principal, MMP' }}</div>
        </div>
        <div class="flex-1">
            <div class="space-y-4 text-gray-700 text-[13px] leading-[1.8] text-justify whitespace-pre-line">
                @if($currentPrincipal?->message)
                    {!! nl2br(e(Str::limit($currentPrincipal->message, 600))) !!}
                @else
                    <p>It is with immense pleasure that I welcome you to Manmohan Memorial Polytechnic. Here at MMP, we are confident that you will experience an enriching academic journey coupled with robust technical skill enhancement.</p>
                    <p>We provide a vibrant learning environment that ensures our students gain hands-on practical knowledge that satisfies the needs of modern industries, preparing them for national and international career opportunities.</p>
                @endif
            </div>
            <div class="mt-4 border-l-2 border-red-500 pl-4 py-1">
                <a href="{{ route('public.leadership') }}" class="text-[#8B0000] font-bold text-xs hover:underline flex items-center gap-1 uppercase tracking-wide">
                    View All Presidents & Principals »
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ── STATISTICS BANNER ───────────────────────────────────────── --}}
<div class="bg-[#8B0000] text-white py-14 shadow-inner relative overflow-hidden">
    <div class="absolute inset-0 bg-black/10"></div>
    <div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto relative z-10 w-full px-12">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-8 divide-x divide-white/20 text-center">
            <div class="px-2">
                <div class="w-14 h-14 mx-auto border-2 border-red-400/50 rounded-full flex items-center justify-center mb-3 bg-red-900/50 shadow-inner">
                    <i class="ri-graduation-cap-line text-2xl text-yellow-400"></i>
                </div>
                <div class="text-2xl lg:text-3xl font-black mb-1 drop-shadow">2000+</div>
                <div class="text-[10px] sm:text-xs font-bold text-red-100 uppercase tracking-widest">Graduates</div>
            </div>
            <div class="px-2">
                <div class="w-14 h-14 mx-auto border-2 border-red-400/50 rounded-full flex items-center justify-center mb-3 bg-red-900/50 shadow-inner">
                    <i class="ri-group-line text-2xl text-yellow-400"></i>
                </div>
                <div class="text-2xl lg:text-3xl font-black mb-1 drop-shadow">500+</div>
                <div class="text-[10px] sm:text-xs font-bold text-red-100 uppercase tracking-widest">Current Students</div>
            </div>
            <div class="px-2">
                <div class="w-14 h-14 mx-auto border-2 border-red-400/50 rounded-full flex items-center justify-center mb-3 bg-red-900/50 shadow-inner">
                    <i class="ri-briefcase-4-line text-2xl text-yellow-400"></i>
                </div>
                <div class="text-2xl lg:text-3xl font-black mb-1 drop-shadow">60+</div>
                <div class="text-[10px] sm:text-xs font-bold text-red-100 uppercase tracking-widest">Faculty & Staff</div>
            </div>
            <div class="px-2">
                <div class="w-14 h-14 mx-auto border-2 border-red-400/50 rounded-full flex items-center justify-center mb-3 bg-red-900/50 shadow-inner">
                    <i class="ri-book-open-line text-2xl text-yellow-400"></i>
                </div>
                <div class="text-2xl lg:text-3xl font-black mb-1 drop-shadow">5</div>
                <div class="text-[10px] sm:text-xs font-bold text-red-100 uppercase tracking-widest">Diploma Programs</div>
            </div>
        </div>
    </div>
</div>

{{-- ── THREE COLUMN BOTTOM GRID ────────────────────────────────── --}}
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-10 bg-[#f9f9f9]">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Publications --}}
        <div class="bg-white border text-sm shadow-sm">
            <div class="bg-[#8B0000] text-white font-bold p-3.5 flex items-center gap-2 border-b-2 border-yellow-500">
                <i class="ri-book-marked-line text-lg"></i> Publications
            </div>
            <div class="p-5 space-y-5">
                <div class="flex gap-3 items-start border-b border-gray-100 pb-4">
                    <div class="w-10 h-10 bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 rounded">
                        <i class="ri-file-pdf-fill text-xl"></i>
                    </div>
                    <div>
                        <div class="font-bold text-[13px] text-[#8B0000]">MMP Annual Report 2080</div>
                        <div class="text-[11px] text-gray-500 mt-0.5">Comprehensive summary report of academic year 2080</div>
                    </div>
                </div>
                <div class="flex gap-3 items-start">
                    <div class="w-10 h-10 bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 rounded">
                        <i class="ri-file-pdf-fill text-xl"></i>
                    </div>
                    <div>
                        <div class="font-bold text-[13px] text-[#8B0000]">MMP News Bulletin - Vol. IV</div>
                        <div class="text-[11px] text-gray-500 mt-0.5">Quarterly newsletter detailing achievements of the institution.</div>
                    </div>
                </div>
            </div>
            <div class="p-3 bg-gray-50 border-t">
                <a href="{{ route('public.downloads') }}" class="text-xs font-bold text-[#8B0000] hover:underline">All Publications »</a>
            </div>
        </div>

        {{-- Important Links --}}
        <div class="bg-white border text-sm shadow-sm">
            <div class="bg-[#8B0000] text-white font-bold p-3.5 flex items-center gap-2 border-b-2 border-yellow-500">
                <i class="ri-links-line text-lg"></i> Important Links
            </div>
            <ul class="divide-y divide-gray-100 p-1">
                @foreach(['CTEVT' => 'http://ctevt.org.np', 'National Skills Testing Board' => 'http://nstb.org.np', 'Ministry of Education, Science and Technology' => 'https://moest.gov.np', 'Council for Technical Education and Vocational Training' => 'http://ctevt.org.np', 'Department of Education, Nepal' => 'https://doe.gov.np'] as $label => $link)
                <li>
                    <a href="{{ $link }}" target="_blank" class="flex items-center gap-2 px-4 py-2.5 text-[13px] text-gray-700 hover:bg-gray-50 hover:text-[#8B0000] transition-colors group">
                        <i class="ri-external-link-line text-red-500 group-hover:translate-x-1 transition-transform"></i>
                        {{ $label }}
                    </a>
                </li>
                @endforeach
            </ul>
            <div class="p-2.5 bg-[#8B0000] text-white text-xs font-bold text-center cursor-pointer hover:bg-red-900 transition-colors">
                Explore Links »
            </div>
        </div>

        {{-- Tender Information --}}
        <div class="bg-white border text-sm shadow-sm flex flex-col">
            <div class="bg-[#8B0000] text-white font-bold p-3.5 flex items-center gap-2 border-b-2 border-yellow-500">
                <i class="ri-file-paper-2-line text-lg"></i> Tender Information
            </div>
            <div class="p-5 space-y-4 flex-1">
                <div class="flex gap-3 items-start border-b border-gray-100 pb-4">
                    <div class="w-[45px] h-12 bg-green-50 flex flex-col items-center justify-center text-green-700 rounded border border-green-200">
                        <div class="text-[9px] font-bold uppercase leading-none">NOV</div>
                        <div class="text-[17px] font-black leading-none mt-0.5">15</div>
                    </div>
                    <div>
                        <a href="#" class="font-bold text-[13px] text-[#8B0000] hover:underline">Lab Eqiupment Quotation</a>
                        <div class="text-[11px] text-gray-500 mt-1">Status: Open • Ref: 2080-81/01</div>
                    </div>
                </div>
                <div class="flex gap-3 items-start">
                    <div class="w-[45px] h-12 bg-gray-50 flex flex-col items-center justify-center text-gray-500 rounded border border-gray-200">
                        <div class="text-[9px] font-bold uppercase leading-none">OCT</div>
                        <div class="text-[17px] font-black leading-none mt-0.5">20</div>
                    </div>
                    <div>
                        <span class="font-bold text-[13px] text-gray-500">Furniture Procurement</span>
                        <div class="text-[11px] text-red-500 mt-1">Status: Closed • Ref: 2080-81/02</div>
                    </div>
                </div>
            </div>
            <div class="p-3 bg-gray-50 border-t">
                <a href="{{ route('public.notices') }}" class="text-xs font-bold text-[#8B0000] hover:underline">View More »</a>
            </div>
        </div>

    </div>
</div>

{{-- ── FIND US / MAP ───────────────────────────────────────────── --}}
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto pt-10 pb-16 bg-white border-t border-gray-100">
    <div class="flex justify-between items-center mb-6 pb-2 border-b border-gray-100">
        <h2 class="text-2xl font-bold font-serif text-[#8B0000] border-l-[3px] border-[#8B0000] pl-3 leading-none">
            Find Us
        </h2>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-0 border bg-[#f9f9f9] shadow-sm rounded-sm">
        <div class="lg:col-span-2 h-[350px] relative">
            <a href="https://maps.google.com" target="_blank" class="absolute top-4 left-4 bg-white shadow py-1 px-3 text-sm font-bold text-gray-700 hover:text-[#8B0000] rounded-sm z-10 flex items-center gap-1 border">
                <i class="ri-map-pin-2-fill"></i> Open in Map
            </a>
            <iframe class="w-full h-full border-r" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=en&amp;q=Manmohan%20Memorial%20Polytechnic+(Manmohan%20Memorial%20Polytechnic)&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed"></iframe>
        </div>
        <div class="lg:col-span-1 p-6 md:p-8 bg-[#f9f9f9]">
            <h3 class="font-bold text-[#8B0000] text-[15px] mb-5 border-b border-red-200 pb-2">Contact Information</h3>
            <ul class="space-y-4 text-[13px] text-gray-700 font-medium">
                <li class="flex gap-3">
                    <i class="ri-map-pin-2-fill text-[#8B0000] text-[15px]"></i>
                    <div>Budhiganga-4, Morang, Koshi Province, Nepal</div>
                </li>
                <li class="flex gap-3">
                    <i class="ri-phone-fill text-[#8B0000] text-[15px]"></i>
                    <div>+977 21 590696, +977 21 590697</div>
                </li>
                <li class="flex gap-3">
                    <i class="ri-mail-send-fill text-[#8B0000] text-[15px]"></i>
                    <div>info@mmp.edu.np</div>
                </li>
                <li class="flex gap-3">
                    <i class="ri-global-line text-[#8B0000] text-[15px]"></i>
                    <div>www.mmp.edu.np</div>
                </li>
            </ul>
            
            <h3 class="font-bold text-[#8B0000] text-[15px] mt-8 mb-5 border-b border-red-200 pb-2">Current Office</h3>
            <ul class="space-y-4 text-[13px] text-gray-700 font-medium">
                <li class="flex gap-3">
                    <i class="ri-building-4-fill text-[#8B0000] text-[15px]"></i>
                    <div>
                        Manmohan Technical University<br>
                        Budhiganga-4, Morang<br>
                        Phone: +977 21 590696
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>

@endsection
