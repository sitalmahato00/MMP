@extends('layouts.guest')
@section('title', 'Facilities & Resources')
@section('breadcrumb', true)

@section('content')
<div class="bg-[#f9f9f9] border-t border-gray-100 min-h-screen">
    <div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-12">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold font-serif text-[#8B0000]">Campus Facilities & Resources</h1>
            <p class="text-gray-600 mt-2">State-of-the-art infrastructure facilitating excellence in technical education.</p>
        </div>

        {{-- Department Filter --}}
        <div class="mb-8 flex justify-center">
            <div class="inline-flex flex-wrap items-center gap-2 bg-white rounded-xl border border-gray-200 p-2 shadow-sm">
                <a href="{{ route('public.facilities') }}" 
                   class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors {{ !request('department') ? 'bg-[#8B0000] text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    All Departments
                </a>
                @foreach($departments as $dept)
                    <a href="{{ route('public.facilities', ['department' => $dept->slug]) }}" 
                       class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors {{ request('department') === $dept->slug ? 'bg-[#8B0000] text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        {{ $dept->name }}
                    </a>
                @endforeach
            </div>
        </div>

        @foreach($facilities->groupBy('category') as $category => $items)
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-[#003366] border-b-2 border-yellow-500 pb-2 mb-6 capitalize">
                    {{ str_replace('_', ' ', $category) }}
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($items as $facility)
                        <div class="bg-white rounded shadow-sm border border-gray-200 overflow-hidden group">
                            {{-- Image Carousel or Thumbnail --}}
                            <div class="w-full h-48 bg-gray-200 relative overflow-hidden">
                                @if(count($facility->image_urls) > 0)
                                    <img src="{{ $facility->image_urls[0] }}" alt="{{ $facility->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    @if(count($facility->image_urls) > 1)
                                        <div class="absolute bottom-2 right-2 bg-black/60 text-white text-xs px-2 py-1 rounded shadow">
                                            +{{ count($facility->image_urls) - 1 }} Photos
                                        </div>
                                    @endif
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                                        <svg class="w-12 h-12 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="p-5">
                                <h3 class="font-bold text-gray-900 text-lg mb-1 group-hover:text-[#8B0000] transition-colors">{{ $facility->name }}</h3>
                                
                                @if($facility->department || $facility->program)
                                <div class="text-[11px] font-bold text-blue-600 uppercase tracking-wider mb-3">
                                    {{ optional($facility->department)->name ?? optional($facility->program)->name }}
                                </div>
                                @endif
                                
                                @if($facility->location)
                                <div class="text-xs text-gray-500 mb-3 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-[#8B0000] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ $facility->location }}
                                    @if($facility->capacity)
                                    <span class="mx-2">|</span>
                                    <svg class="w-3 h-3 text-[#8B0000] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ $facility->capacity }} Seats
                                    @endif
                                </div>
                                @endif
                                
                                <p class="text-[13px] text-gray-600 line-clamp-3 mb-4">
                                    {{ $facility->description ?? strip_tags($facility->content) }}
                                </p>
                                
                                @if(count($facility->document_urls) > 0)
                                <div class="border-t border-gray-100 pt-3">
                                    <h4 class="text-xs font-bold text-gray-900 mb-2">Resources</h4>
                                    <ul class="space-y-2">
                                        @foreach($facility->document_urls as $index => $docUrl)
                                        <li>
                                            <a href="{{ $docUrl }}" target="_blank" class="flex items-center gap-2 text-xs text-blue-600 hover:text-blue-800 transition-colors">
                                                <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                Document {{ $index + 1 }}
                                            </a>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
        
        @if($facilities->isEmpty())
        <div class="text-center text-gray-500 py-16 bg-white border border-gray-100 rounded">
            @if(request('department'))
                No facilities found for this department. Check back later for updates.
            @else
                Check back later for updates on campus facilities.
            @endif
        </div>
        @endif
        
    </div>
</div>
@endsection
