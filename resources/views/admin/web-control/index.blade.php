@extends('layouts.app')
@section('title', 'Web Control Panel')

@section('content')
<x-page-header title="Full Web Control Panel" subtitle="Manage CMS texts, landing pages, facilities, and institutional history.">
    <x-slot name="actions">
        <x-btn type="submit" form="web-control-form">Save All Changes</x-btn>
    </x-slot>
</x-page-header>

<x-tab-group :tabs="['About MMP', 'Facilities & Resources', 'Leadership History', 'Student Affairs', 'Contact & Info']">
    <form method="POST" action="{{ route('admin.web-control.update') }}" id="web-control-form" enctype="multipart/form-data">
        @csrf

        {{-- About MMP --}}
        <x-tab-panel :index="0">
            @php
                $aboutSettings = collect($settings->get('about', []))->keyBy('key');
                $aboutOrder = ['site_logo', 'what_is_mmp', 'objectives', 'welcome_message', 'principals_message', 'principal_photo', 'president_name', 'principal_name'];
                $aboutItems = collect($aboutOrder)
                    ->map(fn ($key) => $aboutSettings->get($key))
                    ->filter();

                $extraAboutItems = $aboutSettings
                    ->except($aboutOrder)
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

        {{-- Facilities --}}
        <x-tab-panel :index="1">
            <div class="mb-4 flex justify-between items-center">
                <p class="text-sm text-gray-500">Manage interactive multi-resource environments (Classrooms, Labs, Workshops).</p>
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
                                <a href="{{ route('admin.facilities.edit', $facility) }}" class="text-[#8B0000] hover:text-[#5c0000] font-medium text-sm">Edit Entry</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="p-8 text-center text-gray-400">No facilities set up yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-tab-panel>

        {{-- Leadership History --}}
        <x-tab-panel :index="2">
            <div class="mb-4 flex justify-between items-center">
                <p class="text-sm text-gray-500">Chronological records of Presidents and Principals.</p>
                <x-btn href="{{ route('admin.executives.create') }}" size="sm" color="blue" icon="plus">Add Executive Record</x-btn>
            </div>
            
            <div class="bg-white rounded-xl border border-gray-100 overflow-hidden shadow-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100">
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Executive Details</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Role & Designation</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Tenure (BS)</th>
                            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($executives as $exec)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3 flex items-center gap-3">
                                <x-avatar :src="$exec->avatar ? url('storage/'.$exec->avatar) : null" :name="$exec->name" size="md"/>
                                <div>
                                    <span class="font-semibold text-gray-900 block flex items-center gap-2">
                                        {{ $exec->name }}
                                        @if($exec->is_current) <x-badge color="green">Current</x-badge> @endif
                                    </span>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <x-badge color="blue">{{ ucfirst($exec->type) }}</x-badge>
                                <div class="text-xs text-gray-500 mt-1">{{ optional($exec)->designation }}</div>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-600">
                                {{ $exec->start_date_bs }} to {{ $exec->end_date_bs ?: 'Present' }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.executives.edit', $exec) }}" class="text-[#8B0000] hover:text-[#5c0000] font-medium text-sm">Update Tenure</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="p-8 text-center text-gray-400">No historical records created yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-tab-panel>

        {{-- Student Affairs --}}
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

        {{-- Contact --}}
        <x-tab-panel :index="4">
            <div class="space-y-6">
                @foreach($settings->get('contact', []) as $setting)
                    <x-card>
                        <x-form-field :label="$setting->label" :name="$setting->key" span="full">
                            @if($setting->type === 'richtext' || $setting->type === 'textarea')
                                <x-textarea :name="$setting->key" rows="3">{{ $setting->value }}</x-textarea>
                            @else
                                <x-input :name="$setting->key" :value="$setting->value" />
                            @endif
                        </x-form-field>
                    </x-card>
                @endforeach
            </div>
        </x-tab-panel>
    </form>
</x-tab-group>
@endsection
