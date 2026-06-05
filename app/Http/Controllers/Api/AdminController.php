<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    /**
     * Admin Dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'total_users' => User::count(),
                    'total_sessions' => User::where('last_login_at', '!=', null)->count(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Users
     */
    public function users(Request $request): JsonResponse
    {
        try {
            $users = User::paginate(20);

            return response()->json([
                'success' => true,
                'data' => $users->map(fn($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'role' => $u->role,
                    'is_active' => $u->is_active,
                ])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Audit Logs
     */
    public function auditLogs(Request $request): JsonResponse
    {
        try {
            $logs = AuditLog::orderBy('created_at', 'desc')->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $logs->map(fn($log) => [
                    'id' => $log->id,
                    'user' => $log->user?->name,
                    'action' => $log->action,
                    'description' => $log->description,
                    'timestamp' => $log->created_at,
                ])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch audit logs: ' . $e->getMessage(),
            ], 500);
        }
    }
}
