@extends('layouts.guest')

@section('title', 'Manmohan Memorial Polytechnic')
@section('meta_description', 'Best Technical College in Koshi Province, Nepal. Diploma programs in IT, Civil, Electrical, Mechanical & Electronics Engineering.')
@section('no_breadcrumb', true)

@section('content')

@php
    $bannerSlides = ($banners ?? collect())->sortBy('order')->values();
    $hasSlides = $bannerSlides->count() > 0;
    
    $deptList = ($departments ?? collect());
    $currentPrincipal = isset($leadership['principals']) ? $leadership['principals']->firstWhere('is_current', true) : null;
    if (!$currentPrincipal && isset($leadership['principals'])) {
        $currentPrincipal = $leadership['principals']->first();
    }
@endphp

<div>

{{-- ── 1. HERO SLIDER (Alpine.js Auto-Slide) ────────────────── --}}
<section class="hero-section relative w-full h-[320px] sm:h-[380px] md:h-[450px] lg:h-[480px] overflow-hidden bg-gray-900 dark:bg-gray-950"
    x-data="{
        current: 0,
        total: {{ $hasSlides ? $bannerSlides->count() : 1 }},
        autoplay: null,
        init() {
            this.autoplay = setInterval(() => { this.next() }, 5000);
        },
        next() {
            this.current = (this.current + 1) % this.total;
        },
        prev() {
            this.current = (this.current - 1 + this.total) % this.total;
        },
        goTo(i) {
            this.current = i;
            clearInterval(this.autoplay);
            this.autoplay = setInterval(() => { this.next() }, 5000);
        }
    }">

    @if($hasSlides)
        @foreach($bannerSlides as $i => $banner)
            <div class="absolute inset-0 transition-opacity duration-700"
                 :class="current === {{ $i }} ? 'opacity-100 z-10' : 'opacity-0 z-0'">
                <img src="{{ $banner->image_url ?? asset('assets/image.png') }}" alt="{{ $banner->title }}" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/50 to-black/30"></div>
                <div class="absolute inset-0 flex flex-col justify-center">
                    <div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto text-white">
                        <div class="max-w-3xl pl-2 sm:pl-4 md:pl-8">
                            <div class="mb-2 sm:mb-3">
                                <span class="bg-[#EAB308] text-gray-900 text-[10px] sm:text-xs font-extrabold px-3 py-1 uppercase tracking-wider rounded-xs shadow">BEST TECHNICAL COLLEGE IN NEPAL</span>
                            </div>
                            <h1 class="text-2xl sm:text-3xl md:text-5xl lg:text-6xl font-black uppercase text-white drop-shadow-2xl mb-3 leading-tight tracking-tight">
                                {{ $banner->title ?? 'MANMOHAN MEMORIAL POLYTECHNIC' }}
                            </h1>
                            <div class="text-xs sm:text-sm md:text-base font-semibold text-gray-200 drop-shadow-lg flex flex-wrap items-center gap-2 mb-6">
                                <span>Information Technology</span> <span class="text-[#EAB308]">|</span>
                                <span>Civil Engineering</span> <span class="text-[#EAB308]">|</span>
                                <span>Electrical Engineering</span> <span class="text-[#EAB308]">|</span>
                                <span>Mechanical Engineering</span> <span class="text-[#EAB308]">|</span>
                                <span>Electronics Engineering</span>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ route('public.page', 'what-is-mmp') }}" class="border-2 border-white/80 hover:border-white hover:bg-white/10 text-white px-5 py-2.5 text-xs sm:text-sm font-bold inline-flex items-center gap-2 rounded-xs transition-all">
                                    Learn More <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="absolute inset-0">
            <div class="w-full h-full bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900"></div>
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-blue-900/30 via-transparent to-transparent"></div>
        </div>
        <div class="absolute inset-0 flex flex-col justify-center">
            <div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto text-white">
                <div class="max-w-3xl pl-2 sm:pl-4 md:pl-8">
                    <div class="mb-3">
                        <span class="bg-[#EAB308] text-gray-900 text-[10px] sm:text-xs font-extrabold px-3 py-1 uppercase tracking-wider rounded-xs shadow">BEST TECHNICAL COLLEGE IN NEPAL</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl md:text-5xl lg:text-6xl font-black uppercase text-white drop-shadow-2xl mb-3 leading-tight tracking-tight">
                        MANMOHAN MEMORIAL POLYTECHNIC
                    </h1>
                    <div class="text-xs sm:text-sm md:text-base font-semibold text-gray-200 drop-shadow-lg flex flex-wrap items-center gap-2 mb-6">
                        <span>Information Technology</span> <span class="text-[#EAB308]">|</span>
                        <span>Civil Engineering</span> <span class="text-[#EAB308]">|</span>
                        <span>Electrical Engineering</span> <span class="text-[#EAB308]">|</span>
                        <span>Mechanical Engineering</span> <span class="text-[#EAB308]">|</span>
                        <span>Electronics Engineering</span>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('public.page', 'what-is-mmp') }}" class="border-2 border-white/80 hover:border-white hover:bg-white/10 text-white px-5 py-2.5 text-xs sm:text-sm font-bold inline-flex items-center gap-2 rounded-xs transition-all">
                            Learn More <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($hasSlides && $bannerSlides->count() > 1)
        <button @click="prev(); clearInterval(autoplay); autoplay = setInterval(() => next(), 5000)" class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-black/40 hover:bg-black/70 text-white rounded-full flex items-center justify-center z-20 transition-colors backdrop-blur-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button @click="next(); clearInterval(autoplay); autoplay = setInterval(() => next(), 5000)" class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-black/40 hover:bg-black/70 text-white rounded-full flex items-center justify-center z-20 transition-colors backdrop-blur-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
    @endif
</section>

{{-- ── 2. TOP 3 CARDS ROW ────────────────────────────────────── --}}
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8 bg-[#f4f6f9] dark:bg-slate-900">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">

        {{-- Card 1: Why Choose MMP? --}}
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden flex flex-col justify-between transition-all hover:shadow-md">
            <div>
                <div class="bg-[#003D82] text-white font-bold p-3.5 flex items-center gap-2 text-sm border-b-2 border-yellow-500">
                    <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Why Choose MMP?
                </div>
                <div class="p-4 space-y-3">
                    @foreach([
                        ['title' => 'CTEVT Affiliated', 'desc' => 'Government recognized technical education.'],
                        ['title' => 'Modern Labs & Workshops', 'desc' => 'Hands-on practical learning environment.'],
                        ['title' => 'Industry Placements', 'desc' => 'Internship and job placement support.'],
                        ['title' => 'Inclusive Environment', 'desc' => 'Scholarships & physical support.']
                    ] as $item)
                        <div class="flex gap-2.5 items-start">
                            <div class="w-4 h-4 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div>
                                <div class="font-bold text-[13px] text-slate-800 dark:text-slate-200 leading-tight">{{ $item['title'] }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $item['desc'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <a href="{{ route('public.facilities') }}" class="bg-[#003D82] hover:bg-blue-900 text-white text-xs font-semibold py-2.5 px-4 text-center block transition-colors border-t border-[#002d62]">
                Explore Features →
            </a>
        </div>

        {{-- Card 2: Welcome to MMP (Solid Blue Card) --}}
        <div class="bg-[#1e3a8a] text-white rounded-lg shadow-md overflow-hidden flex flex-col justify-between p-6 text-center relative">
            <div class="space-y-3">
                <h3 class="text-xl font-bold tracking-wide">Welcome to MMP</h3>
                <p class="text-xs text-blue-100 leading-relaxed">
                    This website is designed to introduce you to Manmohan Memorial Polytechnic. For all MMP community members, you will explore, access, and share your most relevant academic data with speed and security.
                </p>
                <p class="text-[11px] text-blue-200 leading-relaxed pt-1">
                    We strive to create a culture of lifelong learning, dynamic inquiry, and active citizenship. Join us on this journey to create a brighter tomorrow.
                </p>
            </div>
            <div class="pt-5">
                <a href="{{ route('public.page', 'what-is-mmp') }}" class="inline-block bg-white text-[#1e3a8a] hover:bg-yellow-400 hover:text-gray-900 font-extrabold text-xs uppercase px-6 py-2.5 rounded-sm transition-all shadow-sm">
                    ABOUT MMP
                </a>
            </div>
        </div>

        {{-- Card 3: Academic Resources --}}
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden flex flex-col justify-between transition-all hover:shadow-md">
            <div>
                <div class="bg-[#003D82] text-white font-bold p-3.5 flex items-center gap-2 text-sm border-b-2 border-yellow-500">
                    <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    Academic Resources
                </div>
                <div class="p-4 space-y-3">
                    @foreach([
                        ['title' => 'Check Exam Results', 'desc' => 'Quickly check your academic results online.', 'url' => route('public.result'), 'icon' => '<svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'],
                        ['title' => 'Question Bank', 'desc' => 'Access previous years exam questions.', 'url' => route('public.question-bank'), 'icon' => '<svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>'],
                        ['title' => 'Downloads & Syllabus', 'desc' => 'Get syllabus, forms & academic calendar.', 'url' => route('public.downloads'), 'icon' => '<svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>'],
                        ['title' => 'Alumni Network', 'desc' => 'Connect with our graduate directory.', 'url' => route('public.alumni'), 'icon' => '<svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>']
                    ] as $item)
                        <a href="{{ $item['url'] }}" class="flex gap-2.5 items-start group/item hover:bg-slate-50 dark:hover:bg-slate-700/30 p-1 rounded-sm transition-colors">
                            <div class="w-4 h-4 rounded-full bg-blue-100 dark:bg-blue-900/40 text-[#003D82] dark:text-blue-400 flex items-center justify-center flex-shrink-0 mt-0.5 group-hover/item:scale-110 transition-transform">
                                {!! $item['icon'] !!}
                            </div>
                            <div>
                                <div class="font-bold text-[13px] text-slate-800 dark:text-slate-200 leading-tight group-hover/item:text-[#003D82] dark:group-hover/item:text-blue-400 transition-colors">{{ $item['title'] }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $item['desc'] }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            <a href="{{ route('login') }}" class="bg-[#003D82] hover:bg-blue-900 text-white text-xs font-semibold py-2.5 px-4 text-center block transition-colors border-t border-[#002d62]">
                Access Student Portal →
            </a>
        </div>

    </div>
</div>

{{-- ── 3. NOTICE BOARD & NEWS/EVENTS SECTION ───────────────────── --}}
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8 bg-white dark:bg-slate-950">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">

        {{-- Left 7 Cols: Notice Board & Exam Results Tabs --}}
        <div class="lg:col-span-6 flex flex-col bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden" x-data="{ activeNoticeTab: 'general' }">
            <div class="grid grid-cols-2 bg-[#003D82] text-white">
                <button type="button" @click="activeNoticeTab = 'general'" :class="activeNoticeTab === 'general' ? 'bg-[#0b4a92] text-white border-b-2 border-yellow-400' : 'bg-[#003D82] text-white/80 hover:text-white'" class="py-3 font-bold text-sm flex items-center justify-center gap-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Notice Board
                </button>
                <button type="button" @click="activeNoticeTab = 'exam'" :class="activeNoticeTab === 'exam' ? 'bg-[#0b4a92] text-white border-b-2 border-yellow-400' : 'bg-[#003D82] text-white/80 hover:text-white'" class="py-3 font-bold text-sm flex items-center justify-center gap-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Exam Results
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-2 min-h-[260px]">
                <ul class="divide-y divide-slate-100 dark:divide-slate-700" x-show="activeNoticeTab === 'general'">
                    @forelse(($notices ?? collect())->take(4) as $notice)
                    <li>
                        <a href="{{ route('public.notice.show', $notice->slug) }}" class="flex items-center gap-3 p-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded transition-colors group">
                            @php $noticeDate = $notice->published_at ?? $notice->created_at; @endphp
                            <div class="w-10 h-11 bg-[#003D82] text-white flex flex-col items-center justify-center rounded flex-shrink-0 text-center">
                                <span class="text-[7px] font-bold uppercase leading-none">{{ bsDate($noticeDate, 'F') }}</span>
                                <span class="text-xs font-extrabold leading-tight">{{ bsDate($noticeDate, 'd') }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-semibold text-slate-800 dark:text-slate-200 group-hover:text-[#003D82] truncate">{{ $notice->title }}</h4>
                                <span class="text-[10px] text-slate-400">{{ bsDate($noticeDate, 'Y, F d') }}</span>
                            </div>
                        </a>
                    </li>
                    @empty
                    <li class="py-12 text-center text-slate-400 text-xs">No notices online/offline.</li>
                    @endforelse
                </ul>
                <ul class="divide-y divide-slate-100 dark:divide-slate-700" x-show="activeNoticeTab === 'exam'" x-cloak>
                    @forelse(($examNotices ?? collect())->take(4) as $notice)
                    <li>
                        <a href="{{ route('public.notice.show', $notice->slug) }}" class="flex items-center gap-3 p-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded transition-colors group">
                            @php $noticeDate = $notice->published_at ?? $notice->created_at; @endphp
                            <div class="w-10 h-11 bg-[#003D82] text-white flex flex-col items-center justify-center rounded flex-shrink-0 text-center">
                                <span class="text-[7px] font-bold uppercase leading-none">{{ bsDate($noticeDate, 'F') }}</span>
                                <span class="text-xs font-extrabold leading-tight">{{ bsDate($noticeDate, 'd') }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-semibold text-slate-800 dark:text-slate-200 group-hover:text-[#003D82] truncate">{{ $notice->title }}</h4>
                                <span class="text-[10px] text-slate-400">{{ bsDate($noticeDate, 'Y, F d') }}</span>
                            </div>
                        </a>
                    </li>
                    @empty
                    <li class="py-12 text-center text-slate-400 text-xs">No exam result notices currently.</li>
                    @endforelse
                </ul>
            </div>
            <a href="{{ route('public.notices') }}" class="bg-[#003D82] hover:bg-blue-900 text-white text-xs font-semibold py-2.5 px-4 text-center block transition-colors border-t border-[#002d62]">
                View All Notices →
            </a>
        </div>

        {{-- Right 5 Cols: News & Events --}}
        <div class="lg:col-span-6 flex flex-col bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="bg-[#003D82] text-white font-bold p-3.5 text-sm border-b-2 border-yellow-500">
                NEWS & EVENTS
            </div>
            <div class="flex-1 p-4 flex flex-col justify-center items-center text-center">
                @forelse(($newsEvents ?? collect())->take(2) as $event)
                    @php $eventDate = $event->published_at ?? $event->created_at; @endphp
                    <a href="{{ route('public.news-events.show', $event->slug) }}" class="block w-full p-2.5 text-left hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded mb-2 border-b border-slate-100 dark:border-slate-700 last:border-0">
                        <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 line-clamp-1">{{ $event->title }}</div>
                        <div class="text-[10px] text-slate-400 mt-0.5">{{ bsDate($eventDate, 'Y, F d') }}</div>
                    </a>
                @empty
                    <div class="py-6 text-slate-400">
                        <svg class="w-12 h-12 mx-auto mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-xs">No news or events yet.</p>
                    </div>
                @endforelse
            </div>
            <a href="{{ route('public.news-events') }}" class="bg-[#003D82] hover:bg-blue-900 text-white text-xs font-semibold py-2.5 px-4 text-center block transition-colors border-t border-[#002d62]">
                View All News & Events →
            </a>
        </div>

    </div>
</div>

{{-- ── 4. OUR DIPLOMA PROGRAMS ─────────────────────────────────── --}}
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-12 bg-white dark:bg-slate-950 border-t border-[#f9f9f9] dark:border-slate-800">
    <div class="flex justify-between items-center mb-8 pb-3 border-b border-gray-100 dark:border-slate-800">
        <h2 class="text-2xl font-bold text-[#003D82] dark:text-blue-400 border-l-[3px] border-[#003D82] dark:border-blue-400 pl-3 leading-none">
            Our Diploma Programs
        </h2>
        <a href="{{ route('public.departments') }}" class="text-xs font-bold text-gray-500 dark:text-slate-400 hover:text-[#003D82] dark:hover:text-blue-400 flex items-center gap-1 border border-gray-200 dark:border-slate-700 px-3 py-1.5 rounded-sm hover:border-[#003D82] dark:hover:border-blue-400 transition-colors">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            VIEW ALL
        </a>
    </div>

    <div class="flex flex-wrap justify-center gap-5">
        @php
            $programIcons = [
                'Information Technology' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
                'Civil Engineering' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
                'Electrical Engineering' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>',
                'Mechanical Engineering' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                'Electronics Engineering' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>',
                'Architecture Engineering' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
            ];
            $programData = ($departments ?? collect())->take(6);
            if ($programData->isEmpty()) {
                $programData = collect(array_keys($programIcons))->map(fn($n) => (object)['name'=>$n, 'slug'=>\Illuminate\Support\Str::slug($n)]);
            }
        @endphp

        @foreach($programData as $prog)
            <a href="{{ route('public.department.show', $prog->slug) }}" class="program-card group rounded-2xl shadow-md p-6 text-center flex flex-col items-center transition-all duration-300 h-full hover:shadow-2xl hover:-translate-y-1 hover:scale-[1.02] bg-white dark:bg-slate-800 dark:text-slate-200 w-full sm:w-[calc(50%-10px)] md:w-[calc(33.333%-14px)] xl:w-[calc(16.666%-17px)]">
                <div class="program-logo w-14 h-14 bg-blue-50 dark:bg-slate-700 border border-blue-100 dark:border-slate-600 rounded-full shadow-sm flex items-center justify-center text-[#003D82] dark:text-blue-400 group-hover:text-[#003D82] mb-4 group-hover:bg-white dark:group-hover:bg-white transition-colors">
                    {!! $programIcons[$prog->name] ?? '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>' !!}
                </div>
                <h3 class="font-semibold text-[13px] leading-snug mb-1.5 text-gray-900 dark:text-slate-200 group-hover:text-white transition-colors">Diploma in<br>{{ str_replace('Diploma in ', '', $prog->name) }}</h3>
                <p class="text-[11px] text-gray-400 dark:text-slate-500 font-normal group-hover:text-blue-200 mb-3">3 Years / 6 Semesters</p>
            </a>
        @endforeach
    </div>

    <div class="text-center mt-10">
        <a href="{{ route('public.departments') }}" class="bg-[#003D82] dark:bg-blue-600 text-white px-6 py-2.5 rounded-sm font-bold shadow hover:bg-blue-900 dark:hover:bg-blue-700 transition-colors inline-flex items-center gap-2 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            View All Programs
        </a>
    </div>
</div>


{{-- ── 6. STATISTICS COUNTER BAR (6 Stats) ─────────────────────── --}}
<div class="bg-[#1e3a8a] text-white py-8 shadow-inner">
    <div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto">
        @php $s = $stats ?? []; @endphp
        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-6 text-center">
            
            {{-- Stat 1: Graduates --}}
            <div class="space-y-1">
                <div class="w-10 h-10 mx-auto flex items-center justify-center text-yellow-400">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342"/></svg>
                </div>
                <div class="text-2xl font-black">{{ number_format($s['graduates'] ?? 0) }}+</div>
                <div class="text-[10px] font-bold uppercase tracking-widest text-blue-200">GRADUATES</div>
            </div>

            {{-- Stat 2: Current Students --}}
            <div class="space-y-1">
                <div class="w-10 h-10 mx-auto flex items-center justify-center text-yellow-400">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                </div>
                <div class="text-2xl font-black">{{ number_format($s['students'] ?? 1) }}+</div>
                <div class="text-[10px] font-bold uppercase tracking-widest text-blue-200">CURRENT STUDENTS</div>
            </div>

            {{-- Stat 3: Faculty & Staff --}}
            <div class="space-y-1">
                <div class="w-10 h-10 mx-auto flex items-center justify-center text-yellow-400">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z"/></svg>
                </div>
                <div class="text-2xl font-black">{{ number_format($s['faculty_staff'] ?? 0) }}+</div>
                <div class="text-[10px] font-bold uppercase tracking-widest text-blue-200">FACULTY & STAFF</div>
            </div>

            {{-- Stat 4: Partners --}}
            <div class="space-y-1">
                <div class="w-10 h-10 mx-auto flex items-center justify-center text-yellow-400">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div class="text-2xl font-black">0+</div>
                <div class="text-[10px] font-bold uppercase tracking-widest text-blue-200">PARTNERS</div>
            </div>

            {{-- Stat 5: Diploma Programs --}}
            <div class="space-y-1">
                <div class="w-10 h-10 mx-auto flex items-center justify-center text-yellow-400">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                </div>
                <div class="text-2xl font-black">5</div>
                <div class="text-[10px] font-bold uppercase tracking-widest text-blue-200">DIPLOMA PROGRAMS</div>
            </div>

            {{-- Stat 6: Years of Excellence --}}
            <div class="space-y-1">
                <div class="w-10 h-10 mx-auto flex items-center justify-center text-yellow-400">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="text-2xl font-black">16+</div>
                <div class="text-[10px] font-bold uppercase tracking-widest text-blue-200">YEARS OF EXCELLENCE</div>
            </div>

        </div>
    </div>
</div>

{{-- ── 5. PRINCIPAL'S MESSAGE ─────────────────────────────────── --}}
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-10 bg-white dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800">
    <div class="mb-6 pb-2 border-b border-slate-200 dark:border-slate-700">
        <h2 class="text-xl font-extrabold text-[#003D82] dark:text-blue-400 border-l-4 border-[#003D82] pl-3 leading-none">
            Principal's Message
        </h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        {{-- Photo Frame (Wireframe look or image) --}}
        <div class="lg:col-span-3 flex flex-col items-center text-center">
            <div class="w-48 h-60 bg-slate-100 dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-700 rounded-sm relative overflow-hidden flex items-center justify-center shadow-sm">
                @if($currentPrincipal?->avatar)
                    <img src="{{ asset('storage/' . $currentPrincipal->avatar) }}" alt="Principal" class="w-full h-full object-cover">
                @else
                    {{-- Wireframe Cross Placeholder if no image --}}
                    <svg class="w-full h-full text-slate-300 dark:text-slate-700 stroke-1" viewBox="0 0 100 100" preserveAspectRatio="none">
                        <line x1="0" y1="0" x2="100" y2="100" stroke="currentColor" stroke-width="0.5"/>
                        <line x1="100" y1="0" x2="0" y2="100" stroke="currentColor" stroke-width="0.5"/>
                    </svg>
                @endif
            </div>
            <div class="mt-3">
                <h4 class="font-extrabold text-sm text-[#003D82] dark:text-blue-400">{{ $currentPrincipal?->name ?? 'Dr. Sudip Adhikari' }}</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ $currentPrincipal?->designation ?? 'Principal, MMP' }}</p>
            </div>
        </div>

        {{-- Message Text and Video --}}
        <div class="lg:col-span-9 space-y-4 text-xs md:text-sm text-slate-700 dark:text-slate-300 leading-relaxed">
            @if($currentPrincipal?->video_url)
                <div class="relative rounded-lg overflow-hidden shadow-sm border border-slate-200 dark:border-slate-700 max-w-2xl">
                    <video controls class="w-full h-auto" preload="metadata" style="max-height: 400px;">
                        <source src="{{ $currentPrincipal->video_url }}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            @endif

            @if($currentPrincipal?->message)
                @foreach(array_filter(explode("\n\n", $currentPrincipal->message)) as $para)
                    <p>{{ trim($para) }}</p>
                @endforeach
            @else
                <p>This website is designed to introduce you to Manmohan Memorial Polytechnic. We at MMP are confident that you will find your plans and values with maximum patience with our students and staffs. The practical focus of our courses ensures the practical knowledge that will optimize the capacity of our students to compete the labor market nationally and internationally.</p>
                <p>MMP is highly thankful to its sponsor and donor of execution in the technical education centre of Nepal. We offer the best and the most relevant dynamic technical course content that MMP expects to deliver tailored to technical need.</p>
                <p>We provide state-of-the-art facilities and services for our students and faculty, while our faculty are eager to enhance your practical skills and learning experience at MMP.</p>
            @endif
        </div>
    </div>
</div>


{{-- ── 7. TWO COLUMN LOWER SECTION ────────────────────────────── --}}
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-10 bg-[#f4f6f9] dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-stretch">

        {{-- Left: Downloads & Publications --}}
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden flex flex-col justify-between">
            <div>
                <div class="bg-[#003D82] text-white font-bold p-3.5 flex items-center gap-2 text-sm border-b-2 border-yellow-500">
                    <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Downloads & Publications
                </div>
                <div class="p-4">
                    @forelse(($recentDownloads ?? collect())->take(3) as $dl)
                        <div class="flex gap-3 items-center py-2 border-b border-slate-100 dark:border-slate-700 last:border-0">
                            <div class="w-8 h-8 bg-blue-50 dark:bg-slate-700 rounded flex items-center justify-center text-[#003D82] dark:text-blue-400 flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-xs text-slate-800 dark:text-slate-200 truncate">{{ $dl->title }}</h4>
                                <span class="text-[10px] text-slate-400">{{ bsDate($dl->created_at, 'Y, F d') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-slate-400">
                            <svg class="w-10 h-10 mx-auto mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            <p class="text-xs">Downloads coming soon.</p>
                        </div>
                    @endforelse
                </div>
            </div>
            <a href="{{ route('public.downloads') }}" class="bg-[#003D82] hover:bg-blue-900 text-white text-xs font-semibold py-2.5 px-4 text-center block transition-colors border-t border-[#002d62]">
                All Downloads & Publications →
            </a>
        </div>

        {{-- Right: Important Links --}}
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden flex flex-col justify-between">
            <div>
                <div class="bg-[#003D82] text-white font-bold p-3.5 flex items-center gap-2 text-sm border-b-2 border-yellow-500">
                    <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    Important Links
                </div>
                <ul class="divide-y divide-slate-100 dark:divide-slate-700 p-2 text-xs font-semibold">
                    @foreach([
                        'CTEVT' => 'https://ctevt.org.np',
                        'Manmohan Technical University' => 'https://mtu.edu.np',
                        'National Skills Testing Board' => 'https://nstb.org.np',
                        'Ministry of Education, Science & Technology' => 'https://moest.gov.np',
                        'Department of Education, Nepal' => 'https://doe.gov.np',
                    ] as $label => $link)
                        <li>
                            <a href="{{ $link }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 px-3 py-2 text-slate-700 dark:text-slate-300 hover:text-[#003D82] dark:hover:text-blue-400 transition-colors">
                                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                {{ $label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

    </div>
</div>

{{-- ── 8. FIND US / MAP ────────────────────────────────────────── --}}
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-10 bg-white dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800">
    <div class="flex justify-between items-center mb-6 pb-2 border-b border-slate-200 dark:border-slate-700">
        <h2 class="text-xl font-extrabold text-[#003D82] dark:text-blue-400 border-l-4 border-[#003D82] pl-3 leading-none">
            Find Us
        </h2>
        <a href="{{ route('public.contact') }}" class="text-xs font-bold border border-slate-300 dark:border-slate-700 px-3 py-1.5 rounded-xs hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-slate-700 dark:text-slate-300">
            Contact Us
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden p-2">
        {{-- Left: Map Wireframe Container --}}
        <div class="lg:col-span-8 h-[280px] bg-slate-200 dark:bg-slate-800 rounded relative flex items-center justify-center overflow-hidden border border-slate-300 dark:border-slate-700">
            @php
                $googleMapsIframe = optional($siteSettings->get('google_maps_iframe'))->value;
            @endphp
            @if($googleMapsIframe && str_contains($googleMapsIframe, 'iframe'))
                <div class="w-full h-full [&>iframe]:w-full [&>iframe]:h-full [&>iframe]:border-0">
                    {!! $googleMapsIframe !!}
                </div>
            @else
                {{-- Wireframe Cross Container with MAP AREA text --}}
                <svg class="absolute inset-0 w-full h-full text-slate-300 dark:text-slate-700 stroke-1" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <line x1="0" y1="0" x2="100" y2="100" stroke="currentColor" stroke-width="0.3"/>
                    <line x1="100" y1="0" x2="0" y2="100" stroke="currentColor" stroke-width="0.3"/>
                </svg>
                <span class="relative z-10 font-bold text-slate-400 tracking-widest text-sm uppercase">MAP AREA</span>
            @endif
        </div>

        {{-- Right: Contact Information --}}
        <div class="lg:col-span-4 p-4 space-y-4">
            <h3 class="font-extrabold text-sm text-[#003D82] dark:text-blue-400 border-b border-slate-200 dark:border-slate-700 pb-2">Contact Information</h3>
            <ul class="space-y-3 text-xs text-slate-700 dark:text-slate-300 font-medium">
                <li class="flex gap-2.5 items-start">
                    <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <div>Budhiganga-4, Hathimuda, Morang, Koshi Province, Nepal</div>
                </li>
                <li class="flex gap-2.5 items-center">
                    <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <div>+977-21-503075</div>
                </li>
                <li class="flex gap-2.5 items-center">
                    <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <div>info@mmp.edu.np</div>
                </li>
            </ul>
        </div>
    </div>
</div>

</div>

@endsection
