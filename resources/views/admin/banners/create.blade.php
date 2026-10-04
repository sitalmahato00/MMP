@extends('layouts.app')
@section('title', 'Add Banner')

@section('content')
<x-form-layout title="Add Banner" subtitle="Upload a new hero image for the homepage." back="{{ route('admin.banners.index') }}">
    <x-slot name="breadcrumb">
        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-900">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.banners.index') }}" class="hover:text-slate-900">Banners</a>
            <span>/</span>
            <span class="font-semibold text-slate-900">Add Banner</span>
        </nav>
    </x-slot>

    <x-slot name="sidebar">
        <x-form-sidebar />
    </x-slot>

    <form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data" class="max-w-2xl space-y-6">
    @csrf
    <x-form-section title="Banner Details">
        <x-form-row>
            <x-form-field label="Title" name="title" span="full">
                <x-input name="title" placeholder="Main banner heading (optional)"/>
            </x-form-field>
            <x-form-field label="Subtitle" name="subtitle" span="full">
                <x-input name="subtitle" placeholder="Secondary text (optional)"/>
            </x-form-field>
            <x-form-field label="Banner Media (Image or Video)" name="image" :required="true" span="full" hint="High-resolution image (1920×1080) or video (MP4/WebM/MOV up to 100MB).">
                <x-file-input name="image" accept="image/*,video/mp4,video/webm,video/quicktime,.mov,.m4v,.avi" label="Upload hero image or video (MP4/WebM/MOV up to 100MB)" :maxMb="100"/>
            </x-form-field>
            <x-form-field label="Order" name="order">
                <x-input name="order" type="number" value="0"/>
            </x-form-field>
            <x-form-field label="Active" name="is_active">
                <label class="flex items-center gap-3 cursor-pointer mt-2">
                    <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 accent-[#8B0000] rounded">
                    <span class="text-sm text-gray-600">Show on public site</span>
                </label>
            </x-form-field>
        </x-form-row>
    </x-form-section>
    <div class="flex items-center gap-3">
        <x-btn type="submit">Add Banner</x-btn>
        <x-btn href="{{ route('admin.banners.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>

{{-- Upload Progress Overlay --}}
<div id="banner-upload-overlay" style="display:none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-sm text-center">
        <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4 border" style="background-color: #FEE2E2; border-color: #FECACA;">
            <svg class="w-7 h-7 text-[#8B0000] animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
        </div>
        <h3 id="b-upload-title" class="font-bold text-gray-800 text-lg mb-1">Uploading Banner…</h3>
        <p id="b-upload-filename" class="text-xs text-gray-600 font-medium mb-4 truncate px-2"></p>
        <div class="w-full bg-gray-200 rounded-full h-3.5 mb-2 overflow-hidden shadow-inner p-0.5" style="background-color: #E2E8F0;">
            <div id="b-upload-bar" class="h-full rounded-full transition-all duration-150"
                 style="width: 2%; min-width: 6px; background-color: #8B0000; background-image: linear-gradient(90deg, #8B0000 0%, #DC2626 100%);"></div>
        </div>
        <div class="flex justify-between text-xs font-semibold text-gray-700 mt-1">
            <span id="b-upload-pct">0%</span>
            <span id="b-upload-size" class="text-gray-500 font-normal"></span>
        </div>
        <div id="b-upload-speed" class="text-[11px] text-gray-400 mt-1 font-mono"></div>
        <p class="text-xs text-gray-400 mt-4 flex items-center justify-center gap-1.5">
            <svg class="w-3.5 h-3.5 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Please wait — do not close this tab.
        </p>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const form = document.querySelector('form[action="{{ route('admin.banners.store') }}"]');
    if (!form) return;
    const overlay = document.getElementById('banner-upload-overlay');
    const bar = document.getElementById('b-upload-bar');
    const pct = document.getElementById('b-upload-pct');
    const size = document.getElementById('b-upload-size');
    const speed = document.getElementById('b-upload-speed');
    const title = document.getElementById('b-upload-title');
    const fileP = document.getElementById('b-upload-filename');

    function fmt(bytes) {
        if (!bytes || bytes <= 0) return '0 B';
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    form.addEventListener('submit', function(e) {
        const fileInput = form.querySelector('input[type="file"][name="image"]');
        if (!fileInput || !fileInput.files || !fileInput.files[0]) return;
        const file = fileInput.files[0];
        if (file.size > 100 * 1024 * 1024) {
            e.preventDefault();
            alert('File exceeds 100MB limit.');
            return;
        }

        e.preventDefault();
        overlay.style.display = 'flex';
        fileP.textContent = file.name + ' (' + fmt(file.size) + ')';
        size.textContent = '0 B / ' + fmt(file.size);
        pct.textContent = '0%';
        bar.style.width = '2%';
        speed.textContent = 'Starting upload…';

        const xhr = new XMLHttpRequest();
        const startTime = Date.now();
        const total = file.size;

        function onProg(evt) {
            const tot = (evt && evt.lengthComputable && evt.total > 0) ? evt.total : total;
            const loaded = (evt && typeof evt.loaded === 'number') ? evt.loaded : 0;
            const p = Math.min(99, Math.round((loaded / tot) * 100));
            bar.style.width = Math.max(2, p) + '%';
            pct.textContent = p + '%';
            size.textContent = fmt(loaded) + ' / ' + fmt(tot);

            const elapsed = (Date.now() - startTime) / 1000;
            if (elapsed > 0.5 && loaded > 0) {
                const spd = loaded / elapsed;
                const rem = Math.max(1, Math.round((tot - loaded) / spd));
                speed.textContent = fmt(spd) + '/s • ' + (rem > 60 ? Math.ceil(rem/60) + ' min left' : rem + 's left');
            }
            if (p >= 99) {
                title.textContent = 'Saving banner on server…';
                speed.textContent = 'Uploaded — writing file…';
            }
        }

        if (xhr.upload) {
            xhr.upload.onprogress = onProg;
            xhr.upload.addEventListener('progress', onProg);
            xhr.upload.onload = function() {
                bar.style.width = '100%';
                bar.style.backgroundColor = '#16A34A';
                bar.style.backgroundImage = 'linear-gradient(90deg, #16A34A 0%, #22C55E 100%)';
                pct.textContent = '100%';
                title.textContent = 'Saving banner…';
            };
        }

        xhr.addEventListener('load', function() {
            if (xhr.status >= 200 && xhr.status < 400) {
                title.textContent = 'Success!';
                bar.style.width = '100%';
                bar.style.backgroundColor = '#16A34A';
                setTimeout(function() {
                    window.location.href = xhr.responseURL || '{{ route('admin.banners.index') }}';
                }, 300);
            } else {
                overlay.style.display = 'none';
                let msg = 'Upload failed (HTTP ' + xhr.status + ').';
                try {
                    const json = JSON.parse(xhr.responseText);
                    if (json.errors) {
                        let errs = [];
                        for (let k in json.errors) errs = errs.concat(json.errors[k]);
                        if (errs.length) msg = errs.join('\n');
                    } else if (json.message) {
                        msg = json.message;
                    }
                } catch(_) {}
                alert(msg);
            }
        });

        xhr.addEventListener('error', function() {
            overlay.style.display = 'none';
            alert('Network error during upload. Please check connection and try again.');
        });

        xhr.timeout = 900000;
        xhr.open('POST', form.action, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(new FormData(form));
    });
})();
</script>
@endpush
</x-form-layout>
@endsection
