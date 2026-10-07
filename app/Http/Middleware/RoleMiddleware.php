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

        // Check if user has any of the required roles (with principal/admin and alumni/alumnus aliases)
        $hasRole = false;
        foreach ($roles as $required) {
            $requiredRoles = [$required];
            if ($required === 'alumni') {
                $requiredRoles[] = 'alumnus';
            } elseif ($required === 'alumnus') {
                $requiredRoles[] = 'alumni';
            } elseif ($required === 'admin') {
                $requiredRoles[] = 'principal';
            } elseif ($required === 'principal') {
                $requiredRoles[] = 'admin';
            }

            foreach ($requiredRoles as $roleToCheck) {
                if (in_array($roleToCheck, $userRoles) || (method_exists($user, 'hasRole') && $user->hasRole($roleToCheck))) {
                    $hasRole = true;
                    break 2;
                }
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

            // Redirect user strictly to their own assigned portal
            $ownDashboard = match (true) {
                in_array('principal', $userRoles) || in_array('admin', $userRoles) => route('admin.dashboard'),
                in_array('hod', $userRoles) => route('hod.dashboard'),
                in_array('teacher', $userRoles) => route('teacher.dashboard'),
                in_array('student', $userRoles) => route('student.dashboard'),
                in_array('parent', $userRoles) => route('parent.dashboard'),
                in_array('alumni', $userRoles) || in_array('alumnus', $userRoles) => route('alumni.dashboard'),
                default => route('home'),
            };

            return redirect($ownDashboard)->with('error', 'Access restricted: You can only access your own portal.');
        }

        return $next($request);
    }
}
