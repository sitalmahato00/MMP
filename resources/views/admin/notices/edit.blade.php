@extends('layouts.app')
@section('title', 'Edit Notice')

@section('content')
<x-page-header title="Edit Notice" :subtitle="$notice->title"
               back="{{ route('admin.notices.index') }}"/>

<form method="POST" action="{{ route('admin.notices.update', $notice) }}" enctype="multipart/form-data" class="max-w-3xl space-y-6">
    @csrf @method('PUT')
    <x-form-section title="Notice Details">
        <x-form-row :cols="1">
            <x-form-field label="Title" name="title" :required="true">
                <x-input name="title" :value="$notice->title" :required="true"/>
            </x-form-field>
            <x-form-field label="Content" name="content" :required="true">
                <x-textarea name="content" rows="6" :required="true">{{ $notice->content }}</x-textarea>
            </x-form-field>
            <x-form-field label="Replace Attachment" name="attachment">
                <x-file-input name="attachment" accept="image/*,.pdf,video/*" label="Upload notice attachment (Image, PDF, Video)" :current="$notice->attachment"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Targeting & Scheduling">
        <x-form-row>
            <x-form-field label="Notice Type / Audience" name="type" :required="true">
                <x-select name="type" :required="true">
                    @foreach(['general'=>'Notice Board','exam'=>'Exam Schedules & Results','department'=>'Specific Department','class'=>'Specific Class','teachers'=>'Teachers Only'] as $val => $label)
                        <option value="{{ $val }}" {{ $notice->type === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </x-select>
            </x-form-field>
            <x-form-field label="Publish Date" name="published_at">
                <x-input name="published_at" type="datetime-local" :value="$notice->published_at ? \Carbon\Carbon::parse($notice->published_at)->format('Y-m-d\TH:i') : ''"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <div class="flex items-center gap-3">
        <x-btn type="submit">Save Changes</x-btn>
        <x-btn href="{{ route('admin.notices.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
