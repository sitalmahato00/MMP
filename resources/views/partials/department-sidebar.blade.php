{{--
    Department Sidebar Partial
    Usage: @include('partials.department-sidebar', ['department' => $department, 'activePage' => 'overview'])
    activePage: overview | about | notices | people | programs | gallery | downloads | contact
--}}
@php
    $deptSlug   = $department->slug;
    $activePage = $activePage ?? 'overview';

    $navItems = [
        ['key' => 'overview',  'label' => 'Overview',         'icon' => 'home',     'route' => route('public.department.show', $deptSlug)],
        ['key' => 'about',     'label' => 'About Department', 'icon' => 'info',     'route' => route('public.department.about', $deptSlug)],
        ['key' => 'programs',  'label' => 'Programs',         'icon' => 'book',     'route' => route('public.department.programs', $deptSlug)],
        ['key' => 'notices',   'label' => 'Notices',          'icon' => 'bell',     'route' => route('public.department.notices', $deptSlug)],
        ['key' => 'gallery',   'label' => 'Gallery',          'icon' => 'photo',    'route' => route('public.department.gallery', $deptSlug)],
        ['key' => 'downloads', 'label' => 'Downloads',        'icon' => 'download', 'route' => route('public.downloads', ['department' => $department->code])],
        ['key' => 'contact',   'label' => 'Contact',          'icon' => 'mail',     'route' => route('public.contact')],
    ];

    $icons = [
        'home'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
        'info'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'users'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',
        'book'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>',
        'bell'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',
        'photo'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
        'download' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>',
        'mail'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
    ];
@endphp

{{-- ── DESKTOP SIDEBAR ─────────────────────────────────────── --}}
<aside class="hidden lg:block self-start">

    {{-- 
        sticky top-[64px]: navbar is ~48px + 16px gap = 64px offset.
        The aside MUST have self-start (align-self: start) on the grid column.
        No overflow:hidden on this element or any ancestor for sticky to work.
    --}}
    <div class="sticky space-y-4" style="top: 68px;">

        {{-- Department Navigation --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 flex items-center gap-2" style="background-color: #003D82;">
                <svg class="w-4 h-4 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <span class="text-white font-bold text-sm">Department Navigation</span>
            </div>
            <nav class="divide-y divide-gray-100">
                @foreach($navItems as $item)
                    <a href="{{ $item['route'] }}"
                       class="flex items-center gap-3 px-4 py-2.5 text-sm transition-all duration-150 group
                              {{ $activePage === $item['key']
                                  ? 'bg-blue-50 text-[#003D82] font-semibold border-l-4 border-[#003D82]'
                                  : 'text-gray-700 hover:bg-blue-50 hover:text-[#003D82] border-l-4 border-transparent hover:border-blue-200' }}">
                        <svg class="w-4 h-4 flex-shrink-0 {{ $activePage === $item['key'] ? 'text-[#003D82]' : 'text-gray-400 group-hover:text-[#003D82]' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $icons[$item['icon']] !!}
                        </svg>
                        {{ $item['label'] }}
                        @if($activePage === $item['key'])
                            <span class="ml-auto w-1.5 h-1.5 rounded-full bg-[#003D82]"></span>
                        @endif
                    </a>
                @endforeach
            </nav>
        </div>

        {{-- Downloads (only when data exists) --}}
        @if(isset($downloads) && $downloads->count())
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3" style="background-color: #003D82;">
                <span class="text-white font-bold text-sm">Downloads</span>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($downloads->take(4) as $dl)
                <a href="{{ route('public.downloads', ['department' => $department->code]) }}"
                   class="flex items-center justify-between px-4 py-2.5 hover:bg-blue-50 transition-colors group">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <svg class="w-4 h-4 text-[#003D82] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="text-xs text-gray-700 truncate group-hover:text-[#003D82]">{{ $dl->title }}</span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Upcoming Events (only when data exists) --}}
        @if(isset($events) && $events->count())
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3" style="background-color: #003D82;">
                <span class="text-white font-bold text-sm">Upcoming Events</span>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($events as $event)
                <a href="{{ route('public.notice.show', $event->slug) }}"
                   class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition-colors group">
                    <div class="flex-shrink-0 text-center w-10">
                        <span class="block text-lg font-black leading-none text-[#003D82]">
                            {{ \Carbon\Carbon::parse($event->published_at)->format('d') }}
                        </span>
                        <span class="block text-[10px] font-bold uppercase text-gray-500">
                            {{ \Carbon\Carbon::parse($event->published_at)->format('M') }}
                        </span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-gray-800 group-hover:text-[#003D82] line-clamp-2">
                            {{ $event->title }}
                        </p>
                    </div>
                </a>
                @endforeach
            </div>
            <div class="px-4 py-2 border-t border-gray-100">
                <a href="{{ route('public.news-events') }}" class="text-xs font-semibold text-[#003D82] hover:underline">
                    View All Events →
                </a>
            </div>
        </div>
        @endif

    </div>{{-- end sticky wrapper --}}

</aside>

{{-- ── MOBILE COLLAPSIBLE MENU ─────────────────────────────── --}}
<div class="lg:hidden mb-4" x-data="{ mobileNavOpen: false }">
    <button @click="mobileNavOpen = !mobileNavOpen"
            class="flex w-full items-center justify-between px-4 py-3 rounded-xl text-white font-bold text-sm shadow-md"
            style="background-color: #003D82;">
        <span class="flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            Department Menu
        </span>
        <svg class="w-4 h-4 transition-transform" :class="mobileNavOpen ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="mobileNavOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="mt-2 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @foreach($navItems as $item)
            <a href="{{ $item['route'] }}"
               class="flex items-center gap-3 px-4 py-3 text-sm border-b border-gray-100 last:border-b-0 transition-colors
                      {{ $activePage === $item['key']
                          ? 'bg-blue-50 text-[#003D82] font-semibold'
                          : 'text-gray-700 hover:bg-blue-50 hover:text-[#003D82]' }}">
                <svg class="w-4 h-4 {{ $activePage === $item['key'] ? 'text-[#003D82]' : 'text-gray-400' }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {!! $icons[$item['icon']] !!}
                </svg>
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>
</div>
