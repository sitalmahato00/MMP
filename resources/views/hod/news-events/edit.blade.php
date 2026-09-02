@extends('layouts.app')
@section('title', 'Edit News/Event')

@section('content')
<x-page-header title="Edit News/Event" subtitle="Update the post details and audience."
               back="{{ route('hod.news-events.index') }}"/>

<form method="POST" action="{{ route('hod.news-events.update', $notice) }}" enctype="multipart/form-data" class="max-w-4xl space-y-6">
    @csrf
    @method('PUT')

    <x-form-section title="Basic Information" subtitle="Title, category, and audience.">
        <x-form-row>
            <x-form-field label="Title" name="title" :required="true" span="full">
                <x-input name="title" :value="old('title', $notice->title)" :required="true" placeholder="Enter title"/>
            </x-form-field>

            <x-form-field label="Category" name="type" :required="true">
                <x-select name="type" :required="true">
                    <option value="news" @selected(old('type', $notice->type) === 'news')>News</option>
                    <option value="event" @selected(old('type', $notice->type) === 'event')>Event</option>
                </x-select>
            </x-form-field>

            <x-form-field label="Status" name="is_published">
                <x-select name="is_published">
                    <option value="0" @selected(old('is_published', $notice->is_published ? '1' : '0') === '0')>Save as Draft</option>
                    <option value="1" @selected(old('is_published', $notice->is_published ? '1' : '0') === '1')>Publish Now</option>
                </x-select>
            </x-form-field>
        </x-form-row>

        <x-form-row>
            <x-form-field label="Target Program" name="program_id">
                <x-select name="program_id">
                    <option value="">All Programs in {{ $department->name }}</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" @selected(old('program_id', $notice->program_id) == $program->id)>{{ $program->name }}</option>
                    @endforeach
                </x-select>
            </x-form-field>

            <x-form-field label="Target Semester" name="semester">
                <x-select name="semester">
                    <option value="">All Semesters</option>
                    @for($i = 1; $i <= 6; $i++)
                        <option value="{{ $i }}" @selected(old('semester', $notice->semester) == $i)>Semester {{ $i }}</option>
                    @endfor
                </x-select>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Content" subtitle="Update the full post and attachments.">
        <x-form-row>
            <x-form-field label="Content" name="content" :required="true" span="full">
                <x-textarea name="content" rows="7" :required="true" placeholder="Write the update here...">{{ old('content', $notice->content) }}</x-textarea>
            </x-form-field>

            {{-- Dedicated Cover Image --}}
            <x-form-field label="Cover Image / Featured Photo" name="cover_image" span="full">
                <div x-data="{ preview: @js($notice->cover_image_url) }" class="space-y-2">
                    <label class="flex h-36 w-full cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-blue-200 bg-blue-50/30 transition-all duration-200 hover:border-blue-500 hover:bg-blue-50/60 overflow-hidden relative">
                        <template x-if="!preview">
                            <div class="flex flex-col items-center justify-center gap-1.5 px-4 text-center">
                                <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-xs font-semibold text-blue-900">Upload Cover Image (JPG, PNG, WebP, GIF)</p>
                                <p class="text-[11px] text-slate-400">Main banner shown at top of post and cards</p>
                            </div>
                        </template>
                        <template x-if="preview">
                            <div class="relative w-full h-full flex items-center justify-center bg-slate-900">
                                <img :src="preview" alt="Cover Preview" class="h-full w-auto object-contain">
                                <span class="absolute bottom-1 right-2 bg-black/60 text-white text-[10px] px-2 py-0.5 rounded font-bold">Change Cover</span>
                            </div>
                        </template>
                        <input type="file" name="cover_image" accept="image/*" class="hidden"
                               @change="const file = $event.target.files[0]; if (file) { const r = new FileReader(); r.onload = e => preview = e.target.result; r.readAsDataURL(file); }">
                    </label>
                </div>
            </x-form-field>

            {{-- Existing attachments --}}
            @if($notice->attachments->isNotEmpty())
            <x-form-field label="Current Gallery Photos & Attachments" name="delete_attachments" span="full">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach($notice->attachments as $att)
                    <div class="relative rounded-lg border border-slate-200 bg-white p-2 shadow-sm">
                        <div class="mb-1.5 flex h-24 items-center justify-center overflow-hidden rounded bg-slate-100">
                            @if($att->is_image)
                                <img src="{{ $att->url }}" class="h-full w-full object-cover rounded" alt="{{ $att->file_name }}"/>
                            @else
                                <div class="flex flex-col items-center gap-1 text-slate-400">
                                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="text-xs uppercase font-semibold">{{ $att->file_type }}</span>
                                </div>
                            @endif
                        </div>
                        <p class="truncate text-xs text-slate-600">{{ $att->file_name }}</p>
                        <a href="{{ $att->url }}" target="_blank" class="text-xs text-blue-600 hover:underline">View</a>

                        {{-- Delete checkbox --}}
                        <label class="absolute -right-1.5 -top-1.5 flex h-5 w-5 cursor-pointer items-center justify-center rounded-full bg-red-500 text-white shadow hover:bg-red-600"
                               title="Remove this attachment">
                            <input type="checkbox" name="delete_attachments[]" value="{{ $att->id }}"
                                   class="peer sr-only"
                                   onchange="this.closest('.relative').classList.toggle('opacity-40', this.checked)">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </label>
                    </div>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-slate-500">Click the red × on any file to mark it for removal on save.</p>
            </x-form-field>
            @endif

            {{-- New attachments --}}
            <x-form-field label="Add New Attachments" name="attachments" span="full">
                <div x-data="filePreview()" class="space-y-3">
                    <label
                        class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 p-6 text-center transition hover:border-blue-400 hover:bg-blue-50"
                        @dragover.prevent @drop.prevent="handleDrop($event)">
                        <svg class="mb-2 h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <span class="text-sm font-medium text-slate-600">Click to browse or drag & drop</span>
                        <span class="mt-1 text-xs text-slate-400">PDF, DOC, DOCX, JPG, PNG · max 10 MB each · up to 10 files</span>
                        <input type="file" name="attachments[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.webp"
                               class="hidden" @change="handleFiles($event.target.files)">
                    </label>

                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3" x-show="files.length > 0">
                        <template x-for="(f, i) in files" :key="i">
                            <div class="relative rounded-lg border border-slate-200 bg-white p-2 shadow-sm">
                                <div class="mb-1.5 flex h-24 items-center justify-center overflow-hidden rounded bg-slate-100">
                                    <img x-show="f.isImage" :src="f.url" class="h-full w-full object-cover rounded"/>
                                    <div x-show="!f.isImage" class="flex flex-col items-center gap-1 text-slate-400">
                                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span class="text-xs uppercase font-semibold" x-text="f.ext"></span>
                                    </div>
                                </div>
                                <p class="truncate text-xs text-slate-600" x-text="f.name"></p>
                                <p class="text-xs text-slate-400" x-text="f.size"></p>
                                <button type="button"
                                        class="absolute -right-1.5 -top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-white shadow hover:bg-red-600"
                                        @click="remove(i)">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
                @error('attachments.*')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <div class="flex items-center gap-3 pb-6">
        <x-btn type="submit">Update Post</x-btn>
        <x-btn href="{{ route('hod.news-events.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>

@push('scripts')
<script>
function filePreview() {
    return {
        files: [],
        dataTransfer: new DataTransfer(),
        handleFiles(fileList) {
            Array.from(fileList).forEach(f => this.addFile(f));
        },
        handleDrop(e) {
            this.handleFiles(e.dataTransfer.files);
        },
        addFile(f) {
            if (this.files.length >= 10) return;
            const ext = f.name.split('.').pop().toLowerCase();
            const isImage = ['jpg','jpeg','png','gif','webp'].includes(ext);
            const url = isImage ? URL.createObjectURL(f) : null;
            const size = f.size > 1048576
                ? (f.size / 1048576).toFixed(1) + ' MB'
                : (f.size / 1024).toFixed(0) + ' KB';
            this.files.push({ name: f.name, ext, isImage, url, size, file: f });
            this.dataTransfer.items.add(f);
            this.syncInput();
        },
        remove(i) {
            if (this.files[i].isImage) URL.revokeObjectURL(this.files[i].url);
            this.files.splice(i, 1);
            this.dataTransfer = new DataTransfer();
            this.files.forEach(f => this.dataTransfer.items.add(f.file));
            this.syncInput();
        },
        syncInput() {
            const input = this.$el.querySelector('input[type=file]');
            input.files = this.dataTransfer.files;
        }
    };
}
</script>
@endpush
@endsection
