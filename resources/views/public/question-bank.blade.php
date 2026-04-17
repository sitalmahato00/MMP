@extends('layouts.guest')
@section('title', 'Question Bank')
@section('meta_description', 'Download past exam question papers from Manmohan Memorial Polytechnic.')
@section('breadcrumb', true)

@section('content')
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <div class="section-header flex items-center justify-between" style="background-color: #8B0000;">
                <span>📝 Question Bank</span>
                <span class="text-red-200 text-xs">{{ $downloads->count() }} papers</span>
            </div>

            <div class="bg-white border border-gray-200 border-t-0">
                @forelse($downloads as $download)
                    <div class="flex items-center gap-4 px-5 py-4 border-b border-gray-100 last:border-0 hover:bg-red-50 transition-colors group">
                        <div class="flex-shrink-0 w-12 h-12 bg-yellow-50 text-yellow-700 rounded flex items-center justify-center border border-yellow-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-sm text-gray-900 group-hover:text-[#8B0000] transition-colors">{{ $download->title }}</div>
                            <div class="flex items-center gap-2 mt-1 flex-wrap">
                                @if($download->department)
                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">{{ $download->department->name }}</span>
                                @endif
                                <span class="text-xs text-gray-400">{{ bsDate($download->created_at, 'M d, Y') }}</span>
                            </div>
                        </div>
                        <a href="{{ asset('storage/' . $download->file_path) }}" download class="flex-shrink-0 flex items-center gap-2 px-4 py-2 text-sm font-bold text-white rounded transition-colors hover:opacity-90" style="background-color: #8B0000;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download
                        </a>
                    </div>
                @empty
                    <div class="py-16 text-center text-gray-400">
                        <p class="text-5xl mb-4">📝</p>
                        <p class="font-semibold text-gray-500">No question papers available yet.</p>
                        <p class="text-sm text-gray-400 mt-2">Past exam question papers will be uploaded soon.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            <div>
                <div class="section-header" style="background-color: #8B0000;">🔗 Quick Links</div>
                <div class="bg-white border border-gray-200 border-t-0">
                    @foreach([
                        ['label' => 'Downloads & Forms', 'href' => route('public.downloads')],
                        ['label' => 'Notice Board', 'href' => route('public.notices')],
                        ['label' => 'Our Programs', 'href' => route('public.departments')],
                        ['label' => 'Student Portal', 'href' => route('login')],
                    ] as $link)
                        <a href="{{ $link['href'] }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 last:border-0 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 transition-colors">
                            <span class="text-red-600">›</span>{{ $link['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div>
                <div class="section-header" style="background-color: #8B0000;">ℹ️ About Question Bank</div>
                <div class="bg-white border border-gray-200 border-t-0 p-4 space-y-3">
                    <p class="text-sm text-gray-600 leading-relaxed">The question bank contains past exam question papers from various departments and semesters to help students prepare effectively.</p>
                    <p class="text-sm text-gray-600">If you need a specific question paper, please contact the examination section.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
