@extends('layouts.app')

@section('title', 'My Classes')

@section('content')
<div class="space-y-6">
    {{-- ═══════════════════════════════════════════════════════════
         1. PAGE HEADER
    ═══════════════════════════════════════════════════════════ --}}
    <x-page-header 
        title="My Classes" 
        subtitle="View all your assigned subjects and enrolled students"
        icon="user-group"
    >
        <x-slot:breadcrumb>
            <x-breadcrumb-item href="{{ route('teacher.dashboard') }}" icon="home">Dashboard</x-breadcrumb-item>
            <x-breadcrumb-item>My Classes</x-breadcrumb-item>
        </x-slot:breadcrumb>

        <x-slot:actions>
            <span class="text-xs text-slate-500">
                {{ $subjects->count() }} subjects assigned
            </span>
        </x-slot:actions>
    </x-page-header>

    {{-- ═══════════════════════════════════════════════════════════
         2. SEARCH & FILTERS
    ═══════════════════════════════════════════════════════════ --}}
    <x-search-filter>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <x-input 
                    name="search" 
                    placeholder="Search subjects..." 
                    :value="request('search')"
                    icon="search"
                />
            </div>
            
            <div>
                <x-select name="program_id" placeholder="All Programs">
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                            {{ $program->name }}
                        </option>
                    @endforeach
                </x-select>
            </div>

            <div>
                <x-select name="semester" placeholder="All Semesters">
                    @foreach($semesters as $semester)
                        <option value="{{ $semester }}" {{ request('semester') == $semester ? 'selected' : '' }}>
                            Semester {{ $semester }}
                        </option>
                    @endforeach
                </x-select>
            </div>
        </div>
    </x-search-filter>

    {{-- ═══════════════════════════════════════════════════════════
         3. SUBJECTS LIST WITH VIEW TOGGLE
    ═══════════════════════════════════════════════════════════ --}}
    <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm" 
         x-data="{ 
             view: localStorage.getItem('mmp_teacher_classes_view') || 'cards',
             toggleView(newView) {
                 this.view = newView;
                 localStorage.setItem('mmp_teacher_classes_view', newView);
             }
         }">
        <div class="border-b border-slate-100 px-6 py-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">My Subjects</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Showing {{ $subjects->count() }} subjects
                    </p>
                </div>
                
                <div class="flex items-center gap-2">
                    <div class="flex rounded-lg bg-slate-100 p-1">
                        <button @click="toggleView('table')" 
                                :class="view === 'table' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                class="flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition-all">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            List
                        </button>
                        <button @click="toggleView('cards')" 
                                :class="view === 'cards' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                class="flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition-all">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            Cards
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table/List View --}}
        <div x-show="view === 'table'" class="overflow-hidden">
            @if($subjects->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50">
                                <th class="px-6 py-3 text-left font-semibold text-slate-700">Subject</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-700">Code</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-700">Program</th>
                                <th class="px-6 py-3 text-center font-semibold text-slate-700">Semester</th>
                                <th class="px-6 py-3 text-center font-semibold text-slate-700">Section</th>
                                <th class="px-6 py-3 text-center font-semibold text-slate-700">Students</th>
                                <th class="px-6 py-3 text-center font-semibold text-slate-700">Credit Hrs</th>
                                <th class="px-6 py-3 text-right font-semibold text-slate-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($subjects as $subject)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                                                <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.832 18.477 19.246 18 17.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                </svg>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-slate-900 truncate">{{ $subject->name }}</p>
                                                <p class="text-xs text-slate-500">{{ $subject->program->department->name }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-medium text-slate-900">{{ $subject->code }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-slate-900">{{ $subject->program->name }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <x-badge variant="blue">{{ $subject->semester }}</x-badge>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <x-badge variant="slate">{{ $subject->section_taught }}</x-badge>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="font-semibold text-slate-900">{{ $subject->student_count }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-slate-900">{{ $subject->credit_hours ?? 'N/A' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <x-btn 
                                            href="{{ route('teacher.students.index', ['program_id' => $subject->program_id, 'semester' => $subject->semester]) }}" 
                                            variant="ghost" 
                                            size="sm"
                                            icon="eye"
                                        >
                                            View Students
                                        </x-btn>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-12">
                    <x-empty-state 
                        icon="user-group"
                        title="No classes found"
                        description="No subjects match your current filters or you don't have any assigned subjects."
                    />
                </div>
            @endif
        </div>

        {{-- Cards View --}}
        <div x-show="view === 'cards'" class="p-6">
            @if($subjects->count() > 0)
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($subjects as $subject)
                        <x-card class="group transition-all hover:shadow-md hover:-translate-y-0.5">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.832 18.477 19.246 18 17.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <x-badge variant="blue" size="sm">
                                    Sem {{ $subject->semester }}
                                </x-badge>
                            </div>

                            <h3 class="text-lg font-semibold text-slate-900 mb-1 line-clamp-2">{{ $subject->name }}</h3>
                            <p class="text-sm text-slate-600 mb-1">{{ $subject->code }}</p>
                            <p class="text-xs text-slate-500 mb-3">{{ $subject->program->name }}</p>

                            <div class="pt-3 border-t border-slate-100 space-y-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-500">Students</span>
                                    <span class="font-semibold text-slate-900">{{ $subject->student_count }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-500">Section</span>
                                    <span class="font-semibold text-slate-900">{{ $subject->section_taught }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-500">Credit Hours</span>
                                    <span class="font-semibold text-slate-900">{{ $subject->credit_hours ?? 'N/A' }}</span>
                                </div>
                            </div>

                            <div class="mt-4 pt-4 border-t border-slate-100">
                                <a href="{{ route('teacher.students.index', ['program_id' => $subject->program_id, 'semester' => $subject->semester]) }}" 
                                   class="flex items-center justify-between text-sm text-blue-600 font-medium group-hover:text-blue-700">
                                    <span>View Students</span>
                                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </x-card>
                    @endforeach
                </div>
            @else
                <x-empty-state 
                    icon="user-group"
                    title="No classes found"
                    description="No subjects match your current filters or you don't have any assigned subjects."
                />
            @endif
        </div>
    </div>
</div>
@endsection
