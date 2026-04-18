@extends('layouts.app')
@section('title', 'Notices')

@section('content')
<x-page-header title="Notice Board" subtitle="Publish and manage institutional notices.">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.notices.create') }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Post Notice
        </x-btn>
    </x-slot>
</x-page-header>

<x-search-filter>
    <x-input name="search" value="{{ request('search') }}" placeholder="Search notices…" class="flex-1 min-w-[200px]"/>
    <x-select name="type">
        <option value="">All Types</option>
        @foreach(['general' => 'Notice Board', 'exam' => 'Exam Schedules & Results', 'news' => 'News', 'event' => 'Event', 'department' => 'Department', 'class' => 'Class', 'teachers' => 'Teachers'] as $value => $label)
            <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </x-select>
</x-search-filter>

<x-data-table :paginator="$notices">
    <x-slot name="head">
        <tr>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Title</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Type</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Author</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Published</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Attachment</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
        </tr>
    </x-slot>

    @forelse($notices as $notice)
    @php
        $typeColors = ['general'=>'blue','exam'=>'red','department'=>'indigo','class'=>'amber','teachers'=>'green','news'=>'purple','event'=>'teal'];
    @endphp
    <tr class="hover:bg-gray-50/70 transition-colors">
        <td class="px-5 py-3.5">
            <p class="font-semibold text-gray-900 truncate max-w-xs">{{ $notice->title }}</p>
            <p class="text-xs text-gray-400 mt-0.5 truncate max-w-xs">{{ Str::limit(strip_tags($notice->content), 60) }}</p>
        </td>
        <td class="px-5 py-3.5">
            <x-badge :color="$typeColors[$notice->type] ?? 'gray'">
                {{ $notice->type === 'exam' ? 'Exam Schedules & Results' : ucfirst($notice->type) }}
            </x-badge>
        </td>
        <td class="px-5 py-3.5">
            <span class="text-sm text-gray-600">{{ $notice->author?->name ?? 'System' }}</span>
        </td>
        <td class="px-5 py-3.5 text-gray-400 text-xs whitespace-nowrap">
            {{ $notice->published_at ? bsDate($notice->published_at, 'Y, F d') : bsDate($notice->created_at, 'Y, F d') }}
        </td>
        <td class="px-5 py-3.5">
            @php $totalFiles = ($notice->attachments_count ?? 0) + ($notice->attachment ? 1 : 0); @endphp
            @if($totalFiles > 0)
                <div x-data="{ showFiles: false }" class="relative">
                    <button type="button" @click="showFiles = !showFiles" class="inline-flex items-center gap-1 text-sm text-[#8B0000] hover:underline">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        {{ $totalFiles }} file{{ $totalFiles > 1 ? 's' : '' }}
                    </button>
                    <div x-show="showFiles" x-cloak @click.outside="showFiles = false"
                         class="absolute left-0 top-full mt-1 z-20 bg-white rounded-lg shadow-xl border border-gray-100 p-2 min-w-[200px] space-y-1">
                        @if($notice->attachment)
                            <x-file-preview
                                :url="asset('storage/'.$notice->attachment)"
                                :filename="basename($notice->attachment)"
                                :type="pathinfo($notice->attachment, PATHINFO_EXTENSION)"
                            />
                        @endif
                        @foreach($notice->attachments ?? [] as $att)
                            <div class="block">
                                <x-file-preview
                                    :url="$att->url"
                                    :filename="$att->file_name"
                                    :type="$att->file_type"
                                />
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <span class="text-xs text-gray-300">—</span>
            @endif
        </td>
        <td class="px-5 py-3.5">
            <x-table-actions
                :edit="route('admin.notices.edit', $notice)"
                :destroy="route('admin.notices.destroy', $notice)"
                name="{{ $notice->title }}"
            />
        </td>
    </tr>
    @empty
    <tr><td colspan="6"><x-empty-state title="No notices published" message="Post your first institutional notice." action="{{ route('admin.notices.create') }}" actionLabel="Post Notice"/></td></tr>
    @endforelse
</x-data-table>
@endsection
