{{--
    Modern SaaS Sidebar — MMP College Management System
    Role-aware, badge-enabled, collapsible, and optimized for the admin workflow.
--}}

@php
    $user = auth()->user();
    $role = $user?->getRoleNames()->first() ?? 'guest';

    $isAdmin = $user?->isPrincipal();
    $isHod = $user?->isHod();
    $isTeacher = $user?->isTeacher();
    $isStudent = $user?->isStudent();
    $isParent = $user?->isParent();
    $isAlumni = $user?->isAlumni();

    $accent = match (true) {
        $isAdmin   => '#8B0000',
        $isHod     => '#1d4ed8',
        $isTeacher => '#047857',
        $isStudent => '#7c3aed',
        $isParent  => '#b45309',
        $isAlumni  => '#334155',
        default    => '#334155',
    };

    $roleLabel = match (true) {
        $isAdmin   => 'Admin Portal',
        $isHod     => 'HOD Portal',
        $isTeacher => 'Teacher Portal',
        $isStudent => 'Student Portal',
        $isParent  => 'Parent Portal',
        $isAlumni  => 'Alumni Portal',
        default    => 'Portal',
    };

    $active = fn (string|array $patterns): bool => request()->routeIs((array) $patterns);
    $portalRoute = fn (string $name, string $fallback) => \Illuminate\Support\Facades\Route::has($name)
        ? route($name)
        : route($fallback);
    $brandLogoUrl = route('public.brand-logo') . '?v=' . logoVersion();

    $currentUserId = $user?->id;
    $hasNotificationsTable = \Illuminate\Support\Facades\Schema::hasTable('notifications');
    // Application feature removed
    $unreadNotifications = $isAdmin && $currentUserId && $hasNotificationsTable
           ? \Illuminate\Support\Facades\Cache::remember("sidebar:notifications:{$currentUserId}", 180, fn () => $user->unreadNotifications()->count())
        : 0;

    $adminGroups = [
        // ── Dashboard ──────────────────────────────────────────
        [
            'label'      => 'Dashboard',
            'standalone' => true,
            'items'      => [
                ['label' => 'Overview', 'iconName' => 'home', 'href' => route('admin.dashboard'), 'isActive' => $active('admin.dashboard')],
            ],
        ],

        // ── People ─────────────────────────────────────────────
        [
            'label' => 'People',
            'items' => [
                ['label' => 'Students',           'iconName' => 'academic-cap',   'href' => route('admin.students.index'),          'isActive' => $active('admin.students.*')],
                ['label' => 'Parents',            'iconName' => 'heart',          'href' => route('admin.parents.index'),           'isActive' => $active('admin.parents.*')],
                ['label' => 'Alumni',             'iconName' => 'graduation-cap', 'href' => route('admin.alumni.index'),            'isActive' => $active('admin.alumni.*')],
                ['label' => 'Teachers',           'iconName' => 'briefcase',      'href' => route('admin.teachers.index'),          'isActive' => $active('admin.teachers.*')],
                ['label' => 'Staff & HR',    'iconName' => 'users',          'href' => route('admin.staff.index'),        'isActive' => $active('admin.staff.*')],
                ['label' => 'Departments',  'iconName' => 'office-building','href' => route('admin.departments.index'), 'isActive' => $active('admin.departments.*')],
                ['label' => 'HODs',         'iconName' => 'user-circle',    'href' => route('admin.hods.index'),         'isActive' => $active('admin.hods.*')],
                ['label' => 'Executives',   'iconName' => 'star',           'href' => route('admin.executives.index'),   'isActive' => $active('admin.executives.*')],
                ['label' => 'User Accounts','iconName' => 'user-group',     'href' => route('admin.users.index'),        'isActive' => $active('admin.users.*')],
                ['label' => 'Roles & Permissions','iconName' => 'shield',         'href' => route('admin.roles-permissions.index'), 'isActive' => $active('admin.roles-permissions.*')],
            ],
        ],

        // ── Academics ──────────────────────────────────────────
        [
            'label' => 'Academics',
            'items' => [
                ['label' => 'Programs',           'iconName' => 'book-open',       'href' => route('admin.programs.index'),          'isActive' => $active('admin.programs.*')],
                ['label' => 'Academic Calendar',  'iconName' => 'calendar',        'href' => route('admin.academic-sessions.index'), 'isActive' => $active('admin.academic-sessions.*')],
                ['label' => 'Attendance',         'iconName' => 'clipboard-check', 'href' => route('admin.attendance.index'),        'isActive' => $active('admin.attendance.*')],
                ['label' => 'Examinations',       'iconName' => 'chart-bar',       'href' => route('admin.exams.index'),             'isActive' => $active('admin.exams.*')],

            ],
        ],

        // ── Student Services ───────────────────────────────────
        [
            'label' => 'Student Services',
            'items' => [
                ['label' => 'Student ID Cards', 'iconName' => 'identification', 'href' => route('admin.id-cards.students.index'), 'isActive' => $active('admin.id-cards.students.*')],
                ['label' => 'Staff ID Cards',   'iconName' => 'identification', 'href' => route('admin.id-cards.staff.index'),    'isActive' => $active('admin.id-cards.staff.*')],
            ],
        ],

        // ── Website & Communication ────────────────────────────
        [
            'label' => 'Website & Communication',
            'items' => [
                ['label' => 'Website Content',      'iconName' => 'doc-text',   'href' => route('admin.web-control.index'),  'isActive' => $active('admin.web-control.*')],
                ['label' => 'Media Gallery',         'iconName' => 'photo',      'href' => route('admin.media.index'),        'isActive' => $active('admin.media.*')],
                ['label' => 'News & Announcements',  'iconName' => 'bell',       'href' => route('admin.notices.index'),      'isActive' => $active('admin.notices.*'), 'badge' => $unreadNotifications],
                ['label' => 'Events',            'iconName' => 'collection', 'href' => route('admin.news-events.index'),  'isActive' => $active('admin.news-events.*')],
                ['label' => 'File Repository',   'iconName' => 'download',   'href' => route('admin.downloads.index'),    'isActive' => $active('admin.downloads.*')],
                ['label' => 'Banner Management', 'iconName' => 'collection', 'href' => route('admin.banners.index'),      'isActive' => $active('admin.banners.*')],
            ],
        ],

        // ── Configuration ──────────────────────────────────────
        [
            'label' => 'Configuration',
            'items' => [
                ['label' => 'Institution Settings', 'iconName' => 'cog',      'href' => route('admin.web-control.index'),       'isActive' => $active('admin.web-control.*')],
                ['label' => 'Academic Settings',    'iconName' => 'calendar', 'href' => route('admin.academic-sessions.index'), 'isActive' => $active('admin.academic-sessions.*')],
            ],
        ],

        // ── System ─────────────────────────────────────────────
        [
            'label' => 'System',
            'items' => [
                ['label' => 'System Users',         'iconName' => 'user-group', 'href' => route('admin.users.index'),             'isActive' => $active('admin.users.*')],
                ['label' => 'Access Control',       'iconName' => 'shield',     'href' => route('admin.roles-permissions.index'), 'isActive' => $active('admin.roles-permissions.*')],
                ['label' => 'Audit Logs',       'iconName' => 'doc-report', 'href' => route('admin.audit-logs.index'),  'isActive' => $active('admin.audit-logs.*')],
                ['label' => 'Account Settings', 'iconName' => 'cog',        'href' => route('admin.settings.index'),   'isActive' => $active('admin.settings.*')],
            ],
        ],
    ];

    $hodGroups = [
        [
            'label' => 'Dashboard',
            'standalone' => true,
            'items' => [
                ['label' => 'Overview', 'iconName' => 'home', 'href' => route('hod.dashboard'), 'isActive' => $active('hod.dashboard')],
            ],
        ],
        [
            'label' => 'User',
            'items' => [
                ['label' => 'Students', 'iconName' => 'user-group', 'href' => $portalRoute('hod.students.index', 'hod.dashboard'), 'isActive' => $active('hod.students.*')],
                ['label' => 'Teachers', 'iconName' => 'briefcase', 'href' => $portalRoute('hod.teachers.index', 'hod.dashboard'), 'isActive' => $active('hod.teachers.*')],
            ],
        ],
        [
            'label' => 'Academic Operations',
            'items' => [
                ['label' => 'Subjects', 'iconName' => 'book-open', 'href' => $portalRoute('hod.subjects.index', 'hod.dashboard'), 'isActive' => $active('hod.subjects.*')],
                ['label' => 'Attendance', 'iconName' => 'clipboard-check', 'href' => $portalRoute('hod.attendance.index', 'hod.dashboard'), 'isActive' => $active('hod.attendance.*')],
                ['label' => 'Exams & Marks', 'iconName' => 'chart-bar', 'href' => $portalRoute('hod.exams.index', 'hod.dashboard'), 'isActive' => $active('hod.exams.*')],
                ['label' => 'Timetable', 'iconName' => 'calendar', 'href' => $portalRoute('hod.timetable.index', 'hod.dashboard'), 'isActive' => $active('hod.timetable.*')],
            ],
        ],
        [
            'label' => 'Communication',
            'items' => [
                ['label' => 'Notices', 'iconName' => 'bell', 'href' => $portalRoute('hod.notices.index', 'hod.dashboard'), 'isActive' => $active('hod.notices.*')],
                ['label' => 'News & Events', 'iconName' => 'collection', 'href' => $portalRoute('hod.news-events.index', 'hod.dashboard'), 'isActive' => $active('hod.news-events.*')],
            ],
        ],
        [
            'label' => 'Resources',
            'items' => [
                ['label' => 'Facilities', 'iconName' => 'doc-text', 'href' => $portalRoute('hod.facilities.index', 'hod.dashboard'), 'isActive' => $active('hod.facilities.*')],
                ['label' => 'Media Gallery', 'iconName' => 'photo', 'href' => $portalRoute('hod.media.index', 'hod.dashboard'), 'isActive' => $active('hod.media.*')],
                ['label' => 'Downloads', 'iconName' => 'download', 'href' => $portalRoute('hod.downloads.index', 'hod.dashboard'), 'isActive' => $active('hod.downloads.*')],
            ],
        ],
        [
            'label' => 'Alumni Management',
            'standalone' => true,
            'items' => [
                ['label' => 'Alumni Preparation', 'iconName' => 'graduation-cap', 'href' => $portalRoute('hod.alumni.index', 'hod.dashboard'), 'isActive' => $active('hod.alumni.*')],
            ],
        ],
        [
            'label' => 'Account',
            'standalone' => true,
            'items' => [
                ['label' => 'Settings', 'iconName' => 'cog', 'href' => $portalRoute('hod.settings.index', 'hod.dashboard'), 'isActive' => $active('hod.settings.*')],
            ],
        ],
    ];

    $teacherGroups = [
        [
            'label' => 'Dashboard',
            'standalone' => true,
            'items' => [
                ['label' => 'Overview', 'iconName' => 'home', 'href' => route('teacher.dashboard'), 'isActive' => $active('teacher.dashboard')],
            ],
        ],
        [
            'label' => 'Classroom Management',
            'items' => [
                ['label' => 'My Classes', 'iconName' => 'book-open', 'href' => $portalRoute('teacher.classes.index', 'teacher.dashboard'), 'isActive' => $active('teacher.classes.*')],
                ['label' => 'Students', 'iconName' => 'user-group', 'href' => $portalRoute('teacher.students.index', 'teacher.dashboard'), 'isActive' => $active('teacher.students.*')],
                ['label' => 'Attendance', 'iconName' => 'clipboard-check', 'href' => $portalRoute('teacher.attendance.index', 'teacher.dashboard'), 'isActive' => $active('teacher.attendance.*')],
                ['label' => 'Assignments', 'iconName' => 'doc-text', 'href' => $portalRoute('teacher.assignments.index', 'teacher.dashboard'), 'isActive' => $active('teacher.assignments.*')],
                ['label' => 'Timetable', 'iconName' => 'calendar', 'href' => $portalRoute('teacher.timetable.index', 'teacher.dashboard'), 'isActive' => $active('teacher.timetable.*')],
            ],
        ],
        [
            'label' => 'Exams & Results',
            'standalone' => true,
            'items' => [
                ['label' => 'Exams & Marks', 'iconName' => 'chart-bar', 'href' => $portalRoute('teacher.exams.index', 'teacher.dashboard'), 'isActive' => $active('teacher.exams.*')],
            ],
        ],
        [
            'label' => 'Resources & Settings',
            'items' => [
                ['label' => 'Downloads', 'iconName' => 'download', 'href' => $portalRoute('teacher.downloads.index', 'teacher.dashboard'), 'isActive' => $active('teacher.downloads.*')],
                ['label' => 'Notices', 'iconName' => 'bell', 'href' => $portalRoute('teacher.notices.index', 'teacher.dashboard'), 'isActive' => $active('teacher.notices.*')],
                ['label' => 'News & Events', 'iconName' => 'collection', 'href' => $portalRoute('teacher.news-events.index', 'teacher.dashboard'), 'isActive' => $active('teacher.news-events.*')],
                ['label' => 'Settings', 'iconName' => 'cog', 'href' => $portalRoute('teacher.settings.index', 'teacher.dashboard'), 'isActive' => $active('teacher.settings.*')],
            ],
        ],
    ];

    $studentGroups = [
        [
            'label' => 'Dashboard',
            'standalone' => true,
            'items' => [
                ['label' => 'Overview', 'iconName' => 'home', 'href' => route('student.dashboard'), 'isActive' => $active('student.dashboard')],
            ],
        ],
        [
            'label' => 'My Academics',
            'items' => [
                ['label' => 'Timetable', 'iconName' => 'calendar', 'href' => $portalRoute('student.timetable.index', 'student.dashboard'), 'isActive' => $active('student.timetable.*')],
                ['label' => 'Subjects', 'iconName' => 'book-open', 'href' => $portalRoute('student.subjects.index', 'student.dashboard'), 'isActive' => $active('student.subjects.*')],
                ['label' => 'Attendance', 'iconName' => 'clipboard-check', 'href' => $portalRoute('student.attendance.index', 'student.dashboard'), 'isActive' => $active('student.attendance.*')],
                ['label' => 'Assignments', 'iconName' => 'doc-text', 'href' => $portalRoute('student.assignments.index', 'student.dashboard'), 'isActive' => $active('student.assignments.*')],
                ['label' => 'Results', 'iconName' => 'chart-bar', 'href' => $portalRoute('student.marks.index', 'student.dashboard'), 'isActive' => $active('student.marks.*')],
            ],
        ],
        [
            'label' => 'Resources & Updates',
            'items' => [
                ['label' => 'Notices', 'iconName' => 'bell', 'href' => $portalRoute('student.notices.index', 'student.dashboard'), 'isActive' => $active('student.notices.*')],
                ['label' => 'News & Events', 'iconName' => 'collection', 'href' => $portalRoute('student.news-events.index', 'student.dashboard'), 'isActive' => $active('student.news-events.*')],
                ['label' => 'Downloads', 'iconName' => 'download', 'href' => $portalRoute('student.downloads.index', 'student.dashboard'), 'isActive' => $active('student.downloads.*')],
            ],
        ],
        [
            'label' => 'My ID Card',
            'standalone' => true,
            'items' => [
                ['label' => 'ID Card', 'iconName' => 'identification', 'href' => $portalRoute('student.id-card.index', 'student.dashboard'), 'isActive' => $active('student.id-card.*')],
            ],
        ],
        [
            'label' => 'Account',
            'standalone' => true,
            'items' => [
                ['label' => 'Settings', 'iconName' => 'cog', 'href' => $portalRoute('student.settings.index', 'student.dashboard'), 'isActive' => $active('student.settings.*')],
            ],
        ],
    ];

    $parentGroups = [
        [
            'label' => 'Dashboard',
            'standalone' => true,
            'items' => [
                ['label' => 'Overview', 'iconName' => 'home', 'href' => route('parent.dashboard'), 'isActive' => $active('parent.dashboard')],
            ],
        ],
        [
            'label' => 'Child Academic Progress',
            'items' => [
                ['label' => 'Subjects', 'iconName' => 'book-open', 'href' => $portalRoute('parent.subjects.index', 'parent.dashboard'), 'isActive' => $active('parent.subjects.*')],
                ['label' => 'Attendance', 'iconName' => 'clipboard-check', 'href' => $portalRoute('parent.attendance.index', 'parent.dashboard'), 'isActive' => $active('parent.attendance.*')],
                ['label' => 'Assignments', 'iconName' => 'document-text', 'href' => $portalRoute('parent.assignments.index', 'parent.dashboard'), 'isActive' => $active('parent.assignments.*')],
                ['label' => 'Results', 'iconName' => 'chart-bar', 'href' => $portalRoute('parent.results.index', 'parent.dashboard'), 'isActive' => $active('parent.results.*')],
            ],
        ],
        [
            'label' => 'Communication',
            'items' => [
                ['label' => 'Notices', 'iconName' => 'bell', 'href' => $portalRoute('parent.notices.index', 'parent.dashboard'), 'isActive' => $active('parent.notices.*')],
                ['label' => 'News & Events', 'iconName' => 'collection', 'href' => $portalRoute('parent.news-events.index', 'parent.dashboard'), 'isActive' => $active('parent.news-events.*')],
            ],
        ],
        [
            'label' => 'Account',
            'standalone' => true,
            'items' => [
                ['label' => 'Settings', 'iconName' => 'cog', 'href' => $portalRoute('parent.settings.index', 'parent.dashboard'), 'isActive' => $active('parent.settings.*')],
            ],
        ],
    ];

    $alumniGroups = [
        [
            'label' => 'Dashboard',
            'standalone' => true,
            'items' => [
                ['label' => 'Overview', 'iconName' => 'home', 'href' => route('alumni.dashboard'), 'isActive' => $active('alumni.dashboard')],
            ],
        ],
        [
            'label' => 'My Profile',
            'items' => [
                ['label' => 'Profile', 'iconName' => 'users', 'href' => $portalRoute('alumni.profile.index', 'alumni.dashboard'), 'isActive' => $active('alumni.profile.*')],
                ['label' => 'Career', 'iconName' => 'briefcase', 'href' => $portalRoute('alumni.career.index', 'alumni.dashboard'), 'isActive' => $active('alumni.career.*')],
            ],
        ],
        [
            'label' => 'Portfolio',
            'items' => [
                ['label' => 'Projects', 'iconName' => 'code', 'href' => $portalRoute('alumni.projects.index', 'alumni.dashboard'), 'isActive' => $active('alumni.projects.*')],
                ['label' => 'Achievements', 'iconName' => 'star', 'href' => $portalRoute('alumni.achievements.index', 'alumni.dashboard'), 'isActive' => $active('alumni.achievements.*')],
            ],
        ],
        [
            'label' => 'Community',
            'standalone' => true,
            'items' => [
                ['label' => 'Notices', 'iconName' => 'bell', 'href' => $portalRoute('alumni.notices.index', 'alumni.dashboard'), 'isActive' => $active('alumni.notices.*')],
            ],
        ],
        [
            'label' => 'Account',
            'standalone' => true,
            'items' => [
                ['label' => 'Settings', 'iconName' => 'cog', 'href' => $portalRoute('alumni.settings.index', 'alumni.dashboard'), 'isActive' => $active('alumni.settings.*')],
            ],
        ],
    ];

    $sidebarExpandedWidth = '17rem';
    $sidebarCollapsedWidth = '4.75rem';
@endphp

<aside
    class="fixed inset-y-0 left-0 z-50 flex h-screen flex-col bg-[#F8FAFC] dark:bg-slate-900 text-slate-800 dark:text-white shadow-2xl transition-[transform,width] duration-300 ease-out lg:sticky lg:top-0 lg:flex-shrink-0 lg:translate-x-0"
    :style="sidebarCollapsed ? 'width: {{ $sidebarCollapsedWidth }}' : 'width: {{ $sidebarExpandedWidth }}'"
    :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }"
    x-cloak>

    {{-- Brand --}}
    <div class="flex h-16 items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 lg:px-5">
        <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3 overflow-hidden">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-white shadow-lg" style="background-color: #fff !important;">
                <img src="{{ $brandLogoUrl }}" alt="MMP Logo" class="h-8 w-8 rounded-xl object-cover" onerror="this.style.display='none'">
            </div>
            <div x-show="!sidebarCollapsed" x-cloak class="min-w-0">
                <p class="truncate text-sm font-bold tracking-tight text-slate-800">MMP College</p>
                <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-[0.22em]" style="color: {{ $accent }};">{{ $roleLabel }}</p>
            </div>
        </a>

        <button type="button"
            @click="sidebarCollapsed = !sidebarCollapsed"
            class="hidden rounded-xl border border-slate-200 bg-white p-2 text-slate-400 transition-all duration-200 hover:-translate-y-0.5 hover:bg-slate-100 hover:text-slate-800 lg:inline-flex">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path x-show="!sidebarCollapsed" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                <path x-show="sidebarCollapsed" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto overflow-x-visible px-3 py-3 scrollbar-thin scrollbar-thumb-slate-200 text-slate-700">
        @if($isAdmin)
            @foreach($adminGroups as $group)
                @if(!empty($group['standalone']))
                    <div class="pt-4 pb-1 px-1 space-y-1">
                        @foreach($group['items'] as $item)
                            @include('components.sidebar-items.nav-link', [
                                'href'     => $item['href'],
                                'iconName' => $item['iconName'],
                                'label'    => $item['label'],
                                'isActive' => $item['isActive'],
                                'accent'   => $accent,
                                'badge'    => $item['badge'] ?? null,
                                'disabled' => $item['disabled'] ?? false,
                            ])
                        @endforeach
                    </div>
                @else
                    @include('components.sidebar-items.nav-section', ['label' => $group['label']])
                    @foreach($group['items'] as $item)
                        @include('components.sidebar-items.nav-link', [
                            'href'     => $item['href'],
                            'iconName' => $item['iconName'],
                            'label'    => $item['label'],
                            'isActive' => $item['isActive'],
                            'accent'   => $accent,
                            'badge'    => $item['badge'] ?? null,
                            'disabled' => $item['disabled'] ?? false,
                        ])
                    @endforeach
                    @include('components.sidebar-items.nav-section-end')
                @endif
            @endforeach
        @elseif($isHod)
            @foreach($hodGroups as $group)
                @include('components.sidebar-items.nav-section', ['label' => $group['label']])
                @foreach($group['items'] as $item)
                    @include('components.sidebar-items.nav-link', [
                        'href' => $item['href'],
                        'iconName' => $item['iconName'],
                        'label' => $item['label'],
                        'isActive' => $item['isActive'],
                        'accent' => $accent,
                    ])
                @endforeach
                @include('components.sidebar-items.nav-section-end')
            @endforeach
        @elseif($isTeacher)
            @foreach($teacherGroups as $group)
                @include('components.sidebar-items.nav-section', ['label' => $group['label']])
                @foreach($group['items'] as $item)
                    @include('components.sidebar-items.nav-link', [
                        'href' => $item['href'],
                        'iconName' => $item['iconName'],
                        'label' => $item['label'],
                        'isActive' => $item['isActive'],
                        'accent' => $accent,
                    ])
                @endforeach
                @include('components.sidebar-items.nav-section-end')
            @endforeach
        @elseif($isStudent)
            @foreach($studentGroups as $group)
                @if(!empty($group['standalone']))
                    <div class="pt-4 pb-1 px-1 space-y-1">
                        @foreach($group['items'] as $item)
                            @include('components.sidebar-items.nav-link', [
                                'href'     => $item['href'],
                                'iconName' => $item['iconName'],
                                'label'    => $item['label'],
                                'isActive' => $item['isActive'],
                                'accent'   => $accent,
                                'disabled' => $item['disabled'] ?? false,
                            ])
                        @endforeach
                    </div>
                @else
                    @include('components.sidebar-items.nav-section', ['label' => $group['label']])
                    @foreach($group['items'] as $item)
                        @include('components.sidebar-items.nav-link', [
                            'href'     => $item['href'],
                            'iconName' => $item['iconName'],
                            'label'    => $item['label'],
                            'isActive' => $item['isActive'],
                            'accent'   => $accent,
                            'disabled' => $item['disabled'] ?? false,
                        ])
                    @endforeach
                    @include('components.sidebar-items.nav-section-end')
                @endif
            @endforeach
        @elseif($isParent)
            @foreach($parentGroups as $group)
                @include('components.sidebar-items.nav-section', ['label' => $group['label']])
                @foreach($group['items'] as $item)
                    @include('components.sidebar-items.nav-link', [
                        'href' => $item['href'],
                        'iconName' => $item['iconName'],
                        'label' => $item['label'],
                        'isActive' => $item['isActive'],
                        'accent' => $accent,
                    ])
                @endforeach
                @include('components.sidebar-items.nav-section-end')
            @endforeach
        @elseif($isAlumni)
            @foreach($alumniGroups as $group)
                @include('components.sidebar-items.nav-section', ['label' => $group['label']])
                @foreach($group['items'] as $item)
                    @include('components.sidebar-items.nav-link', [
                        'href' => $item['href'],
                        'iconName' => $item['iconName'],
                        'label' => $item['label'],
                        'isActive' => $item['isActive'],
                        'accent' => $accent,
                    ])
                @endforeach
                @include('components.sidebar-items.nav-section-end')
            @endforeach
        @endif

        <div class="mt-4 border-t border-slate-200 pt-4 text-slate-700">
            <a href="{{ route('home') }}" target="_blank"
               class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-500 transition-all duration-200 hover:-translate-y-0.5 hover:bg-slate-100 hover:text-slate-900">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-400 group-hover:bg-slate-200 group-hover:text-slate-900">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </span>
                <span x-show="!sidebarCollapsed" x-cloak class="truncate">Public Site</span>
                <span x-show="sidebarCollapsed" x-cloak class="pointer-events-none absolute left-full top-1/2 ml-3 -translate-y-1/2 whitespace-nowrap rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white opacity-0 shadow-2xl ring-1 ring-slate-200 transition-opacity duration-200 group-hover:opacity-100 lg:block">Public Site</span>
            </a>
        </div>
    </nav>

    {{-- Footer --}}
    <div class="border-t border-slate-200 p-3 lg:p-4 text-slate-700">
        <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-lg shadow-slate-200/20">
            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-10 w-10 shrink-0 rounded-full object-cover ring-2 ring-slate-200">
            <div x-show="!sidebarCollapsed" x-cloak class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-slate-800">{{ $user->name }}</p>
                <p class="truncate text-[10px] uppercase tracking-[0.22em] text-slate-400">{{ $roleLabel }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="flex-shrink-0">
                @csrf
                <button type="submit" title="Sign out" class="rounded-xl p-2 text-slate-400 transition-colors duration-200 hover:bg-rose-500/10 hover:text-rose-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
