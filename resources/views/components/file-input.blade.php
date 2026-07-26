{{--
    x-file-input
    Styled file upload field.
    Props:
      name    - Input name (required)
      accept  - Accepted MIME types (e.g. "image/*,.pdf")
      current - Path to currently uploaded file (optional)
      label   - Help text inside the drop zone (optional)
      maxMb   - Max file size in MB (default: 20 for video/file, 4 for images)
--}}
@props(['name', 'accept' => '*', 'current' => null, 'label' => 'Click to upload or drag & drop', 'maxMb' => null])

@php
    $currentUrl = null;
    $currentName = $current ? basename($current) : '';
    if ($current) {
        $currentPath = ltrim($current, '/');
        if (\Illuminate\Support\Str::startsWith($currentPath, ['http://', 'https://', '/'])) {
            $currentUrl = $currentPath;
        } else {
            $currentUrl = asset('storage/' . $currentPath);
        }
    }
    $showCurrentPreview = $currentUrl && preg_match('/\.(jpe?g|png|gif|webp|bmp|svg)$/i', $currentUrl);
    // Determine if this field accepts video (to show the right size limit)
    $acceptsVideo = str_contains($accept, 'video') || str_contains($accept, 'mp4') || str_contains($accept, 'webm');
    $resolvedMaxMb = $maxMb ?? ($acceptsVideo ? 20 : 4);
    $xData = json_encode([
        'fileName'      => $currentName,
        'hasFile'       => (bool) $current,
        'preview'       => $showCurrentPreview ? $currentUrl : null,
        'originalPreview' => $showCurrentPreview ? $currentUrl : null,
        'sizeError'     => '',
        'maxMb'         => (int) $resolvedMaxMb,
    ]);
@endphp

<div class="space-y-2" x-data="{{ $xData }}">
    <template x-if="preview">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <img :src="preview" class="h-32 w-full object-cover"/>
        </div>
    </template>

    <label for="{{ $name }}"
           class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-gray-200 rounded-xl bg-gray-50 hover:bg-gray-100 hover:border-[#8B0000]/40 cursor-pointer transition-all duration-200 group {{ $errors->has($name) ? 'border-red-400 bg-red-50' : '' }}"
           :class="sizeError ? 'border-red-400 bg-red-50' : ''">
        <div class="flex flex-col items-center justify-center gap-2 text-center px-4">
            <svg class="w-6 h-6 text-gray-300 group-hover:text-[#8B0000]/60 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            <p class="text-xs text-gray-400 group-hover:text-gray-600 transition-colors">{{ $label }}</p>
            <p class="text-[10px] text-gray-300">Max {{ $resolvedMaxMb }}MB</p>
        </div>
        <input type="file" id="{{ $name }}" name="{{ $name }}" accept="{{ $accept }}" class="hidden"
               x-on:change="
                   const f = $event.target.files[0];
                   sizeError = '';
                   if (f && f.size > maxMb * 1024 * 1024) {
                       sizeError = 'File is too large. Maximum allowed size is ' + maxMb + 'MB. Your file is ' + (f.size / (1024*1024)).toFixed(1) + 'MB.';
                       $event.target.value = '';
                       fileName = '';
                       hasFile = false;
                       preview = originalPreview;
                       return;
                   }
                   fileName = f ? f.name : '';
                   hasFile = !!f;
                   if (f && f.type.startsWith('image/')) {
                       preview = URL.createObjectURL(f);
                   } else if (!f) {
                       preview = originalPreview;
                   } else {
                       preview = null;
                   }
               ">
    </label>

    <div x-show="sizeError" x-transition class="flex items-start gap-2 text-xs text-red-600 bg-red-50 px-3 py-2 rounded-lg border border-red-200">
        <svg class="w-4 h-4 text-red-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span x-text="sizeError"></span>
    </div>

    @error($name)
        <div class="flex items-start gap-2 text-xs text-red-600 bg-red-50 px-3 py-2 rounded-lg border border-red-200">
            <svg class="w-4 h-4 text-red-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ $message }}</span>
        </div>
    @enderror

    @php $fileLabel = $current ? 'Current' : 'Selected'; @endphp
    <div x-show="hasFile && !sizeError" x-transition class="flex items-center gap-2 text-xs text-gray-500 bg-gray-50 px-3 py-2 rounded-lg border border-gray-100">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
        </svg>
        <span class="truncate" x-text="'{{ $fileLabel }}: ' + fileName"></span>
    </div>
</div>
