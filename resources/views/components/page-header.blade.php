{{--
    x-page-header
    Props:
      title     - Page title (required)
      subtitle  - Small description under title (optional)
      back      - URL for back arrow (optional)
      icon      - Icon name for the page (optional)
    Slots:
      $actions    - Buttons/links on the right side (optional)
      $breadcrumb - Breadcrumb navigation (optional)
--}}
@props(['title', 'subtitle' => null, 'back' => null, 'icon' => null])

<div class="mb-6">
    {{-- Breadcrumb Navigation --}}
    @if(isset($breadcrumb))
        <nav class="mb-4" aria-label="Breadcrumb">
            <div class="flex items-center space-x-2 breadcrumb-container">
                {{ $breadcrumb }}
            </div>
        </nav>
        
        <style>
        .breadcrumb-container > *:not(:first-child)::before {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            margin-right: 8px;
            margin-left: 8px;
            background-image: url("data:image/svg+xml,%3Csvg class='h-4 w-4 text-slate-400' fill='none' viewBox='0 0 24 24' stroke='%23a1a1aa'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 5l7 7-7 7'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: center;
        }
        </style>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            @if($back)
                <a href="{{ $back }}"
                   class="p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-all duration-200 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
            @endif
            
            @if($icon)
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-slate-100">
                    @if($icon === 'user-group')
                        <svg class="h-6 w-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM6 20h12a6 6 0 00-6-6 6 6 0 00-6 6z"/>
                        </svg>
                    @elseif($icon === 'user')
                        <svg class="h-6 w-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    @elseif($icon === 'home')
                        <svg class="h-6 w-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    @endif
                </div>
            @endif
            
            <div>
                <h1 class="text-2xl font-black text-gray-900 leading-tight">{{ $title }}</h1>
                @if($subtitle)
                    <p class="text-sm text-gray-500 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        @if(isset($actions))
            <div class="flex items-center gap-2 flex-shrink-0">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
