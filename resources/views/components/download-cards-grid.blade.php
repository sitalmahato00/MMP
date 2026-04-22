{{--
    x-download-cards-grid
    Grid display for downloads in card format
    Props:
      downloads - Paginated downloads collection
--}}
@props(['downloads'])

@if($downloads->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach($downloads as $download)
            <x-download-card :download="$download" />
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($downloads->hasPages())
        <div class="mt-8">
            {{ $downloads->links() }}
        </div>
    @endif
@else
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-12">
        <x-empty-state 
            title="No resources uploaded"
            message="Upload forms, syllabi, notes, or question banks for your department."
            action="{{ route('hod.downloads.create') }}"
            actionLabel="Upload Resource"
        />
    </div>
@endif
