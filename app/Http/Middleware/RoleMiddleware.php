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

        // Determine user roles via Spatie trait if available
        $user = $request->user();
        $userRoles = [];
        if (method_exists($user, 'getRoleNames')) {
            $userRoles = $user->getRoleNames()->toArray();
        } elseif (property_exists($user, 'role')) {
            $userRoles = [$user->role];
        }

        // Check if user has any of the required roles
        $hasRole = false;
        foreach ($roles as $required) {
            if (in_array($required, $userRoles) || (method_exists($user, 'hasRole') && $user->hasRole($required))) {
                $hasRole = true;
                break;
            }
        }

        if (! $hasRole) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden - Insufficient permissions',
                    'required_role' => implode(', ', $roles),
                    'user_roles' => $userRoles,
                ], 403);
            }
            abort(403, 'Unauthorized. You do not have the required role.');
        }

        return $next($request);
    }
}
