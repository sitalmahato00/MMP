@extends('layouts.app')
@section('title', 'Web Control Panel')

@section('content')
<x-page-header title="Web Control Panel" subtitle="Manage all CMS content: branding, pages, people, facilities, and contact information.">
    <x-slot name="actions">
        <x-btn type="submit" form="web-control-form">Save All Changes</x-btn>
    </x-slot>
</x-page-header>

<x-tab-group :tabs="['Branding & About', 'Facilities', 'Leadership History', 'Student Affairs', 'Contact & Maps']">
    <form method="POST" action="{{ route('admin.web-control.update') }}" id="web-control-form" enctype="multipart/form-data">
        @csrf

        {{-- Tab 0: Branding & About --}}
        <x-tab-panel :index="0">
            @php
                $aboutSettings = collect($settings->get('about', []))->keyBy('key');
                $aboutOrder = ['site_logo', 'president_name', 'principal_name', 'principal_photo', 'principal_message_media', 'what_is_mmp', 'welcome_message', 'principals_message', 'objectives'];
                $aboutItems = collect($aboutOrder)
                    ->map(fn ($key) => $aboutSettings->get($key))
                    ->filter();
                $extraAboutItems = $aboutSettings->except($aboutOrder)
                    ->filter(fn ($setting) => $setting->key !== 'presidents_message')
                    ->values();
            @endphp
            <div class="space-y-6">
                @foreach($aboutItems->merge($extraAboutItems) as $setting)
                    <x-card>
                        <x-form-field :label="$setting->label" :name="$setting->key" span="full">
                            @if($setting->type === 'image')
                                @if($setting->value)
                                    <div class="mb-3">
                                        <img src="{{ asset('storage/' . $setting->value) }}" alt="{{ $setting->label }}" class="w-24 h-28 rounded-lg border border-gray-200 object-cover">
                                    </div>
                                @endif
                                <x-file-input :name="$setting->key" accept="image/*" :current="$setting->value" :label="$setting->key === 'site_logo' ? 'Upload site logo (JPG/PNG/WebP)' : 'Upload principal photo (JPG/PNG/WebP)'" />
                            @elseif($setting->type === 'file')
                                @if($setting->value)
                                    @php $ext = strtolower(pathinfo($setting->value, PATHINFO_EXTENSION)); @endphp
                                    <div class="mb-3 flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                        @if(in_array($ext, ['jpg','jpeg','png','gif','webp']))
                                            <img src="{{ asset('storage/' . $setting->value) }}" class="w-20 h-14 object-cover rounded border">
                                        @elseif(in_array($ext, ['mp4','webm','mov']))
                                            <video src="{{ asset('storage/' . $setting->value) }}" class="w-32 h-16 rounded border bg-black" muted></video>
                                        @elseif($ext === 'pdf')
                                            <div class="w-12 h-14 bg-red-100 border border-red-200 rounded flex items-center justify-center">
                                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                            </div>
                                        @endif
                                        <div class="text-xs text-gray-500">
                                            Current: <span class="font-medium text-gray-700">{{ basename($setting->value) }}</span>
                                            <a href="{{ asset('storage/' . $setting->value) }}" target="_blank" class="ml-2 text-blue-600 hover:underline">View</a>
                                        </div>
                                    </div>
                                @endif
                                <x-file-input :name="$setting->key" accept="image/*,video/mp4,video/webm,.pdf" :current="$setting->value" label="Upload image, video (MP4/WebM) or PDF — shown in Principal's Message" />
                            @elseif($setting->type === 'richtext' || $setting->type === 'textarea')
                                <x-textarea :name="$setting->key" rows="6">{{ $setting->value }}</x-textarea>
                            @else
                                <x-input :name="$setting->key" :value="$setting->value" />
                            @endif
                        </x-form-field>
                    </x-card>
                @endforeach
            </div>
        </x-tab-panel>

        {{-- Tab 1: Facilities --}}
        <x-tab-panel :index="1">
            <div class="mb-4 flex justify-between items-center">
                <p class="text-sm text-gray-500">Manage campus facilities (classrooms, labs, workshops, resources).</p>
                <x-btn href="{{ route('admin.facilities.create') }}" size="sm" color="indigo" icon="plus">Add Facility</x-btn>
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
                            <td class="px-5 py-3"><span class="font-semibold text-gray-900">{{ $facility->name }}</span></td>
                            <td class="px-5 py-3"><x-badge color="purple">{{ ucfirst($facility->category) }}</x-badge></td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.facilities.edit', $facility) }}" class="text-[#8B0000] hover:text-[#5c0000] font-medium text-sm">Edit</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="p-8 text-center text-gray-400">No facilities set up yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-tab-panel>

        {{-- Tab 2: Leadership History --}}
        <x-tab-panel :index="2">
            <div class="mb-4 flex justify-between items-center">
                <p class="text-sm text-gray-500">Chronological records of Presidents and Principals.</p>
                <x-btn href="{{ route('admin.executives.create') }}" size="sm" color="blue" icon="plus">Add Executive Record</x-btn>
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
                            <td class="px-5 py-3 flex items-center gap-3">
                                <x-avatar :src="$exec->avatar ? url('storage/'.$exec->avatar) : null" :name="$exec->name" size="md"/>
                                <span class="font-semibold text-gray-900">
                                    {{ $exec->name }}
                                    @if($exec->is_current) <x-badge color="green" class="ml-1">Current</x-badge> @endif
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <x-badge color="blue">{{ ucfirst($exec->type) }}</x-badge>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-600">
                                {{ $exec->start_date_bs }} — {{ $exec->end_date_bs ?: 'Present' }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.executives.edit', $exec) }}" class="text-[#8B0000] hover:text-[#5c0000] font-medium text-sm">Edit</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="p-8 text-center text-gray-400">No historical records created yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-tab-panel>

        {{-- Tab 3: Student Affairs --}}
        <x-tab-panel :index="3">
            <div class="space-y-6">
                @foreach($settings->get('student_affairs', []) as $setting)
                    <x-card>
                        <x-form-field :label="$setting->label" :name="$setting->key" span="full">
                            @if($setting->type === 'richtext' || $setting->type === 'textarea')
                                <x-textarea :name="$setting->key" rows="6">{{ $setting->value }}</x-textarea>
                            @else
                                <x-input :name="$setting->key" :value="$setting->value" />
                            @endif
                        </x-form-field>
                    </x-card>
                @endforeach
            </div>
        </x-tab-panel>

        {{-- Tab 4: Contact & Maps --}}
        <x-tab-panel :index="4">
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

                {{-- Contact Fields --}}
                <div class="space-y-4">
                    <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wide border-b border-gray-100 pb-2">Contact Information</h3>
                    @foreach($settings->get('contact', []) as $setting)
                        @if($setting->key !== 'google_maps_iframe' && $setting->key !== 'contact_us_content')
                        <x-card>
                            <x-form-field :label="$setting->label" :name="$setting->key" span="full">
                                <x-input :name="$setting->key" :value="$setting->value" />
                            </x-form-field>
                        </x-card>
                        @endif
                    @endforeach

                    {{-- Contact Page Content --}}
                    @php $contactContent = collect($settings->get('contact', []))->firstWhere('key', 'contact_us_content'); @endphp
                    @if($contactContent)
                    <x-card>
                        <x-form-field label="Contact Page Description" name="contact_us_content" span="full">
                            <x-textarea name="contact_us_content" rows="4">{{ $contactContent->value }}</x-textarea>
                        </x-form-field>
                    </x-card>
                    @endif
                </div>

                {{-- Google Maps Embed --}}
                <div class="space-y-4">
                    <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wide border-b border-gray-100 pb-2">Google Maps Embed</h3>
                    @php $mapSetting = collect($settings->get('contact', []))->firstWhere('key', 'google_maps_iframe'); @endphp
                    <x-card>
                        <x-form-field label="Maps Embed Code" name="google_maps_iframe" span="full"
                            hint="Paste the full &lt;iframe&gt; embed code from Google Maps. Used on both the homepage and Contact Us page.">
                            <x-textarea name="google_maps_iframe" rows="5" placeholder="&lt;iframe src=&quot;https://www.google.com/maps/embed?...&quot;&gt;&lt;/iframe&gt;">{{ $mapSetting?->value }}</x-textarea>
                        </x-form-field>
                    </x-card>

                    {{-- Live Preview --}}
                    @if($mapSetting?->value && str_contains($mapSetting->value, 'iframe'))
                    <x-card>
                        <p class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">Live Preview</p>
                        <div class="w-full h-48 rounded overflow-hidden border border-gray-200 [&>iframe]:w-full [&>iframe]:h-full">
                            {!! $mapSetting->value !!}
                        </div>
                    </x-card>
                    @endif
                </div>
            </div>
        </x-tab-panel>
    </form>
</x-tab-group>
@endsection

