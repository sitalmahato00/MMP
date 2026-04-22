@extends('layouts.app')
@section('title', 'Resources')

@section('content')
<x-page-header title="Department Gallery Files & Images" subtitle="Manage department gallery files and images.">
    <x-slot name="actions">
        <x-btn href="{{ route('hod.downloads.create') }}">Upload Files</x-btn>
    </x-slot>
</x-page-header>

{{-- Statistics Cards --}}
<x-download-stats :downloads="$downloads" />

{{-- Filter Form --}}
<x-download-filters :categories="['Forms & Downloads', 'Syllabus', 'Notes', 'Question Bank', 'Reports & Publications']" />

{{-- View Toggle & Results Info --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div class="text-sm text-gray-600 font-medium">
        Showing {{ $downloads->count() }} of {{ $downloads->total() }} files
    </div>
    <div class="flex gap-1 bg-gray-100 rounded-lg p-1">
        <button 
            type="button" 
            onclick="toggleView('table')"
            id="view-table-btn"
            class="px-4 py-2 rounded-md font-medium transition-colors text-gray-700 hover:text-gray-900"
        >
            <svg class="w-4 h-4 inline mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            Table
        </button>
        <button 
            type="button" 
            onclick="toggleView('cards')"
            id="view-cards-btn"
            class="px-4 py-2 rounded-md font-medium transition-colors text-gray-700 hover:text-gray-900 bg-gray-900 text-white"
        >
            <svg class="w-4 h-4 inline mr-1.5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M3 3h8v8H3V3zm10 0h8v8h-8V3zM3 13h8v8H3v-8zm10 0h8v8h-8v-8z"/>
            </svg>
            Cards
        </button>
    </div>
</div>

{{-- Cards View --}}
<div id="view-cards" class="hidden">
    <x-download-cards-grid :downloads="$downloads" />
</div>

{{-- Table View --}}
<div id="view-table">
    <x-download-table :downloads="$downloads" />
</div>

<script>
    function toggleView(view) {
        const cardsView = document.getElementById('view-cards');
        const tableView = document.getElementById('view-table');
        const cardsBtn = document.getElementById('view-cards-btn');
        const tableBtn = document.getElementById('view-table-btn');

        if (view === 'cards') {
            cardsView.classList.remove('hidden');
            tableView.classList.add('hidden');
            cardsBtn.classList.add('bg-gray-900', 'text-white');
            cardsBtn.classList.remove('text-gray-700', 'hover:text-gray-900');
            tableBtn.classList.remove('bg-gray-900', 'text-white');
            tableBtn.classList.add('text-gray-700', 'hover:text-gray-900');
            localStorage.setItem('download_view', 'cards');
        } else {
            cardsView.classList.add('hidden');
            tableView.classList.remove('hidden');
            tableBtn.classList.add('bg-gray-900', 'text-white');
            tableBtn.classList.remove('text-gray-700', 'hover:text-gray-900');
            cardsBtn.classList.remove('bg-gray-900', 'text-white');
            cardsBtn.classList.add('text-gray-700', 'hover:text-gray-900');
            localStorage.setItem('download_view', 'table');
        }
    }

    // Load saved view preference
    document.addEventListener('DOMContentLoaded', function() {
        const savedView = localStorage.getItem('download_view') || 'cards';
        toggleView(savedView);
    });
</script>

@endsection
