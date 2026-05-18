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
    <meta property="og:site_name" content="{{ $collegeName }}">
    <meta property="og:title" content="@yield('title', $collegeName)">
    <meta property="og:description" content="Official website of {{ $collegeName }}.">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ $logoUrl }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', $collegeName)">
    <meta name="twitter:description" content="Official website of {{ $collegeName }}.">
    <meta name="twitter:image" content="{{ $logoUrl }}">

    <!-- Structured data (JSON-LD) for College/Organization -->
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "CollegeOrUniversity",
      "name": "{{ $collegeName }}",
      "url": "{{ $siteUrl }}",
      "logo": "{{ $logoUrl }}"
      @if(!empty($sameAs)),
      "sameAs": {!! json_encode($sameAs) !!}
      @endif
      @if(!empty($contactPhone)),
      "telephone": "{{ $contactPhone }}"
      @endif
      @if(!empty($contactAddress)),
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "{{ addslashes($contactAddress) }}"
      }
      @endif
    }
    </script>

    <title>@yield('title', 'MMP CMS') | {{ config('app.name') }}</title>

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
<body class="h-full overflow-x-hidden bg-gray-50 text-gray-800 antialiased transition-colors dark:bg-slate-950 dark:text-slate-100" x-data="mmpAppShell()" x-init="init()">

    <div x-show="sidebarOpen" class="fixed inset-0 z-40 bg-gray-950/65 backdrop-blur-sm lg:hidden" x-cloak @click="sidebarOpen = false"></div>

    <div x-show="!isStandalone && !installDismissed" x-cloak class="fixed inset-x-4 bottom-[calc(env(safe-area-inset-bottom)+5.75rem)] z-40 flex justify-center lg:inset-x-auto lg:right-6 lg:bottom-6">
        <div class="w-full max-w-md rounded-[26px] border border-slate-200/80 bg-white/95 p-4 shadow-[0_18px_50px_rgba(15,23,42,0.18)] backdrop-blur-xl dark:border-slate-700/80 dark:bg-slate-900/95">
            <div class="flex items-center gap-3">
                <img src="{{ route('public.brand-logo') }}?v={{ logoVersion() }}" alt="MMP logo" class="h-16 w-16 shrink-0 rounded-[20px] border border-slate-200 bg-white p-2 shadow-sm object-contain dark:border-slate-700">
                <div class="min-w-0 flex-1">
                    <p class="text-lg font-bold text-slate-900 dark:text-slate-50">Install MMP App</p>
                    <p class="mt-1 text-sm leading-5 text-slate-500 dark:text-slate-400">Faster access • Offline support • Updates &amp; notifications</p>
                    <div x-show="installHelpVisible" x-transition.opacity.duration.200ms class="mt-2 rounded-2xl bg-slate-100 px-3 py-2 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        <span x-text="installHelpMessage"></span>
                    </div>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-end gap-3">
                <button type="button" @click="dismissInstall()" class="inline-flex items-center rounded-2xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                    Not now
                </button>
                <button type="button" @click="installApp()" class="inline-flex items-center rounded-2xl bg-[#2563eb] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-900/20 transition hover:bg-[#1d4ed8]">
                    Download
                </button>
            </div>
        </div>
    </div>

    <div class="flex h-full w-full overflow-x-hidden">
        <x-sidebar />

        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
            <header class="fixed inset-x-0 top-0 z-30 border-b border-slate-200/80 bg-white/95 shadow-sm backdrop-blur lg:hidden dark:border-slate-800 dark:bg-slate-950/95">
                <div class="flex items-center justify-between gap-3 px-4 pb-3 pt-[max(0.75rem,env(safe-area-inset-top))]">
                    <div class="flex min-w-0 items-center gap-3">
                        <button type="button" @click="sidebarOpen = true" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <div class="min-w-0">
                            <p class="truncate text-[10px] font-semibold uppercase tracking-[0.22em] text-slate-400">{{ $portalLabel }}</p>
                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-slate-50">{{ $currentTitle }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="toggleTheme()" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                            <svg x-show="effectiveTheme !== 'dark'" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20.354 15.354A9 9 0 018.646 3.646 9 9 0 1012 21a8.96 8.96 0 008.354-5.646z"/>
                            </svg>
                            <svg x-show="effectiveTheme === 'dark'" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v2.25M12 18.75V21m9-9h-2.25M5.25 12H3m15.364 6.364l-1.591-1.591M7.227 7.227L5.636 5.636m12.728 0l-1.591 1.591M7.227 16.773l-1.591 1.591M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
                            </svg>
                        </button>
                        <a href="{{ route('notifications.index') }}" class="relative inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @if($unreadCount > 0)
                                <span class="absolute -right-1 -top-1 inline-flex min-h-[1.25rem] min-w-[1.25rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">
                                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                </span>
                            @endif
                        </a>
                    </div>
                </div>
            </header>

            <div class="hidden lg:block">
                <x-navbar />
            </div>

            <main class="min-w-0 flex-1 overflow-y-auto overflow-x-hidden px-3 pb-[calc(env(safe-area-inset-bottom)+6rem)] pt-[calc(env(safe-area-inset-top)+5.5rem)] sm:px-4 lg:p-8">
                <div class="mx-auto w-full max-w-full">
                    @if (session('success'))
                        <x-alert type="success" :message="session('success')" class="mb-6" />
                    @endif

                    @if (session('error'))
                        @php
                            $showError = true;
                            if (str_contains(session('error'), 'department is assigned') &&
                                auth()->check() &&
                                auth()->user()->hasRole('hod')) {
                                $dept = \App\Models\Department::where('hod_id', auth()->id())->first();
                                if ($dept) {
                                    $showError = false;
                                }
                            }
                        @endphp
                        @if($showError)
                            <x-alert type="error" :message="session('error')" class="mb-6" />
                        @endif
                    @endif

                    @yield('content')

                    {{ $slot ?? '' }}
                </div>
            </main>
        </div>
    </div>

    <nav data-shell-bottom-nav class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200/80 bg-white/95 shadow-[0_-10px_30px_rgba(15,23,42,0.12)] lg:hidden dark:border-slate-800 dark:bg-slate-950/95">
        <div class="flex items-center justify-around gap-1 px-2 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-2">
            @foreach($mobileNavItems as $item)
                <a href="{{ $item['href'] }}" class="flex flex-col items-center justify-center gap-1 rounded-2xl px-2 py-2.5 text-center transition flex-1 {{ $item['active'] ? 'bg-[#8B0000]/10 text-[#8B0000] dark:bg-blue-500/15 dark:text-blue-300' : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-900' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        @if($item['icon'] === 'cog')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $iconPaths[$item['icon']] }}"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $iconPaths[$item['icon']] ?? $iconPaths['home'] }}"/>
                        @endif
                    </svg>
                    <span class="truncate text-[11px] font-semibold">{{ $item['label'] }}</span>
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
