<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">
    <title>@yield('title', 'Manmohan Memorial Polytechnic') | Best Technical College — Koshi Province, Nepal</title>
    <meta name="description" content="@yield('meta_description', 'Manmohan Memorial Polytechnic (MMP) — Best Technical College in Koshi Province, Nepal. CTEVT Diploma programs in IT, Civil, Electrical, Mechanical & Electronics Engineering.')">
    <meta name="keywords" content="Manmohan Memorial Polytechnic, MMP, technical college Nepal, diploma engineering, CTEVT, Koshi Province, Morang">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'Manmohan Memorial Polytechnic')">
    <meta property="og:description" content="@yield('meta_description', 'Best Technical College in Koshi Province offering CTEVT diploma programs.')">
    <meta property="og:type" content="website">
    <link rel="manifest" href="{{ asset('manifest.json') }}?v=3">
    <meta name="application-name" content="Manmohan Memorial Polytechnic">
    <meta name="apple-mobile-web-app-title" content="Manmohan Memorial Polytechnic">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="#003D82" id="guest-theme-color">
    <link rel="icon" href="{{ route('public.brand-logo') }}?v={{ logoVersion() }}">
    <link rel="shortcut icon" href="{{ route('public.brand-logo') }}?v={{ logoVersion() }}">
    <link rel="apple-touch-icon" href="{{ route('public.brand-logo') }}?v={{ logoVersion() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Merriweather:wght@400;700;900&display=swap" rel="stylesheet">
    <script>
        (() => {
            const themeChoice = localStorage.getItem('mmp.theme') || 'system';
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const effectiveTheme = themeChoice === 'system' ? systemTheme : themeChoice;
            document.documentElement.classList.toggle('dark', effectiveTheme === 'dark');
            document.documentElement.dataset.theme = effectiveTheme;
            document.documentElement.style.colorScheme = effectiveTheme;
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --blue-primary: #003D82;
            --blue-bright: #0052B3;
            --blue-light: #E8F0F8;
            --gold: #EAB308;
        }
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Merriweather', serif; }
        .bg-primary { background-color: var(--blue-primary); }
        .text-primary { color: var(--blue-primary); }
        .border-primary { border-color: var(--blue-primary); }
        .section-header {
            background-color: var(--blue-primary);
            color: white;
            padding: 0.5rem 1rem;
            font-family: 'Merriweather', serif;
            font-weight: 700;
            font-size: 0.95rem;
        }
    </style>
    @stack('styles')
</head>
<body class="overflow-x-hidden bg-gray-100 text-gray-900 antialiased transition-colors dark:bg-slate-950 dark:text-slate-100" x-data="mmpGuestShell()" x-init="init()">
    @php $brandLogoUrl = route('public.brand-logo') . '?v=' . logoVersion(); @endphp

    @php
        $courseMenu = collect($publicCourses ?? []);
        $publicMobileNav = [
            ['label' => 'Home', 'href' => route('home'), 'icon' => 'home', 'active' => request()->routeIs('home')],
            ['label' => 'Notices', 'href' => route('public.notices'), 'icon' => 'bell', 'active' => request()->routeIs('public.notices') || request()->routeIs('public.notice.show')],
            ['label' => 'About', 'href' => route('public.page', 'what-is-mmp'), 'icon' => 'info', 'active' => request()->routeIs('public.page') && request()->route('slug') === 'what-is-mmp'],
            ['label' => 'Login', 'href' => route('login'), 'icon' => 'login', 'active' => request()->routeIs('login')],
        ];
        $publicIconPaths = [
            'home' => 'M3 10.5L12 3l9 7.5V20a1 1 0 01-1 1h-5.5v-6h-5v6H4a1 1 0 01-1-1v-9.5z',
            'bell' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
            'info' => 'M13 16h-1v-4h-1m1-4h.01M12 21a9 9 0 100-18 9 9 0 000 18z',
            'login' => 'M15 3h3a2 2 0 012 2v14a2 2 0 01-2 2h-3M10 17l5-5-5-5M15 12H3',
        ];
    @endphp

    <header class="fixed inset-x-0 top-0 z-50 border-b-2 border-slate-200 bg-white/95 shadow-md backdrop-blur lg:hidden dark:border-slate-700 dark:bg-slate-900/95" x-data="{ mobileMenuOpen: false }">
        <div class="flex items-center justify-between gap-2 px-3 sm:px-4 pb-2 sm:pb-3 pt-[max(0.5rem,env(safe-area-inset-top))] sm:pt-[max(0.75rem,env(safe-area-inset-top))]">
            <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="inline-flex h-9 w-9 sm:h-11 sm:w-11 items-center justify-center rounded-xl sm:rounded-2xl border-2 border-slate-300 bg-white text-slate-600 shadow-md transition-all duration-200 hover:bg-slate-50 hover:scale-105 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                    <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                
                <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-2 sm:gap-3 flex-1">
                    <div class="flex h-9 w-9 sm:h-11 sm:w-11 shrink-0 items-center justify-center rounded-xl sm:rounded-2xl bg-[#003D82] shadow-lg border-2 border-blue-600 dark:border-blue-500">
                        <img src="{{ $brandLogoUrl }}" alt="MMP Logo" class="h-7 w-7 sm:h-9 sm:w-9 rounded-xl sm:rounded-2xl object-cover">
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-[8px] sm:text-[9px] font-bold uppercase tracking-wider text-[#003D82] dark:text-blue-300 leading-tight">MMP</p>
                        <p class="truncate text-[11px] sm:text-xs font-bold text-slate-900 dark:text-slate-50 leading-tight">Manmohan Memorial Polytechnic</p>
                    </div>
                </a>
            </div>

            <div class="flex items-center gap-1.5 sm:gap-2">
                <button type="button" x-show="canInstall && !isStandalone" x-cloak @click="installApp()" class="inline-flex h-9 w-9 sm:h-11 sm:w-11 items-center justify-center rounded-xl sm:rounded-2xl border-2 border-slate-300 bg-white text-slate-600 shadow-md transition-all duration-200 hover:bg-slate-50 hover:scale-105 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                    <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v10m0 0l4-4m-4 4l-4-4M4 16.5v1A2.5 2.5 0 006.5 20h11a2.5 2.5 0 002.5-2.5v-1"/>
                    </svg>
                </button>
                <button type="button" @click="toggleTheme()" class="inline-flex h-9 w-9 sm:h-11 sm:w-11 items-center justify-center rounded-xl sm:rounded-2xl border-2 border-slate-300 bg-white text-slate-600 shadow-md transition-all duration-200 hover:bg-slate-50 hover:scale-105 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                    <svg x-show="effectiveTheme !== 'dark'" x-cloak class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9 9 0 1012 21a8.96 8.96 0 008.354-5.646z"/>
                    </svg>
                    <svg x-show="effectiveTheme === 'dark'" x-cloak class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2.25M12 18.75V21m9-9h-2.25M5.25 12H3m15.364 6.364l-1.591-1.591M7.227 7.227L5.636 5.636m12.728 0l-1.591 1.591M7.227 16.773l-1.591 1.591M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
                    </svg>
                </button>
            </div>
        </div>
        
        {{-- Mobile Menu Dropdown --}}
        <div x-show="mobileMenuOpen" 
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="border-t-2 border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-900">
            <div class="max-h-[70vh] overflow-y-auto px-3 sm:px-4 py-3">
                <nav class="space-y-1">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-xl px-3 sm:px-4 py-2.5 sm:py-3 text-sm font-semibold text-slate-700 transition-all duration-200 hover:bg-slate-100 hover:scale-[1.02] dark:text-slate-200 dark:hover:bg-slate-800 border-2 border-transparent hover:border-slate-200 dark:hover:border-slate-700 {{ request()->routeIs('home') ? 'bg-[#003D82] text-white dark:bg-[#003D82] border-[#003D82] shadow-md' : '' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Home
                    </a>
                    
                    <a href="{{ route('public.apply') }}" class="flex items-center gap-3 rounded-xl bg-[#d35400] px-3 sm:px-4 py-2.5 sm:py-3 text-sm font-semibold text-white transition-all duration-200 hover:bg-[#e67e22] hover:scale-[1.02] shadow-md hover:shadow-lg border-2 border-[#c44d00]">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Apply Now
                    </a>
                    
                    <div x-data="{ aboutOpen: false }">
                        <button @click="aboutOpen = !aboutOpen" class="flex w-full items-center justify-between rounded-xl px-3 sm:px-4 py-2.5 sm:py-3 text-sm font-semibold text-slate-700 transition-all duration-200 hover:bg-slate-100 hover:scale-[1.02] dark:text-slate-200 dark:hover:bg-slate-800 border-2 border-transparent hover:border-slate-200 dark:hover:border-slate-700">
                            <span class="flex items-center gap-3">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                About Us
                            </span>
                            <svg class="h-4 w-4 transition-transform" :class="aboutOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="aboutOpen" x-cloak class="ml-6 sm:ml-8 mt-1 space-y-1 border-l-2 border-slate-300 dark:border-slate-600 pl-3">
                            <a href="{{ route('public.page', 'what-is-mmp') }}" class="block rounded-lg px-3 sm:px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 transition-all duration-200 hover:translate-x-1 border border-transparent hover:border-slate-200 dark:hover:border-slate-700">What is MMP</a>
                            <a href="{{ route('public.page', 'objectives') }}" class="block rounded-lg px-3 sm:px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 transition-all duration-200 hover:translate-x-1 border border-transparent hover:border-slate-200 dark:hover:border-slate-700">Objectives</a>
                            <a href="{{ route('public.leadership') }}" class="block rounded-lg px-3 sm:px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 transition-all duration-200 hover:translate-x-1 border border-transparent hover:border-slate-200 dark:hover:border-slate-700">Presidents & Principals</a>
                            <a href="{{ route('public.contact') }}" class="block rounded-lg px-3 sm:px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 transition-all duration-200 hover:translate-x-1 border border-transparent hover:border-slate-200 dark:hover:border-slate-700">Contact Us</a>
                        </div>
                    </div>
                    
                    <div x-data="{ deptOpen: false }">
                        <button @click="deptOpen = !deptOpen" class="flex w-full items-center justify-between rounded-xl px-3 sm:px-4 py-2.5 sm:py-3 text-sm font-semibold text-slate-700 transition-all duration-200 hover:bg-slate-100 hover:scale-[1.02] dark:text-slate-200 dark:hover:bg-slate-800 border-2 border-transparent hover:border-slate-200 dark:hover:border-slate-700">
                            <span class="flex items-center gap-3">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                Departments
                            </span>
                            <svg class="h-4 w-4 transition-transform" :class="deptOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="deptOpen" x-cloak class="ml-8 mt-1 space-y-1">
                            @forelse($courseMenu as $course)
                                <a href="{{ route('public.department.show', $course->slug) }}" class="block rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800">{{ $course->name }}</a>
                            @empty
                                <span class="block px-4 py-2 text-sm text-slate-400">No departments</span>
                            @endforelse
                            <a href="{{ route('public.departments') }}" class="block rounded-lg px-4 py-2 text-sm font-semibold text-[#003D82] hover:bg-slate-100 dark:text-blue-300 dark:hover:bg-slate-800">All Departments →</a>
                        </div>
                    </div>
                    
                    <a href="{{ route('public.news-events') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800 {{ request()->routeIs('public.news-events*') ? 'bg-[#003D82] text-white dark:bg-[#003D82]' : '' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                        </svg>
                        News & Events
                    </a>
                    
                    <div x-data="{ featuresOpen: false }">
                        <button @click="featuresOpen = !featuresOpen" class="flex w-full items-center justify-between rounded-xl px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">
                            <span class="flex items-center gap-3">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                                Features
                            </span>
                            <svg class="h-4 w-4 transition-transform" :class="featuresOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="featuresOpen" x-cloak class="ml-8 mt-1 space-y-1">
                            <a href="{{ route('public.facilities') }}" class="block rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800">Campus Facilities & Resources</a>
                            <a href="{{ route('public.page', 'scholarship-schemes') }}" class="block rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800">Scholarship Schemes</a>
                            <a href="{{ route('public.page', 'internships') }}" class="block rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800">Internships & Placements</a>
                        </div>
                    </div>
                    
                    <a href="{{ route('public.notices') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800 {{ request()->routeIs('public.notices') || request()->routeIs('public.notice.show') ? 'bg-[#003D82] text-white dark:bg-[#003D82]' : '' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        Notices
                    </a>
                    
                    <a href="{{ route('public.gallery') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800 {{ request()->routeIs('public.gallery') ? 'bg-[#003D82] text-white dark:bg-[#003D82]' : '' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Gallery
                    </a>
                    
                    <a href="{{ route('public.alumni') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800 {{ request()->routeIs('public.alumni') ? 'bg-[#003D82] text-white dark:bg-[#003D82]' : '' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Alumni
                    </a>
                    
                    <a href="{{ route('public.result') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800 {{ request()->routeIs('public.result') ? 'bg-[#003D82] text-white dark:bg-[#003D82]' : '' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Result
                    </a>
                    
                    <div x-data="{ resourcesOpen: false }">
                        <button @click="resourcesOpen = !resourcesOpen" class="flex w-full items-center justify-between rounded-xl px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">
                            <span class="flex items-center gap-3">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Resources
                            </span>
                            <svg class="h-4 w-4 transition-transform" :class="resourcesOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="resourcesOpen" x-cloak class="ml-8 mt-1 space-y-1">
                            <a href="{{ route('public.downloads') }}" class="block rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800">All Resources</a>
                            <a href="{{ route('public.downloads', ['category' => 'forms']) }}" class="block rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800">Forms & Downloads</a>
                            <a href="{{ route('public.downloads', ['category' => 'syllabus']) }}" class="block rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800">Syllabus</a>
                            <a href="{{ route('public.question-bank') }}" class="block rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800">Question Bank</a>
                        </div>
                    </div>
                    
                    <a href="{{ route('login') }}" class="flex items-center gap-3 rounded-xl border-2 border-[#003D82] px-4 py-3 text-sm font-semibold text-[#003D82] transition hover:bg-[#003D82] hover:text-white dark:border-blue-400 dark:text-blue-400 dark:hover:bg-blue-400 dark:hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Login Portal
                    </a>
                </nav>
            </div>
        </div>
    </header>

    {{-- ── TOP INFO BAR (CTEVT Blue) ─────────────── --}}
    <div style="background-color: #003D82;" class="hidden py-1.5 text-xs text-white lg:block">
        <div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto flex justify-between items-center">
            <div class="flex items-center gap-5">
                <span class="flex items-center gap-1.5">
                    <svg class="w-3 h-3 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Budhiganga-4, Morang, Koshi Province, Nepal
                </span>
                <span class="text-blue-400">|</span>
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
                <button type="button" @click="toggleTheme()" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-white/20 text-white transition hover:bg-white/10">
                    <svg x-show="effectiveTheme !== 'dark'" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20.354 15.354A9 9 0 018.646 3.646 9 9 0 1012 21a8.96 8.96 0 008.354-5.646z"/>
                    </svg>
                    <svg x-show="effectiveTheme === 'dark'" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v2.25M12 18.75V21m9-9h-2.25M5.25 12H3m15.364 6.364l-1.591-1.591M7.227 7.227L5.636 5.636m12.728 0l-1.591 1.591M7.227 16.773l-1.591 1.591M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
                    </svg>
                </button>
                <button type="button" x-show="canInstall && !isStandalone" x-cloak @click="installApp()" class="rounded bg-white/10 px-3 py-1 text-[11px] font-semibold text-white transition hover:bg-white/20">
                    Install App
                </button>
                <a href="{{ route('login') }}" class="bg-yellow-500 hover:bg-yellow-400 text-gray-900 font-semibold px-4 py-1 rounded text-[11px] transition-colors">
                    Login Portal
                </a>
            </div>
        </div>
    </div>

    {{-- ── LOGO BAR (White, matching mmp.edu.np) ──────────────── --}}
    @unless(request()->routeIs('home'))
    {{-- slim spacer for non-home pages --}}
    @else
    <div class="hidden border-b border-gray-200 bg-white py-2.5 shadow-sm lg:block md:py-3">
        <div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto flex items-center justify-between gap-3">
            <a href="{{ route('home') }}" class="flex min-w-0 flex-1 items-center gap-3">
                {{-- MMP Seal/Emblem --}}
                <div class="w-11 h-11 md:w-14 md:h-14 flex-shrink-0 rounded-full flex items-center justify-center" style="background: radial-gradient(circle, #003D82, #001F4D); border: 2px solid #DAA520;">
                    @if($brandLogoUrl)
                        <img src="{{ $brandLogoUrl }}" alt="MMP Logo" class="w-full h-full object-cover rounded-full">
                    @else
                        <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    @endif
                </div>
                <div class="min-w-0 leading-tight">
                    <div class="text-base sm:text-xl font-semibold font-serif leading-tight text-[#003D82] line-clamp-1">Manmohan Memorial Polytechnic</div>
                    <div class="text-[11px] sm:text-sm font-normal text-[#DAA520] line-clamp-1">Best Technical College in Koshi Province</div>
                    <div class="hidden sm:block text-xs text-gray-500 font-normal">A Constituent College of Manmohan Technical University</div>
                    <div class="sm:hidden text-[10px] font-normal text-gray-500">mmp.edu.np</div>
                </div>
            </a>
        </div>
    </div>
    @endunless

    {{-- ── MAIN NAVIGATION (CTEVT Blue) ──────────────────────────── --}}
    <nav style="background-color: #003D82;" class="hidden sticky top-0 z-50 shadow-md lg:block" x-data="{ mobileOpen: false }">
        <div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto">
            <div class="flex items-center justify-between">

                {{-- Desktop Nav Links --}}
                <div class="hidden xl:flex items-center flex-1">
                    {{-- Active state uses pb-1, border-b-[4px] for the thick white underline --}}
                    <a href="{{ route('home') }}" class="text-white text-xs font-semibold uppercase px-2.5 py-3 hover:bg-white/10 transition-colors border-b-4 {{ request()->routeIs('home') ? 'border-white bg-white/10' : 'border-transparent hover:border-white' }}">HOME</a>

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
                            <button class="text-white text-xs font-semibold uppercase px-2.5 py-3 hover:bg-white/10 transition-colors border-b-4 border-transparent hover:border-white flex items-center gap-1">
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

                    <a href="{{ route('public.news-events') }}" class="text-white text-xs font-semibold uppercase px-2.5 py-3 hover:bg-white/10 transition-colors border-b-4 {{ request()->routeIs('public.news-events*') ? 'border-white bg-white/10' : 'border-transparent hover:border-white' }}">NEWS & EVENTS</a>
                    <a href="{{ route('public.notices') }}" class="text-white text-xs font-semibold uppercase px-2.5 py-3 hover:bg-white/10 transition-colors border-b-4 {{ request()->routeIs('public.notices') || request()->routeIs('public.notice.show') ? 'border-white bg-white/10' : 'border-transparent hover:border-white' }}">NOTICES</a>
                    <a href="{{ route('public.gallery') }}" class="text-white text-xs font-semibold uppercase px-2.5 py-3 hover:bg-white/10 transition-colors border-b-4 {{ request()->routeIs('public.gallery') ? 'border-white bg-white/10' : 'border-transparent hover:border-white' }}">GALLERY</a>
                    <a href="{{ route('public.alumni') }}" class="text-white text-xs font-semibold uppercase px-2.5 py-3 hover:bg-white/10 transition-colors border-b-4 {{ request()->routeIs('public.alumni') ? 'border-white bg-white/10' : 'border-transparent hover:border-white' }}">ALUMNI</a>
                    <a href="{{ route('public.result') }}" class="text-white text-xs font-semibold uppercase px-2.5 py-3 hover:bg-white/10 transition-colors border-b-4 {{ request()->routeIs('public.result') ? 'border-white bg-white/10' : 'border-transparent hover:border-white' }}">RESULT</a>
                    
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
                        <button class="text-white text-xs font-semibold uppercase px-2.5 py-3 hover:bg-white/10 transition-colors border-b-4 border-transparent hover:border-white flex items-center gap-1">
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

                    <a href="{{ route('public.apply') }}" class="ml-2 inline-flex items-center gap-1.5 rounded-sm bg-[#d35400] px-3 py-2.5 text-xs font-semibold uppercase text-white shadow-md transition-colors hover:bg-[#e67e22]">
                        Apply Now
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>

                {{-- Phone Numbers exactly on the right side --}}
                <div class="hidden xl:flex flex-col items-end opacity-60 text-right pr-2">
                    <div class="text-[11px] font-bold text-white tracking-widest leading-tight hover:opacity-100 transition-opacity cursor-pointer">021-590696</div>
                    <div class="text-[11px] font-bold text-white tracking-widest leading-tight hover:opacity-100 transition-opacity cursor-pointer">021-590697</div>
                </div>

                {{-- Mobile: Hamburger Menu (Left) --}}
                <button @click="mobileOpen = !mobileOpen" class="xl:hidden text-white p-3 hover:bg-white/10 transition-colors h-14 flex items-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                {{-- Mobile: Login Button (Right) --}}
                <a href="{{ route('login') }}" class="xl:hidden ml-auto inline-flex items-center gap-1.5 rounded-md bg-yellow-500 hover:bg-yellow-400 px-3 py-2 text-[11px] font-bold uppercase tracking-wide text-gray-900 shadow-sm transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    Login
                </a>
            </div>
        </div>

        {{-- Mobile Nav (Full structure) --}}
        <div x-show="mobileOpen" x-cloak class="xl:hidden bg-[#333333] border-t border-white/10 text-white max-h-[80vh] overflow-y-auto">
            <div class="px-0 py-0 divide-y divide-white/10 text-sm font-semibold uppercase tracking-wider">
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
                
                <a href="{{ route('public.news-events') }}" class="block px-5 py-4 hover:bg-white/5 transition-colors border-l-4 {{ request()->routeIs('public.news-events*') ? 'border-white bg-white/10' : 'border-transparent' }}">NEWS & EVENTS</a>
                <a href="{{ route('public.notices') }}" class="block px-5 py-4 hover:bg-white/5 transition-colors border-l-4 {{ request()->routeIs('public.notices') || request()->routeIs('public.notice.show') ? 'border-white bg-white/10' : 'border-transparent' }}">NOTICES</a>
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
            <div style="background: linear-gradient(to right, #001F4D, #003D82, #001F4D);" class="relative hidden overflow-hidden py-6 text-white lg:block">
                <div class="absolute inset-0 opacity-5" style="background-image: url(\"data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E\");"></div>
                <div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto relative">
                    <h1 class="text-xl font-bold font-serif mb-1.5">@yield('title')</h1>
                    <nav class="flex items-center gap-2 text-blue-200 text-xs">
                        <a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a>
                        <span class="text-blue-400">›</span>
                        <span class="text-yellow-300">@yield('title')</span>
                    </nav>
                </div>
            </div>
        @endif
    @endif

    {{-- Main Content --}}
    <main class="pb-[calc(env(safe-area-inset-bottom)+5.75rem)] pt-[calc(env(safe-area-inset-top)+5.25rem)] lg:pb-0 lg:pt-0">
        @yield('content')
    </main>

    <nav data-shell-bottom-nav class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200/80 bg-white/95 shadow-[0_-10px_30px_rgba(15,23,42,0.12)] lg:hidden dark:border-slate-800 dark:bg-slate-950/95">
        <div class="grid grid-cols-4 gap-1 px-2 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-2">
            @foreach($publicMobileNav as $item)
                <a href="{{ $item['href'] }}" class="flex flex-col items-center justify-center gap-1 rounded-2xl px-2 py-2.5 text-center transition {{ $item['active'] ? 'bg-[#003D82]/10 text-[#003D82] dark:bg-blue-500/15 dark:text-blue-300' : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-900' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $publicIconPaths[$item['icon']] }}"/>
                    </svg>
                    <span class="text-[11px] font-semibold">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>

    {{-- ── FOOTER ─────────────────────────────────────────────── --}}
    <footer style="background-color: #003D82;" class="mt-8 hidden pb-0 pt-12 text-white lg:block">
        <div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 pb-10 border-b border-blue-700">

                {{-- About --}}
                <div>
                    <h3 class="font-bold font-serif text-lg mb-4 text-yellow-400">Manmohan Memorial Polytechnic</h3>
                    <p class="text-blue-200 text-sm leading-relaxed mb-5">
                        Best Technical College in Koshi Province. CTEVT affiliated constituent college of Manmohan Technical University (MTU).
                    </p>
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-700 hover:bg-blue-600 flex items-center justify-center cursor-pointer transition-colors text-sm font-bold">f</div>
                        <div class="w-8 h-8 rounded-full bg-blue-700 hover:bg-blue-600 flex items-center justify-center cursor-pointer transition-colors text-sm font-bold">t</div>
                        <div class="w-8 h-8 rounded-full bg-blue-700 hover:bg-blue-600 flex items-center justify-center cursor-pointer transition-colors text-sm font-bold">y</div>
                    </div>
                </div>

                {{-- Quick Links --}}
                <div>
                    <h3 class="font-bold font-serif text-lg mb-4 text-yellow-400">Quick Links</h3>
                    <ul class="space-y-2 text-sm text-blue-200">
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
                    <ul class="space-y-2 text-sm text-blue-200">
                        @forelse($courseMenu as $course)
                            <li><a href="{{ route('public.department.show', $course->slug) }}" class="hover:text-white transition-colors flex items-center gap-2"><span class="text-blue-500">›</span> {{ $course->name }}</a></li>
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
                    <ul class="space-y-3 text-sm text-blue-200">
                        <li class="flex items-start gap-2"><span class="mt-0.5 text-blue-400">📍</span><span>Budhiganga-4, Morang, Koshi Province, Nepal</span></li>
                        <li class="flex items-start gap-2"><span class="text-blue-400">📞</span><span>+977 21 590696 / 590697</span></li>
                        <li class="flex items-start gap-2"><span class="text-blue-400">✉️</span><span>info@mmp.edu.np</span></li>
                        <li class="mt-4">
                            <p class="text-xs text-blue-300 font-semibold uppercase tracking-wider mb-2">Useful Links</p>
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
            <div style="background-color: #001F4D;" class="-mx-4 px-4 py-4 mt-0 text-center text-sm text-blue-300">
                <p>© {{ date('Y') }} Manmohan Memorial Polytechnic (www.mmp.edu.np). All Rights Reserved.</p>
                <p class="text-xs mt-1 text-blue-400">Budhiganga-4, Morang, Koshi Province, Nepal | Phone: +977 21 590696 | info@mmp.edu.np</p>
            </div>
        </div>
    </footer>

    <div id="pwa-install-banner" x-show="canInstall && !installDismissed && !isStandalone" x-cloak class="fixed inset-x-3 bottom-[calc(env(safe-area-inset-bottom)+5.75rem)] z-50 mx-auto w-full max-w-lg rounded-3xl border border-white/70 bg-white/95 shadow-2xl shadow-red-900/20 backdrop-blur-xl dark:border-white/10 dark:bg-slate-900/95 lg:inset-x-auto lg:right-6 lg:bottom-6">
        <div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center">
            <div class="flex items-start gap-3">
                <img src="{{ route('public.brand-logo') }}" alt="MMP college logo" class="h-14 w-14 rounded-2xl border border-red-100 bg-white p-1 shadow-sm object-contain">
                <div>
                    <div class="text-sm font-bold text-gray-900 dark:text-slate-50">Install MMP App</div>
                    <p class="mt-1 text-xs leading-relaxed text-gray-600 dark:text-slate-400">Use the portal as a mobile or desktop app with faster launch and standalone windows.</p>
                    <div class="mt-2 text-[11px] text-gray-500 dark:text-slate-500">Website: <span class="font-semibold text-gray-700 dark:text-slate-300">{{ url('/') }}</span></div>
                </div>
            </div>
            <div class="flex w-full gap-2 sm:w-auto sm:justify-end">
                <button type="button" @click="dismissInstall()" class="flex-1 rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 hover:border-gray-300 hover:bg-gray-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 sm:flex-none">Not now</button>
                <button type="button" @click="installApp()" class="flex-1 rounded-xl bg-[#8B0000] px-4 py-2 text-sm font-semibold text-white hover:bg-[#6B0000] sm:flex-none">Install</button>
            </div>
        </div>
    </div>

    {{-- Install App Modal (Shows on first visit to home page) --}}
    @if(request()->routeIs('home'))
    <div id="install-modal" x-data="{ show: false }" x-show="show" x-cloak class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 p-4 backdrop-blur-sm sm:items-center" style="display: none;">
        <div @click.away="show = false" x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#8B0000] to-[#5B0000] flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-slate-50">Install MMP App</h3>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Manmohan Memorial Polytechnic</p>
                    </div>
                </div>
                
                <p class="mb-6 text-sm leading-relaxed text-gray-600 dark:text-slate-400">
                    Add the system to your home screen for a faster, app-like experience and offline access to recent pages.
                </p>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button @click="show = false; installApp()" class="flex-1 bg-[#8B0000] hover:bg-[#6B0000] text-white font-bold py-3 px-6 rounded-xl transition-colors shadow-lg">
                        Install
                    </button>
                    <button @click="show = false; dismissInstall()" class="flex-1 rounded-xl bg-gray-100 px-6 py-3 font-semibold text-gray-700 transition-colors hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                        Not now
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @stack('scripts')

    <script>
        window.mmpGuestShell = function () {
            return {
                canInstall: false,
                isStandalone: window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true,
                installDismissed: localStorage.getItem('mmp.install.dismissed') === '1',
                effectiveTheme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                init() {
                    if (window.mmpTheme) {
                        this.effectiveTheme = window.mmpTheme.applyTheme(localStorage.getItem('mmp.theme') || 'system');
                    }

                    window.addEventListener('mmp:theme-changed', (event) => {
                        this.effectiveTheme = event.detail.theme;
                    });

                    if (window.mmpPwa) {
                        window.mmpPwa.subscribe((state) => {
                            this.canInstall = state.canInstall;
                            this.isStandalone = state.isInstalled;
                        });
                    }

                    const installModal = document.getElementById('install-modal');
                    if (installModal && !this.installDismissed && !this.isStandalone) {
                        setTimeout(() => {
                            if (installModal.__x && this.canInstall) {
                                installModal.__x.$data.show = true;
                            }
                        }, 1800);
                    }
                },
                toggleTheme() {
                    if (!window.mmpTheme) {
                        return;
                    }

                    this.effectiveTheme = window.mmpTheme.toggle();
                },
                async installApp() {
                    if (!window.mmpPwa) {
                        return;
                    }

                    const installModal = document.getElementById('install-modal');
                    await window.mmpPwa.prompt();
                    if (installModal && installModal.__x) {
                        installModal.__x.$data.show = false;
                    }
                    localStorage.setItem('mmp.install.dismissed', '1');
                    this.installDismissed = true;
                },
                dismissInstall() {
                    const installModal = document.getElementById('install-modal');
                    if (installModal && installModal.__x) {
                        installModal.__x.$data.show = false;
                    }
                    localStorage.setItem('mmp.install.dismissed', '1');
                    this.installDismissed = true;
                }
            };
        };
    </script>

    <script>
        (function () {
            const installModal = document.getElementById('install-modal');
            if (!installModal) {
                return;
            }

            window.installApp = () => window.mmpPwa?.prompt();
            window.dismissInstall = () => localStorage.setItem('mmp.install.dismissed', '1');
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
