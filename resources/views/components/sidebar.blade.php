<aside 
    class="fixed inset-y-0 left-0 z-50 w-72 bg-gray-900 text-white transition-transform duration-300 ease-in-out transform lg:static lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    x-cloak>
    
    <div class="flex items-center justify-center h-16 border-b border-gray-800 px-4">
        <a href="{{ route('dashboard') }}" class="text-xl font-bold tracking-tight text-white flex items-center gap-2">
            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            MMP CMS
        </a>
    </div>

    <div class="h-[calc(100vh-4rem)] overflow-y-auto px-3 py-4 space-y-1">
        
        <x-sidebar-link href="{{ route('dashboard') }}" icon="home" :active="request()->routeIs('dashboard')">
            Dashboard
        </x-sidebar-link>

        @if(auth()->user()->isPrincipal() || auth()->user()->isHod())
            <div class="pt-4 pb-2">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Management</p>
            </div>
            
            @if(auth()->user()->isPrincipal())
            <x-sidebar-link href="#" icon="office-building">Departments</x-sidebar-link>
            <x-sidebar-link href="#" icon="users">User Management</x-sidebar-link>
            <x-sidebar-link href="#" icon="academic-cap">Academic Sessions</x-sidebar-link>
            @endif

            <x-sidebar-link href="#" icon="user-group">Students</x-sidebar-link>
            <x-sidebar-link href="#" icon="briefcase">Teachers</x-sidebar-link>
            <x-sidebar-link href="#" icon="calendar">Timetables</x-sidebar-link>
        @endif

        @if(auth()->user()->isTeacher() || auth()->user()->isHod() || auth()->user()->isPrincipal())
            <div class="pt-4 pb-2">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Academic</p>
            </div>
            
            <x-sidebar-link href="#" icon="clipboard-check">Attendance</x-sidebar-link>
            <x-sidebar-link href="#" icon="document-text">Exams</x-sidebar-link>
            <x-sidebar-link href="#" icon="chart-bar">Marks & Results</x-sidebar-link>
            <x-sidebar-link href="#" icon="document-report">Reports</x-sidebar-link>
        @endif

        @if(auth()->user()->isStudent() || auth()->user()->isParent())
            <div class="pt-4 pb-2">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">My Academic</p>
            </div>
            
            <x-sidebar-link href="#" icon="clipboard-check">My Attendance</x-sidebar-link>
            <x-sidebar-link href="#" icon="chart-bar">My Results</x-sidebar-link>
            <x-sidebar-link href="#" icon="calendar">My Timetable</x-sidebar-link>
        @endif
        
        <div class="pt-4 pb-2">
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">General</p>
        </div>
        <x-sidebar-link href="#" icon="bell">Notices</x-sidebar-link>
    </div>
</aside>
