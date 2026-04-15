@extends('layouts.guest')
@section('title', 'Facilities & Resources')
@section('breadcrumb', true)

@section('content')
<div class="bg-[#f9f9f9] border-t border-gray-100 min-h-screen">
    <div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-12">
        <div class="text-center mb-12">
            <h1 class="text-3xl font-bold font-serif text-[#8B0000]">Campus Facilities & Resources</h1>
            <p class="text-gray-600 mt-2">State-of-the-art infrastructure facilitating excellence in technical education.</p>
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
                                @if(is_array($facility->images) && count($facility->images) > 0)
                                    <img src="{{ asset('storage/' . $facility->images[0]) }}" alt="{{ $facility->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    @if(count($facility->images) > 1)
                                        <div class="absolute bottom-2 right-2 bg-black/60 text-white text-xs px-2 py-1 rounded shadow">
                                            +{{ count($facility->images) - 1 }} Photos
                                        </div>
                                    @endif
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                                        <i class="ri-building-line text-4xl mb-2"></i>
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
                                    <i class="ri-map-pin-line text-[#8B0000]"></i> {{ $facility->location }}
                                    @if($facility->capacity)
                                    <span class="mx-2">|</span>
                                    <i class="ri-group-line text-[#8B0000]"></i> {{ $facility->capacity }} Seats
                                    @endif
                                </div>
                                @endif
                                
                                <p class="text-[13px] text-gray-600 line-clamp-3 mb-4">
                                    {{ $facility->description ?? strip_tags($facility->content) }}
                                </p>
                                
                                @if(is_array($facility->documents) && count($facility->documents) > 0)
                                <div class="border-t border-gray-100 pt-3">
                                    <h4 class="text-xs font-bold text-gray-900 mb-2">Resources</h4>
                                    <ul class="space-y-2">
                                        @foreach($facility->documents as $doc)
                                        <li>
                                            <a href="{{ asset('storage/' . $doc) }}" target="_blank" class="flex items-center gap-2 text-xs text-blue-600 hover:text-blue-800 transition-colors">
                                                <i class="ri-file-pdf-line text-red-500 text-base"></i> Document {{ $loop->iteration }}
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
            Check back later for updates on campus facilities.
        </div>
        @endif
        
    </div>
</div>
@endsection
