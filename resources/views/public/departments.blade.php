@extends('layouts.guest')
@section('title', 'Our Programs & Departments')
@section('breadcrumb', true)

@section('content')
<div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-8">
    <div class="section-header mb-6" style="background-color: #8B0000;">🎓 CTEVT Diploma Programs Offered at MMP</div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @php
            $fallback = [
                ['name' => 'Diploma in Information Technology', 'code' => 'IT', 'icon' => '💻', 'slug' => 'information-technology', 'desc' => 'Network administration, software development, database management and IT infrastructure.'],
                ['name' => 'Diploma in Civil Engineering', 'code' => 'CE', 'icon' => '🏗️', 'slug' => 'civil-engineering', 'desc' => 'Design, construction and maintenance of infrastructure including roads, bridges and buildings.'],
                ['name' => 'Diploma in Electrical Engineering', 'code' => 'EL', 'icon' => '⚡', 'slug' => 'electrical-engineering', 'desc' => 'Electrical systems, power generation, wiring, switchgear and electrical installations.'],
                ['name' => 'Diploma in Electronics Engineering', 'code' => 'EE', 'icon' => '📡', 'slug' => 'electronics-engineering', 'desc' => 'Electronics circuits, communication systems, embedded systems and signal processing.'],
                ['name' => 'Diploma in Mechanical Engineering', 'code' => 'ME', 'icon' => '⚙️', 'slug' => 'mechanical-engineering', 'desc' => 'Machine design, manufacturing, thermodynamics and mechanical systems.'],
                ['name' => 'Diploma in Architecture Engineering', 'code' => 'AR', 'icon' => '📐', 'slug' => 'architecture-engineering', 'desc' => 'Architectural design, drafting, building planning and construction management.'],
            ];
            $deptIcons = ['IT' => '💻', 'CE' => '🏗️', 'EL' => '⚡', 'EE' => '📡', 'ME' => '⚙️', 'AR' => '📐'];
        @endphp

        @forelse($departments as $dept)
            <a href="{{ route('public.department.show', $dept->slug) }}" class="group bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg hover:border-red-200 transition-all">
                <div class="h-2" style="background-color: #8B0000;"></div>
                <div class="p-6">
                    <div class="text-4xl mb-4">{{ $deptIcons[$dept->code] ?? '📚' }}</div>
                    <h3 class="font-bold text-lg text-gray-900 font-serif mb-2 leading-tight group-hover:text-red-800 transition-colors">{{ $dept->name }}</h3>
                    <p class="text-sm text-gray-500 mb-4 leading-relaxed line-clamp-2">{{ $dept->description ?? 'CTEVT approved diploma engineering program.' }}</p>
                    <div class="flex items-center justify-between text-xs text-gray-500 pt-3 border-t border-gray-100">
                        <span>🕐 3 Years / 6 Semesters</span>
                        <span class="font-bold text-red-700 group-hover:gap-2 flex items-center gap-1">Learn More →</span>
                    </div>
                </div>
            </a>
        @empty
            @foreach($fallback as $dept)
                <a href="{{ route('public.department.show', $dept['slug']) }}" class="group bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg hover:border-red-200 transition-all">
                    <div class="h-2" style="background-color: #8B0000;"></div>
                    <div class="p-6">
                        <div class="text-4xl mb-4">{{ $dept['icon'] }}</div>
                        <h3 class="font-bold text-lg text-gray-900 font-serif mb-2 leading-tight group-hover:text-red-800 transition-colors">{{ $dept['name'] }}</h3>
                        <p class="text-sm text-gray-500 mb-4 leading-relaxed">{{ $dept['desc'] }}</p>
                        <div class="flex items-center justify-between text-xs text-gray-500 pt-3 border-t border-gray-100">
                            <span>🕐 3 Years / 6 Semesters</span>
                            <span class="font-bold text-red-700">Learn More →</span>
                        </div>
                    </div>
                </a>
            @endforeach
        @endforelse
    </div>
</div>
@endsection
