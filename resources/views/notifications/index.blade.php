@extends('layouts.app')
@section('title', 'Notifications')

@section('content')
<div class="mx-auto max-w-5xl">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">Notification Center</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">Inbox</h1>
            <p class="mt-2 text-sm text-slate-500">Review notices, exam alerts, result updates, and official feed notifications in one place.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('notifications.index') }}"
               class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition {{ $show === 'all' ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:text-slate-900' }}">
                All
                <span class="ml-2 rounded-full bg-white/80 px-2 py-0.5 text-xs">{{ $totalCount }}</span>
            </a>
            <a href="{{ route('notifications.index', ['show' => 'unread']) }}"
               class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition {{ $show === 'unread' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:text-slate-900' }}">
                Unread
                <span class="ml-2 rounded-full bg-white/80 px-2 py-0.5 text-xs">{{ $unreadCount }}</span>
            </a>
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:text-slate-900">
                    Mark All Read
                </button>
            </form>
        </div>
    </div>

    <div class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm">
        @if($notifications->isEmpty())
            <div class="px-8 py-16 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <h2 class="mt-5 text-lg font-semibold text-slate-900">No notifications yet</h2>
                <p class="mt-2 text-sm text-slate-500">When notices, results, or official updates are sent to your account, they’ll appear here.</p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($notifications as $notification)
                    @php
                        $title = data_get($notification->data, 'title', 'Notification');
                        $message = data_get($notification->data, 'message', '');
                        $scope = data_get($notification->data, 'scope_label');
                        $isUnread = is_null($notification->read_at);
                    @endphp
                    <div class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-start sm:justify-between {{ $isUnread ? 'bg-blue-50/40' : 'bg-white' }}">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start gap-3">
                                <span class="mt-1.5 h-2.5 w-2.5 flex-shrink-0 rounded-full {{ $isUnread ? 'bg-blue-500' : 'bg-slate-300' }}"></span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 class="text-sm font-semibold text-slate-900">{{ $title }}</h2>
                                        @if($scope)
                                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-medium text-slate-500">{{ $scope }}</span>
                                        @endif
                                        @if($isUnread)
                                            <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-[11px] font-semibold text-blue-700">New</span>
                                        @endif
                                    </div>
                                    @if($message)
                                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $message }}</p>
                                    @endif
                                    <p class="mt-3 text-xs font-medium text-slate-400">{{ $notification->created_at?->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 sm:pl-6">
                            <a href="{{ route('notifications.open', $notification) }}"
                               class="inline-flex items-center rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                                {{ data_get($notification->data, 'action_label', 'Open') }}
                            </a>
                            <form method="POST" action="{{ route('notifications.destroy', $notification) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-500 transition hover:border-rose-200 hover:text-rose-600">
                                    Remove
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if($notifications->hasPages())
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
