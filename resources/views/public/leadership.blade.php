@extends('layouts.guest')
@section('title', 'Presidents & Principals')
@section('breadcrumb', true)

@section('content')
<div class="bg-white border-t border-gray-100">
    <div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-12">
        
        {{-- Presidents Section --}}
        <div class="mb-16">
            <div class="bg-[#8B0000] border-b-2 border-yellow-500 text-white text-center py-3 rounded-t-lg shadow font-bold text-lg mb-8">
                Presidents of MMP
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-16 justify-center">
                @forelse($presidents as $president)
                <div class="flex flex-col items-center text-center">
                    <div class="w-48 h-64 md:w-56 md:h-72 rounded-full overflow-hidden border-[6px] border-white shadow-lg mb-6 bg-gray-100 object-cover">
                        @if($president->avatar)
                            <img src="{{ asset('storage/' . $president->avatar) }}" alt="{{ $president->name }}" class="w-full h-full object-cover">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($president->name) }}&background=E5E7EB&color=4B5563&size=400" alt="{{ $president->name }}" class="w-full h-full object-cover">
                        @endif
                    </div>
                    <h3 class="font-bold text-gray-800 text-lg mb-1">{{ $president->name }}</h3>
                    <div class="text-sm text-gray-600 mb-1">{{ $president->designation ?: 'President' }}</div>
                    <div class="text-sm text-gray-500 font-medium">
                        ({{ $president->start_date_bs }} to {{ $president->end_date_bs ?: 'till now' }})BS
                    </div>
                    @if($president->message)
                        <div class="mt-4 text-sm text-gray-600 italic">
                            "{{ Str::limit($president->message, 150) }}"
                        </div>
                    @endif
                </div>
                @empty
                <div class="col-span-full text-center text-gray-500 py-8">No records available.</div>
                @endforelse
            </div>
        </div>

        {{-- Principals Section --}}
        <div>
            <div class="bg-[#8B0000] border-b-2 border-yellow-500 text-white text-center py-3 rounded-t-lg shadow font-bold text-lg mb-8">
                Principals of MMP
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 justify-center">
                @forelse($principals as $principal)
                <div class="flex flex-col items-center text-center">
                    <div class="w-40 h-56 md:w-48 md:h-64 rounded-[40%] overflow-hidden border-4 border-white shadow-md mb-5 bg-gray-100">
                        @if($principal->avatar)
                            <img src="{{ asset('storage/' . $principal->avatar) }}" alt="{{ $principal->name }}" class="w-full h-full object-cover">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($principal->name) }}&background=E5E7EB&color=4B5563&size=400" alt="{{ $principal->name }}" class="w-full h-full object-cover">
                        @endif
                    </div>
                    <h3 class="font-bold text-gray-800 text-md mb-1">{{ $principal->name }}</h3>
                    <div class="text-xs text-gray-600 mb-1">{{ $principal->designation ?: 'Principal' }}</div>
                    <div class="text-xs text-gray-500 font-medium">
                        ({{ $principal->start_date_bs }} to {{ $principal->end_date_bs ?: 'till now' }})BS
                    </div>
                </div>
                @empty
                <div class="col-span-full text-center text-gray-500 py-8">No records available.</div>
                @endforelse
            </div>
        </div>
        
    </div>
</div>
@endsection
