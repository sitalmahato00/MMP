@props([
    'exportUrl',
    'queryParams' => [],
    'formats' => ['csv', 'excel', 'pdf'],
    'buttonText' => 'Export',
    'buttonClass' => 'inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700'
])

<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" 
            class="{{ $buttonClass }}">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        {{ $buttonText }}
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    
    <div x-show="open" @click.away="open = false" x-cloak
         class="absolute right-0 z-10 mt-2 w-48 rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5">
        
        @if(in_array('csv', $formats))
            <a href="{{ $exportUrl }}?{{ http_build_query(array_merge($queryParams, ['format' => 'csv'])) }}" 
               class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export as CSV
                <span class="ml-auto text-xs text-gray-500">.csv</span>
            </a>
        @endif

        @if(in_array('excel', $formats))
            <a href="{{ $exportUrl }}?{{ http_build_query(array_merge($queryParams, ['format' => 'excel'])) }}" 
               class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                <svg class="h-4 w-4 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export as Excel
                <span class="ml-auto text-xs text-gray-500">.xlsx</span>
            </a>
        @endif

        @if(in_array('pdf', $formats))
            <a href="{{ $exportUrl }}?{{ http_build_query(array_merge($queryParams, ['format' => 'pdf'])) }}" 
               class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Export as PDF
                <span class="ml-auto text-xs text-gray-500">.pdf</span>
            </a>
        @endif

        @if(in_array('print', $formats))
            <button onclick="window.print()" 
                    class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print Page
                <span class="ml-auto text-xs text-gray-500">Ctrl+P</span>
            </button>
        @endif
    </div>
</div>