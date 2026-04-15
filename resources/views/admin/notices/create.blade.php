@extends('layouts.app')
@section('title', 'Post Notice')

@section('content')
<x-page-header title="Post Notice" subtitle="Publish a new notice to the system."
               back="{{ route('admin.notices.index') }}"/>

<form method="POST" action="{{ route('admin.notices.store') }}" enctype="multipart/form-data" class="max-w-3xl space-y-6">
    @csrf
    <x-form-section title="Notice Details">
        <x-form-row :cols="1">
            <x-form-field label="Title" name="title" :required="true">
                <x-input name="title" :required="true" placeholder="Notice title…"/>
            </x-form-field>
            <x-form-field label="Content" name="content" :required="true">
                <x-textarea name="content" rows="6" placeholder="Full notice content…"/>
            </x-form-field>
            <x-form-field label="Attachment" name="attachment">
                <x-file-input name="attachment" accept="image/*,.pdf,video/*" label="Upload notice attachment (Image, PDF, Video)"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Targeting & Scheduling">
        <x-form-row>
            <x-form-field label="Notice Type / Audience" name="type" :required="true">
                <x-select name="type" :required="true">
                    @foreach(['general'=>'General Everyone','department'=>'Specific Department','class'=>'Specific Class','teachers'=>'Teachers Only','exam'=>'Examination'] as $val => $label)
                        <option value="{{ $val }}" {{ old('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Publish Date" name="published_at">
                <x-input name="published_at" type="datetime-local" :value="now()->format('Y-m-d\TH:i')"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <div class="flex items-center gap-3">
        <x-btn type="submit">Publish Notice</x-btn>
        <x-btn href="{{ route('admin.notices.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
