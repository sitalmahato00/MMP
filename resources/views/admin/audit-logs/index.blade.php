@extends('layouts.app')
@section('title', 'Audit Logs')

@section('content')
<x-page-header title="Audit Logs" subtitle="Immutable trail of all critical system actions."/>

<x-search-filter>
    <x-input name="search" value="{{ request('search') }}" placeholder="Search action or user…" class="flex-1 min-w-[200px]"/>
    <x-select name="action">
        <option value="">All Actions</option>
        @foreach(['login','logout','created','updated','deleted'] as $action)
            <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ ucfirst($action) }}</option>
        @endforeach
    </x-select>
</x-search-filter>

<x-data-table :paginator="$logs">
    <x-slot name="head">
        <tr>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Timestamp</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">User</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Resource</th>
            <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">IP Address</th>
        </tr>
    </x-slot>

    @forelse($logs as $log)
    @php
        $actionColors = ['login'=>'green','logout'=>'gray','created'=>'blue','updated'=>'yellow','deleted'=>'red'];
        $color = $actionColors[$log->action] ?? 'gray';
    @endphp
    <tr class="hover:bg-gray-50/70 transition-colors">
        <td class="px-5 py-3 text-xs text-gray-400 whitespace-nowrap font-mono">
            {{ bsDate($log->created_at, 'd F Y') }} {{ $log->created_at->format('H:i:s') }}
        </td>
        <td class="px-5 py-3">
            @if($log->user)
                <div class="flex items-center gap-2">
                    <x-avatar :src="$log->user->avatar_url" :name="$log->user->name" size="xs"/>
                    <span class="text-sm font-medium text-gray-700">{{ $log->user->name }}</span>
                </div>
            @else
                <span class="text-xs text-gray-400 italic">System</span>
            @endif
        </td>
        <td class="px-5 py-3"><x-badge :color="$color">{{ $log->action }}</x-badge></td>
        <td class="px-5 py-3 text-xs text-gray-500 font-mono">
            {{ $log->model_type ? class_basename($log->model_type).' #'.$log->model_id : '—' }}
        </td>
        <td class="px-5 py-3 text-xs text-gray-400 font-mono">{{ $log->ip_address ?? '—' }}</td>
    </tr>
    @empty
    <tr><td colspan="5"><x-empty-state title="No audit logs found" message="System activity will appear here."/></td></tr>
    @endforelse
</x-data-table>
@endsection
