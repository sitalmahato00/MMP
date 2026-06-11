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
    $designation = null;
    if ($user->hasRole('hod')) {
        $department  = $user->hodDepartment;
        $designation = 'Head of Department';
    } elseif ($user->hasRole('teacher') && $user->teacher) {
        $department  = $user->teacher->department;
        $designation = $user->teacher->designation ?? 'Teacher';
    } elseif ($user->hasRole('student') && $user->student) {
        $department  = $user->student->program->department ?? null;
        $designation = 'Student';
    }

    // Site settings (cached in layout already, re-use)
    $siteSettings = \Illuminate\Support\Facades\Cache::remember('public:site_settings', 600, function () {
        \App\Models\SiteSetting::ensureDefaults();
        return \App\Models\SiteSetting::all()->pluck('value', 'key')->toArray();
    });
    $collegeName   = $siteSettings['college_name'] ?? config('app.name');
    $collegeTagline = $siteSettings['college_tagline'] ?? 'College Administration Management System';
    $brandLogoUrl  = route('public.brand-logo') . '?v=' . logoVersion();

    // Current Nepali date
    $todayBs = bsDate(now(), 'F d, Y');

    // Active session (shared via middleware)
    $navActiveSession = $activeSession ?? null;
@endphp

<header class="fixed inset-x-0 top-0 z-40 flex h-16 items-center justify-between gap-4 px-4 sm:px-5 lg:px-6"
        style="background-color: #0B2E6B; border-bottom: 1px solid rgba(255,255,255,0.10);">

    {{-- ── LEFT: Brand ─────────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 min-w-0">
        <!-- Mobile menu button -->
        <button @click="sidebarOpen = !sidebarOpen"
                class="flex-shrink-0 rounded p-1.5 text-blue-200 transition-colors hover:bg-white/10 hover:text-white lg:hidden">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <!-- Logo + Name -->
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 min-w-0">
            <div class="flex-shrink-0 h-9 w-9 flex items-center justify-center rounded"
                 style="background-color: #fff; border: 1.5px solid rgba(255,255,255,0.3);">
                <img src="{{ $brandLogoUrl }}" alt="Logo" class="h-7 w-7 rounded object-cover">
            </div>
            <div class="hidden sm:flex flex-col min-w-0">
                <span class="text-sm font-bold text-white leading-tight truncate">{{ $collegeName }}</span>
                <span class="text-[10px] font-normal text-blue-200 leading-tight truncate">{{ $collegeTagline }}</span>
            </div>
        </a>
    </div>

    {{-- ── CENTER: Session badge ────────────────────────────────────── --}}
    <div class="hidden md:flex items-center justify-center flex-shrink-0">
        @if($navActiveSession)
            <span class="inline-flex items-center gap-1.5 rounded px-3.5 py-1.5 text-sm font-semibold"
                  style="background-color: rgba(255,255,255,0.12); color: #ffffff; border: 1px solid rgba(255,255,255,0.2);">
                Session {{ $navActiveSession->name }}
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 rounded px-3.5 py-1.5 text-sm font-semibold"
                  style="background-color: rgba(255,255,255,0.08); color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.12);">
                No Active Session
            </span>
        @endif
    </div>

    {{-- ── RIGHT: Date / Lang / Actions / User ─────────────────────── --}}
    <div class="flex items-center gap-1 sm:gap-2 flex-shrink-0">

        <!-- Date (Nepali) -->
        <div class="hidden lg:flex items-center gap-1.5 text-xs text-blue-200 mr-1">
            <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="font-medium text-white">{{ $todayBs }}</span>
        </div>

        <!-- Theme Toggle -->
        <button type="button" @click="toggleTheme()"
                class="inline-flex h-8 w-8 items-center justify-center rounded text-blue-200 transition-colors hover:bg-white/10 hover:text-white">
            <svg x-show="effectiveTheme !== 'dark'" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20.354 15.354A9 9 0 018.646 3.646 9 9 0 1012 21a8.96 8.96 0 008.354-5.646z"/>
            </svg>
            <svg x-show="effectiveTheme === 'dark'" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v2.25M12 18.75V21m9-9h-2.25M5.25 12H3m15.364 6.364l-1.591-1.591M7.227 7.227L5.636 5.636m12.728 0l-1.591 1.591M7.227 16.773l-1.591 1.591M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
            </svg>
        </button>

        <!-- Notifications -->
        <div class="relative" x-data="{ notificationsOpen: false }">
            <button @click="notificationsOpen = !notificationsOpen"
                    @click.away="notificationsOpen = false"
                    class="relative inline-flex h-8 w-8 items-center justify-center rounded text-blue-200 transition-colors hover:bg-white/10 hover:text-white focus:outline-none">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                @if($unreadNotificationCount > 0)
                    <span class="absolute -right-0.5 -top-0.5 inline-flex min-h-[1.1rem] min-w-[1.1rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">
                        {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                    </span>
                @endif
            </button>

            <!-- Notifications Panel -->
            <div x-show="notificationsOpen"
                 x-transition.opacity.duration.150ms
                 class="absolute right-0 mt-2 w-80 overflow-hidden bg-white shadow-xl"
                 style="border: 1px solid #DCE3EB; border-radius: 4px;"
                 x-cloak>
                <div class="flex items-center justify-between px-4 py-3" style="border-bottom: 1px solid #F0F3F7;">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Notifications</p>
                        <p class="text-xs text-gray-500">{{ $unreadNotificationCount }} unread</p>
                    </div>
                    <a href="{{ route('notifications.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">View all</a>
                </div>
                @if($headerNotifications->isEmpty())
                    <div class="px-5 py-8 text-center">
                        <div class="mx-auto flex h-10 w-10 items-center justify-center rounded" style="background-color: #F4F7FB;">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                        <p class="mt-3 text-sm font-semibold text-gray-800">No notifications</p>
                        <p class="mt-1 text-xs text-gray-500">Official updates will appear here.</p>
                    </div>
                @else
                    <div class="max-h-[24rem] divide-y overflow-y-auto" style="border-color: #F0F3F7;">
                        @foreach($headerNotifications as $notification)
                            @php
                                $nTitle   = data_get($notification->data, 'title', 'Notification');
                                $nMessage = data_get($notification->data, 'message', '');
                                $nUnread  = is_null($notification->read_at);
                            @endphp
                            <a href="{{ route('notifications.open', $notification) }}"
                               class="block px-4 py-3 transition hover:bg-gray-50 {{ $nUnread ? 'bg-blue-50/40' : '' }}">
                                <div class="flex items-start gap-3">
                                    <span class="mt-1.5 h-2 w-2 flex-shrink-0 rounded-full {{ $nUnread ? 'bg-blue-500' : 'bg-gray-300' }}"></span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-gray-900">{{ $nTitle }}</p>
                                        @if($nMessage)
                                            <p class="mt-1 text-xs text-gray-600">{{ \Illuminate\Support\Str::limit($nMessage, 80) }}</p>
                                        @endif
                                        <p class="mt-1.5 text-[11px] text-gray-400">{{ $notification->created_at?->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
                <div class="px-4 py-2.5" style="border-top: 1px solid #F0F3F7; background-color: #F8FAFC;">
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="text-xs font-medium text-gray-600 hover:text-gray-900">Mark all as read</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Divider -->
        <span class="h-5 w-px hidden sm:block mx-0.5" style="background-color: rgba(255,255,255,0.15);"></span>

        <!-- User Dropdown -->
        <div class="relative" x-data="{ userMenuOpen: false }">
            <button @click="userMenuOpen = !userMenuOpen"
                    @click.away="userMenuOpen = false"
                    class="flex items-center gap-2 rounded px-2 py-1.5 transition-colors hover:bg-white/10 focus:outline-none">
                <img src="{{ auth()->user()->avatar_url }}"
                     alt="{{ auth()->user()->name }}"
                     class="h-7 w-7 rounded-full object-cover flex-shrink-0"
                     style="border: 1.5px solid rgba(255,255,255,0.35);">
                <div class="hidden md:flex flex-col items-start">
                    <span class="text-sm font-semibold text-white leading-tight">{{ auth()->user()->name }}</span>
                    <span class="text-[10px] text-blue-200 capitalize leading-tight">{{ auth()->user()->roles->pluck('name')->first() ?? 'User' }}</span>
                </div>
                <svg class="h-3 w-3 text-blue-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <!-- User Dropdown Panel -->
            <div x-show="userMenuOpen"
                 x-transition.opacity.duration.150ms
                 class="absolute right-0 z-50 mt-2 w-64 overflow-hidden bg-white shadow-xl"
                 style="border: 1px solid #DCE3EB; border-radius: 4px;"
                 x-cloak>

                <!-- Header -->
                <div class="px-4 py-3" style="background-color: #F4F7FB; border-bottom: 1px solid #DCE3EB;">
                    <div class="flex items-center gap-3">
                        <img src="{{ auth()->user()->avatar_url }}"
                             alt="{{ auth()->user()->name }}"
                             class="h-10 w-10 rounded-full object-cover flex-shrink-0"
                             style="border: 2px solid #DCE3EB;">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-1">
                        @foreach(auth()->user()->roles as $role)
                            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide"
                                  style="background-color: #DBEAFE; color: #1e40af; border-radius: 3px;">
                                {{ $role->name }}
                            </span>
                        @endforeach
                        @if($department)
                            <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-semibold"
                                  style="background-color: #D1FAE5; color: #065f46; border-radius: 3px;">
                                {{ $department->name }}
                            </span>
                        @endif
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

                <a href="{{ $settingsRoute }}"
                   class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 transition-colors hover:bg-gray-50 hover:text-blue-700">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    My Profile
                </a>
                <a href="{{ $settingsRoute }}"
                   class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 transition-colors hover:bg-gray-50 hover:text-blue-700">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Settings
                </a>

                <div style="border-top: 1px solid #F0F3F7;"></div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex w-full items-center gap-2.5 px-4 py-2.5 text-sm transition-colors hover:bg-red-50"
                            style="color: #dc2626;">
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
