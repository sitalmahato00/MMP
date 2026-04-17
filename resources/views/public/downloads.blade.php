@extends('layouts.guest')
@section('title', 'Downloads & Resources')
@section('breadcrumb', true)

@section('content')
@php
    $activeResourceCategory = trim((string) request('category'));
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
            <div class="section-header" style="background-color: #8B0000;">📥 Downloads & Resources</div>
            <div class="bg-white border border-gray-200 border-t-0">
                <div class="p-4 border-b border-gray-100 flex flex-wrap gap-2">
                    @foreach($resourceFilters as $filter)
                        @php $isActive = $activeResourceCategory === (string) ($filter['category'] ?? ''); @endphp
                        <a href="{{ $filter['category'] ? route('public.downloads', ['category' => $filter['category']]) : route('public.downloads') }}"
                           class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors {{ $isActive ? 'bg-[#8B0000] text-white border-[#8B0000]' : 'bg-gray-50 text-gray-600 border-gray-200 hover:text-[#8B0000] hover:border-[#8B0000]/30' }}">
                            {{ $filter['label'] }}
                        </a>
                    @endforeach
                </div>
                @forelse($downloads as $download)
                    <div class="flex items-center gap-4 px-5 py-4 border-b border-gray-100 last:border-0 hover:bg-red-50 transition-colors group">
                        <div class="flex-shrink-0 w-10 h-10 bg-red-100 text-red-700 rounded flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-sm text-gray-900 group-hover:text-red-800 transition-colors">{{ $download->title }}</div>
                            @if($download->category)
                                <span class="text-xs text-red-700 bg-red-50 px-2 py-0.5 rounded border border-red-100 mt-1 inline-block uppercase font-bold">{{ $download->category }}</span>
                            @endif
                        </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <a href="{{ asset('storage/' . $download->file_path) }}" target="_blank" rel="noopener" class="flex items-center gap-2 px-4 py-2 text-sm font-bold rounded transition-colors border border-[#8B0000] text-[#8B0000] hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    View
                                </a>
                                <a href="{{ asset('storage/' . $download->file_path) }}" download class="flex items-center gap-2 px-4 py-2 text-sm font-bold text-white rounded transition-colors" style="background-color: #8B0000; hover:opacity-80">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Download
                                </a>
                            </div>
                    </div>
                @empty
                    <div class="py-16 text-center">
                        <p class="text-5xl mb-4">📂</p>
                        <p class="text-gray-500 font-medium">
                            {{ $activeResourceCategory !== '' ? 'No resources found for this category yet.' : 'No downloads available yet.' }}
                        </p>
                        <p class="text-sm text-gray-400 mt-2">Please check back later or contact the college office.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div>
            <div class="section-header" style="background-color: #8B0000;">⚡ Quick Links</div>
            <div class="bg-white border border-gray-200 border-t-0">
                <a href="{{ route('public.notices') }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 transition-colors"><span class="text-red-600">›</span> Notice Board</a>
                    <a href="{{ route('public.downloads', ['category' => 'syllabus']) }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 transition-colors"><span class="text-red-600">›</span> Syllabus</a>
                    <a href="{{ route('public.downloads', ['category' => 'notes']) }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 transition-colors"><span class="text-red-600">›</span> Notes</a>
                <a href="{{ route('public.question-bank') }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 transition-colors"><span class="text-red-600">›</span> Question Bank</a>
                    <a href="{{ route('public.downloads') }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 transition-colors"><span class="text-red-600">›</span> All Resources</a>
                <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-red-800"><span>🔐</span> Student Portal</a>
            </div>
        </div>
    </div>
</div>
@endsection
