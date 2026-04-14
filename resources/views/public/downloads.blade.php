@extends('layouts.guest')
@section('title', 'Downloads & Resources')
@section('breadcrumb', true)

@section('content')
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <div class="section-header" style="background-color: #8B0000;">📥 Downloads & Resources</div>
            <div class="bg-white border border-gray-200 border-t-0">
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
                        <a href="{{ asset('storage/' . $download->file_path) }}" download class="flex-shrink-0 flex items-center gap-2 px-4 py-2 text-sm font-bold text-white rounded transition-colors" style="background-color: #8B0000; hover:opacity-80">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download
                        </a>
                    </div>
                @empty
                    <div class="py-16 text-center">
                        <p class="text-5xl mb-4">📂</p>
                        <p class="text-gray-500 font-medium">No downloads available yet.</p>
                        <p class="text-sm text-gray-400 mt-2">Please check back later or contact the college office.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div>
            <div class="section-header" style="background-color: #8B0000;">⚡ Quick Links</div>
            <div class="bg-white border border-gray-200 border-t-0">
                <a href="{{ route('public.notices') }}" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 transition-colors"><span class="text-red-600">›</span> Notice Board</a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 text-sm text-gray-700 hover:bg-red-50 hover:text-red-800 transition-colors"><span class="text-red-600">›</span> Question Bank</a>
                <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-red-800"><span>🔐</span> Student Portal</a>
            </div>
        </div>
    </div>
</div>
@endsection
