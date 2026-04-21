<header class="bg-white border-b border-gray-200 sticky top-0 z-30 flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16 shadow-sm">
    <div class="flex items-center gap-2">
        <!-- Mobile Sidebar Toggle -->
        <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700 focus:outline-none lg:hidden">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>

        <button type="button"
            @click="sidebarCollapsed = !sidebarCollapsed"
            class="hidden lg:inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-600 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-[#8B0000]/20 hover:text-[#8B0000]">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path x-show="!sidebarCollapsed" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                <path x-show="sidebarCollapsed" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
            <span x-show="!sidebarCollapsed" x-cloak>Collapse</span>
            <span x-show="sidebarCollapsed" x-cloak>Expand</span>
        </button>
        
        @php
            $user = auth()->user();
            $department = null;
            $designation = null;
            
            // Get department and designation based on user role
            if ($user->hasRole('hod') && $user->teacher) {
                $department = $user->teacher->department;
                $designation = 'Head of Department';
            } elseif ($user->hasRole('teacher') && $user->teacher) {
                $department = $user->teacher->department;
                $designation = $user->teacher->designation ?? 'Teacher';
            } elseif ($user->hasRole('student') && $user->student) {
                $department = $user->student->program->department ?? null;
                $designation = 'Student';
            }
        @endphp

        <!-- Context Information Badges -->
        <div class="hidden md:flex items-center gap-2">
            <!-- Active Session Badge -->
            @if(isset($activeSession))
                <span class="inline-flex items-center bg-blue-50 text-blue-700 text-xs font-semibold px-2.5 py-0.5 rounded-full border border-blue-200">
                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-1.5 animate-pulse"></span>
                    {{ $activeSession->name }}
                </span>
            @endif

            <!-- Department Badge -->
            @if($department)
                <span class="inline-flex items-center bg-emerald-50 text-emerald-700 text-xs font-semibold px-2.5 py-0.5 rounded-full border border-emerald-200">
                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    {{ $department->name }}
                </span>
            @endif

            <!-- Designation Badge -->
            @if($designation)
                <span class="inline-flex items-center bg-violet-50 text-violet-700 text-xs font-semibold px-2.5 py-0.5 rounded-full border border-violet-200">
                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    {{ $designation }}
                </span>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-4">
        <!-- Quick Stats (for HOD) -->
        @if($user->hasRole('hod') && $department)
            <div class="hidden xl:flex items-center gap-3 mr-2">
                @php
                    $stats = [
                        'teachers' => $department->teachers()->where('is_active', true)->count(),
                        'students' => \App\Models\Student::whereHas('program', function($q) use ($department) {
                            $q->where('department_id', $department->id);
                        })->where('is_active', true)->count(),
                        'programs' => $department->programs()->count(),
                    ];
                @endphp
                
                <div class="flex items-center gap-1 text-xs text-slate-600">
                    <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span class="font-semibold text-slate-900">{{ $stats['teachers'] }}</span>
                    <span class="text-slate-500">Teachers</span>
                </div>
                
                <div class="w-px h-4 bg-slate-300"></div>
                
                <div class="flex items-center gap-1 text-xs text-slate-600">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span class="font-semibold text-slate-900">{{ $stats['students'] }}</span>
                    <span class="text-slate-500">Students</span>
                </div>
                
                <div class="w-px h-4 bg-slate-300"></div>
                
                <div class="flex items-center gap-1 text-xs text-slate-600">
                    <svg class="w-3.5 h-3.5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span class="font-semibold text-slate-900">{{ $stats['programs'] }}</span>
                    <span class="text-slate-500">Programs</span>
                </div>
            </div>
        @endif

        <!-- User Dropdown Menu -->
        <div class="relative" x-data="{ userMenuOpen: false }">
            <button @click="userMenuOpen = !userMenuOpen" @click.away="userMenuOpen = false" class="flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-full pr-2">
                <div class="w-8 h-8 rounded-full overflow-hidden bg-gray-200 border border-gray-300">
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                </div>
                <div class="hidden md:flex flex-col items-start translate-y-0.5">
                    <span class="text-sm font-semibold text-gray-700 leading-none">{{ auth()->user()->name }}</span>
                    <span class="text-xs text-gray-500 capitalize">{{ auth()->user()->roles->pluck('name')->implode(', ') }}</span>
                </div>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            <!-- Dropdown -->
            <div x-show="userMenuOpen" x-transition.opacity.duration.200ms class="absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-50 overflow-hidden" x-cloak>
                <div class="px-4 py-3 border-b border-gray-100 mb-1">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                    
                    <!-- Role & Department Badges -->
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach(auth()->user()->roles as $role)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                {{ ucfirst($role->name) }}
                            </span>
                        @endforeach
                        
                        @if($department)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                {{ $department->name }}
                            </span>
                        @endif
                        
                        @if($designation)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-violet-100 text-violet-800">
                                {{ $designation }}
                            </span>
                        @endif
                    </div>
                </div>
                
                <a href="{{ route('admin.settings.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary-600 transition-colors">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        My Profile
                    </div>
                </a>
                <a href="{{ route('admin.settings.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary-600 transition-colors">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Settings
                    </div>
                </a>
                
                <div class="border-t border-gray-100 my-1"></div>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Sign Out
                        </div>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
