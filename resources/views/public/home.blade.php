@extends('layouts.guest')

@section('title', 'Manmohan Memorial Polytechnic')
@section('meta_description', 'Best Technical College in Koshi Province, Nepal. CTEVT Diploma programs in IT, Civil, Electrical, Mechanical & Electronics Engineering.')
@section('no_breadcrumb', true)

@section('content')

{{-- ── HERO SLIDER (Alpine.js Auto-Slide) ────────────────────── --}}
@php
    $bannerSlides = ($banners ?? collect())->sortBy('order')->values();
    $hasSlides = $bannerSlides->count() > 0;
    $publicTickerItems = collect(($notices ?? collect())->take(6))
        ->merge(($examNotices ?? collect())->take(6))
        ->sortByDesc(function ($notice) {
            return optional($notice->published_at ?? $notice->created_at)->timestamp ?? 0;
        })
        ->take(8)
        ->map(function ($notice) {
            $noticeDate = $notice->published_at ?? $notice->created_at;
            $type = $notice->type ?? 'general';
            $typeLabel = $type === 'exam' ? 'Exam' : ucfirst($type);
            $badgeClass = match ($type) {
                'exam' => 'bg-blue-400/15 text-blue-300 border border-blue-300/20',
                'department' => 'bg-blue-400/15 text-blue-300 border border-blue-300/20',
                'program' => 'bg-green-400/15 text-green-300 border border-green-300/20',
                'news' => 'bg-violet-400/15 text-violet-300 border border-violet-300/20',
                'event' => 'bg-cyan-400/15 text-cyan-300 border border-cyan-300/20',
                default => 'bg-white/10 text-gray-200',
            };

            return [
                'title' => $notice->title,
                'href' => route('public.notices', ['type' => $type]),
                'date' => bsDate($noticeDate, 'Y, F d'),
                'timestamp' => optional($noticeDate)->valueOf() ?? 0,
                'source' => $typeLabel,
                'badge_class' => $badgeClass,
            ];
        });
@endphp
@php
    $mobileDepartments = collect($departments ?? collect())->take(6);
    $mobileNotices = collect($notices ?? collect())->take(5);
    $mobileDownloads = collect($latestDownloads ?? collect())->take(4);
    $mobileHero = $bannerSlides->first();
@endphp

{{-- Mobile view now shows desktop content --}}

<div>
<section class="hero-section relative w-full h-[300px] sm:h-[350px] md:h-[420px] overflow-hidden bg-gray-900 dark:bg-gray-900"
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
                <img src="{{ $banner->image_url ?? asset('assets/image.png') }}" alt="{{ $banner->title }}" class="w-full h-full object-cover dark:opacity-100">
                <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/30 to-transparent dark:from-black/70 dark:via-black/40"></div>
                <div class="absolute inset-0 flex flex-col justify-center">
                    <div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto text-white">
                        <div class="max-w-3xl pl-2 sm:pl-4 md:pl-10">
                            {{-- College identity always visible --}}
                            <div class="flex flex-wrap items-center gap-1 sm:gap-2 mb-2 sm:mb-4 bg-transparent p-0">
                                <span class="text-yellow-400 text-[10px] sm:text-xs md:text-[0.85rem] font-bold sm:font-extrabold uppercase tracking-wider drop-shadow-lg">Best Technical College in Nepal</span>
                                <span class="text-white/40 text-[10px] sm:text-xs md:text-[0.85rem]">·</span>
                                <span class="text-white/85 text-[10px] sm:text-xs md:text-[0.85rem] font-medium">Est. 2054 B.S.</span>
                            </div>
                            @if($banner->subtitle)
                                <span class="rounded-none bg-[#e74c3c] text-[9px] sm:text-[10px] md:text-[0.7rem] font-bold px-2 sm:px-3 py-1 sm:py-1.5 mb-2 sm:mb-4 inline-block uppercase text-white tracking-wider">{{ $banner->subtitle }}</span>
                            @endif
                            <h2 class="text-xl sm:text-2xl md:text-4xl lg:text-5xl xl:text-[3.8rem] font-black leading-tight uppercase text-white drop-shadow-2xl mb-2 sm:mb-4 md:mb-5">
                                {{ $banner->title }}
                            </h2>
                            {{-- Department program list --}}
                            <div class="text-[11px] sm:text-xs md:text-sm lg:text-[0.95rem] font-semibold text-white drop-shadow-lg flex flex-wrap items-center gap-1 sm:gap-2 mb-3 sm:mb-5 md:mb-7">
                                @php $deptNames = ($departments ?? collect())->pluck('name'); @endphp
                                @if($deptNames->count())
                                    @foreach($deptNames as $dn)
                                        <span>{{ $dn }}</span>@if(!$loop->last)<span class="text-yellow-400">|</span>@endif
                                    @endforeach
                                @else
                                    <span>Information Technology</span> <span class="text-yellow-400">|</span>
                                    <span>Civil</span> <span class="text-yellow-400">|</span>
                                    <span>Electrical</span> <span class="text-yellow-400">|</span>
                                    <span>Mechanical</span> <span class="text-yellow-400">|</span>
                                    <span>Electronics Engineering</span>
                                @endif
                            </div>
                            <div class="flex flex-wrap gap-2 sm:gap-3">
                                <a href="{{ route('public.apply') }}" class="bg-[#d35400] hover:bg-[#e67e22] text-white px-3 sm:px-4 md:px-5 py-1.5 sm:py-2 md:py-2.5 text-[11px] sm:text-xs md:text-sm font-bold shadow-lg transition-colors inline-flex items-center gap-1 sm:gap-2 rounded-sm">
                                    Apply Now <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                                <a href="{{ route('public.page', 'what-is-mmp') }}" class="border-2 border-white/65 hover:border-white text-white px-3 sm:px-4 md:px-5 py-1.5 sm:py-2 md:py-2.5 text-[11px] sm:text-xs md:text-sm font-bold inline-flex items-center gap-1 sm:gap-2 rounded-sm backdrop-blur-sm transition-colors">
                                    Learn More <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        {{-- Default slide when no banners in DB --}}
        <div class="absolute inset-0">
            <img src="{{ asset('assets/image.png') }}" alt="MMP Campus" class="w-full h-full object-cover mix-blend-overlay opacity-90">
            <div class="absolute inset-0 bg-gradient-to-r from-black/80 to-transparent"></div>
        </div>
        <div class="absolute inset-0 flex flex-col justify-center">
            <div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto text-white">
                <div class="max-w-3xl pl-4 md:pl-10">
                    <span class="bg-[#e74c3c] text-[10px] font-bold px-3 py-1 mb-3 inline-block uppercase text-white shadow-sm tracking-wide">New Admission</span>
                    <h1 class="text-3xl md:text-5xl lg:text-[50px] font-bold font-serif leading-[1.15] mb-4 text-white drop-shadow-lg">
                        ADMISSION OPEN FOR DIPLOMA<br>COURSES
                    </h1>
                    <div class="text-sm md:text-[15px] mb-8 text-gray-200 drop-shadow flex flex-wrap items-center gap-1 md:gap-2 tracking-wide font-light">
                        <span>Information Technology</span> <span class="text-blue-400">|</span>
                        <span>Civil</span> <span class="text-blue-400">|</span>
                        <span>Electrical</span> <span class="text-blue-400">|</span>
                        <span>Mechanical</span> <span class="text-blue-400">|</span>
                        <span>Electronics Engineering</span>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('public.apply') }}" class="bg-[#d35400] hover:bg-[#e67e22] text-white px-5 py-2.5 text-sm font-bold shadow-lg transition-colors inline-flex items-center gap-2 rounded-sm leading-none">
                            Apply Now <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                        <a href="{{ route('public.page', 'what-is-mmp') }}" class="border-2 border-white/65 hover:border-white text-white px-5 py-2.5 text-sm font-bold inline-flex items-center gap-2 rounded-sm leading-none backdrop-blur-sm transition-colors">
                            Learn More <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Slider Controls --}}
    @if($hasSlides)
        <button @click="prev(); clearInterval(autoplay); autoplay = setInterval(() => next(), 5000)" class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-black/30 hover:bg-black/60 text-white rounded-full flex items-center justify-center z-20 transition-colors backdrop-blur-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button @click="next(); clearInterval(autoplay); autoplay = setInterval(() => next(), 5000)" class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-black/30 hover:bg-black/60 text-white rounded-full flex items-center justify-center z-20 transition-colors backdrop-blur-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
        @if($bannerSlides->count() > 1)
        <div class="absolute bottom-6 left-0 w-full flex justify-center gap-2 z-20">
            @foreach($bannerSlides as $i => $banner)
                <button @click="goTo({{ $i }})" class="w-2.5 h-2.5 rounded-full transition-all duration-300" :class="current === {{ $i }} ? 'bg-white w-6' : 'bg-white/40 hover:bg-white/70'"></button>
            @endforeach
        </div>
        @endif
    @endif
</section>

{{-- ── SCROLLING NOTICE TICKER ──────────────────────────────────── --}}
@if($publicTickerItems->count() > 0)
<div class="bg-[#2c2c2c] text-white overflow-hidden border-b-2 border-yellow-500" data-notice-banner>
    <div class="w-full flex items-center">
        <div class="bg-[#003D82] flex-shrink-0 flex items-center gap-2 px-4 py-2.5 font-bold text-sm z-10 shadow-md relative">
            <svg class="w-4 h-4 text-yellow-400 animate-pulse" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/></svg>
            Unread Notices
            <div class="absolute right-0 top-0 h-full w-4 bg-gradient-to-r from-[#003D82] to-transparent translate-x-full"></div>
        </div>
        <div class="flex-1 overflow-hidden" data-notice-ticker>
            <div class="flex animate-ticker whitespace-nowrap py-2.5">
                <div class="ticker-content flex items-center gap-8 pl-6" data-notice-strip>
                    @foreach($publicTickerItems as $noticeItem)
                        <a href="{{ $noticeItem['href'] }}" data-notice-item data-notice-timestamp="{{ $noticeItem['timestamp'] }}" class="flex items-center gap-2 text-sm text-gray-200 hover:text-yellow-400 transition-colors flex-shrink-0">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $noticeItem['badge_class'] }}">{{ $noticeItem['source'] }}</span>
                            <span class="font-medium">{{ $noticeItem['title'] }}</span>
                            @if(!empty($noticeItem['date']))
                                <span class="text-[11px] text-gray-500">({{ $noticeItem['date'] }})</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ── MAIN CONTENT TOP (3 COLUMNS) ────────────────────────────── --}}
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-10 bg-[#f9f9f9] dark:bg-slate-900">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- LEFT COLUMN (Quick Links & Officials) --}}
        <div class="order-2 lg:order-none lg:col-span-3 space-y-6">
            {{-- Quick Links Card --}}
            <div class="dashboard-card hover:shadow-xl transition-shadow duration-300">
                <div class="bg-[#003D82] dark:bg-slate-700 text-white font-normal p-3.5 flex items-center gap-2 border-b-2 border-yellow-500 dark:border-yellow-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    Quick Links
                </div>
                <ul class="divide-y divide-gray-100 dark:divide-slate-700 p-1">
                    @foreach([
                        ['label' => 'MMP At A Glance', 'href' => route('public.page', 'what-is-mmp')],
                        ['label' => 'Courses & Programs', 'href' => route('public.departments')],
                        ['label' => 'Notice Board', 'href' => route('public.notices')],
                        ['label' => 'Downloads & Forms', 'href' => route('public.downloads')],
                        ['label' => 'Question Bank', 'href' => route('public.question-bank')],
                        ['label' => 'Campus Facilities', 'href' => route('public.facilities')],
                        ['label' => 'Scholarship Schemes', 'href' => route('public.page', 'scholarship-schemes')],
                        ['label' => 'Internships & Placements', 'href' => route('public.page', 'internships')],
                    ] as $link)
                    <li><a href="{{ $link['href'] }}" class="block px-4 py-2.5 text-gray-700 dark:text-slate-300 hover:text-[#003D82] dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-slate-700 text-[13px] transition-all duration-200 hover:translate-x-1"><span class="text-blue-500 dark:text-blue-400 font-bold mr-2">›</span> {{ $link['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- People/Officials (Dynamic) --}}
            <div class="dashboard-card text-sm hover:shadow-xl transition-shadow duration-300">
                <div class="bg-[#003D82] dark:bg-slate-700 text-white font-normal p-3.5 flex items-center gap-2 border-b-2 border-yellow-500 dark:border-yellow-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Managements
                </div>
                <div class="p-4 space-y-5">
                    @php
                        $currentPrincipal = isset($leadership['principals']) ? $leadership['principals']->firstWhere('is_current', true) : null;
                        $currentPresident = isset($leadership['presidents']) ? $leadership['presidents']->firstWhere('is_current', true) : null;
                    @endphp
                    @foreach(array_filter([$currentPresident, $currentPrincipal]) as $exec)
                    <div class="flex gap-4 items-center">
                        <div class="w-14 h-16 bg-gray-200 dark:bg-slate-700 border shadow-sm flex-shrink-0 overflow-hidden -ml-1">
                            @if(isset($exec->avatar_url) && $exec->avatar_url)
                                <img src="{{ $exec->avatar_url }}" class="w-full h-full object-cover" alt="{{ $exec->name }}">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-2xl font-black" style="background:#f3f4f6;color:#003D82;">{{ strtoupper(substr($exec->name ?? 'N',0,1)) }}</div>
                            @endif
                        </div>
                        <div>
                            <div class="font-bold text-[#003D82] dark:text-blue-400 text-[13px]">{{ $exec->name ?? 'N/A' }}</div>
                            <div class="text-[11px] text-gray-500 dark:text-slate-400 mt-0.5">{{ $exec->designation ?? ucfirst($exec->type ?? 'N/A') }}</div>
                        </div>
                    </div>
                    @endforeach
                    @if(empty(array_filter([$currentPresident ?? null, $currentPrincipal ?? null])))
                        <p class="text-xs text-gray-400 dark:text-slate-500 text-center py-2">Management details coming soon.</p>
                    @endif
                </div>
                <a href="{{ route('public.leadership') }}" class="card-footer-link">View All Presidents & Principals »</a>
            </div>
        </div>

        {{-- CENTER COLUMN (Welcome & Notice Tabs) --}}
        <div class="order-1 lg:order-none lg:col-span-6 space-y-6">
            {{-- Welcome Box --}}
            <div class="bg-[#003D82] dark:bg-slate-800 text-white p-8 text-center rounded-sm relative overflow-hidden shadow-sm border border-gray-200 dark:border-slate-700">
                <div class="absolute inset-0 opacity-10 bg-gradient-to-tr from-black to-transparent"></div>
                @php
                    $welcomeMessage = trim((string) optional($siteSettings->get('what_is_mmp'))->value ?? 'Manmohan Memorial Polytechnic (MMP) is a constituent college of Manmohan Technical University — the first technical university in Nepal.');
                    $welcomeParagraphs = collect(preg_split('/\n\s*\n/u', $welcomeMessage) ?: [])
                        ->map(fn ($paragraph) => trim((string) $paragraph))
                        ->filter()
                        ->values();
                @endphp
                <div class="relative z-10">
                    <h2 class="font-serif text-2xl font-semibold mb-3">Welcome to MMP</h2>
                    <div class="mx-auto max-w-3xl px-4">
                        <div class="max-h-[180px] md:max-h-[220px] overflow-y-auto pr-2 text-left">
                            <div class="space-y-3 text-[13px] leading-relaxed text-gray-100">
                                @forelse($welcomeParagraphs as $paragraph)
                                    <p>{{ $paragraph }}</p>
                                @empty
                                    <p>{{ $welcomeMessage }}</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('public.page', 'what-is-mmp') }}" class="inline-block border border-white text-white px-6 py-2 text-xs font-bold hover:bg-white hover:text-[#003D82] transition-colors uppercase tracking-wide">
                        About MMP
                    </a>
                </div>
            </div>

            {{-- Notice Board Tabs --}}
            @php
                $ctevtGeneralItems = collect($ctevtGeneralNotices['items'] ?? []);
                $ctevtResultItems = collect($ctevtResultNotices['items'] ?? []);
            @endphp
            <div class="dashboard-card flex flex-col min-h-[520px] h-[520px] hover:shadow-xl transition-shadow duration-300" x-data="{ activeNoticeTab: 'general', activeCtevtTab: 'general' }">
                <div class="flex">
                    <button type="button" @click="activeNoticeTab = 'general'" :class="activeNoticeTab === 'general' ? 'bg-[#003D82] text-white border-yellow-500' : 'bg-[#f5f5f5] dark:bg-slate-700 text-gray-700 dark:text-slate-300 border-transparent hover:bg-[#e9e9e9] dark:hover:bg-slate-600'" class="flex-1 py-3.5 font-semibold text-sm flex items-center justify-center gap-2 transition-colors border-t-[3px] relative">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Notice Board
                    </button>
                    <button type="button" @click="activeNoticeTab = 'exam'" :class="activeNoticeTab === 'exam' ? 'bg-[#003D82] text-white border-yellow-500' : 'bg-[#f5f5f5] dark:bg-slate-700 text-gray-700 dark:text-slate-300 border-transparent hover:bg-[#e9e9e9] dark:hover:bg-slate-600'" class="flex-1 py-3.5 font-semibold text-sm flex items-center justify-center gap-2 transition-colors border-t-[3px]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Exam Results
                    </button>
                    <button type="button" @click="activeNoticeTab = 'ctevt'" :class="activeNoticeTab === 'ctevt' ? 'bg-[#003D82] text-white border-yellow-500' : 'bg-[#f5f5f5] dark:bg-slate-700 text-gray-700 dark:text-slate-300 border-transparent hover:bg-[#e9e9e9] dark:hover:bg-slate-600'" class="flex-1 py-3.5 font-semibold text-sm flex items-center justify-center gap-2 transition-colors border-t-[3px]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a10 10 0 100 20 10 10 0 000-20z"/></svg>
                        CTEVT Notices
                    </button>
                </div>
                <div class="p-0 overflow-y-auto flex-1">
                    <ul class="divide-y divide-gray-100 dark:divide-slate-700" x-show="activeNoticeTab === 'general'" x-cloak>
                        @forelse(($notices ?? collect())->take(6) as $notice)
                        <li>
                            <a href="{{ route('public.notice.show', $notice->slug) }}" class="flex items-start gap-4 px-4 py-3 hover:bg-blue-50 dark:hover:bg-slate-700 group transition-colors">
                                @php $noticeDate = $notice->published_at ?? $notice->created_at; @endphp
                                <div class="flex-shrink-0 w-11 h-14 text-white flex flex-col items-center justify-center rounded text-center" style="background-color: #003D82;">
                                    <span class="text-[8px] font-bold leading-none">{{ bsDate($noticeDate, 'Y') }}</span>
                                    <span class="text-sm font-black leading-tight">{{ bsDate($noticeDate, 'd') }}</span>
                                    <span class="text-[7px] font-bold uppercase leading-none">{{ bsDate($noticeDate, 'F') }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[13px] text-gray-700 dark:text-slate-300 group-hover:text-[#003D82] dark:group-hover:text-blue-400 font-medium leading-snug pt-0.5">{{ $notice->title }}</div>
                                    <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                                        <span class="text-[9px] font-bold text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-1.5 py-0.5 rounded border border-blue-100 dark:border-blue-800 uppercase">
                                            {{ $notice->type === 'academic' ? 'ACADEMIC' : strtoupper($notice->type) }}
                                        </span>
                                        @if($notice->department)
                                            <span class="text-[9px] font-medium text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-1.5 py-0.5 rounded border border-emerald-100 dark:border-emerald-800">
                                                {{ $notice->department->name }}
                                            </span>
                                        @endif
                                        @if($notice->program)
                                            <span class="text-[9px] font-medium text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/30 px-1.5 py-0.5 rounded border border-green-100 dark:border-green-800">
                                                {{ $notice->program->name }}
                                            </span>
                                        @endif
                                        @if($notice->semester)
                                            <span class="text-[9px] font-medium text-purple-700 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/30 px-1.5 py-0.5 rounded border border-purple-100 dark:border-purple-800">
                                                Semester {{ $notice->semester }}
                                            </span>
                                        @endif
                                        <span class="text-[10px] text-gray-400 dark:text-slate-500">{{ bsDate($noticeDate, 'Y, F d') }}</span>
                                        @if($notice->attachment)
                                            <span class="text-[10px] text-blue-700 dark:text-blue-400 flex items-center gap-1 font-semibold">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                Attachment
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-gray-300 dark:text-slate-600 group-hover:text-[#003D82] dark:group-hover:text-blue-400"><svg class="w-4 h-4 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></div>
                            </a>
                        </li>
                        @empty
                        <li class="px-4 py-8 text-center text-gray-500 dark:text-slate-400 text-sm">No recent notices found.</li>
                        @endforelse
                    </ul>
                    <ul class="divide-y divide-gray-100 dark:divide-slate-700" x-show="activeNoticeTab === 'exam'" x-cloak>
                        @forelse(($examNotices ?? collect())->take(6) as $notice)
                        <li>
                            <a href="{{ route('public.notice.show', $notice->slug) }}" class="flex items-start gap-4 px-4 py-3 hover:bg-blue-50 dark:hover:bg-slate-700 group transition-colors">
                                @php $noticeDate = $notice->published_at ?? $notice->created_at; @endphp
                                <div class="flex-shrink-0 w-11 h-14 text-white flex flex-col items-center justify-center rounded text-center" style="background-color: #003D82;">
                                    <span class="text-[8px] font-bold leading-none">{{ bsDate($noticeDate, 'Y') }}</span>
                                    <span class="text-sm font-black leading-tight">{{ bsDate($noticeDate, 'd') }}</span>
                                    <span class="text-[7px] font-bold uppercase leading-none">{{ bsDate($noticeDate, 'F') }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[13px] text-gray-700 dark:text-slate-300 group-hover:text-[#003D82] dark:group-hover:text-blue-400 font-medium leading-snug pt-0.5">{{ $notice->title }}</div>
                                    <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                                        <span class="text-[9px] font-bold text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-1.5 py-0.5 rounded border border-blue-100 dark:border-blue-800 uppercase">EXAM</span>
                                        @if($notice->department)
                                            <span class="text-[9px] font-medium text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-1.5 py-0.5 rounded border border-emerald-100 dark:border-emerald-800">
                                                {{ $notice->department->name }}
                                            </span>
                                        @endif
                                        @if($notice->program)
                                            <span class="text-[9px] font-medium text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/30 px-1.5 py-0.5 rounded border border-green-100 dark:border-green-800">
                                                {{ $notice->program->name }}
                                            </span>
                                        @endif
                                        @if($notice->semester)
                                            <span class="text-[9px] font-medium text-purple-700 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/30 px-1.5 py-0.5 rounded border border-purple-100 dark:border-purple-800">
                                                Semester {{ $notice->semester }}
                                            </span>
                                        @endif
                                        <span class="text-[10px] text-gray-400 dark:text-slate-500">{{ bsDate($noticeDate, 'Y, F d') }}</span>
                                        @if($notice->attachment)
                                            <span class="text-[10px] text-blue-700 dark:text-blue-400 flex items-center gap-1 font-semibold">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                Attachment
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-gray-300 dark:text-slate-600 group-hover:text-[#003D82] dark:group-hover:text-blue-400"><svg class="w-4 h-4 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></div>
                            </a>
                        </li>
                        @empty
                        <li class="px-4 py-8 text-center text-gray-500 dark:text-slate-400 text-sm">No exam schedules or result notices found.</li>
                        @endforelse
                    </ul>
                    <div x-show="activeNoticeTab === 'ctevt'" x-cloak class="flex flex-col h-full">
                        <div class="flex border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800">
                            <button type="button" @click="activeCtevtTab = 'general'" :class="activeCtevtTab === 'general' ? 'bg-[#003D82] text-white' : 'bg-transparent text-gray-700 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-slate-700'" class="flex-1 py-3 text-xs md:text-sm font-bold transition-colors">
                                General Notices
                            </button>
                            <button type="button" @click="activeCtevtTab = 'result'" :class="activeCtevtTab === 'result' ? 'bg-[#003D82] text-white' : 'bg-transparent text-gray-700 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-slate-700'" class="flex-1 py-3 text-xs md:text-sm font-bold transition-colors">
                                Published Result
                            </button>
                        </div>

                        <ul class="divide-y divide-gray-100 dark:divide-slate-700 flex-1 overflow-y-auto" x-show="activeCtevtTab === 'general'" x-cloak>
                            @forelse($ctevtGeneralItems as $notice)
                                <li>
                                    <a href="{{ $notice['url'] ?? route('public.notices', ['type' => 'general']) }}" target="_blank" rel="noopener noreferrer" class="flex items-start gap-4 px-4 py-3 hover:bg-blue-50 dark:hover:bg-slate-700 group transition-colors">
                                        <div class="flex-shrink-0 w-11 h-11 text-white flex flex-col items-center justify-center rounded text-center" style="background-color: #003D82;">
                                            <span class="text-[8px] font-bold uppercase leading-none">CTEVT</span>
                                        </div>
                                        <div class="flex-1 text-[13px] text-gray-700 dark:text-slate-300 group-hover:text-[#003D82] dark:group-hover:text-blue-400 font-medium leading-snug pt-0.5">
                                            {{ $notice['title'] ?? 'Notice' }}
                                            <div class="mt-1 flex flex-wrap items-center gap-2 text-[10px] font-normal text-gray-400 dark:text-slate-500">
                                                @if(!empty($notice['updated_date']))
                                                    <span>{{ $notice['updated_date'] }}</span>
                                                @endif
                                                @if(!empty($notice['publisher']))
                                                    <span>• {{ $notice['publisher'] }}</span>
                                                @endif
                                                @if(!empty($notice['files_count']))
                                                    <span class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-400">{{ $notice['files_count'] }} file{{ $notice['files_count'] > 1 ? 's' : '' }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-gray-300 dark:text-slate-600 group-hover:text-[#003D82] dark:group-hover:text-blue-400"><svg class="w-4 h-4 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></div>
                                    </a>
                                </li>
                            @empty
                                <li class="px-4 py-8 text-center text-gray-500 dark:text-slate-400 text-sm">No live CTEVT general notices found.</li>
                            @endforelse
                        </ul>

                        <ul class="divide-y divide-gray-100 dark:divide-slate-700 flex-1 overflow-y-auto" x-show="activeCtevtTab === 'result'" x-cloak>
                            @forelse($ctevtResultItems as $notice)
                                <li>
                                    <a href="{{ $notice['url'] ?? route('public.notices', ['type' => 'exam']) }}" target="_blank" rel="noopener noreferrer" class="flex items-start gap-4 px-4 py-3 hover:bg-blue-50 dark:hover:bg-slate-700 group transition-colors">
                                        <div class="flex-shrink-0 w-11 h-11 text-white flex flex-col items-center justify-center rounded text-center" style="background-color: #003D82;">
                                            <span class="text-[8px] font-bold uppercase leading-none">CTEVT</span>
                                        </div>
                                        <div class="flex-1 text-[13px] text-gray-700 dark:text-slate-300 group-hover:text-[#003D82] dark:group-hover:text-blue-400 font-medium leading-snug pt-0.5">
                                            {{ $notice['title'] ?? 'Result Notice' }}
                                            <div class="mt-1 flex flex-wrap items-center gap-2 text-[10px] font-normal text-gray-400 dark:text-slate-500">
                                                @if(!empty($notice['updated_date']))
                                                    <span>{{ $notice['updated_date'] }}</span>
                                                @endif
                                                @if(!empty($notice['publisher']))
                                                    <span>• {{ $notice['publisher'] }}</span>
                                                @endif
                                                @if(!empty($notice['files_count']))
                                                    <span class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-400">{{ $notice['files_count'] }} file{{ $notice['files_count'] > 1 ? 's' : '' }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-gray-300 dark:text-slate-600 group-hover:text-[#003D82] dark:group-hover:text-blue-400"><svg class="w-4 h-4 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></div>
                                    </a>
                                </li>
                            @empty
                                <li class="px-4 py-8 text-center text-gray-500 dark:text-slate-400 text-sm">No live CTEVT result notices found.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
                <div class="px-0 pt-0 pb-0 mt-0 border-0 bg-transparent rounded-b-2xl">
                    <a x-show="activeNoticeTab === 'general'" x-cloak href="{{ route('public.notices') }}" class="card-footer-link">View All Notices »</a>
                    <a x-show="activeNoticeTab === 'exam'" x-cloak href="{{ route('public.notices') }}" class="card-footer-link">View All Notices »</a>
                    <div x-show="activeNoticeTab === 'ctevt'" x-cloak class="flex items-center gap-4 flex-wrap">
                        <a href="{{ route('public.notices', ['type' => 'ctevt-general']) }}" class="card-footer-link">View CTEVT General »</a>
                        <a href="{{ route('public.notices', ['type' => 'ctevt-result']) }}" class="card-footer-link">View CTEVT Results »</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN (News & Events - Dynamic) --}}
        <div class="order-3 lg:order-none lg:col-span-3 space-y-6 flex flex-col">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md overflow-hidden flex-1 flex flex-col hover:shadow-xl transition-shadow duration-300">
                <div class="bg-[#003D82] dark:bg-slate-700 text-white font-normal p-3.5 flex items-center gap-2 border-b-2 border-yellow-500 dark:border-yellow-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    News & Events
                </div>
                <div class="p-4 space-y-4 flex-1 overflow-y-auto">
                    @forelse(($newsEvents ?? collect())->take(5) as $event)
                        @php $eventDate = $event->published_at ?? $event->created_at; @endphp
                        <div class="flex gap-3 group">
                            <div class="w-12 h-12 flex-shrink-0 text-white flex flex-col items-center justify-center rounded text-center shadow-sm" style="background-color: #003D82;">
                                <span class="text-[8px] font-bold leading-none">{{ bsDate($eventDate, 'Y') }}</span>
                                <span class="text-sm font-black leading-tight">{{ bsDate($eventDate, 'd') }}</span>
                                <span class="text-[7px] font-bold uppercase leading-none">{{ bsDate($eventDate, 'F') }}</span>
                            </div>
                            <div class="flex-1 w-full overflow-hidden">
                                @php $eventTypeLabel = $event->type === 'event' ? 'Event' : 'News'; @endphp
                                <div class="flex items-center gap-2 mb-0.5 flex-wrap">
                                    <div class="text-[10px] font-bold text-gray-400 dark:text-slate-500">{{ bsDate($eventDate, 'Y, F d') }}</div>
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full border {{ $event->type === 'event' ? 'bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400 border-teal-100 dark:border-teal-800' : 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 border-purple-100 dark:border-purple-800' }}">
                                        {{ $eventTypeLabel }}
                                    </span>
                                </div>
                                <a href="{{ route('public.news-events.show', $event->slug) }}" class="font-medium text-gray-800 dark:text-slate-200 text-[12px] leading-tight hover:text-[#003D82] dark:hover:text-blue-400 block transition-colors line-clamp-2">{{ $event->title }}</a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-gray-400 dark:text-slate-500">
                            <svg class="w-10 h-10 mx-auto mb-2 text-gray-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <p class="text-xs">No news or events yet.</p>
                        </div>
                    @endforelse
                </div>
                <a href="{{ route('public.news-events') }}" class="block p-2.5 bg-[#003D82] dark:bg-blue-600 text-white text-xs font-bold text-left hover:bg-blue-900 dark:hover:bg-blue-700 transition-colors px-4">
                    View All News & Events »
                </a>
            </div>
        </div>

    </div>
</div>

{{-- ── PRINCIPAL'S MESSAGE ─────────────────────────────────────── --}}
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-12 bg-gray-50 dark:bg-slate-900 border-t border-gray-100 dark:border-slate-800 relative overflow-hidden">
    <div class="absolute top-0 right-0 opacity-[0.03] dark:opacity-[0.02] -translate-y-1/4 translate-x-1/4 pointer-events-none">
        <svg class="w-[300px] h-[300px]" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
    </div>

    <div class="flex justify-between items-center mb-8 pb-3 border-b border-gray-200 dark:border-slate-700">
        <h2 class="text-2xl font-bold font-serif text-[#003D82] dark:text-blue-400 border-l-[3px] border-[#003D82] dark:border-blue-400 pl-3 leading-none">
            Principal's Message
        </h2>
    </div>

    @php
        $currentPrincipal = $leadership['principals']->firstWhere('is_current', true);
        $principalMedia = trim((string) optional($siteSettings->get('principal_message_media'))->value);
        $mediaExt = $principalMedia ? strtolower(pathinfo($principalMedia, PATHINFO_EXTENSION)) : '';
        $isVideo = in_array($mediaExt, ['mp4', 'webm', 'mov']);
        $isImage = in_array($mediaExt, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        $isPdf   = $mediaExt === 'pdf';
    @endphp
    <div class="flex flex-col lg:flex-row gap-10 items-start">

        {{-- Photo: large, centered --}}
        <div class="w-full lg:w-64 flex-shrink-0 flex flex-col items-center">
            <div class="w-52 h-[260px] overflow-hidden bg-gray-200 border-4 border-white rounded-sm" style="box-shadow: 0 8px 32px rgba(139,0,0,0.18);">
                @if($currentPrincipal?->avatar)
                    <img src="{{ asset('storage/'.$currentPrincipal->avatar) }}" alt="Principal" class="w-full h-full object-cover object-top">
                @else
                    <div class="w-full h-full flex items-center justify-center text-7xl font-black" style="background:#f3f4f6;color:#003D82;">{{ strtoupper(substr($currentPrincipal?->name ?? 'P',0,1)) }}</div>
                @endif
            </div>
            <div class="mt-4 text-center">
                <div class="font-bold text-[#003D82] text-base">{{ $currentPrincipal?->name ?? 'Principal' }}</div>
                <div class="text-xs text-gray-500 font-medium mt-0.5">{{ $currentPrincipal?->designation ?? 'Principal, MMP' }}</div>
            </div>
        </div>

        {{-- Message text --}}
        <div class="flex-1 min-w-0">
           

            {{-- Media attachment (video / image / PDF) - shown FIRST --}}
            @if($principalMedia)
                <div class="mb-6 rounded-lg overflow-hidden border border-gray-200 shadow-sm">
                    @if($isVideo)
                        <video controls class="w-full max-h-72 bg-black" preload="metadata">
                            <source src="{{ asset('storage/' . $principalMedia) }}" type="{{ $mediaExt === 'webm' ? 'video/webm' : 'video/mp4' }}">
                        </video>
                    @elseif($isImage)
                        <img src="{{ asset('storage/' . $principalMedia) }}" alt="Principal's message media" class="w-full max-h-72 object-contain bg-gray-50">
                    @elseif($isPdf)
                        <div class="bg-gray-50 p-4 flex items-center gap-4">
                            <div class="w-12 h-14 bg-blue-100 border border-blue-200 rounded flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800">PDF Document</p>
                                <p class="text-xs text-gray-500 truncate">{{ basename($principalMedia) }}</p>
                            </div>
                            <a href="{{ asset('storage/' . $principalMedia) }}" target="_blank" class="flex-shrink-0 flex items-center gap-1.5 px-4 py-2 text-sm font-bold text-white rounded transition-colors" style="background:#003D82;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                Open PDF
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            <div class="text-gray-700 dark:text-slate-300 text-[15px] leading-[2] text-justify space-y-4">
                @if($currentPrincipal?->message)
                    @foreach(array_filter(explode("\n\n", $currentPrincipal->message)) as $para)
                        <p>{{ trim($para) }}</p>
                    @endforeach
                @else
                    <p>It is with immense pleasure that I welcome you to Manmohan Memorial Polytechnic. Here at MMP, we are confident that you will experience an enriching academic journey coupled with robust technical skill enhancement.</p>
                    <p>We provide a vibrant learning environment that ensures our students gain hands-on practical knowledge that satisfies the needs of modern industries, preparing them for national and international career opportunities.</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── DIPLOMA PROGRAMS (GRID) ───────────────────────────────── --}}
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-12 bg-white dark:bg-slate-950 border-t border-[#f9f9f9] dark:border-slate-800">
    <div class="flex justify-between items-center mb-8 pb-3 border-b border-gray-100 dark:border-slate-800">
        <h2 class="text-2xl font-bold font-serif text-[#003D82] dark:text-blue-400 border-l-[3px] border-[#003D82] dark:border-blue-400 pl-3 leading-none">
            Our Diploma Programs
        </h2>
        <a href="{{ route('public.departments') }}" class="text-xs font-bold text-gray-500 dark:text-slate-400 hover:text-[#003D82] dark:hover:text-blue-400 flex items-center gap-1 border border-gray-200 dark:border-slate-700 px-3 py-1.5 rounded-sm hover:border-[#003D82] dark:hover:border-blue-400 transition-colors">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            VIEW ALL
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-5">
        @php
            $programIcons = [
                'Information Technology' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
                'Civil Engineering' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
                'Electrical Engineering' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>',
                'Mechanical Engineering' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                'Electronics Engineering' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>',
                'Architecture Engineering' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
            ];
            $programData = $departments->take(6);
            if ($programData->isEmpty()) {
                $programData = collect(array_keys($programIcons))->map(fn($n) => (object)['name'=>$n, 'slug'=>\Illuminate\Support\Str::slug($n)]);
            }
        @endphp

        @foreach($programData as $prog)
            <a href="{{ route('public.department.show', $prog->slug) }}" class="program-card group rounded-2xl shadow-md p-6 text-center flex flex-col items-center transition-all duration-300 h-full hover:shadow-2xl hover:-translate-y-1 hover:scale-[1.02] bg-white dark:bg-slate-800 dark:text-slate-200">
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

{{-- ── STATISTICS BANNER ───────────────────────────────────────── --}}
<div class="bg-[#003D82] dark:bg-slate-900 text-white py-14 shadow-inner relative overflow-hidden">
    <div class="absolute inset-0 bg-black/10 dark:bg-black/20"></div>
    <div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto relative z-10">
        @php $s = $stats ?? []; @endphp
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 md:gap-6 text-center">
            @foreach([
                ['icon' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342"/></svg>', 'value' => number_format($s['graduates'] ?? 0) . '+', 'label' => 'Graduates'],
                ['icon' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>', 'value' => number_format($s['students'] ?? 0) . '+', 'label' => 'Current Students'],
                ['icon' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z"/></svg>', 'value' => number_format($s['faculty_staff'] ?? 0) . '+', 'label' => 'Faculty & Staff'],
                ['icon' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>', 'value' => $s['programs'] ?? 0, 'label' => 'Diploma Programs'],
                ['icon' => '<svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0016.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.003 6.003 0 01-3.77 1.522m0 0a6.003 6.003 0 01-3.77-1.522"/></svg>', 'value' => ($s['years'] ?? 0) . '+', 'label' => 'Years of Excellence'],
            ] as $stat)
                <div class="px-2">
                    <div class="w-14 h-14 mx-auto border-2 border-blue-400/30 rounded-full flex items-center justify-center mb-3 bg-blue-900/40 text-yellow-400">
                        {!! $stat['icon'] !!}
                    </div>
                    <div class="text-2xl lg:text-3xl font-black mb-1 drop-shadow">{{ $stat['value'] }}</div>
                    <div class="text-[10px] sm:text-xs font-bold text-blue-100 uppercase tracking-widest">{{ $stat['label'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── THREE COLUMN BOTTOM GRID ────────────────────────────────── --}}
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-10 bg-[#f9f9f9] dark:bg-slate-900">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Downloads & Publications (Dynamic) --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md overflow-hidden text-sm flex flex-col hover:shadow-xl transition-shadow duration-300">
            <div class="bg-[#003D82] dark:bg-slate-700 text-white font-normal p-3.5 flex items-center gap-2 border-b-2 border-yellow-500 dark:border-yellow-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Downloads & Publications
            </div>
            <div class="p-5 space-y-4 flex-1">
                @forelse(($recentDownloads ?? collect())->take(4) as $dl)
                    <div class="flex gap-3 items-start {{ !$loop->last ? 'border-b border-gray-100 dark:border-slate-700 pb-4' : '' }}">
                        <div class="w-10 h-10 bg-blue-50 dark:bg-slate-700 border border-blue-100 dark:border-slate-600 flex items-center justify-center text-[#003D82] dark:text-blue-400 rounded flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-[13px] text-[#003D82] dark:text-blue-400 truncate">{{ $dl->title }}</div>
                            <div class="text-[11px] text-gray-400 dark:text-slate-500 mt-0.5">{{ bsDate($dl->created_at, 'Y, F d') }}{{ $dl->category ? ' · '.ucfirst(str_replace('-', ' ', $dl->category)) : '' }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-gray-400 dark:text-slate-500">
                        <svg class="w-10 h-10 mx-auto mb-2 text-gray-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        <p class="text-xs">Downloads coming soon.</p>
                    </div>
                @endforelse
            </div>
            <div class="p-0">
                <a href="{{ route('public.downloads') }}" class="card-footer-link">All Downloads & Publications »</a>
            </div>
        </div>

        {{-- Important Links --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md overflow-hidden text-sm flex flex-col hover:shadow-xl transition-shadow duration-300">
            <div class="bg-[#003D82] dark:bg-slate-700 text-white font-normal p-3.5 flex items-center gap-2 border-b-2 border-yellow-500 dark:border-yellow-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                Important Links
            </div>
            <ul class="divide-y divide-gray-100 dark:divide-slate-700 p-1 flex-1">
                @foreach([
                    'CTEVT' => 'https://ctevt.org.np',
                    'Manmohan Technical University' => 'https://mtu.edu.np',
                    'National Skills Testing Board' => 'https://nstb.org.np',
                    'Ministry of Education, Science & Technology' => 'https://moest.gov.np',
                    'Department of Education, Nepal' => 'https://doe.gov.np',
                ] as $label => $link)
                <li>
                    <a href="{{ $link }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 px-4 py-2.5 text-[13px] text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700 hover:text-[#003D82] dark:hover:text-blue-400 transition-colors group">
                        <svg class="w-3.5 h-3.5 text-blue-500 dark:text-blue-400 group-hover:translate-x-0.5 transition-transform flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        {{ $label }}
                    </a>
                </li>
                @endforeach
            </ul>
        </div>

        {{-- Why Choose MMP? --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md overflow-hidden text-sm flex flex-col hover:shadow-xl transition-shadow duration-300">
            <div class="bg-[#003D82] dark:bg-slate-700 text-white font-normal p-3.5 flex items-center gap-2 border-b-2 border-yellow-500 dark:border-yellow-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                Why Choose MMP?
            </div>
            <div class="p-5 space-y-4 flex-1">
                @foreach([
                    ['title' => 'CTEVT Affiliated', 'desc' => 'Government recognized technical education.'],
                    ['title' => 'Modern Labs & Workshops', 'desc' => 'Hands-on practical learning environment.'],
                    ['title' => 'Industry Placements', 'desc' => 'Internship and job placement support.'],
                    ['title' => 'Scholarship Programs', 'desc' => 'Merit and need-based financial support.'],
                    ['title' => 'Part of MTU', 'desc' => 'Constituent college of Nepal\'s first technical university.'],
                ] as $feature)
                    <div class="flex gap-3 items-start {{ !$loop->last ? 'border-b border-gray-50 dark:border-slate-700 pb-3' : '' }}">
                        <div class="w-5 h-5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <div class="font-bold text-[13px] text-gray-800 dark:text-slate-200">{{ $feature['title'] }}</div>
                            <div class="text-[11px] text-gray-500 dark:text-slate-400 mt-0.5">{{ $feature['desc'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="p-0">
                <a href="{{ route('public.facilities') }}" class="card-footer-link">Explore Facilities »</a>
            </div>
        </div>

    </div>
</div>

{{-- ── FIND US / MAP ───────────────────────────────────────────── --}}
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto pt-10 pb-16 bg-white dark:bg-slate-950 border-t border-gray-100 dark:border-slate-800">
    <div class="flex justify-between items-center mb-6 pb-2 border-b border-gray-100 dark:border-slate-800">
        <h2 class="text-2xl font-bold font-serif text-[#003D82] dark:text-blue-400 border-l-[3px] border-[#003D82] dark:border-blue-400 pl-3 leading-none">
            Find Us
        </h2>
        <a href="{{ route('public.page', 'contact-us') }}" class="text-xs font-bold text-gray-500 dark:text-slate-400 hover:text-[#003D82] dark:hover:text-blue-400 flex items-center gap-1 border border-gray-200 dark:border-slate-700 px-3 py-1.5 rounded-sm hover:border-[#003D82] dark:hover:border-blue-400 transition-colors">
            Contact Us
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-0 bg-[#f9f9f9] dark:bg-slate-900 shadow-md rounded-2xl overflow-hidden hover:shadow-xl transition-shadow duration-300">
        <div class="lg:col-span-2 h-[350px] relative">
            @php
                $googleMapsIframe = optional($siteSettings->get('google_maps_iframe'))->value;
            @endphp
            @if($googleMapsIframe && str_contains($googleMapsIframe, 'iframe'))
                <div class="w-full h-full [&>iframe]:w-full [&>iframe]:h-full [&>iframe]:border-0">
                    {!! $googleMapsIframe !!}
                </div>
            @else
                <iframe class="w-full h-full" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=en&amp;q=Manmohan%20Memorial%20Polytechnic+(Manmohan%20Memorial%20Polytechnic)&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed" loading="lazy"></iframe>
            @endif
        </div>
        <div class="lg:col-span-1 p-6 md:p-8 bg-[#f9f9f9] dark:bg-slate-900">
            <h3 class="font-semibold text-[#003D82] dark:text-blue-400 text-[15px] mb-5 border-b border-blue-200 dark:border-slate-700 pb-2">Contact Information</h3>
            <ul class="space-y-4 text-[13px] text-gray-700 dark:text-slate-300 font-medium">
                @if($contactAddress = optional($siteSettings->get('contact_address'))->value)
                <li class="flex gap-3">
                    <svg class="w-4 h-4 text-[#003D82] dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <div>{{ $contactAddress }}</div>
                </li>
                @endif
                @if($contactPhone = optional($siteSettings->get('contact_phone'))->value)
                <li class="flex gap-3">
                    <svg class="w-4 h-4 text-[#003D82] dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <div>{{ $contactPhone }}</div>
                </li>
                @endif
                @if($contactEmail = optional($siteSettings->get('contact_email'))->value)
                <li class="flex gap-3">
                    <svg class="w-4 h-4 text-[#003D82] dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <div>{{ $contactEmail }}</div>
                </li>
                @endif
                @if($website = optional($siteSettings->get('website_url'))->value)
                <li class="flex gap-3">
                    <svg class="w-4 h-4 text-[#003D82] dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                    <div>{{ $website }}</div>
                </li>
                @endif
            </ul>

            @if($affiliation = optional($siteSettings->get('affiliation_text'))->value)
            <h3 class="font-semibold text-[#003D82] dark:text-blue-400 text-[15px] mt-8 mb-5 border-b border-blue-200 dark:border-slate-700 pb-2">Affiliated Under</h3>
            <ul class="space-y-3 text-[13px] text-gray-700 dark:text-slate-300 font-medium">
                <li class="flex gap-3">
                    <svg class="w-4 h-4 text-[#003D82] dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <div>{!! nl2br(e($affiliation)) !!}</div>
                </li>
            </ul>
            @endif
        </div>
    </div>
</div>

</div>

@endsection

@push('styles')
<style>
    @keyframes ticker {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    .animate-ticker {
        animation: ticker 40s linear infinite;
    }
    .animate-ticker:hover {
        animation-play-state: paused;
    }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const banner = document.querySelector('[data-notice-banner]');
        const ticker = document.querySelector('[data-notice-ticker]');
        const storageKey = 'mmp:notice-ticker-last-seen';

        if (!banner || !ticker || !window.localStorage) {
            return;
        }

        const strip = ticker.querySelector('[data-notice-strip]');

        if (!strip) {
            return;
        }

        let lastSeen = Number(localStorage.getItem(storageKey) || '0');
        if (!Number.isFinite(lastSeen) || lastSeen < 0) {
            lastSeen = 0;
        }

        const items = Array.from(strip.querySelectorAll('[data-notice-item]'));
        let visibleCount = 0;

        items.forEach((item) => {
            const publishedAt = Number(item.dataset.noticeTimestamp || '0');

            if (!publishedAt || publishedAt <= lastSeen) {
                item.remove();
                return;
            }

            visibleCount += 1;
        });

        if (visibleCount === 0) {
            banner.classList.add('hidden');
            return;
        }

        const clone = strip.cloneNode(true);
        strip.parentNode.appendChild(clone);

        try {
            localStorage.setItem(storageKey, String(Date.now()));
        } catch (error) {
            // Ignore storage errors; unread filtering is best-effort.
        }
    })();
</script>
@endpush

