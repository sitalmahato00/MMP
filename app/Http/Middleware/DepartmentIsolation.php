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
            
            abort(403, 'You are not assigned to any department.');
        }

        $request->merge(['department_id' => $departmentId]);
        view()->share('userDepartmentId', $departmentId);

        return $next($request);
    }
}
