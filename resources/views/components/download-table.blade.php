{{--
    x-download-table
    Table display for downloads
    Props:
      downloads - Paginated downloads collection
--}}
@props(['downloads'])

<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Preview</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Title</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Size</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Uploaded</th>
                <th class="px-5 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($downloads as $download)
                <x-download-table-row :download="$download" />
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-8">
                        <x-empty-state 
                            title="No resources uploaded"
                            message="Upload forms, syllabi, notes, or question banks for your department."
                            action="{{ route('hod.downloads.create') }}"
                            actionLabel="Upload Resource"
                        />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($downloads->hasPages())
    <div class="mt-6">
        {{ $downloads->links() }}
    </div>
@endif
