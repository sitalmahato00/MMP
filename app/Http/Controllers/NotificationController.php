<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $show = $request->string('show')->toString() === 'unread' ? 'unread' : 'all';

        if (! Schema::hasTable('notifications')) {
            $notifications = new LengthAwarePaginator(
                collect(),
                0,
                20,
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            return view('notifications.index', [
                'notifications' => $notifications,
                'show' => $show,
                'totalCount' => 0,
                'unreadCount' => 0,
            ]);
        }

        $query = $request->user()->notifications()->latest();

        if ($show === 'unread') {
            $query->whereNull('read_at');
        }

        return view('notifications.index', [
            'notifications' => $query->paginate(20)->withQueryString(),
            'show' => $show,
            'totalCount' => $request->user()->notifications()->count(),
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        if (Schema::hasTable('notifications')) {
            $request->user()->unreadNotifications()->update(['read_at' => now()]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    public function open(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        $notification = $request->user()->notifications()->whereKey($notification->id)->firstOrFail();

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        $targetUrl = trim((string) data_get($notification->data, 'action_url', route('notifications.index')));

        if ($targetUrl === '') {
            return redirect()->route('notifications.index');
        }

        if (Str::startsWith($targetUrl, ['http://', 'https://'])) {
            return redirect()->away($targetUrl);
        }

        return redirect($targetUrl);
    }

    public function destroy(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        $request->user()->notifications()->whereKey($notification->id)->firstOrFail()->delete();

        return back()->with('success', 'Notification removed.');
    }
}
