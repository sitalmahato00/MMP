@php
    $statusMap = [
        'active'    => ['label'=>'Active',     'cls'=>'bg-blue-50 text-blue-700'],
        'inactive'  => ['label'=>'Inactive',   'cls'=>'bg-slate-100 text-slate-600'],
        'graduated' => ['label'=>'Alumni',     'cls'=>'bg-emerald-50 text-emerald-700'],
        'suspended' => ['label'=>'Suspended',  'cls'=>'bg-amber-50 text-amber-700'],
        'dropped'   => ['label'=>'Dropped',    'cls'=>'bg-red-50 text-red-700'],
    ];
    $st = $statusMap[$student->status] ?? ['label'=>ucfirst($student->status),'cls'=>'bg-slate-100 text-slate-600'];
    $initials = strtoupper(substr($student->user?->name ?? 'S', 0, 1));
    $gradients = ['from-blue-500 to-indigo-600','from-violet-500 to-purple-600','from-emerald-500 to-teal-600','from-amber-500 to-orange-600','from-rose-500 to-pink-600','from-cyan-500 to-sky-600'];
    $grad = $gradients[$student->id % 6];
@endphp

<div x-data="{ activeTab: 'overview' }" class="h-full flex flex-col">
    {{-- Student Header --}}
    <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-6 py-6 text-white">
        <div class="flex items-start gap-4">
            @if($student->user?->avatar)
                <img src="{{ asset('storage/'.$student->user->avatar) }}" alt=""
                     class="h-16 w-16 rounded-2xl object-cover ring-2 ring-white/20 shadow-lg"/>
            @else
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br {{ $grad }} text-2xl font-black text-white shadow-lg ring-2 ring-white/20">
                    {{ $initials }}
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <h3 class="text-xl font-bold truncate">{{ $student->user?->name }}</h3>
                <p class="text-sm text-slate-300 font-mono">{{ $student->student_no }}</p>
                <div class="flex items-center gap-3 mt-2">
                    <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold {{ $st['cls'] }}">
                        <span class="h-1.5 w-1.5 rounded-full bg-current opacity-60"></span>
                        {{ $st['label'] }}
                    </span>
                    <span class="inline-flex items-center rounded-lg bg-violet-50 px-2.5 py-1 text-xs font-bold text-violet-700">
                        Sem {{ $student->current_semester }}
                    </span>
                </div>
                <p class="text-sm text-slate-300 mt-1">{{ $student->program?->name ?? 'No program assigned' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('hod.students.edit', $student) }}" 
                   class="inline-flex items-center gap-2 rounded-lg bg-white/10 px-3 py-2 text-sm font-semibold text-white hover:bg-white/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
                <a href="{{ route('hod.students.show', $student) }}" 
                   class="inline-flex items-center gap-2 rounded-lg bg-white/10 px-3 py-2 text-sm font-semibold text-white hover:bg-white/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Full page
                </a>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <div class="border-b border-slate-200 bg-white">
        <nav class="flex px-6" aria-label="Tabs">
            <button @click="activeTab = 'overview'" 
                    :class="activeTab === 'overview' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm mr-8 transition">
                Overview
            </button>
            <button @click="activeTab = 'attendance'" 
                    :class="activeTab === 'attendance' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm mr-8 transition">
                Attendance
            </button>
            <button @click="activeTab = 'marks'" 
                    :class="activeTab === 'marks' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm mr-8 transition">
                Marks
            </button>
            <button @click="activeTab = 'assignments'" 
                    :class="activeTab === 'assignments' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm mr-8 transition">
                Assignments
            </button>
            <button @click="activeTab = 'parent'" 
                    :class="activeTab === 'parent' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm mr-8 transition">
                Parent
            </button>
            <button @click="activeTab = 'timeline'" 
                    :class="activeTab === 'timeline' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                Timeline
            </button>
        </nav>
    </div>

    {{-- Tab Content --}}
    <div class="flex-1 overflow-y-auto bg-slate-50">
        {{-- Overview Tab --}}
        <div x-show="activeTab === 'overview'" class="p-6">
            {{-- Quick Stats --}}
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl p-4 text-center border border-slate-200">
                    <div class="text-2xl font-bold text-slate-900">{{ $attendanceRate }}%</div>
                    <div class="text-xs text-slate-500 mt-1">Attendance</div>
                </div>
                <div class="bg-white rounded-xl p-4 text-center border border-slate-200">
                    <div class="text-2xl font-bold text-slate-900">{{ $examRecords }}</div>
                    <div class="text-xs text-slate-500 mt-1">Exam records</div>
                </div>
                <div class="bg-white rounded-xl p-4 text-center border border-slate-200">
                    <div class="text-2xl font-bold text-slate-900">{{ $assignments }}</div>
                    <div class="text-xs text-slate-500 mt-1">Assignments</div>
                </div>
            </div>

            {{-- Personal Information --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h4 class="text-sm font-bold text-slate-900 mb-4 uppercase tracking-wider">Personal</h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <label class="text-slate-500 text-xs">Email</label>
                        <p class="font-medium text-slate-900">{{ $student->user?->email ?? '—' }}</p>
                    </div>
                    <div>
                        <label class="text-slate-500 text-xs">Phone</label>
                        <p class="font-medium text-slate-900">{{ $student->user?->phone ?? '—' }}</p>
                    </div>
                    <div>
                        <label class="text-slate-500 text-xs">Gender</label>
                        <p class="font-medium text-slate-900">{{ $student->user?->gender ? ucfirst($student->user->gender) : '—' }}</p>
                    </div>
                    <div>
                        <label class="text-slate-500 text-xs">Date of Birth</label>
                        <p class="font-medium text-slate-900">{{ $student->user?->dob ? bsDate($student->user->dob, 'F d, Y') : '—' }}</p>
                    </div>
                    <div class="col-span-2">
                        <label class="text-slate-500 text-xs">Address</label>
                        <p class="font-medium text-slate-900">{{ $student->user?->address ?? '—' }}</p>
                    </div>
                    <div>
                        <label class="text-slate-500 text-xs">Blood Group</label>
                        <p class="font-medium text-slate-900">{{ $student->blood_group ?? '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- Academic Information --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 mt-4">
                <h4 class="text-sm font-bold text-slate-900 mb-4 uppercase tracking-wider">Academic</h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <label class="text-slate-500 text-xs">Student Number</label>
                        <p class="font-medium text-slate-900 font-mono">{{ $student->student_no }}</p>
                    </div>
                    <div>
                        <label class="text-slate-500 text-xs">Roll Number</label>
                        <p class="font-medium text-slate-900 font-mono">{{ $student->roll_number ?? '—' }}</p>
                    </div>
                    <div>
                        <label class="text-slate-500 text-xs">Registration Number</label>
                        <p class="font-medium text-slate-900 font-mono">{{ $student->registration_number ?? '—' }}</p>
                    </div>
                    <div>
                        <label class="text-slate-500 text-xs">Current Semester</label>
                        <p class="font-medium text-slate-900">{{ $student->current_semester }}</p>
                    </div>
                    <div>
                        <label class="text-slate-500 text-xs">Section</label>
                        <p class="font-medium text-slate-900">{{ $student->section ?? '—' }}</p>
                    </div>
                    <div>
                        <label class="text-slate-500 text-xs">Batch</label>
                        <p class="font-medium text-slate-900">{{ $student->batch ?? '—' }}</p>
                    </div>
                    <div>
                        <label class="text-slate-500 text-xs">Admission Date</label>
                        <p class="font-medium text-slate-900">{{ $student->admission_date ? bsDate($student->admission_date, 'F d, Y') : '—' }}</p>
                    </div>
                    <div>
                        <label class="text-slate-500 text-xs">Academic Session</label>
                        <p class="font-medium text-slate-900">{{ $student->academicSession?->name ?? '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- Guardian Information --}}
            @if($student->guardian_name || $student->guardian_phone)
            <div class="bg-white rounded-xl border border-slate-200 p-6 mt-4">
                <h4 class="text-sm font-bold text-slate-900 mb-4 uppercase tracking-wider">Guardian</h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <label class="text-slate-500 text-xs">Guardian Name</label>
                        <p class="font-medium text-slate-900">{{ $student->guardian_name ?? '—' }}</p>
                    </div>
                    <div>
                        <label class="text-slate-500 text-xs">Guardian Phone</label>
                        <p class="font-medium text-slate-900">{{ $student->guardian_phone ?? '—' }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Attendance Tab --}}
        <div x-show="activeTab === 'attendance'" class="p-6">
            <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">Attendance Records</h3>
                <p class="text-slate-500">Attendance tracking will be displayed here.</p>
            </div>
        </div>

        {{-- Marks Tab --}}
        <div x-show="activeTab === 'marks'" class="p-6">
            <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">Exam Results</h3>
                <p class="text-slate-500">Student marks and grades will be displayed here.</p>
            </div>
        </div>

        {{-- Assignments Tab --}}
        <div x-show="activeTab === 'assignments'" class="p-6">
            <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">Assignments</h3>
                <p class="text-slate-500">Student assignments and submissions will be displayed here.</p>
            </div>
        </div>

        {{-- Parent Tab --}}
        <div x-show="activeTab === 'parent'" class="p-6">
            @if($student->parents->count() > 0)
                <div class="space-y-4">
                    @foreach($student->parents as $parent)
                    <div class="bg-white rounded-xl border border-slate-200 p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-lg font-black text-white">
                                {{ strtoupper(substr($parent->user?->name ?? 'P', 0, 1)) }}
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-slate-900">{{ $parent->user?->name }}</h4>
                                <p class="text-sm text-slate-500">{{ $parent->user?->email }}</p>
                                <div class="mt-2 grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <label class="text-slate-500 text-xs">Relation</label>
                                        <p class="font-medium text-slate-900">{{ $parent->relation_to_student ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-slate-500 text-xs">Occupation</label>
                                        <p class="font-medium text-slate-900">{{ $parent->occupation ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <label class="text-slate-500 text-xs">Phone</label>
                                        <p class="font-medium text-slate-900">{{ $parent->user?->phone ?? '—' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-slate-900 mb-2">No Parent Linked</h3>
                    <p class="text-slate-500">No parent account has been linked to this student.</p>
                </div>
            @endif
        </div>

        {{-- Timeline Tab --}}
        <div x-show="activeTab === 'timeline'" class="p-6">
            <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-lg font-semibold text-slate-900 mb-2">Activity Timeline</h3>
                <p class="text-slate-500">Student activity timeline will be displayed here.</p>
            </div>
        </div>
    </div>
</div>