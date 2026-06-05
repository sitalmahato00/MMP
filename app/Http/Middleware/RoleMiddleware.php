<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle role-based access. 
     * Usage: middleware('role:student') or middleware('role:teacher,student')
     * Returns JSON for API requests, redirects for web routes
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Authentication required',
                ], 401);
            }
            return redirect()->route('login');
        }

        $userRole = $request->user()->role ?? null;

        // Check if user has required role
        if (!in_array($userRole, $roles)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden - Insufficient permissions',
                    'required_role' => implode(', ', $roles),
                    'user_role' => $userRole,
                ], 403);
            }
            abort(403, 'Unauthorized. You do not have the required role.');
        }

        return $next($request);
    }
}
