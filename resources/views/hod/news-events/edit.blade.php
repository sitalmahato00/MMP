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
                    @for($i = 1; $i <= 8; $i++)
                        <option value="{{ $i }}" @selected(old('semester', $notice->semester) == $i)>Semester {{ $i }}</option>
                    @endfor
                </x-select>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <x-form-section title="Content" subtitle="Update the full post and attachment.">
        <x-form-row>
            <x-form-field label="Content" name="content" :required="true" span="full">
                <x-textarea name="content" rows="8" :required="true" placeholder="Write the update here...">{{ old('content', $notice->content) }}</x-textarea>
            </x-form-field>

            <x-form-field label="Attachment" name="attachment" span="full">
                @if($notice->attachment)
                    <div class="mb-3 flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <span class="text-sm text-slate-700">Current attachment</span>
                        <a href="{{ asset('storage/' . $notice->attachment) }}" target="_blank" class="text-sm text-blue-600 hover:text-blue-800">View</a>
                    </div>
                @endif
                <x-file-input name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" label="Upload new attachment (optional)"/>
            </x-form-field>
        </x-form-row>
    </x-form-section>

    <div class="flex items-center gap-3 pb-6">
        <x-btn type="submit">Update Post</x-btn>
        <x-btn href="{{ route('hod.news-events.index') }}" variant="secondary">Cancel</x-btn>
    </div>
</form>
@endsection
