@extends('layouts.guest')
@section('title', 'Presidents & Principals')
@section('breadcrumb', true)

@section('content')
<div class="bg-white border-t border-gray-100">
    <div class="w-full px-4 md:px-8 xl:px-16 2xl:px-24 mx-auto py-12">
        
        {{-- Presidents Section --}}
        <div class="mb-16">
            <div class="bg-[#003D82] border-b-2 border-yellow-500 text-white text-center py-3 rounded-t-lg shadow font-bold text-lg mb-8">
                Presidents of MMP
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-16 justify-center">
                @forelse($presidents as $president)
                <div class="flex flex-col items-center text-center">
                    <div class="w-48 h-64 md:w-56 md:h-72 rounded-full overflow-hidden border-[6px] border-white shadow-lg mb-6 bg-gray-100 object-cover">
                        @if($president->avatar_url)
                            <img src="{{ $president->avatar_url }}" alt="{{ $president->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-4xl font-black" style="background: linear-gradient(135deg, #f1f5f9, #e2e8f0); color: #64748b;">
                                {{ strtoupper(substr($president->name, 0, 1)) }}
                            </div>
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
            <div class="bg-[#003D82] border-b-2 border-yellow-500 text-white text-center py-3 rounded-t-lg shadow font-bold text-lg mb-8">
                Principals of MMP
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 justify-center">
                @forelse($principals as $principal)
                <div class="flex flex-col items-center text-center">
                    <div class="w-40 h-56 md:w-48 md:h-64 rounded-[40%] overflow-hidden border-4 border-white shadow-md mb-5 bg-gray-100">
                        @if($principal->avatar_url)
                            <img src="{{ $principal->avatar_url }}" alt="{{ $principal->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-3xl font-black" style="background: linear-gradient(135deg, #f1f5f9, #e2e8f0); color: #64748b;">
                                {{ strtoupper(substr($principal->name, 0, 1)) }}
                            </div>
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

