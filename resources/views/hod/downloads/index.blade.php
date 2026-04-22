@extends('layouts.app')
@section('title', 'Resources')

@section('content')
<x-page-header title="Department Gallery Files & Images" subtitle="Manage department gallery files and images.">
    <x-slot name="actions">
        <x-btn href="{{ route('hod.downloads.create') }}">Upload Files</x-btn>
    </x-slot>
</x-page-header>

{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
    <x-stat-card title="Total Files" :value="$downloads->total()" color="blue" icon="heroicon-o-photograph"/>
    <x-stat-card title="Total Size" :value="number_format($downloads->sum('file_size')/1024/1024, 2) . ' MB'" color="green" icon="heroicon-o-trash"/>
    <x-stat-card title="Images" :value="$downloads->where('file_type', 'jpg')->count() + $downloads->where('file_type', 'jpeg')->count() + $downloads->where('file_type', 'png')->count()" color="purple" icon="heroicon-o-photograph"/>
    <x-stat-card title="Documents" :value="$downloads->whereIn('file_type', ['pdf','doc','docx'])->count()" color="yellow" icon="heroicon-o-document-text"/>
</div>

{{-- Filters --}}
<div class="bg-white rounded-2xl shadow p-6 mb-6">
    <form method="GET" class="flex flex-col md:flex-row md:items-end md:space-x-4 space-y-4 md:space-y-0">
        <div class="flex-1">
            <label class="block text-xs font-semibold text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search files or titles..." class="form-input w-full rounded-xl" />
        </div>
        <div class="w-48">
            <label class="block text-xs font-semibold text-gray-500 mb-1">Type</label>
            <select name="category" class="form-select w-full rounded-xl">
                <option value="">All Types</option>
                <option value="Forms & Downloads" @selected(request('category') == 'Forms & Downloads')>Forms & Downloads</option>
                <option value="Syllabus" @selected(request('category') == 'Syllabus')>Syllabus</option>
                <option value="Notes" @selected(request('category') == 'Notes')>Notes</option>
                <option value="Question Bank" @selected(request('category') == 'Question Bank')>Question Bank</option>
                <option value="Reports & Publications" @selected(request('category') == 'Reports & Publications')>Reports & Publications</option>
            </select>
        </div>
        <div class="w-48">
            <label class="block text-xs font-semibold text-gray-500 mb-1">From Date</label>
            <input type="text" name="from_date" placeholder="From BS date" class="form-input w-full rounded-xl" />
        </div>
        <div class="w-48">
            <label class="block text-xs font-semibold text-gray-500 mb-1">To Date</label>
            <input type="text" name="to_date" placeholder="To BS date" class="form-input w-full rounded-xl" />
        </div>
        <div class="flex flex-col gap-2 md:gap-0 md:flex-row md:items-end">
            <button type="submit" class="btn btn-danger rounded-xl px-6">Filter</button>
            <a href="?" class="btn btn-outline-danger rounded-xl px-6">Reset</a>
        </div>
        <div class="flex flex-row gap-2 ml-auto mt-4 md:mt-0">
            <button type="button" class="btn btn-outline rounded-xl">Gallery View</button>
            <button type="button" class="btn btn-danger rounded-xl">Upload Files</button>
        </div>
    </form>
</div>

<div class="flex items-center justify-between mb-2">
    <div class="text-sm text-gray-500 font-medium">Showing {{ $downloads->count() }} files</div>
    <div class="flex gap-2">
        <button type="button" class="btn btn-outline rounded-xl">Cards</button>
        <button type="button" class="btn btn-danger rounded-xl">Table</button>
    </div>
</div>

<x-data-table :paginator="$downloads">
    <x-slot name="head">
        <tr>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Preview</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Title</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Type</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Size</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Uploaded</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
        </tr>
    </x-slot>
    @forelse($downloads as $download)
    <tr class="hover:bg-gray-50/70 transition-colors">
        <td class="px-5 py-3.5">
            @if(Str::startsWith($download->file_type, 'jpg') || Str::startsWith($download->file_type, 'jpeg') || Str::startsWith($download->file_type, 'png'))
                <img src="{{ $download->file_url }}" alt="preview" class="w-16 h-12 object-cover rounded-lg border border-gray-200" />
            @else
                <span class="text-xs text-gray-400">—</span>
            @endif
        </td>
        <td class="px-5 py-3.5 font-semibold text-gray-900">{{ $download->title }}</td>
        <td class="px-5 py-3.5">{{ $download->category ?? 'General' }}</td>
        <td class="px-5 py-3.5">{{ number_format($download->file_size/1024, 1) }} KB</td>
        <td class="px-5 py-3.5 text-gray-400 text-xs whitespace-nowrap">{{ bsDate($download->created_at, 'Y, F d') }}</td>
        <td class="px-5 py-3.5">
            @php $canEdit = $download->uploaded_by === auth()->id(); @endphp
            <x-table-actions
                @if($canEdit)
                    :edit="route('hod.downloads.edit', $download)"
                    :destroy="route('hod.downloads.destroy', $download)"
                @endif
                name="{{ $download->title }}"
            />
        </td>
    </tr>
    @empty
    <tr><td colspan="6">
        <x-empty-state title="No resources uploaded"
            message="Upload forms, syllabi, notes, or question banks for your department."
            action="{{ route('hod.downloads.create') }}"
            actionLabel="Upload Resource"/>
    </td></tr>
    @endforelse
</x-data-table>
@endsection
