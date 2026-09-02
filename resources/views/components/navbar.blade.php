@php
    $user = auth()->user();
    $hasNotificationsTable = \Illuminate\Support\Facades\Schema::hasTable('notifications');
    $headerNotifications = $hasNotificationsTable
        ? $user->notifications()->latest()->limit(5)->get()
        : collect();
    $unreadNotificationCount = $hasNotificationsTable
        ? $user->unreadNotifications()->count()
        : 0;

    $department  = null;
    $role = null;
    if ($user->hasRole('hod')) {
        $department  = $user->hodDepartment;
        $role = 'Head of Department';
    } elseif ($user->hasRole('teacher') && $user->teacher) {
        $department  = $user->teacher->department;
        $role = $user->teacher->designation ?? 'Teacher';
    } elseif ($user->hasRole('student') && $user->student) {
        $department  = $user->student->program->department ?? null;
        $role = 'Student';
    } elseif ($user->isPrincipal()) {
        $role = 'Principal';
    } else {
        $role = ucfirst($user->roles->pluck('name')->first() ?? 'User');
    }

    // Site settings
    $siteSettings = \Illuminate\Support\Facades\Cache::remember('public:site_settings', 600, function () {
        \App\Models\SiteSetting::ensureDefaults();
        return \App\Models\SiteSetting::all()->pluck('value', 'key')->toArray();
    });
    $academicYear = $siteSettings['academic_year'] ?? '2081';

    // Active session
    $navActiveSession = $activeSession ?? null;
    $sessionName = $navActiveSession->name ?? '2081-2082';
@endphp

<header class="sticky top-0 z-30 flex h-16 w-full items-center justify-between gap-4 px-4 sm:px-6 bg-white dark:bg-[#0D1B35] border-b-2 border-[#FF6600] shadow-xs transition-colors">

    {{-- ── LEFT: Red Hamburger + Search ─────────────────────────────── --}}
    <div class="flex items-center gap-3 sm:gap-4 min-w-0">

        <!-- Red Hamburger Button (#FF0000) -->
        <button type="button"
                @click="if (isMobile) { sidebarOpen = !sidebarOpen; } else { toggleSidebarCollapse(); }"
                class="flex-shrink-0 h-9 w-9 rounded-lg bg-[#FF0000] hover:bg-[#DC2626] active:scale-95 text-white flex items-center justify-center shadow-sm transition-all focus:outline-none"
                title="Toggle Sidebar">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <!-- Search input ("Search anything...") -->
        <div class="relative w-56 sm:w-64 md:w-72 lg:w-80" x-data="{ focused: false }">
            <input
                type="search"
                x-model="navSearch"
                @focus="focused = true; sidebarOpen = true; sidebarCollapsed = false"
                @blur="focused = false"
                @keydown.escape="navSearch = ''"
                placeholder="Search anything..."
                autocomplete="off"
                spellcheck="false"
                class="w-full rounded-lg border border-gray-200 dark:border-slate-700 bg-white dark:bg-[#132044] py-2 pl-3.5 pr-9 text-xs font-medium text-slate-900 dark:text-slate-100 placeholder-gray-400 dark:placeholder-slate-400 outline-none transition-all focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-sm"
            >
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                <svg class="h-3.5 w-3.5 text-gray-400 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <button
                x-show="navSearch !== ''"
                x-cloak
                @click="navSearch = ''"
                type="button"
                class="absolute inset-y-0 right-7 flex items-center pr-1 text-slate-400 hover:text-slate-600 transition-colors z-10">
                <svg class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- ── CENTER / BADGES: Academic Year + Session badge ──────────── --}}
    <div class="hidden md:flex items-center justify-center gap-2.5 flex-shrink-0">
        <!-- AY Badge (#0000FF) -->
        <span class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold border border-[#0000FF] bg-blue-50/60 text-[#0000FF] dark:bg-blue-950/40 dark:text-blue-400 dark:border-blue-700 shadow-sm">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            AY {{ $academicYear }}
        </span>

        <!-- Session Badge (#FF6600) -->
        <span class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-semibold border border-[#FF6600] bg-orange-50/60 text-[#FF6600] dark:bg-orange-950/40 dark:text-orange-400 dark:border-orange-700 shadow-sm">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M12 14l9-5-9-5-9 5 9 5z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
            </svg>
            Session {{ $sessionName }}
        </span>
    </div>

    {{-- ── RIGHT: Notification + Calendar + User Profile ───────────── --}}
    <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">

        <!-- Notification Bell -->
        <div class="relative" x-data="{ notificationsOpen: false }">
            <button @click="notificationsOpen = !notificationsOpen"
                    @click.away="notificationsOpen = false"
                    class="relative inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors focus:outline-none"
                    title="Notifications">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="absolute 1 top-1 right-1 inline-flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-[#FF0000] px-1 text-[9px] font-bold text-white shadow-sm">
                    {{ $unreadNotificationCount > 0 ? ($unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount) : '3' }}
                </span>
            </button>

            <!-- Notifications Dropdown Panel (positioned properly below header) -->
            <div x-show="notificationsOpen"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-1"
                 class="absolute right-0 top-full mt-2 w-80 overflow-hidden bg-white dark:bg-[#132044] shadow-2xl border border-gray-200 dark:border-slate-700 rounded-xl z-50"
                 style="position: absolute; top: calc(100% + 8px); right: 0; z-index: 50;"
                 x-cloak>
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-slate-800 bg-gray-50/50 dark:bg-[#0D1B35]">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-slate-100">Notifications</p>
                        <p class="text-xs text-gray-500 dark:text-slate-400">{{ $unreadNotificationCount }} unread</p>
                    </div>
                    <a href="{{ route('notifications.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400">View all</a>
                </div>
                @if($headerNotifications->isEmpty())
                    <div class="px-5 py-8 text-center">
                        <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 dark:bg-slate-800">
                            <svg class="h-5 w-5 text-gray-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                        <p class="mt-3 text-sm font-semibold text-gray-800 dark:text-slate-200">No new notifications</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-slate-400">Updates will appear here.</p>
                    </div>
                @else
                    <div class="max-h-[24rem] divide-y divide-gray-100 dark:divide-slate-800 overflow-y-auto">
                        @foreach($headerNotifications as $notification)
                            @php
                                $nTitle   = data_get($notification->data, 'title', 'Notification');
                                $nMessage = data_get($notification->data, 'message', '');
                                $nUnread  = is_null($notification->read_at);
                            @endphp
                            <a href="{{ route('notifications.open', $notification) }}"
                               class="block px-4 py-3 transition hover:bg-gray-50 dark:hover:bg-slate-800 {{ $nUnread ? 'bg-blue-50/40 dark:bg-blue-900/20' : '' }}">
                                <div class="flex items-start gap-3">
                                    <span class="mt-1.5 h-2 w-2 flex-shrink-0 rounded-full {{ $nUnread ? 'bg-[#0000FF]' : 'bg-gray-300 dark:bg-slate-600' }}"></span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-gray-900 dark:text-slate-100">{{ $nTitle }}</p>
                                        @if($nMessage)
                                            <p class="mt-1 text-xs text-gray-600 dark:text-slate-400">{{ \Illuminate\Support\Str::limit($nMessage, 80) }}</p>
                                        @endif
                                        <p class="mt-1.5 text-[11px] text-gray-400 dark:text-slate-500">{{ $notification->created_at?->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
                <div class="px-4 py-2.5 bg-gray-50 dark:bg-[#0D1B35] border-t border-gray-100 dark:border-slate-800">
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="text-xs font-medium text-gray-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-slate-200">Mark all as read</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Calendar Button -->
        <a href="{{ \Illuminate\Support\Facades\Route::has('admin.academic-sessions.index') ? route('admin.academic-sessions.index') : '#' }}"
           class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors focus:outline-none"
           title="Calendar & Academic Sessions">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </a>

        <!-- User Profile Dropdown -->
        <div class="relative" x-data="{ userMenuOpen: false }">
            <button @click="userMenuOpen = !userMenuOpen"
                    @click.away="userMenuOpen = false"
                    class="flex items-center gap-2.5 rounded-xl px-2 py-1 transition-colors hover:bg-gray-50 dark:hover:bg-slate-800 focus:outline-none">
                <img src="{{ auth()->user()->avatar_url }}"
                     alt="{{ auth()->user()->name }}"
                     class="h-9 w-9 rounded-full object-cover flex-shrink-0 ring-2 ring-[#0000FF]">
                <div class="hidden md:flex flex-col items-start leading-tight">
                    <span class="text-xs font-bold text-gray-900 dark:text-white">{{ auth()->user()->name }}</span>
                    <span class="text-[10px] text-gray-500 dark:text-slate-400 font-medium">{{ $role }}</span>
                </div>
                <svg class="h-3.5 w-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <!-- User Menu Dropdown Panel (positioned properly below header) -->
            <div x-show="userMenuOpen"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-1"
                 class="absolute right-0 top-full mt-2 w-64 overflow-hidden bg-white dark:bg-[#132044] shadow-2xl border border-gray-200 dark:border-slate-700 rounded-xl z-50"
                 style="position: absolute; top: calc(100% + 8px); right: 0; z-index: 50;"
                 x-cloak>

                <!-- User Panel Header -->
                <div class="px-4 py-3 bg-gray-50 dark:bg-[#1a2f50] border-b border-gray-100 dark:border-slate-700">
                    <div class="flex items-center gap-3">
                        <img src="{{ auth()->user()->avatar_url }}"
                             alt="{{ auth()->user()->name }}"
                             class="h-10 w-10 rounded-full object-cover flex-shrink-0 ring-2 ring-[#0000FF]">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-slate-100 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-slate-400 truncate">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-1">
                        @foreach(auth()->user()->roles as $r)
                            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded">
                                {{ $r->name }}
                            </span>
                        @endforeach
                    </div>
                </div>

                @php
                    $settingsRoute = match(true) {
                        $user->hasRole('principal') => route('admin.settings.index'),
                        $user->hasRole('hod')       => route('hod.settings.index'),
                        $user->hasRole('teacher')   => route('teacher.settings.index'),
                        $user->hasRole('student')   => route('student.settings.index'),
                        $user->hasRole('parent')    => route('parent.settings.index'),
                        $user->hasRole('alumni')    => route('alumni.settings.index'),
                        default                     => route('admin.settings.index'),
                    };
                @endphp

                <div class="py-1">
                    <a href="{{ $settingsRoute }}"
                       class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 hover:text-blue-600 transition-colors">
                        <svg class="h-4 w-4 text-gray-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        My Profile
                    </a>
                    <a href="{{ $settingsRoute }}"
                       class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 hover:text-blue-600 transition-colors">
                        <svg class="h-4 w-4 text-gray-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Settings
                    </a>
                </div>

                <div class="border-t border-gray-100 dark:border-slate-800"></div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex w-full items-center gap-2.5 px-4 py-2.5 text-sm text-[#FF0000] hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Sign Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>


