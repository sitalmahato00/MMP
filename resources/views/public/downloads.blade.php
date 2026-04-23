@extends('layouts.guest')
@section('title', 'Downloads & Resources')
@section('breadcrumb', true)

@section('content')
@php
    $activeResourceCategory = trim((string) request('category'));
    $activeDepartment = trim((string) request('department'));
    $searchTerm = trim((string) request('search'));
    $resourceFilters = [
        ['label' => 'All', 'category' => null],
        ['label' => 'Forms', 'category' => 'forms'],
        ['label' => 'Syllabus', 'category' => 'syllabus'],
        ['label' => 'Notes', 'category' => 'notes'],
        ['label' => 'Question Bank', 'category' => 'question-bank'],
        ['label' => 'Reports & Publications', 'category' => 'reports'],
    ];
@endphp

<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <div class="section-header" style="background-color: #003D82;">📥 Downloads & Resources</div>
            
            {{-- Advanced Filters --}}
            <div class="bg-white border border-gray-200 border-t-0 rounded-lg shadow-md p-4 mb-4">
                <form method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Search Input --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-2">Search Resources</label>
                            <input 
                                type="text" 
                                name="search" 
                                value="{{ $searchTerm }}"
                                placeholder="Search by title..." 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 text-sm"
                            />
                        </div>
                        
                        {{-- Department Filter --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-2">Department</label>
                            <select name="department" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 text-sm">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->code }}" @selected($activeDepartment === $dept->code)>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Category Filter --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-2">Category</label>
                            <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 text-sm">
                                <option value="">All Categories</option>
                                <option value="forms" @selected($activeResourceCategory === 'forms')>Forms</option>
                                <option value="syllabus" @selected($activeResourceCategory === 'syllabus')>Syllabus</option>
                                <option value="notes" @selected($activeResourceCategory === 'notes')>Notes</option>
                                <option value="question-bank" @selected($activeResourceCategory === 'question-bank')>Question Bank</option>
                                <option value="reports" @selected($activeResourceCategory === 'reports')>Reports & Publications</option>
                            </select>
                        </div>
                    </div>
                    
                    {{-- Filter Buttons --}}
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold rounded-lg transition-colors">
                            Filter
                        </button>
                        <a href="{{ route('public.downloads') }}" class="px-4 py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-lg transition-colors">
                            Clear
                        </a>
                    </div>
                </form>
            </div>
            
            {{-- Category Quick Filters --}}
            <div class="bg-white border border-gray-200 rounded-lg shadow-md p-4 mb-4">
                <div class="flex flex-wrap gap-2">
                    @foreach($resourceFilters as $filter)
                        @php $isActive = $activeResourceCategory === (string) ($filter['category'] ?? ''); @endphp
                        <a href="{{ $filter['category'] ? route('public.downloads', ['category' => $filter['category']]) : route('public.downloads') }}"
                           class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors {{ $isActive ? 'bg-blue-700 text-white border-blue-700' : 'bg-gray-50 text-gray-600 border-gray-200 hover:text-blue-700 hover:border-blue-700' }}">
                            {{ $filter['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
            
            {{-- Results Count --}}
            <div class="mb-4 text-sm text-gray-600 font-medium">
                Showing {{ $downloads->count() }} resource(s)
            </div>
            
            {{-- Downloads List --}}
            <div class="bg-white border border-gray-200 rounded-lg shadow-md overflow-hidden">
                @forelse($downloads as $download)
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 px-5 py-4 border-b border-gray-100 last:border-0 hover:bg-blue-50 transition-colors group">
                        {{-- File Icon --}}
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-100 text-blue-700 rounded flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        
                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-sm text-gray-900 group-hover:text-blue-800 transition-colors">{{ $download->title }}</div>
                            
                            {{-- Details --}}
                            <div class="flex flex-wrap gap-2 mt-2">
                                @if($download->category)
                                    <span class="text-xs text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-100 uppercase font-bold">{{ $download->category }}</span>
                                @endif
                                
                                @if($download->department)
                                    <span class="text-xs text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-100 font-semibold">{{ $download->department->name }}</span>
                                @endif
                                
                                <span class="text-xs text-gray-500">{{ bsDate($download->created_at, 'Y, F d') }}</span>
                            </div>
                        </div>
                        
                        {{-- Actions --}}
                        <div class="flex items-center gap-2 flex-shrink-0 w-full sm:w-auto">
                            <a href="{{ route('public.downloads.file', $download) }}" target="_blank" rel="noopener" class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-3 py-2 text-xs font-bold rounded transition-colors border border-blue-700 text-blue-700 hover:bg-blue-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Download
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-16 text-center">
                        <p class="text-5xl mb-4">📂</p>
                        <p class="text-gray-500 font-medium">
                            @if($searchTerm !== '' || $activeDepartment !== '' || $activeResourceCategory !== '')
                                No resources found matching your filters.
                            @else
                                No downloads available yet.
                            @endif
                        </p>
                        <p class="text-sm text-gray-400 mt-2">Please check back later or contact the college office.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Sidebar --}}
        <div>
            <div class="section-header" style="background-color: #003D82;">⚡ Quick Links</div>
            <div class="bg-white border border-gray-200 border-t-0 rounded-b-lg shadow-md">
                <a href="{{ route('public.notices') }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition-colors"><span class="text-blue-600">›</span> Notice Board</a>
                <a href="{{ route('public.downloads', ['category' => 'syllabus']) }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition-colors"><span class="text-blue-600">›</span> Syllabus</a>
                <a href="{{ route('public.downloads', ['category' => 'notes']) }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition-colors"><span class="text-blue-600">›</span> Notes</a>
                <a href="{{ route('public.question-bank') }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition-colors"><span class="text-blue-600">›</span> Question Bank</a>
                <a href="{{ route('public.downloads') }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-800 transition-colors"><span class="text-blue-600">›</span> All Resources</a>
                <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-blue-800"><span>🔐</span> Student Portal</a>
            </div>
        </div>
    </div>
</div>
@endsection

