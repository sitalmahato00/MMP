{{--
    x-data-table
    A consistent data table wrapper with optional pagination.
    Props:
      :paginator  - The Laravel paginator object (optional, for pagination)
      emptyIcon   - SVG path string for empty state icon (optional)
      emptyTitle  - Empty state heading (default: 'No records found')
      emptyMsg    - Empty state sub-text (optional)
    Slots:
      $head       - <thead> content (tr > th cells)
      $slot       - <tbody> content (@forelse rows)
--}}
@props([
    'paginator'  => null,
    'emptyTitle' => 'No records found',
    'emptyMsg'   => 'Try adjusting your search or filters.',
])

<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            @if(isset($head))
            <thead class="bg-gray-50 border-b border-gray-100">
                {{ $head }}
            </thead>
            @endif
            <tbody class="divide-y divide-gray-50">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    {{-- Empty state detection is handled inside tbody via @forelse/@empty --}}

    @if($paginator && $paginator->hasPages())
    <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/40">
        {{ $paginator->withQueryString()->links() }}
    </div>
    @endif
</div>
