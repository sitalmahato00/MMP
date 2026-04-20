<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DepartmentIsolation
{
    /**
     * Enforce department-based data isolation for HOD and Teacher roles.
     * Attaches the user's department_id to the request for downstream use.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $departmentId = null;

        // Get department from teacher or HOD profile
        if ($user->teacher) {
            $departmentId = $user->teacher->department_id;
        } elseif ($user->hasRole('hod')) {
            $dept = \App\Models\Department::where('hod_id', $user->id)->first();
            $departmentId = $dept?->id;
            
            // Debug logging
            \Log::info('DepartmentIsolation Middleware - HOD Check', [
                'user_id' => $user->id,
                'has_hod_role' => $user->hasRole('hod'),
                'department_query_result' => $dept ? $dept->toArray() : null,
                'department_id' => $departmentId,
                'route' => $request->route()->getName(),
            ]);
        }

        // Allow HODs to access dashboard even without department (they'll see a helpful message)
        // For other routes, enforce department requirement
        if (!$departmentId && !$user->hasRole('principal')) {
            // Allow HOD to access their dashboard to see the "no department" message
            if ($user->hasRole('hod') && $request->routeIs('hod.dashboard')) {
                $request->merge(['department_id' => null]);
                view()->share('userDepartmentId', null);
                return $next($request);
            }
            
            // TEMPORARY: Allow HOD students access for debugging
            if ($user->hasRole('hod') && $request->routeIs('hod.students.*')) {
                \Log::warning('TEMPORARY: Allowing HOD students access without department check', [
                    'user_id' => $user->id,
                    'route' => $request->route()->getName(),
                ]);
                $request->merge(['department_id' => 2]); // Use the known department ID
                view()->share('userDepartmentId', 2);
                return $next($request);
            }
            
            // TEMPORARY: Allow HOD teachers access for debugging
            if ($user->hasRole('hod') && $request->routeIs('hod.teachers.*')) {
                \Log::warning('TEMPORARY: Allowing HOD teachers access without department check', [
                    'user_id' => $user->id,
                    'route' => $request->route()->getName(),
                ]);
                $request->merge(['department_id' => 2]); // Use the known department ID
                view()->share('userDepartmentId', 2);
                return $next($request);
            }
            
            // For other routes, redirect HOD back to dashboard with a message
            if ($user->hasRole('hod')) {
                \Log::warning('HOD redirected - no department', [
                    'user_id' => $user->id,
                    'route' => $request->route()->getName(),
                ]);
                return redirect()->route('hod.dashboard')
                    ->with('error', 'You cannot access this page until a department is assigned to you. Please contact the Principal.');
            }
            
            abort(403, 'You are not assigned to any department.');
        }

        $request->merge(['department_id' => $departmentId]);
        view()->share('userDepartmentId', $departmentId);

        return $next($request);
    }
}
