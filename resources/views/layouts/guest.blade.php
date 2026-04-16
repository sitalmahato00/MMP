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
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#8B0000">
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
    @php
        $brandLogoUrl = !empty($siteLogoPath ?? null)
            ? asset('storage/' . ltrim($siteLogoPath, '/'))
            : null;
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
    <div class="bg-white py-3 border-b border-gray-200 shadow-sm">
        <div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto flex items-center">
            <a href="{{ route('home') }}" class="flex items-center gap-4">
                {{-- MMP Seal/Emblem --}}
                <div class="w-14 h-14 flex-shrink-0 rounded-full flex items-center justify-center" style="background: radial-gradient(circle, #8B0000, #5B0000); border: 2px solid #DAA520;">
                    @if($brandLogoUrl)
                        <img src="{{ $brandLogoUrl }}" alt="MMP Logo" class="w-full h-full object-cover rounded-full">
                    @else
                        <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    @endif
                </div>
                <div>
                    <div class="text-xl font-black font-serif leading-tight" style="color:#8B0000;">Manmohan Memorial Polytechnic</div>
                    <div class="text-sm italic font-semibold" style="color:#DAA520;">Best Technical College in Koshi Province</div>
                    <div class="text-xs text-gray-500 font-medium">A Constituent College of Manmohan Technical University</div>
                </div>
            </a>
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
                        ['label' => 'COURSES', 'items' => [
                            ['href' => route('public.department.show', 'information-technology'), 'label' => 'Diploma in Information Technology'],
                            ['href' => route('public.department.show', 'architecture-engineering'), 'label' => 'Diploma in Architecture Engineering'],
                            ['href' => route('public.department.show', 'electrical-engineering'), 'label' => 'Diploma in Electrical Engineering'],
                            ['href' => route('public.department.show', 'electronics-engineering'), 'label' => 'Diploma in Electronics Engineering'],
                            ['href' => route('public.department.show', 'mechanical-engineering'), 'label' => 'Diploma in Mechanical Engineering'],
                            ['href' => route('public.department.show', 'civil-engineering'), 'label' => 'Diploma in Civil Engineering'],
                        ]],
                        ['label' => 'FEATURES', 'items' => [
                            ['href' => route('public.facilities'), 'label' => 'Campus Facilities & Resources'],
                            ['href' => route('public.page', 'scholarship-schemes'), 'label' => 'Scholarship Schemes'],
                            ['href' => route('public.page', 'internships'), 'label' => 'Internships & Placements'],
                        ]],
                        ['label' => 'PEOPLES', 'items' => [
                            ['href' => route('public.staff'), 'label' => 'Administrative Staff'],
                            ['href' => route('public.leadership'), 'label' => 'Presidents & Principals'],
                            ['href' => route('public.alumni'), 'label' => 'Alumni Directory'],
                        ]],
                    ] as $menu)
                        <div class="relative group" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                            <button class="text-white text-sm font-bold uppercase px-3 py-3.5 hover:bg-white/10 transition-colors border-b-4 border-transparent hover:border-white flex items-center gap-1">
                                {{ $menu['label'] }}
                                <svg class="w-3 h-3 ml-0.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak
                                class="absolute top-full left-0 mt-0 w-64 bg-[#404040] py-2 z-50 shadow-xl border-t-2 border-white">
                                @foreach($menu['items'] as $item)
                                    <a href="{{ $item['href'] }}" class="block px-5 py-2.5 text-[13px] text-gray-200 hover:text-white hover:bg-white/10 transition-colors font-medium border-b border-white/5 last:border-0">
                                        {{ $item['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <a href="{{ route('public.news-events') }}" class="text-white text-sm font-bold uppercase px-3 py-3.5 hover:bg-white/10 transition-colors border-b-4 {{ request()->routeIs('public.news-events') ? 'border-white bg-white/10' : 'border-transparent hover:border-white' }}">NEWS & EVENTS</a>
                    <a href="{{ route('public.gallery') }}" class="text-white text-sm font-bold uppercase px-3 py-3.5 hover:bg-white/10 transition-colors border-b-4 {{ request()->routeIs('public.gallery') ? 'border-white bg-white/10' : 'border-transparent hover:border-white' }}">GALLERY</a>
                    
                    {{-- RESOURCES Dropdown --}}
                    <div class="relative group" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button class="text-white text-sm font-bold uppercase px-3 py-3.5 hover:bg-white/10 transition-colors border-b-4 border-transparent hover:border-white flex items-center gap-1">
                            RESOURCES
                            <svg class="w-3 h-3 ml-0.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak
                            class="absolute top-full left-0 mt-0 w-56 bg-[#404040] py-2 z-50 shadow-xl border-t-2 border-white">
                            <a href="{{ route('public.downloads') }}" class="block px-5 py-2.5 text-[13px] text-gray-200 hover:text-white hover:bg-white/10 transition-colors font-medium border-b border-white/5">Forms & Downloads</a>
                            <a href="{{ route('public.question-bank') }}" class="block px-5 py-2.5 text-[13px] text-gray-200 hover:text-white hover:bg-white/10 transition-colors font-medium">Question Bank</a>
                        </div>
                    </div>
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
                        COURSES <svg class="w-4 h-4 transition-transform z-10" :class="subOpen ? 'rotate-180':''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="subOpen" class="bg-[#222222] pl-8 pr-4 py-2 space-y-3 font-medium text-[12px] text-gray-300">
                        <a href="{{ route('public.department.show', 'information-technology') }}" class="block hover:text-white">Information Technology</a>
                        <a href="{{ route('public.department.show', 'civil-engineering') }}" class="block hover:text-white">Civil Engineering</a>
                        <a href="{{ route('public.department.show', 'electrical-engineering') }}" class="block hover:text-white">Electrical Engineering</a>
                        <a href="{{ route('public.department.show', 'mechanical-engineering') }}" class="block hover:text-white">Mechanical Engineering</a>
                        <a href="{{ route('public.department.show', 'electronics-engineering') }}" class="block hover:text-white">Electronics Engineering</a>
                        <a href="{{ route('public.department.show', 'architecture-engineering') }}" class="block hover:text-white">Architecture Engineering</a>
                    </div>
                </div>

                <div x-data="{ subOpen: false }" class="border-l-4 border-transparent">
                    <button @click="subOpen = !subOpen" class="w-full flex items-center justify-between px-5 py-4 hover:bg-white/5 transition-colors">
                        PEOPLES <svg class="w-4 h-4 transition-transform z-10" :class="subOpen ? 'rotate-180':''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="subOpen" class="bg-[#222222] pl-8 pr-4 py-2 space-y-3 font-medium text-[12px] text-gray-300">
                        <a href="{{ route('public.staff') }}" class="block hover:text-white">Administrative Staff</a>
                        <a href="{{ route('public.leadership') }}" class="block hover:text-white">Presidents & Principals</a>
                        <a href="{{ route('public.alumni') }}" class="block hover:text-white">Alumni Directory</a>
                    </div>
                </div>
                
                <a href="{{ route('public.news-events') }}" class="block px-5 py-4 hover:bg-white/5 transition-colors border-l-4 {{ request()->routeIs('public.news-events') ? 'border-white bg-white/10' : 'border-transparent' }}">NEWS & EVENTS</a>
                <a href="{{ route('public.gallery') }}" class="block px-5 py-4 hover:bg-white/5 transition-colors border-l-4 {{ request()->routeIs('public.gallery') ? 'border-white bg-white/10' : 'border-transparent' }}">GALLERY</a>

                <div x-data="{ subOpen: false }" class="border-l-4 border-transparent">
                    <button @click="subOpen = !subOpen" class="w-full flex items-center justify-between px-5 py-4 hover:bg-white/5 transition-colors">
                        RESOURCES <svg class="w-4 h-4 transition-transform z-10" :class="subOpen ? 'rotate-180':''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="subOpen" class="bg-[#222222] pl-8 pr-4 py-2 space-y-3 font-medium text-[12px] text-gray-300">
                        <a href="{{ route('public.downloads') }}" class="block hover:text-white">Forms & Downloads</a>
                        <a href="{{ route('public.question-bank') }}" class="block hover:text-white">Question Bank</a>
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
                            ['href' => route('public.departments'), 'label' => 'Courses & Programs'],
                            ['href' => route('public.notices'), 'label' => 'Notice Board'],
                            ['href' => route('public.downloads'), 'label' => 'Downloads & Forms'],
                            ['href' => route('public.contact'), 'label' => 'Contact Us'],
                            ['href' => route('login'), 'label' => '🔐 Student Portal'],
                        ] as $link)
                            <li><a href="{{ $link['href'] }}" class="hover:text-white transition-colors flex items-center gap-2"><span class="text-red-500">›</span> {{ $link['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>

                {{-- Programs --}}
                <div>
                    <h3 class="font-bold font-serif text-lg mb-4 text-yellow-400">Our Programs</h3>
                    <ul class="space-y-2 text-sm text-red-200">
                        @foreach([
                            ['href' => route('public.department.show', 'information-technology'), 'label' => 'Diploma in IT'],
                            ['href' => route('public.department.show', 'civil-engineering'), 'label' => 'Diploma in Civil'],
                            ['href' => route('public.department.show', 'electrical-engineering'), 'label' => 'Diploma in Electrical'],
                            ['href' => route('public.department.show', 'electronics-engineering'), 'label' => 'Diploma in Electronics'],
                            ['href' => route('public.department.show', 'mechanical-engineering'), 'label' => 'Diploma in Mechanical'],
                            ['href' => route('public.department.show', 'architecture-engineering'), 'label' => 'Diploma in Architecture'],
                            ['href' => '#', 'label' => 'Short Term Trainings'],
                        ] as $link)
                            <li><a href="{{ $link['href'] }}" class="hover:text-white transition-colors flex items-center gap-2"><span class="text-red-500">›</span> {{ $link['label'] }}</a></li>
                        @endforeach
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

    @stack('scripts')
</body>
</html>
