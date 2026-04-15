@extends('layouts.app')
@section('title', 'Web Control Panel')

@section('content')
<x-page-header title="Full Web Control Panel" subtitle="Manage CMS texts, landing pages, and institution information across the public site.">
    <x-slot name="actions">
        <x-btn type="submit" form="web-control-form">Save All Changes</x-btn>
    </x-slot>
</x-page-header>

<x-tab-group :tabs="['About MMP', 'Facilities', 'Student Affairs', 'Contact & Info']">
    <form method="POST" action="{{ route('admin.web-control.update') }}" id="web-control-form">
        @csrf

        {{-- About MMP --}}
        <x-tab-panel :index="0">
            <div class="space-y-6">
                @foreach($settings->get('about', []) as $setting)
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

        {{-- Facilities --}}
        <x-tab-panel :index="1">
            <div class="space-y-6">
                @foreach($settings->get('facilities', []) as $setting)
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

        {{-- Student Affairs --}}
        <x-tab-panel :index="2">
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
        <x-tab-panel :index="3">
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
