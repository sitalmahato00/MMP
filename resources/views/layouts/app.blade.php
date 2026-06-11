<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">
    <link rel="manifest" href="{{ asset('manifest.json') }}?v=4">
    <meta name="application-name" content="{{ config('app.name', 'Manmohan Memorial Polytechnic') }}">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Manmohan Memorial Polytechnic') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="#8B0000" id="app-theme-color">
    <link rel="icon" href="{{ route('public.brand-logo') }}?v={{ logoVersion() }}">
    <link rel="shortcut icon" href="{{ route('public.brand-logo') }}?v={{ logoVersion() }}">
    <link rel="apple-touch-icon" href="{{ route('public.brand-logo') }}?v={{ logoVersion() }}">

    @php
        use App\Models\SiteSetting;
        use Illuminate\Support\Facades\Cache;

        $siteSettings = Cache::remember('public:site_settings', 600, function () {
            SiteSetting::ensureDefaults();
            return SiteSetting::all()->pluck('value', 'key')->toArray();
        });

        $collegeName = $siteSettings['college_name'] ?? config('app.name');
        $siteUrl = config('app.url') ?: url('/');
        $logoUrl = route('public.brand-logo') . '?v=' . logoVersion();
        $contactPhone = $siteSettings['contact_phone'] ?? null;
        $contactAddress = $siteSettings['contact_address'] ?? null;
        $mapsIframe = $siteSettings['google_maps_iframe'] ?? null;

        $socialKeys = ['facebook_url','twitter_url','instagram_url','youtube_url','linkedin_url'];
        $sameAs = [];
        foreach ($socialKeys as $k) {
            if (! empty($siteSettings[$k])) {
                $sameAs[] = $siteSettings[$k];
            }
        }
    @endphp

    <meta name="description" content="Official website of {{ $collegeName }}.">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph -->
    <meta property="og:site_name" content="Manmohan Memorial Polytechnic">
    <meta property="og:title" content="@yield('title', 'Manmohan Memorial Polytechnic') | Manmohan Memorial Polytechnic">
    <meta property="og:description" content="Official portal of Manmohan Memorial Polytechnic — {{ $collegeName }}.">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ $logoUrl }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@mmp_edu_np">
    <meta name="twitter:title" content="Manmohan Memorial Polytechnic">
    <meta name="twitter:description" content="Official portal of Manmohan Memorial Polytechnic — {{ $collegeName }}.">
    <meta name="twitter:image" content="{{ $logoUrl }}">

    <!-- Structured data (JSON-LD) — CollegeOrUniversity + Organization -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": ["CollegeOrUniversity", "EducationalOrganization"],
      "@id": "{{ rtrim($siteUrl, '/') }}/#organization",
      "name": "Manmohan Memorial Polytechnic",
      "alternateName": "MMP",
      "url": "{{ rtrim($siteUrl, '/') }}",
      "logo": {
        "@type": "ImageObject",
        "url": "{{ $logoUrl }}",
        "caption": "Manmohan Memorial Polytechnic Logo"
      },
      "foundingDate": "2008"
      @if(!empty($contactPhone)),
      "telephone": "{{ $contactPhone }}"
      @endif
      @if(!empty($contactAddress)),
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "{{ addslashes($contactAddress) }}",
        "addressLocality": "Morang",
        "addressRegion": "Koshi Province",
        "addressCountry": "NP"
      }
      @endif
      @if(!empty($sameAs)),
      "sameAs": {!! json_encode($sameAs) !!}
      @endif
    }
    </script>

    <title>@yield('title', 'MMP CMS') | Manmohan Memorial Polytechnic</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
@php
    $user = auth()->user();
    $routeOrHash = function (array|string $names): string {
        foreach ((array) $names as $name) {
            if (\Illuminate\Support\Facades\Route::has($name)) {
                return route($name);
            }
        }

        return '#';
    };
    $isActive = fn (array|string $patterns): bool => request()->routeIs((array) $patterns);
    $currentTitle = trim($__env->yieldContent('title', 'Dashboard')) ?: 'Dashboard';
    $hasNotificationsTable = \Illuminate\Support\Facades\Schema::hasTable('notifications');
    $unreadCount = $hasNotificationsTable ? $user->unreadNotifications()->count() : 0;

    $portalLabel = match (true) {
        $user?->isPrincipal() => 'Admin Portal',
        $user?->isHod() => 'HOD Portal',
        $user?->isTeacher() => 'Teacher Portal',
        $user?->isStudent() => 'Student Portal',
        $user?->isParent() => 'Parent Portal',
        $user?->isAlumni() => 'Alumni Portal',
        default => 'Portal',
    };

    $iconPaths = [
        'home' => 'M3 10.5L12 3l9 7.5V20a1 1 0 01-1 1h-5.5v-6h-5v6H4a1 1 0 01-1-1v-9.5z',
        'users' => 'M17 20h5v-1a4 4 0 00-5.356-3.773M17 20H7m10 0v-1c0-.653-.126-1.278-.356-1.853M7 20H2v-1a4 4 0 015.356-3.773M7 20v-1c0-.653.126-1.278.356-1.853m0 0a5 5 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
        'book' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        'bell' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
        'download' => 'M4 16.5v1A2.5 2.5 0 006.5 20h11a2.5 2.5 0 002.5-2.5v-1M12 4v10m0 0l4-4m-4 4l-4-4',
        'chart' => 'M7 20V10m5 10V4m5 16v-6',
        'cog' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
        'profile' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        'id-card' => 'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c0 1.306-2.566 2-4 2',
    ];

    $mobileNavItems = match (true) {
        $user?->isPrincipal() => [
            ['label' => 'Dashboard', 'href' => $routeOrHash('admin.dashboard'), 'icon' => 'home', 'active' => $isActive('admin.dashboard')],
            ['label' => 'Users', 'href' => $routeOrHash('admin.users.index'), 'icon' => 'users', 'active' => $isActive('admin.users.*')],
            ['label' => 'Notices', 'href' => $routeOrHash('admin.notices.index'), 'icon' => 'bell', 'active' => $isActive('admin.notices.*')],
            ['label' => 'Resources', 'href' => $routeOrHash(['admin.resources.index', 'admin.downloads.index']), 'icon' => 'download', 'active' => $isActive(['admin.resources.*', 'admin.downloads.*'])],
            ['label' => 'Settings', 'href' => $routeOrHash('admin.settings.index'), 'icon' => 'cog', 'active' => $isActive('admin.settings.*')],
        ],
        $user?->isHod() => [
            ['label' => 'Dashboard', 'href' => $routeOrHash('hod.dashboard'), 'icon' => 'home', 'active' => $isActive('hod.dashboard')],
            ['label' => 'Students', 'href' => $routeOrHash(['hod.students.index', 'hod.dashboard']), 'icon' => 'users', 'active' => $isActive('hod.students.*')],
            ['label' => 'Notices', 'href' => $routeOrHash(['hod.notices.index', 'hod.dashboard']), 'icon' => 'bell', 'active' => $isActive('hod.notices.*')],
            ['label' => 'Resources', 'href' => $routeOrHash(['hod.downloads.index', 'hod.dashboard']), 'icon' => 'download', 'active' => $isActive('hod.downloads.*')],
            ['label' => 'Settings', 'href' => $routeOrHash(['hod.settings.index', 'hod.dashboard']), 'icon' => 'cog', 'active' => $isActive('hod.settings.*')],
        ],
        $user?->isTeacher() => [
            ['label' => 'Dashboard', 'href' => $routeOrHash('teacher.dashboard'), 'icon' => 'home', 'active' => $isActive('teacher.dashboard')],
            ['label' => 'Classes', 'href' => $routeOrHash(['teacher.classes.index', 'teacher.dashboard']), 'icon' => 'book', 'active' => $isActive(['teacher.classes.*', 'teacher.students.*', 'teacher.attendance.*'])],
            ['label' => 'Notices', 'href' => $routeOrHash(['teacher.notices.index', 'teacher.dashboard']), 'icon' => 'bell', 'active' => $isActive(['teacher.notices.*', 'teacher.news-events.*'])],
            ['label' => 'Resources', 'href' => $routeOrHash(['teacher.downloads.index', 'teacher.dashboard']), 'icon' => 'download', 'active' => $isActive('teacher.downloads.*')],
            ['label' => 'Profile', 'href' => $routeOrHash(['teacher.settings.index', 'teacher.profile.show', 'teacher.dashboard']), 'icon' => 'profile', 'active' => $isActive(['teacher.settings.*', 'teacher.profile.*'])],
        ],
        $user?->isStudent() => [
            ['label' => 'Home', 'href' => $routeOrHash('student.dashboard'), 'icon' => 'home', 'active' => $isActive('student.dashboard')],
            ['label' => 'Notices', 'href' => $routeOrHash(['student.notices.index', 'student.dashboard']), 'icon' => 'bell', 'active' => $isActive(['student.notices.*', 'student.news-events.*'])],
            ['label' => 'Resources', 'href' => $routeOrHash(['student.downloads.index', 'student.dashboard']), 'icon' => 'download', 'active' => $isActive('student.downloads.*')],
            ['label' => 'Results', 'href' => $routeOrHash(['student.marks.index', 'student.dashboard']), 'icon' => 'chart', 'active' => $isActive('student.marks.*')],
            ['label' => 'ID Card', 'href' => $routeOrHash(['student.id-card.index', 'student.dashboard']), 'icon' => 'id-card', 'active' => $isActive('student.id-card.*')],
            ['label' => 'Profile', 'href' => $routeOrHash(['student.settings.index', 'student.profile.show', 'student.dashboard']), 'icon' => 'profile', 'active' => $isActive(['student.settings.*', 'student.profile.*'])],
        ],
        $user?->isParent() => [
            ['label' => 'Dashboard', 'href' => $routeOrHash('parent.dashboard'), 'icon' => 'home', 'active' => $isActive('parent.dashboard')],
            ['label' => 'Children', 'href' => $routeOrHash(['parent.subjects.index', 'parent.dashboard']), 'icon' => 'users', 'active' => $isActive(['parent.subjects.*', 'parent.child.*', 'parent.attendance.*', 'parent.assignments.*'])],
            ['label' => 'Notices', 'href' => $routeOrHash(['parent.notices.index', 'parent.dashboard']), 'icon' => 'bell', 'active' => $isActive(['parent.notices.*', 'parent.news-events.*'])],
            ['label' => 'Results', 'href' => $routeOrHash(['parent.results.index', 'parent.dashboard']), 'icon' => 'chart', 'active' => $isActive('parent.results.*')],
            ['label' => 'Profile', 'href' => $routeOrHash(['parent.settings.index', 'parent.dashboard']), 'icon' => 'profile', 'active' => $isActive('parent.settings.*')],
        ],
        default => [
            ['label' => 'Dashboard', 'href' => $routeOrHash('dashboard'), 'icon' => 'home', 'active' => $isActive('dashboard')],
            ['label' => 'Notices', 'href' => $routeOrHash('notifications.index'), 'icon' => 'bell', 'active' => $isActive('notifications.*')],
            ['label' => 'Profile', 'href' => '#', 'icon' => 'profile', 'active' => false],
        ],
    };
@endphp
<body class="h-full overflow-x-hidden antialiased" style="background-color: #F4F7FB;" x-data="mmpAppShell()" x-init="init()"
      :class="sidebarOpen ? 'overflow-hidden' : ''">

    {{-- Mobile sidebar overlay — semi-transparent, correct color --}}
    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[49] lg:hidden"
         style="background-color: rgba(11,46,107,0.45);"
         x-cloak
         @click="sidebarOpen = false"></div>

    {{-- PWA install prompt --}}
    <div x-show="!isStandalone && !installDismissed" x-cloak class="fixed bottom-24 inset-x-4 z-50 flex justify-center lg:inset-x-auto lg:right-6 lg:bottom-6">
        <div class="w-full max-w-md bg-white p-4 shadow-xl" style="border: 1px solid #DCE3EB; border-radius: 4px;">
            <div class="flex items-center gap-3">
                <img src="{{ route('public.brand-logo') }}?v={{ logoVersion() }}" alt="MMP logo" class="h-12 w-12 shrink-0 rounded object-contain" style="border: 1px solid #DCE3EB;">
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-gray-900">Install MMP App</p>
                    <p class="text-xs text-gray-500 mt-0.5">Faster access · Offline support · Notifications</p>
                </div>
            </div>
            <div class="mt-3 flex items-center justify-end gap-2">
                <button type="button" @click="dismissInstall()" class="px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:text-gray-900" style="border: 1px solid #DCE3EB; border-radius: 3px;">Not now</button>
                <button type="button" @click="installApp()" class="px-4 py-1.5 text-xs font-semibold text-white transition" style="background: #1D4ED8; border-radius: 3px;">Download</button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         FIXED TOP NAVBAR — full viewport width, z-30
    ══════════════════════════════════════════════════════════ --}}
    <x-navbar />

    {{-- ══════════════════════════════════════════════════════════
         PAGE BODY — sidebar + content, starts below navbar
    ══════════════════════════════════════════════════════════ --}}
    <div class="flex" style="padding-top: 64px; min-height: calc(100vh - 64px);">

        {{-- SIDEBAR --}}
        <x-sidebar />

        {{-- MAIN CONTENT --}}
        <div class="flex min-w-0 flex-1 flex-col">

            {{-- Mobile header (only on small screens, fixed) --}}
            <header class="fixed inset-x-0 top-0 z-[35] flex h-14 items-center justify-between px-4 lg:hidden"
                    style="background-color: #0B2E6B; border-bottom: 1px solid rgba(255,255,255,0.12);">
                <div class="flex items-center gap-3 min-w-0">
                    <button type="button" @click="sidebarOpen = true"
                            class="inline-flex h-9 w-9 items-center justify-center rounded text-blue-200 hover:bg-white/10 hover:text-white flex-shrink-0">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div class="min-w-0">
                        <p class="truncate text-[10px] font-medium uppercase tracking-[0.18em] text-blue-200">{{ $portalLabel }}</p>
                        <p class="truncate text-sm font-semibold text-white">{{ $currentTitle }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button type="button" @click="toggleTheme()" class="inline-flex h-9 w-9 items-center justify-center rounded text-blue-200 hover:bg-white/10">
                        <svg x-show="effectiveTheme !== 'dark'" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20.354 15.354A9 9 0 018.646 3.646 9 9 0 1012 21a8.96 8.96 0 008.354-5.646z"/></svg>
                        <svg x-show="effectiveTheme === 'dark'" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v2.25M12 18.75V21m9-9h-2.25M5.25 12H3m15.364 6.364l-1.591-1.591M7.227 7.227L5.636 5.636m12.728 0l-1.591 1.591M7.227 16.773l-1.591 1.591M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/></svg>
                    </button>
                    <a href="{{ route('notifications.index') }}" class="relative inline-flex h-9 w-9 items-center justify-center rounded text-blue-200 hover:bg-white/10">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        @if($unreadCount > 0)
                            <span class="absolute -right-0.5 -top-0.5 inline-flex min-h-[1rem] min-w-[1rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                        @endif
                    </a>
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 overflow-x-hidden px-4 py-5 pt-16 lg:px-6 lg:py-6 lg:pt-6 pb-24 lg:pb-6"
                  style="background-color: #F4F7FB; min-height: 100%;">
                <div class="mx-auto w-full max-w-full">
                    @if (session('success'))
                        <x-alert type="success" :message="session('success')" class="mb-5" />
                    @endif
                    @if (session('error'))
                        @php
                            $showError = true;
                            if (str_contains(session('error'), 'department is assigned') && auth()->check() && auth()->user()->hasRole('hod')) {
                                $dept = \App\Models\Department::where('hod_id', auth()->id())->first();
                                if ($dept) { $showError = false; }
                            }
                        @endphp
                        @if($showError)
                            <x-alert type="error" :message="session('error')" class="mb-5" />
                        @endif
                    @endif

                    @yield('content')
                    {{ $slot ?? '' }}
                </div>
            </main>
        </div>
    </div>

    {{-- Mobile bottom nav --}}
    <nav class="fixed inset-x-0 bottom-0 z-30 lg:hidden" style="background-color: #0B2E6B; border-top: 1px solid rgba(255,255,255,0.12);">
        <div class="flex items-stretch justify-around" style="padding-bottom: max(0.5rem, env(safe-area-inset-bottom)); padding-top: 0.375rem;">
            @foreach($mobileNavItems as $item)
                <a href="{{ $item['href'] }}"
                   class="flex flex-col items-center justify-center gap-0.5 flex-1 px-1 py-1 text-center"
                   style="{{ $item['active'] ? 'color: #ffffff; background-color: rgba(255,255,255,0.12); border-radius: 4px;' : 'color: rgba(255,255,255,0.55);' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        @if($item['icon'] === 'cog')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $iconPaths[$item['icon']] }}"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $iconPaths[$item['icon']] ?? $iconPaths['home'] }}"/>
                        @endif
                    </svg>
                    <span class="truncate text-[10px] font-medium">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>

    <script>
        window.mmpAppShell = function () {
            return {
                sidebarOpen: false,
                sidebarCollapsed: false,
                canInstall: false,
                isStandalone: window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true,
                installDismissed: localStorage.getItem('mmp.install.dismissed.v2') === '1',
                effectiveTheme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                installHelpVisible: false,
                installHelpMessage: '',
                installHelpTimer: null,
                get isMobile() {
                    return window.innerWidth < 1024;
                },
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

                    // On mobile, always keep sidebar expanded (not collapsed)
                    const ensureExpanded = () => {
                        if (window.innerWidth < 1024) {
                            this.sidebarCollapsed = false;
                        }
                    };
                    ensureExpanded();
                    window.addEventListener('resize', ensureExpanded);
                },
                toggleSidebarCollapse() {
                    // Collapse only allowed on desktop
                    if (window.innerWidth >= 1024) {
                        this.sidebarCollapsed = !this.sidebarCollapsed;
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

                    const prompted = await window.mmpPwa.prompt();
                    if (!prompted) {
                        this.showInstallHelp(window.mmpPwa.manualInstallMessage());
                    }
                },
                dismissInstall() {
                    localStorage.setItem('mmp.install.dismissed.v2', '1');
                    this.installDismissed = true;
                    this.installHelpVisible = false;
                },
                showInstallHelp(message) {
                    this.installHelpMessage = message;
                    this.installHelpVisible = true;

                    if (this.installHelpTimer) {
                        clearTimeout(this.installHelpTimer);
                    }

                    this.installHelpTimer = setTimeout(() => {
                        this.installHelpVisible = false;
                    }, 5000);
                }
            };
        };

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch((error) => {
                    console.log('SW registration failed', error);
                });
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
