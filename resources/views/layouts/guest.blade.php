<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Manmohan Memorial Polytechnic') | Best Technical College — Koshi Province, Nepal</title>
    <meta name="description" content="@yield('meta_description', 'Manmohan Memorial Polytechnic (MMP) — Best Technical College in Koshi Province, Nepal. CTEVT Diploma programs in IT, Civil, Electrical, Mechanical & Electronics Engineering.')">
    <meta name="keywords" content="Manmohan Memorial Polytechnic, MMP, technical college Nepal, diploma engineering, CTEVT, Koshi Province, Morang">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'Manmohan Memorial Polytechnic')">
    <meta property="og:description" content="@yield('meta_description', 'Best Technical College in Koshi Province offering CTEVT diploma programs.')">
    <meta property="og:type" content="website">
    <link rel="manifest" href="{{ asset('manifest.json') }}?v=2">
    <meta name="application-name" content="Manmohan Memorial Polytechnic">
    <meta name="apple-mobile-web-app-title" content="Manmohan Memorial Polytechnic">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="#8B0000">
    <link rel="icon" href="{{ route('public.brand-logo') }}">
    <link rel="shortcut icon" href="{{ route('public.brand-logo') }}">
    <link rel="apple-touch-icon" href="{{ route('public.brand-logo') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Merriweather:wght@400;700;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --red-primary: #8B0000;
            --red-bright: #B91C1C;
            --red-light: #FEF2F2;
            --gold: #EAB308;
        }
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Merriweather', serif; }
        .bg-primary { background-color: var(--red-primary); }
        .text-primary { color: var(--red-primary); }
        .border-primary { border-color: var(--red-primary); }
        .section-header {
            background-color: var(--red-primary);
            color: white;
            padding: 0.5rem 1rem;
            font-family: 'Merriweather', serif;
            font-weight: 700;
            font-size: 0.95rem;
        }
    </style>
    @stack('styles')
</head>
<body class="antialiased bg-gray-100 text-gray-900 overflow-x-hidden" x-data="{ mobileOpen: false }">
    @php $brandLogoUrl = route('public.brand-logo'); @endphp

    @php
        $courseMenu = collect($publicCourses ?? []);
    @endphp

    {{-- ── TOP INFO BAR (Red, matching mmp.edu.np) ─────────────── --}}
    <div style="background-color: #8B0000;" class="text-white text-xs py-1.5 hidden md:block">
        <div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto flex justify-between items-center">
            <div class="flex items-center gap-5">
                <span class="flex items-center gap-1.5">
                    <svg class="w-3 h-3 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Budhiganga-4, Morang, Koshi Province, Nepal
                </span>
                <span class="text-red-400">|</span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-3 h-3 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    +977 21 590696, +977 21 590697
                </span>
            </div>
            <div class="flex items-center gap-4">
                <a href="mailto:info@mmp.edu.np" class="flex items-center gap-1.5 hover:text-yellow-400 transition-colors">
                    <svg class="w-3 h-3 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    info@mmp.edu.np
                </a>
                <a href="{{ route('login') }}" class="bg-yellow-500 hover:bg-yellow-400 text-gray-900 font-bold px-4 py-1 rounded text-[11px] transition-colors uppercase tracking-wide">
                    Web Mail Login
                </a>
            </div>
        </div>
    </div>

    {{-- ── LOGO BAR (White, matching mmp.edu.np) ──────────────── --}}
    @unless(request()->routeIs('home'))
    {{-- slim spacer for non-home pages --}}
    @else
    <div class="bg-white py-2.5 md:py-3 border-b border-gray-200 shadow-sm">
        <div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto flex items-center justify-between gap-3">
            <a href="{{ route('home') }}" class="flex min-w-0 flex-1 items-center gap-3">
                {{-- MMP Seal/Emblem --}}
                <div class="w-11 h-11 md:w-14 md:h-14 flex-shrink-0 rounded-full flex items-center justify-center" style="background: radial-gradient(circle, #8B0000, #5B0000); border: 2px solid #DAA520;">
                    @if($brandLogoUrl)
                        <img src="{{ $brandLogoUrl }}" alt="MMP Logo" class="w-full h-full object-cover rounded-full">
                    @else
                        <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    @endif
                </div>
                <div class="min-w-0 leading-tight">
                    <div class="text-base sm:text-xl font-black font-serif leading-tight text-[#8B0000] line-clamp-1">Manmohan Memorial Polytechnic</div>
                    <div class="text-[11px] sm:text-sm italic font-semibold text-[#DAA520] line-clamp-1">Best Technical College in Koshi Province</div>
                    <div class="hidden sm:block text-xs text-gray-500 font-medium">A Constituent College of Manmohan Technical University</div>
                    <div class="sm:hidden text-[10px] font-semibold uppercase tracking-[0.18em] text-gray-500">mmp.edu.np</div>
                </div>
            </a>

            <button type="button" id="mobile-install-trigger" class="inline-flex md:hidden shrink-0 items-center gap-1.5 rounded-full border border-[#8B0000]/15 bg-[#8B0000]/5 px-3 py-2 text-[11px] font-bold uppercase tracking-wide text-[#8B0000] shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download
            </button>
        </div>
    </div>
    @endunless

    {{-- ── MAIN NAVIGATION (Deep Red) ──────────────────────────── --}}
    <nav style="background-color: #8B0000;" class="sticky top-0 z-50 shadow-md" x-data="{ mobileOpen: false }">
        <div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto">
            <div class="flex items-center justify-between">

                {{-- Desktop Nav Links --}}
                <div class="hidden xl:flex items-center flex-1">
                    {{-- Active state uses pb-1, border-b-[4px] for the thick white underline --}}
                    <a href="{{ route('home') }}" class="text-white text-sm font-bold uppercase px-3 py-3.5 hover:bg-white/10 transition-colors border-b-4 {{ request()->routeIs('home') ? 'border-white bg-white/10' : 'border-transparent hover:border-white' }}">HOME</a>

                    @foreach([
                        ['label' => 'ABOUT US', 'items' => [
                            ['href' => route('public.page', 'what-is-mmp'), 'label' => 'What is MMP'],
                            ['href' => route('public.page', 'objectives'), 'label' => 'Objectives'],
                            ['href' => route('public.leadership'), 'label' => 'Presidents & Principals'],
                            ['href' => route('public.contact'), 'label' => 'Contact Us'],
                        ]],
                        ['label' => 'DEPARTMENTS', 'items' => []],
                        ['label' => 'FEATURES', 'items' => [
                            ['href' => route('public.facilities'), 'label' => 'Campus Facilities & Resources'],
                            ['href' => route('public.page', 'scholarship-schemes'), 'label' => 'Scholarship Schemes'],
                            ['href' => route('public.page', 'internships'), 'label' => 'Internships & Placements'],
                        ]],
                        ['label' => 'PEOPLE', 'items' => [
                            ['href' => route('public.staff'), 'label' => 'Administrative Staff'],
                            ['href' => route('public.leadership'), 'label' => 'Presidents & Principals'],
                        ]],
                    ] as $menu)
                        <div class="relative group" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                            <button class="text-white text-sm font-bold uppercase px-3 py-3.5 hover:bg-white/10 transition-colors border-b-4 border-transparent hover:border-white flex items-center gap-1">
                                {{ $menu['label'] }}
                                <svg class="w-3 h-3 ml-0.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak
                                class="absolute top-full left-0 mt-0 {{ $menu['label'] === 'PEOPLE' ? 'w-80 max-h-96 overflow-y-auto' : 'w-64' }} bg-[#404040] py-2 z-50 shadow-xl border-t-2 border-white">
                                @if($menu['label'] === 'DEPARTMENTS')
                                    @forelse($courseMenu as $course)
                                        <a href="{{ route('public.department.show', $course->slug) }}" class="block px-5 py-2.5 text-[13px] text-gray-200 hover:text-white hover:bg-white/10 transition-colors font-medium border-b border-white/5 last:border-0">
                                            {{ $course->name }}
                                        </a>
                                    @empty
                                        <span class="block px-5 py-2.5 text-[13px] text-gray-400">No departments available</span>
                                    @endforelse
                                    <div class="my-1 border-t border-white/10"></div>
                                    <a href="{{ route('public.departments') }}" class="block px-5 py-2.5 text-[13px] text-yellow-300 hover:text-white hover:bg-white/10 transition-colors font-bold border-b border-white/5 last:border-0">
                                        All Departments →
                                    </a>
                                @elseif($menu['label'] === 'PEOPLE')
                                    <div class="px-5 py-2 text-[11px] uppercase tracking-[0.18em] text-gray-400 border-b border-white/5">
                                        Departments
                                    </div>
                                    @forelse($courseMenu as $department)
                                        <a href="{{ route('public.people', ['department' => $department->slug]) }}" class="block px-5 py-2.5 text-[13px] text-gray-200 hover:text-white hover:bg-white/10 transition-colors font-medium border-b border-white/5 last:border-0">
                                            {{ $department->name }}
                                        </a>
                                    @empty
                                        <span class="block px-5 py-2.5 text-[13px] text-gray-400 border-b border-white/5">No departments available</span>
                                    @endforelse
                                    <div class="my-1 border-t border-white/10"></div>
                                    @foreach($menu['items'] as $item)
                                        <a href="{{ $item['href'] }}" class="block px-5 py-2.5 text-[13px] text-gray-200 hover:text-white hover:bg-white/10 transition-colors font-medium border-b border-white/5 last:border-0">
                                            {{ $item['label'] }}
                                        </a>
                                    @endforeach
                                @else
                                    @foreach($menu['items'] as $item)
                                        <a href="{{ $item['href'] }}" class="block px-5 py-2.5 text-[13px] text-gray-200 hover:text-white hover:bg-white/10 transition-colors font-medium border-b border-white/5 last:border-0">
                                            {{ $item['label'] }}
                                        </a>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <a href="{{ route('public.news-events') }}" class="text-white text-sm font-bold uppercase px-3 py-3.5 hover:bg-white/10 transition-colors border-b-4 {{ request()->routeIs('public.news-events') ? 'border-white bg-white/10' : 'border-transparent hover:border-white' }}">NEWS & EVENTS</a>
                    <a href="{{ route('public.gallery') }}" class="text-white text-sm font-bold uppercase px-3 py-3.5 hover:bg-white/10 transition-colors border-b-4 {{ request()->routeIs('public.gallery') ? 'border-white bg-white/10' : 'border-transparent hover:border-white' }}">GALLERY</a>
                    <a href="{{ route('public.alumni') }}" class="text-white text-sm font-bold uppercase px-3 py-3.5 hover:bg-white/10 transition-colors border-b-4 {{ request()->routeIs('public.alumni') ? 'border-white bg-white/10' : 'border-transparent hover:border-white' }}">ALUMNI</a>
                    <a href="{{ route('public.result') }}" class="text-white text-sm font-bold uppercase px-3 py-3.5 hover:bg-white/10 transition-colors border-b-4 {{ request()->routeIs('public.result') ? 'border-white bg-white/10' : 'border-transparent hover:border-white' }}">RESULT</a>
                    
                    @php
                        $resourceLinks = [
                            ['label' => 'All Resources', 'href' => route('public.downloads')],
                            ['label' => 'Forms & Downloads', 'href' => route('public.downloads', ['category' => 'forms'])],
                            ['label' => 'Syllabus', 'href' => route('public.downloads', ['category' => 'syllabus'])],
                            ['label' => 'Notes', 'href' => route('public.downloads', ['category' => 'notes'])],
                            ['label' => 'Question Bank', 'href' => route('public.question-bank')],
                            ['label' => 'Reports & Publications', 'href' => route('public.downloads', ['category' => 'reports'])],
                        ];
                    @endphp

                    {{-- RESOURCES Dropdown --}}
                    <div class="relative group" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button class="text-white text-sm font-bold uppercase px-3 py-3.5 hover:bg-white/10 transition-colors border-b-4 border-transparent hover:border-white flex items-center gap-1">
                            RESOURCES
                            <svg class="w-3 h-3 ml-0.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak
                            class="absolute top-full left-0 mt-0 w-72 max-h-96 overflow-y-auto bg-[#404040] py-2 z-50 shadow-xl border-t-2 border-white">
                            @foreach($resourceLinks as $resource)
                                <a href="{{ $resource['href'] }}" class="block px-5 py-2.5 text-[13px] text-gray-200 hover:text-white hover:bg-white/10 transition-colors font-medium border-b border-white/5 last:border-0">{{ $resource['label'] }}</a>
                            @endforeach
                        </div>
                    </div>

                    <a href="{{ route('public.apply') }}" class="ml-2 inline-flex items-center gap-1.5 rounded-sm bg-[#d35400] px-4 py-3.5 text-sm font-bold uppercase text-white shadow-md transition-colors hover:bg-[#e67e22]">
                        Apply Now
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>

                {{-- Phone Numbers exactly on the right side --}}
                <div class="hidden xl:flex flex-col items-end opacity-60 text-right pr-2">
                    <div class="text-[11px] font-bold text-white tracking-widest leading-tight hover:opacity-100 transition-opacity cursor-pointer">021-590696</div>
                    <div class="text-[11px] font-bold text-white tracking-widest leading-tight hover:opacity-100 transition-opacity cursor-pointer">021-590697</div>
                </div>

                {{-- Mobile Toggle --}}
                <button @click="mobileOpen = !mobileOpen" class="xl:hidden text-white p-3 hover:bg-white/10 transition-colors h-14 flex items-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>

        {{-- Mobile Nav (Full structure) --}}
        <div x-show="mobileOpen" x-cloak class="xl:hidden bg-[#333333] border-t border-white/10 text-white max-h-[80vh] overflow-y-auto">
            <div class="px-0 py-0 divide-y divide-white/10 text-sm font-bold uppercase tracking-wider">
                <a href="{{ route('home') }}" class="block px-5 py-4 hover:bg-white/5 transition-colors {{ request()->routeIs('home') ? 'bg-white/10 border-l-4 border-white' : 'border-l-4 border-transparent' }}">Home</a>

                <a href="{{ route('public.apply') }}" class="block px-5 py-4 bg-[#d35400] text-white hover:bg-[#e67e22] transition-colors border-l-4 border-[#f1b27a]">
                    Apply Now
                </a>
                
                <div x-data="{ subOpen: false }" class="border-l-4 border-transparent">
                    <button @click="subOpen = !subOpen" class="w-full flex items-center justify-between px-5 py-4 hover:bg-white/5 transition-colors">
                        ABOUT US <svg class="w-4 h-4 transition-transform z-10" :class="subOpen ? 'rotate-180':''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="subOpen" class="bg-[#222222] pl-8 pr-4 py-2 space-y-3 font-medium text-[12px] text-gray-300">
                        <a href="{{ route('public.page', 'what-is-mmp') }}" class="block hover:text-white">What is MMP</a>
                        <a href="{{ route('public.page', 'objectives') }}" class="block hover:text-white">Objectives</a>
                        <a href="{{ route('public.contact') }}" class="block hover:text-white">Contact Us</a>
                    </div>
                </div>

                <div x-data="{ subOpen: false }" class="border-l-4 border-transparent">
                    <button @click="subOpen = !subOpen" class="w-full flex items-center justify-between px-5 py-4 hover:bg-white/5 transition-colors">
                        DEPARTMENTS <svg class="w-4 h-4 transition-transform z-10" :class="subOpen ? 'rotate-180':''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="subOpen" class="bg-[#222222] pl-8 pr-4 py-2 space-y-3 font-medium text-[12px] text-gray-300">
                        @forelse($courseMenu as $course)
                            <a href="{{ route('public.department.show', $course->slug) }}" class="block hover:text-white">{{ $course->name }}</a>
                        @empty
                            <span class="block text-gray-500">No departments available</span>
                        @endforelse
                        <div class="pt-2 border-t border-white/10">
                            <a href="{{ route('public.departments') }}" class="block hover:text-yellow-300 text-yellow-400 font-bold">All Departments →</a>
                        </div>
                    </div>
                </div>

                <div x-data="{ subOpen: false }" class="border-l-4 border-transparent">
                    <button @click="subOpen = !subOpen" class="w-full flex items-center justify-between px-5 py-4 hover:bg-white/5 transition-colors">
                        PEOPLE <svg class="w-4 h-4 transition-transform z-10" :class="subOpen ? 'rotate-180':''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="subOpen" class="bg-[#222222] pl-8 pr-4 py-2 space-y-3 font-medium text-[12px] text-gray-300">
                        <div class="pt-1 pb-2 text-[11px] uppercase tracking-[0.18em] text-gray-500">Departments</div>
                        @forelse($courseMenu as $department)
                            <a href="{{ route('public.people', ['department' => $department->slug]) }}" class="block hover:text-white">{{ $department->name }}</a>
                        @empty
                            <span class="block text-gray-500">No departments available</span>
                        @endforelse
                        <div class="pt-2 border-t border-white/10 space-y-3">
                            <a href="{{ route('public.staff') }}" class="block hover:text-white">Administrative Staff</a>
                            <a href="{{ route('public.leadership') }}" class="block hover:text-white">Presidents & Principals</a>
                        </div>
                    </div>
                </div>
                
                <a href="{{ route('public.news-events') }}" class="block px-5 py-4 hover:bg-white/5 transition-colors border-l-4 {{ request()->routeIs('public.news-events') ? 'border-white bg-white/10' : 'border-transparent' }}">NEWS & EVENTS</a>
                <a href="{{ route('public.gallery') }}" class="block px-5 py-4 hover:bg-white/5 transition-colors border-l-4 {{ request()->routeIs('public.gallery') ? 'border-white bg-white/10' : 'border-transparent' }}">GALLERY</a>
                <a href="{{ route('public.alumni') }}" class="block px-5 py-4 hover:bg-white/5 transition-colors border-l-4 {{ request()->routeIs('public.alumni') ? 'border-white bg-white/10' : 'border-transparent' }}">ALUMNI</a>
                <a href="{{ route('public.result') }}" class="block px-5 py-4 hover:bg-white/5 transition-colors border-l-4 {{ request()->routeIs('public.result') ? 'border-white bg-white/10' : 'border-transparent' }}">RESULT</a>

                <div x-data="{ subOpen: false }" class="border-l-4 border-transparent">
                    <button @click="subOpen = !subOpen" class="w-full flex items-center justify-between px-5 py-4 hover:bg-white/5 transition-colors">
                        RESOURCES <svg class="w-4 h-4 transition-transform z-10" :class="subOpen ? 'rotate-180':''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="subOpen" class="bg-[#222222] pl-8 pr-4 py-2 space-y-3 font-medium text-[12px] text-gray-300">
                        @foreach($resourceLinks as $resource)
                            <a href="{{ $resource['href'] }}" class="block hover:text-white">{{ $resource['label'] }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </nav>

    {{-- Page Breadcrumb (shown on inner pages) --}}
    @hasSection('no_breadcrumb')
    @else
        @hasSection('breadcrumb')
            <div style="background: linear-gradient(to right, #6B0000, #8B0000, #6B0000);" class="py-6 text-white relative overflow-hidden">
                <div class="absolute inset-0 opacity-5" style="background-image: url(\"data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E\");"></div>
                <div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto relative">
                    <h1 class="text-xl font-bold font-serif mb-1.5">@yield('title')</h1>
                    <nav class="flex items-center gap-2 text-red-200 text-xs">
                        <a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a>
                        <span class="text-red-400">›</span>
                        <span class="text-yellow-300">@yield('title')</span>
                    </nav>
                </div>
            </div>
        @endif
    @endif

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

    {{-- ── FOOTER ─────────────────────────────────────────────── --}}
    <footer style="background-color: #8B0000;" class="text-white pt-12 pb-0 mt-8">
        <div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 pb-10 border-b border-red-700">

                {{-- About --}}
                <div>
                    <h3 class="font-bold font-serif text-lg mb-4 text-yellow-400">Manmohan Memorial Polytechnic</h3>
                    <p class="text-red-200 text-sm leading-relaxed mb-5">
                        Best Technical College in Koshi Province. CTEVT affiliated constituent college of Manmohan Technical University (MTU).
                    </p>
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-red-700 hover:bg-red-600 flex items-center justify-center cursor-pointer transition-colors text-sm font-bold">f</div>
                        <div class="w-8 h-8 rounded-full bg-red-700 hover:bg-red-600 flex items-center justify-center cursor-pointer transition-colors text-sm font-bold">t</div>
                        <div class="w-8 h-8 rounded-full bg-red-700 hover:bg-red-600 flex items-center justify-center cursor-pointer transition-colors text-sm font-bold">y</div>
                    </div>
                </div>

                {{-- Quick Links --}}
                <div>
                    <h3 class="font-bold font-serif text-lg mb-4 text-yellow-400">Quick Links</h3>
                    <ul class="space-y-2 text-sm text-red-200">
                        @foreach([
                            ['href' => route('home'), 'label' => 'Home'],
                            ['href' => route('public.page', 'what-is-mmp'), 'label' => 'About MMP'],
                            ['href' => route('public.departments'), 'label' => 'Departments & Programs'],
                            ['href' => route('public.notices'), 'label' => 'Notice Board'],
                            ['href' => route('public.downloads'), 'label' => 'Downloads & Forms'],
                            ['href' => route('public.contact'), 'label' => 'Contact Us'],
                            ['href' => route('login'), 'label' => '🔐 Student Portal'],
                        ] as $link)
                            <li><a href="{{ $link['href'] }}" class="hover:text-white transition-colors flex items-center gap-2"><span class="text-red-500">›</span> {{ $link['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>

                {{-- Departments --}}
                <div>
                    <h3 class="font-bold font-serif text-lg mb-4 text-yellow-400">Our Departments</h3>
                    <ul class="space-y-2 text-sm text-red-200">
                        @forelse($courseMenu as $course)
                            <li><a href="{{ route('public.department.show', $course->slug) }}" class="hover:text-white transition-colors flex items-center gap-2"><span class="text-red-500">›</span> {{ $course->name }}</a></li>
                        @empty
                            <li><a href="{{ route('public.departments') }}" class="hover:text-white transition-colors flex items-center gap-2"><span class="text-red-500">›</span> Departments & Programs</a></li>
                        @endforelse
                        @if($courseMenu->isNotEmpty())
                            <li><a href="{{ route('public.departments') }}" class="hover:text-white transition-colors flex items-center gap-2"><span class="text-red-500">›</span> View All Departments</a></li>
                        @endif
                    </ul>
                </div>

                {{-- Contact --}}
                <div>
                    <h3 class="font-bold font-serif text-lg mb-4 text-yellow-400">Contact Us</h3>
                    <ul class="space-y-3 text-sm text-red-200">
                        <li class="flex items-start gap-2"><span class="mt-0.5 text-red-400">📍</span><span>Budhiganga-4, Morang, Koshi Province, Nepal</span></li>
                        <li class="flex items-start gap-2"><span class="text-red-400">📞</span><span>+977 21 590696 / 590697</span></li>
                        <li class="flex items-start gap-2"><span class="text-red-400">✉️</span><span>info@mmp.edu.np</span></li>
                        <li class="mt-4">
                            <p class="text-xs text-red-300 font-semibold uppercase tracking-wider mb-2">Useful Links</p>
                            <div class="space-y-1">
                                <a href="http://ctevt.org.np" target="_blank" class="block hover:text-white transition-colors flex items-center gap-2"><span class="text-red-500">›</span> CTEVT</a>
                                <a href="https://mtu.edu.np/" target="_blank" class="block hover:text-white transition-colors flex items-center gap-2"><span class="text-red-500">›</span> Manmohan Technical University</a>
                                <a href="http://nstb.org.np" target="_blank" class="block hover:text-white transition-colors flex items-center gap-2"><span class="text-red-500">›</span> NSTB</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Copyright --}}
            <div style="background-color: #5B0000;" class="-mx-4 px-4 py-4 mt-0 text-center text-sm text-red-300">
                <p>© {{ date('Y') }} Manmohan Memorial Polytechnic (www.mmp.edu.np). All Rights Reserved.</p>
                <p class="text-xs mt-1 text-red-400">Budhiganga-4, Morang, Koshi Province, Nepal | Phone: +977 21 590696 | info@mmp.edu.np</p>
            </div>
        </div>
    </footer>

    <div id="pwa-install-banner" class="hidden fixed inset-x-4 bottom-4 z-50 mx-auto w-full max-w-lg rounded-3xl border border-white/70 bg-white/95 shadow-2xl shadow-red-900/20 backdrop-blur-xl">
        <div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center">
            <div class="flex items-start gap-3">
                <img src="{{ route('public.brand-logo') }}" alt="MMP college logo" class="h-14 w-14 rounded-2xl border border-red-100 bg-white p-1 shadow-sm object-contain">
                <div>
                    <div class="text-sm font-bold text-gray-900">Install MMP CMS</div>
                    <p class="mt-1 text-xs leading-relaxed text-gray-600">Add this site to your device for faster access and offline-ready browsing.</p>
                    <div class="mt-2 text-[11px] text-gray-500">Website: <span class="font-semibold text-gray-700">{{ url('/') }}</span></div>
                </div>
            </div>
            <div class="flex w-full gap-2 sm:w-auto sm:justify-end">
                <button type="button" id="pwa-install-dismiss" class="flex-1 rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 hover:border-gray-300 hover:bg-gray-50 sm:flex-none">Not now</button>
                <button type="button" id="pwa-install-trigger" class="flex-1 rounded-xl bg-[#8B0000] px-4 py-2 text-sm font-semibold text-white hover:bg-[#6B0000] sm:flex-none">Install</button>
            </div>
        </div>
    </div>

    @stack('scripts')

    <script>
        (function () {
            const banner = document.getElementById('pwa-install-banner');
            const installButton = document.getElementById('pwa-install-trigger');
            const dismissButton = document.getElementById('pwa-install-dismiss');
            const mobileInstallButton = document.getElementById('mobile-install-trigger');

            if (!banner || !installButton || !dismissButton) {
                return;
            }

            const storageKey = 'mmp:pwa-install-dismissed';
            const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
            const isMobileViewport = window.matchMedia('(max-width: 767px)').matches;
            const wasDismissed = localStorage.getItem(storageKey) === '1';
            let deferredPrompt = null;

            const revealBanner = function () {
                banner.classList.remove('hidden');

                if (typeof banner.scrollIntoView === 'function') {
                    banner.scrollIntoView({ behavior: 'smooth', block: 'end' });
                }
            };

            const triggerInstall = async function () {
                if (!deferredPrompt) {
                    revealBanner();
                    return;
                }

                deferredPrompt.prompt();

                try {
                    await deferredPrompt.userChoice;
                } catch (error) {
                    // Ignore prompt errors; the browser controls the install UI.
                }

                deferredPrompt = null;
                banner.classList.add('hidden');
                localStorage.setItem(storageKey, '1');
            };

            if (mobileInstallButton) {
                mobileInstallButton.addEventListener('click', triggerInstall);
            }

            window.addEventListener('beforeinstallprompt', function (event) {
                event.preventDefault();
                deferredPrompt = event;

                if (!isStandalone && !wasDismissed) {
                    revealBanner();
                }
            });

            window.addEventListener('appinstalled', function () {
                deferredPrompt = null;
                banner.classList.add('hidden');
                localStorage.setItem(storageKey, '1');
            });

            installButton.addEventListener('click', triggerInstall);

            if (isMobileViewport && !isStandalone && !wasDismissed) {
                revealBanner();
            }

            dismissButton.addEventListener('click', function () {
                banner.classList.add('hidden');
                localStorage.setItem(storageKey, '1');
            });
        })();
    </script>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then(() => {
                    console.log('SW registered');
                }).catch(err => {
                    console.log('SW registration failed', err);
                });
            });
        }
    </script>
</body>
</html>
