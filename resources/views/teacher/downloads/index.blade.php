@extends('layouts.app')

@section('title', 'My Resources')

@section('content')
<div class="space-y-6">
    {{-- ═══════════════════════════════════════════════════════════
         1. PAGE HEADER
    ═══════════════════════════════════════════════════════════ --}}
    <x-page-header 
        title="My Resources" 
        subtitle="Upload and manage study materials for your subjects"
        icon="download"
    >
        <x-slot:breadcrumb>
            <x-breadcrumb-item href="{{ route('teacher.dashboard') }}" icon="home">Dashboard</x-breadcrumb-item>
            <x-breadcrumb-item>Resources</x-breadcrumb-item>
        </x-slot:breadcrumb>

        <x-slot:actions>
            <x-btn href="{{ route('teacher.downloads.create') }}" variant="primary" icon="plus">
                Upload Resource
            </x-btn>
        </x-slot:actions>
    </x-page-header>

    {{-- ═══════════════════════════════════════════════════════════
         2. SEARCH & FILTERS
    ═══════════════════════════════════════════════════════════ --}}
    <x-search-filter>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <x-input 
                    name="search" 
                    placeholder="Search resources..." 
                    :value="request('search')"
                    icon="search"
                />
            </div>
            
            <div>
                <x-select name="subject_id" placeholder="All Subjects">
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </x-select>
            </div>

            <div>
                <x-select name="category" placeholder="All Categories">
                    @foreach($categories as $category)
                        <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                            {{ ucfirst($category) }}
                        </option>
                    @endforeach
                </x-select>
            </div>

            <div>
                <x-select name="visibility" placeholder="All Visibility">
                    <option value="public" {{ request('visibility') == 'public' ? 'selected' : '' }}>Public</option>
                    <option value="students" {{ request('visibility') == 'students' ? 'selected' : '' }}>Students Only</option>
                    <option value="private" {{ request('visibility') == 'private' ? 'selected' : '' }}>Private</option>
                </x-select>
            </div>
        </div>
    </x-search-filter>

    {{-- ═══════════════════════════════════════════════════════════
         3. RESOURCES LIST WITH VIEW TOGGLE
    ═══════════════════════════════════════════════════════════ --}}
    <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm" 
         x-data="{ 
             view: localStorage.getItem('mmp_teacher_downloads_view') || 'table',
             toggleView(newView) {
                 this.view = newView;
                 localStorage.setItem('mmp_teacher_downloads_view', newView);
             }
         }">
        <div class="border-b border-slate-100 px-6 py-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Resources</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Showing {{ $downloads->count() }} of {{ $downloads->total() }} resources
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

        {{-- Table View --}}
        <div x-show="view === 'table'" class="overflow-hidden">
            @if($downloads->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50">
                                <th class="px-6 py-3 text-left font-semibold text-slate-700">Resource</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-700">Subject</th>
                                <th class="px-6 py-3 text-center font-semibold text-slate-700">Category</th>
                                <th class="px-6 py-3 text-center font-semibold text-slate-700">Visibility</th>
                                <th class="px-6 py-3 text-center font-semibold text-slate-700">Size</th>
                                <th class="px-6 py-3 text-center font-semibold text-slate-700">Uploaded</th>
                                <th class="px-6 py-3 text-right font-semibold text-slate-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($downloads as $download)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                                                <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-slate-900 truncate">{{ $download->title }}</p>
                                                <p class="text-xs text-slate-500 truncate">{{ $download->file_name }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="font-medium text-slate-900">{{ $download->subject->name ?? 'N/A' }}</p>
                                            <p class="text-xs text-slate-500">{{ $download->program->name ?? 'N/A' }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <x-badge variant="slate">{{ ucfirst($download->category) }}</x-badge>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @php
                                            $visibilityVariants = [
                                                'public' => 'emerald',
                                                'students' => 'blue',
                                                'private' => 'slate',
                                            ];
                                        @endphp
                                        <x-badge :variant="$visibilityVariants[$download->visibility] ?? 'slate'">
                                            {{ ucfirst($download->visibility) }}
                                        </x-badge>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-slate-900">{{ number_format($download->file_size / 1024, 2) }} KB</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-slate-900">{{ bsDate($download->created_at, 'M d, Y') }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <x-btn 
                                                href="{{ route('teacher.downloads.file', $download) }}" 
                                                variant="ghost" 
                                                size="sm"
                                                icon="download"
                                                target="_blank"
                                            >
                                                Download
                                            </x-btn>
                                            <x-btn 
                                                href="{{ route('teacher.downloads.edit', $download) }}" 
                                                variant="ghost" 
                                                size="sm"
                                                icon="edit"
                                            >
                                                Edit
                                            </x-btn>
                                            <form method="POST" action="{{ route('teacher.downloads.destroy', $download) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this resource?')">
                                                @csrf
                                                @method('DELETE')
                                                <x-btn 
                                                    type="submit"
                                                    variant="ghost" 
                                                    size="sm"
                                                    class="text-red-600 hover:text-red-700"
                                                >
                                                    Delete
                                                </x-btn>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-12">
                    <x-empty-state 
                        icon="download"
                        title="No resources found"
                        description="No resources match your current filters or you haven't uploaded any resources yet."
                    />
                </div>
            @endif
        </div>

        {{-- Cards View --}}
        <div x-show="view === 'cards'" class="p-6">
            @if($downloads->count() > 0)
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($downloads as $download)
                        @php
                            $visibilityVariants = [
                                'public' => 'emerald',
                                'students' => 'blue',
                                'private' => 'slate',
                            ];
                        @endphp
                        <x-card class="group transition-all hover:shadow-md hover:-translate-y-0.5">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <x-badge :variant="$visibilityVariants[$download->visibility] ?? 'slate'" size="sm">
                                    {{ ucfirst($download->visibility) }}
                                </x-badge>
                            </div>
                            
                            <h3 class="font-semibold text-slate-900 line-clamp-2 mb-2">{{ $download->title }}</h3>
                            <p class="text-xs text-slate-500 mb-3 truncate">{{ $download->file_name }}</p>
                            
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-500">Subject</span>
                                    <span class="font-medium text-slate-900 truncate ml-2">{{ $download->subject->name ?? 'N/A' }}</span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-500">Category</span>
                                    <span class="font-medium text-slate-900">{{ ucfirst($download->category) }}</span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-500">Size</span>
                                    <span class="font-medium text-slate-900">{{ number_format($download->file_size / 1024, 2) }} KB</span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-500">Uploaded</span>
                                    <span class="font-medium text-slate-900">{{ bsDate($download->created_at, 'M d, Y') }}</span>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2 pt-3 border-t border-slate-100">
                                <x-btn 
                                    href="{{ route('teacher.downloads.file', $download) }}" 
                                    variant="ghost" 
                                    size="sm" 
                                    class="flex-1 justify-center"
                                    icon="download"
                                    target="_blank"
                                >
                                    Download
                                </x-btn>
                                <x-btn 
                                    href="{{ route('teacher.downloads.edit', $download) }}" 
                                    variant="ghost" 
                                    size="sm"
                                    icon="edit"
                                >
                                    Edit
                                </x-btn>
                                <form method="POST" action="{{ route('teacher.downloads.destroy', $download) }}" class="inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-btn 
                                        type="submit"
                                        variant="ghost" 
                                        size="sm"
                                        class="text-red-600 hover:text-red-700"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </x-btn>
                                </form>
                            </div>
                        </x-card>
                    @endforeach
                </div>
            @else
                <x-empty-state 
                    icon="download"
                    title="No resources found"
                    description="No resources match your current filters or you haven't uploaded any resources yet."
                />
            @endif
        </div>

        {{-- Pagination --}}
        @if($downloads->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $downloads->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
