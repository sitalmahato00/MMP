{{--
    ██████████████████████████████████████████████████████████
    UNIFIED SMART SIDEBAR  —  MMP College Management System
    A single reusable component. Renders nav sections based on
    the authenticated user's role. All logic lives here.
    ██████████████████████████████████████████████████████████
--}}

@php
    $user   = auth()->user();
    $role   = $user?->getRoleNames()->first() ?? 'guest';
    $isAdmin   = $user?->isPrincipal();
    $isHod     = $user?->isHod();
    $isTeacher = $user?->isTeacher();
    $isStudent = $user?->isStudent();
    $isParent  = $user?->isParent();
    $isAlumni  = $user?->isAlumni();

    // ── Colour accent per role ────────────────────────────
    $accent = match(true) {
        $isAdmin   => '#8B0000',   // deep crimson
        $isHod     => '#1e40af',   // royal blue
        $isTeacher => '#065f46',   // forest green
        $isStudent => '#6d28d9',   // purple
        $isParent  => '#b45309',   // amber
        $isAlumni  => '#374151',   // slate
        default    => '#374151',
    };

    $roleLabel = match(true) {
        $isAdmin   => 'Admin Portal',
        $isHod     => 'HOD Portal',
        $isTeacher => 'Teacher Portal',
        $isStudent => 'Student Portal',
        $isParent  => 'Parent Portal',
        $isAlumni  => 'Alumni Portal',
        default    => 'Portal',
    };

    // ── Helper: is current route active? ─────────────────
    $active = fn(string|array $patterns): bool =>
        request()->routeIs((array) $patterns);

    $brandLogoUrl = !empty($siteLogoPath ?? null)
        ? asset('storage/' . ltrim($siteLogoPath, '/'))
        : null;
@endphp

{{-- ─── SVG Icon Map (used throughout this file) ──────────── --}}
@php
    $icon = fn(string $name): string => match($name) {
        'home'            => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
        'calendar'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
        'building'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
        'academic-cap'    => '<path d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>',
        'users'           => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
        'user-group'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>',
        'briefcase'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
        'heart'           => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>',
        'graduation-cap'  => '<path d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>',
        'clipboard-check' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>',
        'doc-text'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
        'chart-bar'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
        'bell'            => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',
        'photo'           => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
        'download'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>',
        'shield'          => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
        'cog'             => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
        'doc-report'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
        'collection'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>',
        'external'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>',
        default           => '<circle cx="12" cy="12" r="3"/>',
    };
@endphp

{{-- ─── Reusable macros as local Blade components emulation ── --}}
{{-- We use @php inline helpers instead of sub-components --}}

<aside
    class="fixed inset-y-0 left-0 z-50 w-72 flex flex-col bg-[#0f1623] text-white
           transition-transform duration-300 ease-in-out
           lg:static lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    x-cloak>

    {{-- ── Logo & Brand ─────────────────────────────────── --}}
    <div class="flex items-center gap-3 h-16 px-5 border-b border-white/10 flex-shrink-0">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center shadow-lg flex-shrink-0"
             style="background: linear-gradient(135deg, {{ $accent }}, {{ $accent }}99);">
            @if($brandLogoUrl)
                <img src="{{ $brandLogoUrl }}" alt="MMP Logo" class="w-full h-full object-cover rounded-xl">
            @else
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {!! $icon('building') !!}
                </svg>
            @endif
        </div>
        <div class="min-w-0">
            <p class="text-sm font-bold tracking-tight text-white leading-none truncate">MMP College</p>
            <p class="text-[10px] font-semibold uppercase tracking-widest mt-0.5"
               style="color: {{ $accent }};">{{ $roleLabel }}</p>
        </div>
    </div>

    {{-- ── Scrollable Navigation ────────────────────────── --}}
    <nav class="flex-1 overflow-y-auto py-3 px-2.5 space-y-0.5 scrollbar-thin scrollbar-thumb-white/10"
         x-ref="sidenav"
         x-init="
             $nextTick(() => { $refs.sidenav.scrollTop = parseInt(localStorage.getItem('sidebar-scroll') || 0); });
             $refs.sidenav.addEventListener('scroll', () => localStorage.setItem('sidebar-scroll', $refs.sidenav.scrollTop));
         ">

        {{-- ════════════════════════════════════════════════
             ADMIN / PRINCIPAL NAV
             ════════════════════════════════════════════════ --}}
        @if($isAdmin)

            @include('components.sidebar-items.nav-link', ['href' => route('admin.dashboard'), 'iconName' => 'home',    'label' => 'Dashboard',   'isActive' => $active('admin.dashboard'), 'accent' => $accent])

            {{-- Configuration --}}
            @include('components.sidebar-items.nav-section',  ['label' => 'CONFIGURATION'])
            @include('components.sidebar-items.nav-link', ['href' => route('admin.academic-sessions.index'), 'iconName' => 'calendar',     'label' => 'Academic Sessions', 'isActive' => $active('admin.academic-sessions.*'), 'accent' => $accent])
            @include('components.sidebar-items.nav-link', ['href' => route('admin.departments.index'),       'iconName' => 'building',      'label' => 'Departments',       'isActive' => $active('admin.departments.*'),        'accent' => $accent])
            @include('components.sidebar-items.nav-link', ['href' => route('admin.programs.index'),          'iconName' => 'academic-cap',  'label' => 'Programs',          'isActive' => $active('admin.programs.*'),           'accent' => $accent])
            @include('components.sidebar-items.nav-section-end')

            {{-- People --}}
            @include('components.sidebar-items.nav-section', ['label' => 'PEOPLE'])
            @include('components.sidebar-items.nav-link', ['href' => route('admin.users.index'),    'iconName' => 'users',      'label' => 'All Users',          'isActive' => $active('admin.users.*'),    'accent' => $accent])
            @include('components.sidebar-items.nav-link', ['href' => route('admin.students.index'), 'iconName' => 'user-group', 'label' => 'Students',           'isActive' => $active('admin.students.*'), 'accent' => $accent])
            @include('components.sidebar-items.nav-link', ['href' => route('admin.teachers.index'), 'iconName' => 'briefcase',  'label' => 'Teachers',           'isActive' => $active('admin.teachers.*'), 'accent' => $accent])
            @include('components.sidebar-items.nav-link', ['href' => route('admin.parents.index'),  'iconName' => 'heart',      'label' => 'Parents/Guardians',  'isActive' => $active('admin.parents.*'),  'accent' => $accent])
            @include('components.sidebar-items.nav-link', ['href' => route('admin.alumni.index'),   'iconName' => 'graduation-cap', 'label' => 'Alumni',         'isActive' => $active('admin.alumni.*'),   'accent' => $accent])
            @include('components.sidebar-items.nav-link', ['href' => route('admin.staff.index'),    'iconName' => 'briefcase',      'label' => 'Staff Directory','isActive' => $active('admin.staff.*'),    'accent' => $accent])
            @include('components.sidebar-items.nav-section-end')

            {{-- Examinations --}}
            @include('components.sidebar-items.nav-section', ['label' => 'EXAMINATIONS'])
            @include('components.sidebar-items.nav-link', ['href' => route('admin.exams.index'), 'iconName' => 'doc-text',    'label' => 'Manage Exams',   'isActive' => $active('admin.exams.*'), 'accent' => $accent])
            @include('components.sidebar-items.nav-section-end')

            {{-- Notices & News --}}
            @include('components.sidebar-items.nav-section', ['label' => 'NOTICES & NEWS'])
            @include('components.sidebar-items.nav-link', ['href' => route('admin.notices.index'),   'iconName' => 'bell',       'label' => 'All Notices',   'isActive' => $active('admin.notices.*'),   'accent' => $accent])
            @include('components.sidebar-items.nav-section-end')

            {{-- Website Management --}}
            @include('components.sidebar-items.nav-section', ['label' => 'WEBSITE'])
            @include('components.sidebar-items.nav-link', ['href' => route('admin.web-control.index'), 'iconName' => 'cog', 'label' => 'Website Settings', 'isActive' => $active(['admin.web-control.*','admin.banners.*','admin.media.*','admin.resources.*','admin.downloads.*','admin.facilities.*','admin.executives.*']), 'accent' => $accent])
            @include('components.sidebar-items.nav-section-end')

            {{-- Security --}}
            @include('components.sidebar-items.nav-section', ['label' => 'SECURITY'])
            @include('components.sidebar-items.nav-link', ['href' => route('admin.audit-logs.index'), 'iconName' => 'shield', 'label' => 'Audit Logs', 'isActive' => $active('admin.audit-logs.*'), 'accent' => $accent])
            @include('components.sidebar-items.nav-section-end')

        {{-- ════════════════════════════════════════════════
             HOD NAV
             ════════════════════════════════════════════════ --}}
        @elseif($isHod)

            @include('components.sidebar-items.nav-link', ['href' => route('hod.dashboard'), 'iconName' => 'home', 'label' => 'Dashboard', 'isActive' => $active('hod.dashboard'), 'accent' => $accent])

            @include('components.sidebar-items.nav-section', ['label' => 'CURRICULUM'])
            @include('components.sidebar-items.nav-link', ['href' => route('hod.programs.index'),  'iconName' => 'academic-cap', 'label' => 'Programs',   'isActive' => $active('hod.programs.*'),  'accent' => $accent])
            @include('components.sidebar-items.nav-link', ['href' => route('hod.subjects.index'),  'iconName' => 'doc-text',     'label' => 'Subjects',   'isActive' => $active('hod.subjects.*'),  'accent' => $accent])
            @include('components.sidebar-items.nav-link', ['href' => route('hod.timetable.index'), 'iconName' => 'calendar',     'label' => 'Timetable',  'isActive' => $active('hod.timetable.*'), 'accent' => $accent])
            @include('components.sidebar-items.nav-section-end')

            @include('components.sidebar-items.nav-section', ['label' => 'DEPARTMENT'])
            @include('components.sidebar-items.nav-link', ['href' => route('hod.teachers.index'), 'iconName' => 'briefcase',  'label' => 'Teachers',  'isActive' => $active('hod.teachers.*'), 'accent' => $accent])
            @include('components.sidebar-items.nav-link', ['href' => route('hod.students.index'), 'iconName' => 'user-group', 'label' => 'Students',  'isActive' => $active('hod.students.*'), 'accent' => $accent])
            @include('components.sidebar-items.nav-section-end')

            @include('components.sidebar-items.nav-section', ['label' => 'ANALYTICS'])
            @include('components.sidebar-items.nav-link', ['href' => route('hod.reports.index'), 'iconName' => 'chart-bar', 'label' => 'Reports', 'isActive' => $active('hod.reports.*'), 'accent' => $accent])
            @include('components.sidebar-items.nav-section-end')

        {{-- ════════════════════════════════════════════════
             TEACHER NAV
             ════════════════════════════════════════════════ --}}
        @elseif($isTeacher)

            @include('components.sidebar-items.nav-link', ['href' => route('teacher.dashboard'), 'iconName' => 'home', 'label' => 'Dashboard', 'isActive' => $active('teacher.dashboard'), 'accent' => $accent])

            @include('components.sidebar-items.nav-section', ['label' => 'CLASSROOM'])
            @include('components.sidebar-items.nav-link', ['href' => route('teacher.attendance.index'),  'iconName' => 'clipboard-check', 'label' => 'Attendance',   'isActive' => $active('teacher.attendance.*'),  'accent' => $accent])
            @include('components.sidebar-items.nav-link', ['href' => route('teacher.assignments.index'), 'iconName' => 'doc-text',        'label' => 'Assignments',  'isActive' => $active('teacher.assignments.*'), 'accent' => $accent])
            @include('components.sidebar-items.nav-link', ['href' => route('teacher.timetable.index'),   'iconName' => 'calendar',        'label' => 'My Timetable', 'isActive' => $active('teacher.timetable.*'),   'accent' => $accent])
            @include('components.sidebar-items.nav-section-end')

            @include('components.sidebar-items.nav-section', ['label' => 'EVALUATION'])
            @include('components.sidebar-items.nav-link', ['href' => route('teacher.marks.index'),  'iconName' => 'chart-bar', 'label' => 'Mark Entry',  'isActive' => $active('teacher.marks.*'),  'accent' => $accent])
            @include('components.sidebar-items.nav-link', ['href' => route('teacher.exams.index'),  'iconName' => 'doc-text',  'label' => 'Exams',       'isActive' => $active('teacher.exams.*'),  'accent' => $accent])
            @include('components.sidebar-items.nav-section-end')

            @include('components.sidebar-items.nav-section', ['label' => 'GENERAL'])
            @include('components.sidebar-items.nav-link', ['href' => route('teacher.notices.index'), 'iconName' => 'bell', 'label' => 'Notices', 'isActive' => $active('teacher.notices.*'), 'accent' => $accent])
            @include('components.sidebar-items.nav-section-end')

        {{-- ════════════════════════════════════════════════
             STUDENT NAV
             ════════════════════════════════════════════════ --}}
        @elseif($isStudent)

            @include('components.sidebar-items.nav-link', ['href' => route('student.dashboard'), 'iconName' => 'home', 'label' => 'Dashboard', 'isActive' => $active('student.dashboard'), 'accent' => $accent])

            @include('components.sidebar-items.nav-section', ['label' => 'MY ACADEMICS'])
            @include('components.sidebar-items.nav-link', ['href' => route('student.timetable.index'),   'iconName' => 'calendar',        'label' => 'My Timetable',    'isActive' => $active('student.timetable.*'),   'accent' => $accent])
            @include('components.sidebar-items.nav-link', ['href' => route('student.attendance.index'),  'iconName' => 'clipboard-check', 'label' => 'My Attendance',   'isActive' => $active('student.attendance.*'),  'accent' => $accent])
            @include('components.sidebar-items.nav-link', ['href' => route('student.assignments.index'), 'iconName' => 'doc-text',        'label' => 'Assignments',      'isActive' => $active('student.assignments.*'), 'accent' => $accent])
            @include('components.sidebar-items.nav-link', ['href' => route('student.results.index'),     'iconName' => 'chart-bar',       'label' => 'My Results',       'isActive' => $active('student.results.*'),     'accent' => $accent])
            @include('components.sidebar-items.nav-section-end')

            @include('components.sidebar-items.nav-section', ['label' => 'RESOURCES'])
            @include('components.sidebar-items.nav-link', ['href' => route('student.notices.index'),   'iconName' => 'bell',     'label' => 'Notices',    'isActive' => $active('student.notices.*'),   'accent' => $accent])
            @include('components.sidebar-items.nav-link', ['href' => route('student.downloads.index'), 'iconName' => 'download', 'label' => 'Downloads',  'isActive' => $active('student.downloads.*'), 'accent' => $accent])
            @include('components.sidebar-items.nav-section-end')

        {{-- ════════════════════════════════════════════════
             PARENT NAV
             ════════════════════════════════════════════════ --}}
        @elseif($isParent)

            @include('components.sidebar-items.nav-link', ['href' => route('parent.dashboard'), 'iconName' => 'home', 'label' => 'Dashboard', 'isActive' => $active('parent.dashboard'), 'accent' => $accent])

            @include('components.sidebar-items.nav-section', ['label' => "MY CHILD'S PROGRESS"])
            @include('components.sidebar-items.nav-link', ['href' => route('parent.attendance.index'), 'iconName' => 'clipboard-check', 'label' => 'Attendance Track',  'isActive' => $active('parent.attendance.*'), 'accent' => $accent])
            @include('components.sidebar-items.nav-link', ['href' => route('parent.results.index'),    'iconName' => 'chart-bar',       'label' => 'Exam Results',       'isActive' => $active('parent.results.*'),    'accent' => $accent])
            @include('components.sidebar-items.nav-section-end')

            @include('components.sidebar-items.nav-section', ['label' => 'COMMUNICATION'])
            @include('components.sidebar-items.nav-link', ['href' => route('parent.notices.index'), 'iconName' => 'bell', 'label' => 'Notices', 'isActive' => $active('parent.notices.*'), 'accent' => $accent])
            @include('components.sidebar-items.nav-section-end')

        {{-- ════════════════════════════════════════════════
             ALUMNI NAV
             ════════════════════════════════════════════════ --}}
        @elseif($isAlumni)

            @include('components.sidebar-items.nav-link', ['href' => route('alumni.dashboard'), 'iconName' => 'home', 'label' => 'Dashboard', 'isActive' => $active('alumni.dashboard'), 'accent' => $accent])

            @include('components.sidebar-items.nav-section', ['label' => 'COMMUNITY'])
            @include('components.sidebar-items.nav-link', ['href' => route('alumni.profile.edit'),  'iconName' => 'users',      'label' => 'My Profile',  'isActive' => $active('alumni.profile.*'),  'accent' => $accent])
            @include('components.sidebar-items.nav-link', ['href' => route('alumni.notices.index'), 'iconName' => 'bell',       'label' => 'Events',      'isActive' => $active('alumni.notices.*'),  'accent' => $accent])
            @include('components.sidebar-items.nav-link', ['href' => route('alumni.gallery.index'), 'iconName' => 'photo',      'label' => 'Gallery',     'isActive' => $active('alumni.gallery.*'),  'accent' => $accent])
            @include('components.sidebar-items.nav-section-end')

        @endif

        {{-- ── Public Site Link (all roles) ───────────────── --}}
        <div class="pt-2 mt-2 border-t border-white/10">
            <a href="{{ route('home') }}" target="_blank"
               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg text-gray-500 hover:bg-white/5 hover:text-white transition-all duration-200">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    {!! $icon('external') !!}
                </svg>
                <span>View Public Site</span>
            </a>
        </div>

    </nav>

    {{-- ── User Footer ──────────────────────────────────── --}}
    <div class="flex-shrink-0 border-t border-white/10 px-4 py-3">
        <div class="flex items-center gap-3">
            <img src="{{ $user->avatar_url }}"
                 alt="{{ $user->name }}"
                 class="w-9 h-9 rounded-full object-cover flex-shrink-0 ring-2 ring-white/10">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-white truncate leading-none">{{ $user->name }}</p>
                <p class="text-[10px] text-gray-400 truncate mt-0.5 capitalize">{{ $role }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="flex-shrink-0">
                @csrf
                <button type="submit" title="Sign out"
                    class="p-1.5 text-gray-500 hover:text-red-400 hover:bg-red-400/10 rounded-lg transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

</aside>
