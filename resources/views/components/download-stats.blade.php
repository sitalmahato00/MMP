{{--
    x-download-stats
    Display statistics for downloads/gallery with improved styling
    Props:
      downloads - Collection of downloads
--}}
@props(['downloads'])

@php
    $totalFiles = $downloads->total();
    $totalSize = number_format($downloads->sum('file_size')/1024/1024, 2);
    $imageCount = $downloads->where('file_type', 'jpg')->count() + 
                  $downloads->where('file_type', 'jpeg')->count() + 
                  $downloads->where('file_type', 'png')->count();
    $docCount = $downloads->whereIn('file_type', ['pdf','doc','docx'])->count();
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card 
        title="Total Files" 
        :value="$totalFiles" 
        color="blue" 
        icon="<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'/></svg>"
    />
    <x-stat-card 
        title="Total Size" 
        :value="$totalSize . ' MB'" 
        color="green" 
        icon="<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'/></svg>"
    />
    <x-stat-card 
        title="Images" 
        :value="$imageCount" 
        color="purple" 
        icon="<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'/></svg>"
    />
    <x-stat-card 
        title="Documents" 
        :value="$docCount" 
        color="yellow" 
        icon="<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'/></svg>"
    />
</div>
