@props(['action' => null, 'method' => 'GET'])

<div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
    <form action="{{ $action }}" method="{{ $method }}" class="space-y-4">
        @csrf
        @if($method === 'POST')
            @method('GET')
        @endif
        
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{ $slot }}
        </div>

        <div class="flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Filter
            </button>
            <a href="{{ request()->url() }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Reset
            </a>
        </div>
    </form>
</div>
