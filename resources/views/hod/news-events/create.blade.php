@extends('layouts.app')
@section('title', 'Create News/Event')

@section('content')
<x-page-header title="Create News/Event" subtitle="Publish a department news post or event update."
               back="{{ route('hod.news-events.index') }}"/>

<form method="POST" action="{{ route('hod.news-events.store') }}" enctype="multipart/form-data" class="max-w-4xl space-y-6">
    @csrf

    <x-form-section title="Basic Information" subtitle="Title, category, and audience.">
        <x-form-row>
            <x-form-field label="Title" name="title" :required="true" span="full">
                <x-input name="title" :value="old('title')" :required="true" placeholder="Enter title"/>
            </x-form-field>

            <x-form-field label="Category" name="type" :required="true">
                <x-select name="type" :required="true">
                    <option value="news" @selected(old('type', 'news') === 'news')>News</option>
                    <option value="event" @selected(old('type') === 'event')>Event</option>
                </x-select>
            </x-form-field>

            <x-form-field label="Status" name="is_published">
                <x-select name="is_published">
                    <option value="0" @selected(old('is_published', '0') === '0')>Save as Draft</option>
                    <option value="1" @selected(old('is_published') === '1')>Publish Now</option>
                </x-select>
            </x-form-field>
        </x-form-row>

        <x-form-row>
            <x-form-field label="Target Program" name="program_id">
                <x-select name="program_id">
                    <option value="">All Programs in {{ $department->name }}</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" @selected(old('program_id') == $program->id)>{{ $program->name }}</option>
                    @endforeach
                </x-select>
            </x-form-field>

            <x-form-field label="Target Semester" name="semester">
                <x-select name="semester">
                    <option value="">All Semesters</option>
                    @for($i = 1; $i <= 8; $i++)
                        <option value="{{ $i }}" @selected(old('semester') == $i)>Semester {{ $i }}</option>
                    @endfor
                </x-select>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Content" subtitle="Write the full update and upload an attachment if needed.">
        <x-form-row>
            <x-form-field label="Content" name="content" :required="true" span="full">
                <x-textarea name="content" rows="8" :required="true" placeholder="Write the update here...">{{ old('content') }}</x-textarea>
            </x-form-field>

            <x-form-field label="Attachment" name="attachment" span="full">
                <x-file-input name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" label="Upload attachment (optional)"/>
                <p class="mt-1.5 text-xs text-slate-500">Supported: PDF, DOC, DOCX, JPG, PNG</p>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <div class="flex items-center gap-3 pb-6">
        <x-btn type="submit">Create Post</x-btn>
        <x-btn href="{{ route('hod.news-events.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
