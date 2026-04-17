@extends('layouts.app')
@section('title', 'Website Settings')

@section('content')
<x-page-header title="Website Settings" subtitle="Manage every section of the public website from one place.">
    <x-slot name="actions">
        <x-btn type="submit" form="web-control-form">Save Settings</x-btn>
    </x-slot>
</x-page-header>

{{--
    10-tab Website Settings hub:
      0  Branding              | settings form
      1  Principal's Corner    | settings form
      2  About & Pages         | settings form
      3  Hero Banners          | content management (own delete forms)
      4  Photo Gallery         | content management (own delete forms)
      5  Resources & Downloads | content management (own delete forms)
    6  News & Events         | content management (own delete forms)
      7  Facilities            | content management (own delete forms)
      8  Leadership            | content management (own delete forms)
      9  Contact & Maps        | settings form
--}}

@php
    $wcTabs = ['Branding', "Principal's Corner", 'About & Pages',
               'Hero Banners', 'Photo Gallery', 'Resources & Downloads',
               'News & Events', 'Facilities', 'Leadership', 'Contact & Maps'];
@endphp

<div x-data="{ activeTab: 0 }">

    {{-- ── Tab Header Bar ──────────────────────────────────── --}}
    <div class="flex gap-1 border-b border-gray-100 mb-6 overflow-x-auto">
        @foreach($wcTabs as $i => $tab)
        <button
            @click="activeTab = {{ $i }}"
            :class="activeTab === {{ $i }} ? 'border-b-2 border-[#8B0000] text-[#8B0000] font-semibold' : 'text-gray-400 hover:text-gray-700'"
            class="px-4 py-2.5 text-sm font-medium whitespace-nowrap transition-all -mb-px">
            {{ $tab }}
        </button>
        @endforeach
    </div>

    {{-- ══════════════════════════════════════════════════════
         SETTINGS FORM — panels 0, 1, 2, 9 live inside this.
         Panels 3-8 are valid HTML siblings (outside the form).
    ══════════════════════════════════════════════════════ --}}
    <form id="web-control-form" method="POST" action="{{ route('admin.web-control.update') }}" enctype="multipart/form-data">
        @csrf
        @php $allSettings = collect($settings->flatten(1))->keyBy('key'); @endphp

        {{-- ── Tab 0: Branding ─────────────────────────────── --}}
        <div x-show="activeTab===0" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
            <div class="space-y-6 max-w-2xl">
                <p class="text-sm text-gray-500">Site logo displayed in the public header, admin sidebar, and emails.</p>
                @php $logo = $allSettings->get('site_logo'); @endphp
                <x-card>
                    <x-form-field label="Site Logo" name="site_logo" span="full" hint="Square PNG/WebP recommended, min 128×128px.">
                        @if($logo?->value)
                            <div class="mb-3 flex items-start gap-4">
                                <img src="{{ asset('storage/'.$logo->value) }}" alt="Logo" class="w-24 h-24 rounded-xl border border-gray-200 object-contain bg-gray-50 p-1">
                                <div class="flex flex-col gap-2 mt-1">
                                    <a href="{{ asset('storage/'.$logo->value) }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800 font-medium">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        View
                                    </a>
                                    <button type="button"
                                        onclick="deleteWebControlFile('{{ route('admin.web-control.clear-file', 'site_logo') }}', 'Remove the current site logo?')"
                                        class="inline-flex items-center gap-1 text-sm text-red-400 hover:text-red-600 font-medium">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Remove
                                    </button>
                                </div>
                            </div>
                        @endif
                        <x-file-input name="site_logo" accept="image/*" :current="$logo?->value" label="Upload site logo (PNG/JPG/WebP, square recommended)" />
                    </x-form-field>
                </x-card>
            </div>
        </div>

        {{-- ── Tab 1: Principal's Corner ──────────────────── --}}
        <div x-show="activeTab===1" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
            <div class="space-y-6 max-w-3xl">
                <p class="text-sm text-gray-500">Principal's profile and message displayed on the homepage.</p>

                @php $pname = $allSettings->get('principal_name'); @endphp
                <x-card>
                    <x-form-field label="Principal's Full Name" name="principal_name" span="full" hint="Displayed under the principal photo on the homepage.">
                        <x-input name="principal_name" :value="$pname?->value" placeholder="e.g. Mr. Ram Prasad Sharma" />
                    </x-form-field>
                </x-card>

                @php $pphoto = $allSettings->get('principal_photo'); @endphp
                <x-card>
                    <x-form-field label="Principal's Photo" name="principal_photo" span="full" hint="Portrait crop recommended (min 400×500px).">
                        @if($pphoto?->value)
                            <div class="mb-3 flex items-start gap-4">
                                <img src="{{ asset('storage/'.$pphoto->value) }}" alt="Principal" class="w-28 h-36 object-cover object-top rounded-lg border border-gray-200 shadow-sm">
                                <div class="flex flex-col gap-2 mt-1">
                                    <a href="{{ asset('storage/'.$pphoto->value) }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800 font-medium">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        View
                                    </a>
                                    <button type="button"
                                        onclick="deleteWebControlFile('{{ route('admin.web-control.clear-file', 'principal_photo') }}', 'Remove the current principal photo?')"
                                        class="inline-flex items-center gap-1 text-sm text-red-400 hover:text-red-600 font-medium">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Remove
                                    </button>
                                </div>
                            </div>
                        @endif
                        <x-file-input name="principal_photo" accept="image/*" :current="$pphoto?->value" label="Upload principal photo (JPG/PNG/WebP)" />
                    </x-form-field>
                </x-card>

                @php $pmsg = $allSettings->get('principals_message'); @endphp
                <x-card>
                    <x-form-field label="Principal's Message" name="principals_message" span="full" hint="Full message on the homepage. Separate paragraphs with a blank line.">
                        <x-textarea name="principals_message" rows="10">{{ $pmsg?->value }}</x-textarea>
                    </x-form-field>
                </x-card>

                @php $pmedia = $allSettings->get('principal_message_media'); @endphp
                <x-card>
                    <x-form-field label="Message Media Attachment" name="principal_message_media" span="full" hint="Optional image, video or PDF shown before the principal's message.">
                        @if($pmedia?->value)
                            @php $mext = strtolower(pathinfo($pmedia->value, PATHINFO_EXTENSION)); @endphp
                            <div class="mb-3 flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                @if(in_array($mext, ['jpg','jpeg','png','gif','webp']))
                                    <img src="{{ asset('storage/'.$pmedia->value) }}" class="w-24 h-16 object-cover rounded border">
                                @elseif(in_array($mext, ['mp4','webm','mov']))
                                    <video src="{{ asset('storage/'.$pmedia->value) }}" class="w-36 h-20 rounded border bg-black" muted></video>
                                @elseif($mext === 'pdf')
                                    <div class="w-10 h-12 bg-red-50 border border-red-200 rounded flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                                <div class="text-xs text-gray-500 min-w-0">
                                    <span class="font-medium text-gray-700 block truncate">{{ basename($pmedia->value) }}</span>
                                    <div class="flex items-center gap-3 mt-1">
                                        <a href="{{ asset('storage/'.$pmedia->value) }}" target="_blank"
                                           class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800 font-medium">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            View
                                        </a>
                                        <button type="button"
                                            onclick="deleteWebControlFile('{{ route('admin.web-control.clear-file', 'principal_message_media') }}', 'Remove this media file?')"
                                            class="inline-flex items-center gap-1 text-sm text-red-400 hover:text-red-600 font-medium">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <x-file-input name="principal_message_media" accept="image/*,video/mp4,video/webm,.pdf" :current="$pmedia?->value" label="Upload image, video (MP4/WebM) or PDF" />
                    </x-form-field>
                </x-card>
            </div>
        </div>

        {{-- ── Tab 2: About & Pages ─────────────────────────── --}}
        <div x-show="activeTab===2" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
            <div class="space-y-6 max-w-3xl">
                <p class="text-sm text-gray-500">Content for the homepage welcome box and managed public pages.</p>
                @php
                    $aboutOrder = ['president_name','welcome_message','what_is_mmp','objectives','scholarship_schemes','internships_placements'];
                    $aboutMeta  = [
                        'president_name'        => ['label'=>"President's Name",       'hint'=>'Shown in the Management sidebar on the homepage.'],
                        'welcome_message'        => ['label'=>'Homepage Welcome Box',   'hint'=>'Shown in the red "Welcome to MMP" box on the homepage. Longer text will scroll inside the card.'],
                        'what_is_mmp'            => ['label'=>'What is MMP (About)',    'hint'=>'Full text for the About / What is MMP page.'],
                        'objectives'             => ['label'=>'Objectives',             'hint'=>'Institutional objectives page content.'],
                        'scholarship_schemes'    => ['label'=>'Scholarship Schemes',    'hint'=>'Content for the Scholarship Schemes page.'],
                        'internships_placements' => ['label'=>'Internships & Placements','hint'=>'Content for the Internships & Placements page.'],
                    ];
                @endphp
                @foreach($aboutOrder as $akey)
                    @php $as = $allSettings->get($akey); @endphp
                    @if($as)
                    <x-card>
                        <x-form-field :label="$aboutMeta[$akey]['label'] ?? $as->label" :name="$akey" span="full" :hint="$aboutMeta[$akey]['hint'] ?? ''">
                            @if($as->type === 'text')
                                <x-input :name="$akey" :value="$as->value" />
                            @else
                                <x-textarea :name="$akey" rows="6">{{ $as->value }}</x-textarea>
                            @endif
                        </x-form-field>
                    </x-card>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- ── Tab 9: Contact & Maps ───────────────────────── --}}
        <div x-show="activeTab===9" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wide border-b border-gray-100 pb-2">Contact Information</h3>
                    @foreach(['contact_email','contact_phone','contact_address'] as $ckey)
                        @php $cs = $allSettings->get($ckey); @endphp
                        @if($cs)
                        <x-card>
                            <x-form-field :label="$cs->label" :name="$ckey" span="full">
                                <x-input :name="$ckey" :value="$cs->value" />
                            </x-form-field>
                        </x-card>
                        @endif
                    @endforeach
                    @php $cContent = $allSettings->get('contact_us_content'); @endphp
                    @if($cContent)
                    <x-card>
                        <x-form-field label="Contact Page Description" name="contact_us_content" span="full" hint="Shown at the top of the public Contact Us page.">
                            <x-textarea name="contact_us_content" rows="4">{{ $cContent->value }}</x-textarea>
                        </x-form-field>
                    </x-card>
                    @endif
                </div>
                <div class="space-y-4">
                    <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wide border-b border-gray-100 pb-2">Google Maps Embed</h3>
                    @php $mapSetting = $allSettings->get('google_maps_iframe'); @endphp
                    <x-card>
                        <x-form-field label="Maps Embed Code" name="google_maps_iframe" span="full"
                            hint="Paste the full &lt;iframe&gt; code from Google Maps → Share → Embed a map.">
                            <x-textarea name="google_maps_iframe" rows="5"
                                placeholder="&lt;iframe src=&quot;https://www.google.com/maps/embed?...&quot;&gt;&lt;/iframe&gt;">{{ $mapSetting?->value }}</x-textarea>
                        </x-form-field>
                    </x-card>
                    @if($mapSetting?->value && str_contains($mapSetting->value, 'iframe'))
                    <x-card>
                        <p class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">Live Preview</p>
                        <div class="w-full h-52 rounded overflow-hidden border border-gray-200 [&>iframe]:w-full [&>iframe]:h-full">
                            {!! $mapSetting->value !!}
                        </div>
                    </x-card>
                    @endif
                </div>
            </div>
        </div>
    </form>{{-- /settings form --}}

    {{-- ══════════════════════════════════════════════════════
         CONTENT MANAGEMENT PANELS (tabs 3-8)
         Sibling to the settings form — no nested form issues.
    ══════════════════════════════════════════════════════ --}}

    {{-- ── Tab 3: Hero Banners ─────────────────────────────── --}}
    <div x-show="activeTab===3" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
        <div class="mb-4 flex justify-between items-center">
            <p class="text-sm text-gray-500">Hero slideshow banners on the public homepage.</p>
            <a href="{{ route('admin.banners.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#8B0000] hover:bg-[#5c0000] text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Add Banner
            </a>
        </div>
        <div class="space-y-3">
            @forelse($banners as $banner)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden flex items-center gap-4 pr-4">
                <div class="w-36 shrink-0 bg-gray-50 overflow-hidden" style="height:88px">
                    @if($banner->image)
                        <img src="{{ asset('storage/'.$banner->image) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-200">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0 py-3">
                    <p class="font-semibold text-gray-900 truncate">{{ $banner->title ?? 'Untitled Banner' }}</p>
                    @if($banner->subtitle)<p class="text-xs text-gray-400 truncate mt-0.5">{{ $banner->subtitle }}</p>@endif
                    <p class="text-xs text-gray-300 mt-0.5">Order: {{ $banner->order }}</p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full {{ $banner->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-400' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $banner->is_active ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                        {{ $banner->is_active ? 'Active' : 'Hidden' }}
                    </span>
                    @if($banner->image)
                    <a href="{{ asset('storage/'.$banner->image) }}" target="_blank" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800 font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        View
                    </a>
                    @endif
                    <a href="{{ route('admin.banners.edit', $banner) }}" class="text-sm text-[#8B0000] hover:text-[#5c0000] font-medium">Edit</a>
                    <form method="POST" action="{{ route('admin.banners.destroy', $banner) }}" onsubmit="return confirm('Delete banner?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-sm text-gray-300 hover:text-red-500 font-medium transition-colors">Delete</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="py-16 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <p class="font-medium">No banners yet</p>
                <a href="{{ route('admin.banners.create') }}" class="mt-2 inline-block text-[#8B0000] hover:underline text-sm">Add your first banner</a>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ── Tab 4: Photo Gallery ─────────────────────────────── --}}
    <div x-show="activeTab===4" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
        <div class="mb-4 flex justify-between items-center">
            <p class="text-sm text-gray-500">Photos, videos and documents for the public media gallery.</p>
            <a href="{{ route('admin.media.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#8B0000] hover:bg-[#5c0000] text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Upload Files
            </a>
        </div>
        @if($media->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <p class="font-medium">No media uploaded yet</p>
            <a href="{{ route('admin.media.create') }}" class="mt-2 inline-block text-[#8B0000] hover:underline text-sm">Upload media files</a>
        </div>
        @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($media as $item)
            <div class="group bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-all duration-200">
                @if(str_starts_with($item->mime_type ?? '', 'image'))
                    <a href="{{ asset('storage/'.$item->file_path) }}" target="_blank">
                        <img src="{{ asset('storage/'.$item->file_path) }}" alt="{{ $item->file_name }}" class="w-full h-28 object-cover group-hover:scale-105 transition-transform duration-300">
                    </a>
                @elseif(str_starts_with($item->mime_type ?? '', 'video'))
                    <a href="{{ asset('storage/'.$item->file_path) }}" target="_blank" class="block relative">
                        <video src="{{ asset('storage/'.$item->file_path) }}" class="w-full h-28 object-cover bg-black" muted preload="none"></video>
                        <span class="absolute inset-0 flex items-center justify-center bg-black/30">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        </span>
                    </a>
                @else
                    <a href="{{ asset('storage/'.$item->file_path) }}" target="_blank" class="flex items-center justify-center w-full h-28 bg-gray-50 hover:bg-gray-100 transition-colors">
                        @php $fext = strtolower(pathinfo($item->file_name ?? $item->file_path, PATHINFO_EXTENSION)); @endphp
                        @if($fext === 'pdf')
                            <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        @else
                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        @endif
                    </a>
                @endif
                <div class="p-2">
                    <p class="text-[10px] text-gray-500 truncate" title="{{ $item->file_name ?? $item->title }}">{{ $item->file_name ?? $item->title }}</p>
                    <div class="flex items-center justify-between mt-1.5 gap-1">
                        <span class="text-[9px] font-semibold px-1.5 py-0.5 rounded-full {{ $item->file_type === 'gallery' ? 'bg-blue-50 text-blue-600' : 'bg-gray-100 text-gray-500' }}">{{ $item->file_type }}</span>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ asset('storage/'.$item->file_path) }}" target="_blank" title="View" class="text-blue-400 hover:text-blue-600">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('admin.media.destroy', $item) }}" onsubmit="return confirm('Delete this file permanently?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Delete" class="text-gray-300 hover:text-red-500 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ── Tab 5: Resources & Downloads ────────────────────── --}}
    <div x-show="activeTab===5" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
        <div class="mb-4 flex justify-between items-center">
            <p class="text-sm text-gray-500">Forms, syllabi, reports and other downloadable resources for the public.</p>
            <a href="{{ route('admin.downloads.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#8B0000] hover:bg-[#5c0000] text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Add Resource
            </a>
        </div>
        @if($downloads->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            <p class="font-medium">No resources uploaded yet</p>
            <a href="{{ route('admin.downloads.create') }}" class="mt-2 inline-block text-[#8B0000] hover:underline text-sm">Add first resource</a>
        </div>
        @else
        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">File</th>
                        <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Size</th>
                        <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Added</th>
                        <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($downloads as $dl)
                    <tr class="hover:bg-gray-50/70 transition-colors">
                        <td class="px-5 py-3 font-semibold text-gray-900 max-w-xs">
                            <p class="truncate">{{ $dl->title }}</p>
                        </td>
                        <td class="px-5 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full">{{ $dl->category ?? 'general' }}</span>
                        </td>
                        <td class="px-5 py-3">
                            @if($dl->file_path)
                            <span class="inline-flex items-center gap-1.5 text-xs text-gray-500">
                                <span class="px-1.5 py-0.5 bg-red-50 text-red-600 font-bold rounded text-[10px]">{{ strtoupper($dl->file_type ?? pathinfo($dl->file_path, PATHINFO_EXTENSION)) }}</span>
                                <span class="truncate max-w-[120px]">{{ $dl->file_name ?? basename($dl->file_path) }}</span>
                            </span>
                            @else<span class="text-xs text-gray-300">No file</span>@endif
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400">
                            @if($dl->file_size)@php $kb=round($dl->file_size/1024,0); @endphp{{ $kb>=1024 ? round($kb/1024,1).' MB' : $kb.' KB' }}@else —@endif
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 whitespace-nowrap">{{ bsDate($dl->created_at, 'd F Y') }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-3">
                                @if($dl->file_path)
                                <a href="{{ asset('storage/'.$dl->file_path) }}" target="_blank" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    View
                                </a>
                                @endif
                                <a href="{{ route('admin.downloads.edit', $dl) }}" class="text-sm text-[#8B0000] hover:text-[#5c0000] font-medium">Edit</a>
                                <form method="POST" action="{{ route('admin.downloads.destroy', $dl) }}" onsubmit="return confirm('Delete this resource?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-sm text-gray-300 hover:text-red-500 font-medium transition-colors">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- ── Tab 6: News & Events ─────────────────────────────── --}}
    <div x-show="activeTab===6" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
            <p class="text-sm text-gray-500">News and event posts shown on the public homepage and news feed.</p>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.notices.create', ['type' => 'news']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#8B0000] hover:bg-[#5c0000] text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Add News
                </a>
                <a href="{{ route('admin.notices.create', ['type' => 'event']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Add Event
                </a>
            </div>
        </div>
        @php
            $ntColors = ['general'=>'bg-blue-50 text-blue-700','exam'=>'bg-red-50 text-red-700','department'=>'bg-indigo-50 text-indigo-700',
                         'class'=>'bg-amber-50 text-amber-700','teachers'=>'bg-green-50 text-green-700','news'=>'bg-purple-50 text-purple-700','event'=>'bg-teal-50 text-teal-700'];
            $ntLabels = ['general'=>'General','exam'=>'Exam/Results','department'=>'Department','class'=>'Class','teachers'=>'Teachers','news'=>'News','event'=>'Event'];
        @endphp
        @if($notices->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <p class="font-medium">No news or events posted yet</p>
            <a href="{{ route('admin.notices.create', ['type' => 'news']) }}" class="mt-2 inline-block text-[#8B0000] hover:underline text-sm">Add the first news post</a>
        </div>
        @else
        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Author</th>
                        <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">File</th>
                        <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($notices as $notice)
                    <tr class="hover:bg-gray-50/70 transition-colors">
                        <td class="px-5 py-3 max-w-xs">
                            <p class="font-semibold text-gray-900 truncate">{{ $notice->title }}</p>
                            <p class="text-xs text-gray-400 truncate mt-0.5">{{ Str::limit(strip_tags($notice->content), 60) }}</p>
                        </td>
                        <td class="px-5 py-3">
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $ntColors[$notice->type] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $ntLabels[$notice->type] ?? ucfirst($notice->type) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-sm text-gray-500">{{ $notice->author?->name ?? 'System' }}</td>
                        <td class="px-5 py-3 text-xs text-gray-400 whitespace-nowrap">
                            {{ bsDate(($notice->published_at ?? $notice->created_at), 'd F Y') }}
                        </td>
                        <td class="px-5 py-3">
                            @if($notice->attachment)
                                <a href="{{ asset('storage/'.$notice->attachment) }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-blue-600 hover:underline">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    View
                                </a>
                            @else<span class="text-xs text-gray-300">—</span>@endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('admin.notices.edit', $notice) }}" class="text-sm text-[#8B0000] hover:text-[#5c0000] font-medium">Edit</a>
                                <form method="POST" action="{{ route('admin.notices.destroy', $notice) }}" onsubmit="return confirm('Delete this notice?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-sm text-gray-300 hover:text-red-500 font-medium transition-colors">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- ── Tab 7: Facilities ────────────────────────────────── --}}
    <div x-show="activeTab===7" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
        <div class="mb-4 flex justify-between items-center">
            <p class="text-sm text-gray-500">Campus facilities shown on the public Facilities page.</p>
            <a href="{{ route('admin.facilities.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#8B0000] hover:bg-[#5c0000] text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Add Facility
            </a>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Facility Name</th>
                        <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($facilities as $facility)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3 font-semibold text-gray-900">{{ $facility->name }}</td>
                        <td class="px-5 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 bg-purple-50 text-purple-700 rounded-full">{{ ucfirst($facility->category) }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('admin.facilities.edit', $facility) }}" class="text-sm text-[#8B0000] hover:text-[#5c0000] font-medium">Edit</a>
                                <form method="POST" action="{{ route('admin.facilities.destroy', $facility) }}" onsubmit="return confirm('Delete this facility?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-sm text-gray-300 hover:text-red-500 font-medium transition-colors">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="p-8 text-center text-gray-400">No facilities added yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Tab 8: Leadership History ─────────────────────────── --}}
    <div x-show="activeTab===8" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
        <div class="mb-4 flex justify-between items-center">
            <p class="text-sm text-gray-500">Chronological records of Presidents and Principals on the public Leadership page.</p>
            <a href="{{ route('admin.executives.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#8B0000] hover:bg-[#5c0000] text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Add Executive Record
            </a>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Tenure (BS)</th>
                        <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($executives as $exec)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                @if($exec->avatar)
                                    <img src="{{ asset('storage/'.$exec->avatar) }}" alt="{{ $exec->name }}" class="w-8 h-8 rounded-full object-cover border border-gray-200 shrink-0">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                                        <span class="text-xs font-bold text-gray-400">{{ substr($exec->name,0,1) }}</span>
                                    </div>
                                @endif
                                <span class="font-semibold text-gray-900">
                                    {{ $exec->name }}
                                    @if($exec->is_current)<span class="ml-1 text-[10px] font-bold px-1.5 py-0.5 bg-green-50 text-green-700 rounded-full">Current</span>@endif
                                </span>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full">{{ ucfirst($exec->type) }}</span>
                        </td>
                        <td class="px-5 py-3 text-sm text-gray-500">{{ $exec->start_date_bs }} — {{ $exec->end_date_bs ?: 'Present' }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-3">
                                @if($exec->avatar)
                                <a href="{{ asset('storage/'.$exec->avatar) }}" target="_blank" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Photo
                                </a>
                                @endif
                                <a href="{{ route('admin.executives.edit', $exec) }}" class="text-sm text-[#8B0000] hover:text-[#5c0000] font-medium">Edit</a>
                                <form method="POST" action="{{ route('admin.executives.destroy', $exec) }}" onsubmit="return confirm('Delete this executive record?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-sm text-gray-300 hover:text-red-500 font-medium transition-colors">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="p-8 text-center text-gray-400">No leadership records yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>{{-- /x-data --}}
@push('scripts')
<script>
    function deleteWebControlFile(url, message) {
        if (!confirm(message)) {
            return;
        }

        fetch(url, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Delete request failed');
                }

                window.location.reload();
            })
            .catch(() => {
                alert('Unable to remove the file right now.');
            });
    }
</script>
@endpush
@endsection