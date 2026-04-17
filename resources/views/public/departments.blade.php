@extends('layouts.guest')
@section('title', 'Our Programs & Departments')
@section('breadcrumb', true)

@section('content')
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
    <div class="section-header mb-6" style="background-color: #8B0000;">🎓 CTEVT Diploma Programs Offered at MMP</div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @php
            $deptIcons = ['IT' => '💻', 'CE' => '🏗️', 'EL' => '⚡', 'EE' => '📡', 'ME' => '⚙️', 'AR' => '📐'];
        @endphp

        @forelse($departments as $dept)
            <a href="{{ route('public.department.show', $dept->slug) }}" class="group bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg hover:border-red-200 transition-all">
                <div class="h-2" style="background-color: #8B0000;"></div>
                <div class="aspect-[16/9] bg-gray-100 overflow-hidden">
                    @if($dept->photo_url)
                        <img src="{{ $dept->photo_url }}" alt="{{ $dept->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-5xl">{{ $deptIcons[$dept->code] ?? '📚' }}</div>
                    @endif
                </div>
                <div class="p-6">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div class="text-4xl">{{ $deptIcons[$dept->code] ?? '📚' }}</div>
                        @if($dept->syllabus_url)
                            <span class="text-[10px] uppercase tracking-wider font-bold text-red-700 bg-red-50 px-2 py-1 rounded border border-red-100">Syllabus Ready</span>
                        @endif
                    </div>
                    <h3 class="font-bold text-lg text-gray-900 font-serif mb-2 leading-tight group-hover:text-red-800 transition-colors">{{ $dept->name }}</h3>
                    <p class="text-sm text-gray-500 mb-4 leading-relaxed line-clamp-2">{{ $dept->description ?? 'CTEVT approved diploma engineering program.' }}</p>
                    <div class="flex items-center justify-between text-xs text-gray-500 pt-3 border-t border-gray-100">
                        <span>🕐 3 Years / 6 Semesters</span>
                        <span class="font-bold text-red-700 group-hover:gap-2 flex items-center gap-1">Learn More →</span>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full bg-white border border-gray-200 rounded-xl p-10 text-center text-gray-500">
                <div class="text-4xl mb-3">📚</div>
                <p class="font-semibold text-gray-700">No courses published yet.</p>
                <p class="text-sm mt-1">Courses will appear here automatically once departments and programs are added in the admin panel.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
