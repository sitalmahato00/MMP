{{-- Teacher Header --}}
<div class="flex items-center gap-4 mb-6">
    @if($teacher->user?->avatar)
        <img src="{{ asset('storage/'.$teacher->user->avatar) }}" alt="" 
             class="h-16 w-16 rounded-2xl object-cover ring-2 ring-slate-200">
    @else
        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-2xl font-bold text-white">
            {{ strtoupper(substr($teacher->user?->name ?? 'T', 0, 1)) }}
        </div>
    @endif
    <div>
        <h3 class="text-lg font-bold text-slate-800">{{ $teacher->user?->name }}</h3>
        <p class="text-sm text-slate-500">{{ $teacher->designation }} • {{ $teacher->employee_id }}</p>
        <div class="mt-1 flex items-center gap-2">
            @php
                $isActive = $teacher->is_active;
                $statusText = $isActive ? 'Active' : 'Inactive';
                $statusClass = $isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600';
            @endphp
            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusClass }}">
                {{ $statusText }}
            </span>
        </div>
    </div>
</div>

{{-- Quick Stats --}}
<div class="grid grid-cols-3 gap-3 mb-6">
    <div class="rounded-xl bg-blue-50 p-3 text-center">
        <p class="text-lg font-bold text-blue-600">{{ $subjectsCount }}</p>
        <p class="text-xs text-slate-500">Subjects</p>
    </div>
    <div class="rounded-xl bg-emerald-50 p-3 text-center">
        <p class="text-lg font-bold text-emerald-600">{{ $studentsCount }}</p>
        <p class="text-xs text-slate-500">Students</p>
    </div>
    <div class="rounded-xl bg-violet-50 p-3 text-center">
        <p class="text-lg font-bold text-violet-600">{{ $assignmentsCount }}</p>
        <p class="text-xs text-slate-500">Assignments</p>
    </div>
</div>

{{-- Tabs --}}
<div x-data="{ activeTab: 'overview' }" class="space-y-4">
    {{-- Tab Navigation --}}
    <div class="flex space-x-1 rounded-xl bg-slate-100 p-1">
        <button @click="activeTab = 'overview'" 
                :class="activeTab === 'overview' ? 'bg-white shadow-sm' : ''" 
                class="flex-1 rounded-lg px-3 py-2 text-xs font-semibold transition">
            Overview
        </button>
        <button @click="activeTab = 'contact'" 
                :class="activeTab === 'contact' ? 'bg-white shadow-sm' : ''" 
                class="flex-1 rounded-lg px-3 py-2 text-xs font-semibold transition">
            Contact
        </button>
        <button @click="activeTab = 'professional'" 
                :class="activeTab === 'professional' ? 'bg-white shadow-sm' : ''" 
                class="flex-1 rounded-lg px-3 py-2 text-xs font-semibold transition">
            Professional
        </button>
    </div>

    {{-- Overview Tab --}}
    <div x-show="activeTab === 'overview'" class="space-y-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <h4 class="mb-3 text-sm font-bold text-slate-700">Basic Information</h4>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Employee ID</dt>
                    <dd class="font-mono text-slate-800">{{ $teacher->employee_id }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Department</dt>
                    <dd class="text-slate-800">{{ $teacher->department?->name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Designation</dt>
                    <dd class="text-slate-800">{{ $teacher->designation }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Status</dt>
                    <dd class="text-slate-800">{{ $statusText }}</dd>
                </div>
                @if($teacher->join_date)
                <div class="flex justify-between">
                    <dt class="text-slate-500">Joined</dt>
                    <dd class="text-slate-800">{{ bsDate($teacher->join_date, 'Y, F d') }}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

    {{-- Contact Tab --}}
    <div x-show="activeTab === 'contact'" class="space-y-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <h4 class="mb-3 text-sm font-bold text-slate-700">Contact Information</h4>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Email</dt>
                    <dd class="text-slate-800 break-all">{{ $teacher->user?->email }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Phone</dt>
                    <dd class="text-slate-800">{{ $teacher->user?->phone ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Gender</dt>
                    <dd class="text-slate-800">{{ ucfirst($teacher->user?->gender ?? '—') }}</dd>
                </div>
                @if($teacher->user?->dob)
                <div class="flex justify-between">
                    <dt class="text-slate-500">Date of Birth</dt>
                    <dd class="text-slate-800">{{ bsDate($teacher->user->dob, 'Y, F d') }}</dd>
                </div>
                @endif
                @if($teacher->user?->address)
                <div class="flex justify-between">
                    <dt class="text-slate-500">Address</dt>
                    <dd class="text-slate-800 text-right">{{ $teacher->user->address }}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

    {{-- Professional Tab --}}
    <div x-show="activeTab === 'professional'" class="space-y-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <h4 class="mb-3 text-sm font-bold text-slate-700">Professional Details</h4>
            <dl class="space-y-2 text-sm">
                @if($teacher->qualification)
                <div class="flex justify-between">
                    <dt class="text-slate-500">Qualification</dt>
                    <dd class="text-slate-800 text-right">{{ $teacher->qualification }}</dd>
                </div>
                @endif
                @if($teacher->specialization)
                <div class="flex justify-between">
                    <dt class="text-slate-500">Specialization</dt>
                    <dd class="text-slate-800 text-right">{{ $teacher->specialization }}</dd>
                </div>
                @endif
                @if($teacher->employment_type)
                <div class="flex justify-between">
                    <dt class="text-slate-500">Employment</dt>
                    <dd class="text-slate-800">{{ ucfirst($teacher->employment_type) }}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>
</div>

{{-- Actions --}}
<div class="mt-6 flex gap-2">
    <a href="{{ route('hod.teachers.show', $teacher) }}" 
       class="flex-1 rounded-xl bg-[#1d4ed8] px-4 py-2 text-center text-sm font-bold text-white hover:bg-[#1e40af] transition">
        View Full Profile
    </a>
    <a href="{{ route('hod.teachers.edit', $teacher) }}" 
       class="flex-1 rounded-xl bg-slate-100 px-4 py-2 text-center text-sm font-bold text-slate-700 hover:bg-slate-200 transition">
        Edit
    </a>
</div>