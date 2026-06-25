<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Schema;

class NotificationApiController extends Controller
{
    /**
     * List notifications for the authenticated user.
     *
     * Query params:
     *   filter = all|unread  (default: all)
     *   per_page = 15        (default: 15, max: 50)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        if (! Schema::hasTable('notifications')) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'notifications' => [],
                    'unread_count'  => 0,
                    'total'         => 0,
                ],
            ]);
        }

        $filter  = $request->query('filter', 'all');
        $perPage = min((int) $request->query('per_page', 15), 50);

        $query = $request->user()->notifications()->latest();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        }

        $paginated    = $query->paginate($perPage);
        $unreadCount  = $request->user()->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'notifications' => $paginated->map(fn ($n) => $this->formatNotification($n)),
                'unread_count'  => $unreadCount,
                'pagination'    => [
                    'total'        => $paginated->total(),
                    'per_page'     => $paginated->perPage(),
                    'current_page' => $paginated->currentPage(),
                    'last_page'    => $paginated->lastPage(),
                    'has_more'     => $paginated->hasMorePages(),
                ],
            ],
        ]);
    }

    /**
     * Get unread notification count only (lightweight poll endpoint).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = Schema::hasTable('notifications')
            ? $request->user()->unreadNotifications()->count()
            : 0;

        return response()->json([
            'success' => true,
            'data'    => [
                'unread_count' => $count,
            ],
        ]);
    }

    /**
     * Mark a single notification as read.
     *
     * @param Request             $request
     * @param DatabaseNotification $notification
     * @return JsonResponse
     */
    public function markRead(Request $request, string $notification): JsonResponse
    {
        // Ensure the notification belongs to the authenticated user
        $notification = $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data'    => [
                'notification' => $this->formatNotification($notification->fresh()),
            ],
        ]);
    }

    /**
     * Mark all notifications as read.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function markAllRead(Request $request): JsonResponse
    {
        if (Schema::hasTable('notifications')) {
            $request->user()->unreadNotifications()->update(['read_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
            'data'    => [
                'unread_count' => 0,
            ],
        ]);
    }

    /**
     * Delete a single notification.
     *
     * @param Request             $request
     * @param DatabaseNotification $notification
     * @return JsonResponse
     */
    public function destroy(Request $request, string $notification): JsonResponse
    {
        $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail()
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }

    /**
     * Format a single notification for the API response.
     *
     * @param DatabaseNotification $notification
     * @return array
     */
    private function formatNotification($notification): array
    {
        $data = $notification->data;

        return [
            'id'         => $notification->id,
            'type'       => class_basename($notification->type),
            'title'      => data_get($data, 'title', 'Notification'),
            'body'       => data_get($data, 'body', data_get($data, 'message', '')),
            'action_url' => data_get($data, 'action_url'),
            'icon'       => data_get($data, 'icon', 'bell'),
            'data'       => $data,
            'is_read'    => ! is_null($notification->read_at),
            'read_at'    => $notification->read_at?->toISOString(),
            'created_at' => $notification->created_at->toISOString(),
        ];
    }
}
