{{-- Subject Header --}}
<div class="flex items-center gap-4 mb-6">
    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
    </div>
    <div>
        <h3 class="text-lg font-bold text-slate-800">{{ $subject->name }}</h3>
        <p class="text-sm text-slate-500 font-mono">{{ $subject->code }}</p>
        <div class="mt-1 flex items-center gap-2">
            <span class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">
                Semester {{ $subject->semester }}
            </span>
            @if($subject->is_active)
                <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                    Active
                </span>
            @else
                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">
                    Inactive
                </span>
            @endif
        </div>
    </div>
</div>

{{-- Quick Stats --}}
<div class="grid grid-cols-2 gap-3 mb-6 sm:grid-cols-4">
    <div class="rounded-xl bg-blue-50 p-3 text-center">
        <p class="text-lg font-bold text-blue-600">{{ $subject->total_full_marks }}</p>
        <p class="text-xs text-slate-500">Total Marks</p>
    </div>
    <div class="rounded-xl bg-emerald-50 p-3 text-center">
        <p class="text-lg font-bold text-emerald-600">{{ $subject->credit_hours ?? 0 }}</p>
        <p class="text-xs text-slate-500">Credit Hours</p>
    </div>
    <div class="rounded-xl bg-violet-50 p-3 text-center">
        <p class="text-lg font-bold text-violet-600">{{ $assignedTeachers->count() }}</p>
        <p class="text-xs text-slate-500">Teachers</p>
    </div>
    <div class="rounded-xl bg-amber-50 p-3 text-center">
        <p class="text-lg font-bold text-amber-600">{{ $subject->syllabus ? 'Yes' : 'No' }}</p>
        <p class="text-xs text-slate-500">Syllabus</p>
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
        <button @click="activeTab = 'marks'" 
                :class="activeTab === 'marks' ? 'bg-white shadow-sm' : ''" 
                class="flex-1 rounded-lg px-3 py-2 text-xs font-semibold transition">
            Marking Scheme
        </button>
        <button @click="activeTab = 'teachers'" 
                :class="activeTab === 'teachers' ? 'bg-white shadow-sm' : ''" 
                class="flex-1 rounded-lg px-3 py-2 text-xs font-semibold transition">
            Teachers
        </button>
    </div>

    {{-- Overview Tab --}}
    <div x-show="activeTab === 'overview'" class="space-y-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <h4 class="mb-3 text-sm font-bold text-slate-700">Basic Information</h4>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Subject Code</dt>
                    <dd class="font-mono text-slate-800">{{ $subject->code }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Program</dt>
                    <dd class="text-slate-800">{{ $subject->program->name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Program Code</dt>
                    <dd class="font-mono text-slate-800">{{ $subject->program->code }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Semester</dt>
                    <dd class="text-slate-800">Semester {{ $subject->semester }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Type</dt>
                    <dd class="text-slate-800 capitalize">{{ $subject->type }}</dd>
                </div>
                @if($subject->credit_hours)
                <div class="flex justify-between">
                    <dt class="text-slate-500">Credit Hours</dt>
                    <dd class="text-slate-800">{{ $subject->credit_hours }}</dd>
                </div>
                @endif
                <div class="flex justify-between">
                    <dt class="text-slate-500">Status</dt>
                    <dd class="text-slate-800">{{ $subject->is_active ? 'Active' : 'Inactive' }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <h4 class="mb-3 text-sm font-bold text-slate-700">Subject Details</h4>
            <p class="whitespace-pre-line text-sm text-slate-600">{{ $subject->details ?: 'No subject details added yet.' }}</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <h4 class="mb-3 text-sm font-bold text-slate-700">Syllabus</h4>
            @if($subject->syllabus_url)
                <p class="text-sm font-semibold text-slate-900">{{ basename($subject->syllabus) }}</p>
                <a href="{{ $subject->syllabus_url }}" target="_blank" rel="noopener" class="mt-3 inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                    View Syllabus PDF
                </a>
            @else
                <p class="text-sm text-slate-500">No syllabus uploaded for this subject.</p>
            @endif
        </div>
    </div>

    {{-- Marking Scheme Tab --}}
    <div x-show="activeTab === 'marks'" class="space-y-4">
        {{-- Theory Component --}}
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <h4 class="mb-3 text-sm font-bold text-slate-700">Theory Component</h4>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Internal Theory</dt>
                    <dd class="font-semibold text-slate-800">
                        {{ $subject->pass_marks_internal_theory ?? 0 }}/{{ $subject->full_marks_internal_theory ?? 0 }}
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">External Theory</dt>
                    <dd class="font-semibold text-slate-800">
                        {{ $subject->pass_marks_external_theory ?? 0 }}/{{ $subject->full_marks_external_theory ?? 0 }}
                    </dd>
                </div>
                <div class="flex justify-between border-t border-slate-200 pt-2">
                    <dt class="text-slate-700 font-semibold">Total Theory</dt>
                    <dd class="font-bold text-slate-900">
                        {{ ($subject->pass_marks_internal_theory ?? 0) + ($subject->pass_marks_external_theory ?? 0) }}/{{ $subject->total_theory_marks }}
                    </dd>
                </div>
            </dl>
        </div>

        {{-- Practical Component --}}
        @if($subject->hasPractical())
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <h4 class="mb-3 text-sm font-bold text-slate-700">Practical Component</h4>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Internal Practical</dt>
                    <dd class="font-semibold text-slate-800">
                        {{ $subject->pass_marks_internal_practical ?? 0 }}/{{ $subject->full_marks_internal_practical ?? 0 }}
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">External Practical</dt>
                    <dd class="font-semibold text-slate-800">
                        {{ $subject->pass_marks_external_practical ?? 0 }}/{{ $subject->full_marks_external_practical ?? 0 }}
                    </dd>
                </div>
                <div class="flex justify-between border-t border-slate-200 pt-2">
                    <dt class="text-slate-700 font-semibold">Total Practical</dt>
                    <dd class="font-bold text-slate-900">
                        {{ ($subject->pass_marks_internal_practical ?? 0) + ($subject->pass_marks_external_practical ?? 0) }}/{{ $subject->total_practical_marks }}
                    </dd>
                </div>
            </dl>
        </div>
        @else
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-center">
            <p class="text-sm text-slate-500">No practical component for this subject</p>
        </div>
        @endif

        {{-- Grand Total --}}
        <div class="rounded-xl bg-blue-50 p-4">
            <div class="flex justify-between items-center">
                <span class="text-sm font-semibold text-blue-900">Grand Total Marks:</span>
                <span class="text-lg font-bold text-blue-900">
                    {{ $subject->total_pass_marks }}/{{ $subject->total_full_marks }}
                </span>
            </div>
        </div>
    </div>

    {{-- Teachers Tab --}}
    <div x-show="activeTab === 'teachers'" class="space-y-4">
        @if($currentSession)
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-3 text-xs text-blue-800">
                <p class="font-medium">Academic Session: {{ $currentSession->name }}</p>
            </div>
        @endif

        @if($assignedTeachers->isEmpty())
            <div class="rounded-xl border border-slate-200 bg-white p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <p class="mt-2 text-sm text-slate-500">No teachers assigned yet</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($assignedTeachers as $teacher)
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white font-bold">
                            {{ strtoupper(substr($teacher->user?->name ?? 'T', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-900 truncate">{{ $teacher->user?->name }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $teacher->employee_id ?? 'N/A' }}
                                @if($teacher->pivot->role)
                                    • <span class="font-medium text-blue-600">{{ $teacher->pivot->role }}</span>
                                @endif
                                @if($teacher->pivot->section)
                                    • Section: {{ $teacher->pivot->section }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- Actions --}}
<div class="mt-6 flex gap-2">
    <a href="{{ route('hod.subjects.show', $subject) }}" 
       class="flex-1 rounded-xl bg-[#1d4ed8] px-4 py-2 text-center text-sm font-bold text-white hover:bg-[#1e40af] transition">
        View Full Details
    </a>
    <a href="{{ route('hod.subjects.edit', $subject) }}" 
       class="flex-1 rounded-xl bg-slate-100 px-4 py-2 text-center text-sm font-bold text-slate-700 hover:bg-slate-200 transition">
        Edit
    </a>
</div>
