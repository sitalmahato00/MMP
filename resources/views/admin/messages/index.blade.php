@extends('layouts.app')
@section('title', 'Messages')

@section('content')
<x-page-header title="Messages" subtitle="Internal communications inbox and message activity.">
    <x-slot name="actions">
        <x-btn href="{{ route('admin.dashboard') }}" variant="secondary">Back to Dashboard</x-btn>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <x-card class="border-l-4 border-l-[#8B0000]">
        <p class="text-xs font-bold uppercase tracking-widest text-gray-500">Total Messages</p>
        <p class="mt-2 text-2xl font-black text-gray-900">{{ number_format($stats['total']) }}</p>
    </x-card>
    <x-card class="border-l-4 border-l-amber-500">
        <p class="text-xs font-bold uppercase tracking-widest text-gray-500">Unread</p>
        <p class="mt-2 text-2xl font-black text-gray-900">{{ number_format($stats['unread']) }}</p>
    </x-card>
    <x-card class="border-l-4 border-l-blue-500">
        <p class="text-xs font-bold uppercase tracking-widest text-gray-500">Sent by Me</p>
        <p class="mt-2 text-2xl font-black text-gray-900">{{ number_format($stats['sent_by_me']) }}</p>
    </x-card>
    <x-card class="border-l-4 border-l-emerald-500">
        <p class="text-xs font-bold uppercase tracking-widest text-gray-500">Received by Me</p>
        <p class="mt-2 text-2xl font-black text-gray-900">{{ number_format($stats['received_by_me']) }}</p>
    </x-card>
</div>

<x-search-filter>
    <x-input name="search" value="{{ request('search') }}" placeholder="Search subject, message, sender or receiver…" class="flex-1 min-w-[240px]"/>
</x-search-filter>

<x-data-table :paginator="$messages">
    <x-slot name="head">
        <tr>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Subject</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">From</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">To</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Sent</th>
        </tr>
    </x-slot>

    @forelse($messages as $message)
        <tr class="hover:bg-gray-50/70 transition-colors align-top">
            <td class="px-5 py-4 max-w-xl">
                <p class="font-semibold text-gray-900">{{ $message->subject ?: 'No subject' }}</p>
                <details class="mt-2 text-sm text-gray-500">
                    <summary class="cursor-pointer list-none text-[#8B0000] font-semibold">View message</summary>
                    <p class="mt-2 whitespace-pre-line leading-relaxed text-gray-600">{{ $message->message }}</p>
                </details>
            </td>
            <td class="px-5 py-4 text-sm text-gray-700">{{ $message->sender?->name ?? 'Unknown sender' }}</td>
            <td class="px-5 py-4 text-sm text-gray-700">{{ $message->receiver?->name ?? 'Unknown receiver' }}</td>
            <td class="px-5 py-4">
                @if($message->is_read)
                    <x-badge color="green">Read</x-badge>
                @else
                    <x-badge color="amber">Unread</x-badge>
                @endif
            </td>
            <td class="px-5 py-4 text-xs text-gray-500 whitespace-nowrap">{{ bsDate($message->created_at, 'Y, F d h:i A') }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="px-5 py-12 text-center text-gray-400">
                <p class="font-medium">No messages found</p>
                <p class="mt-1 text-sm">Messages will appear here when users start sending communications.</p>
            </td>
        </tr>
    @endforelse
</x-data-table>
@endsection