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

        if (!$departmentId && !$user->hasRole('principal')) {
            abort(403, 'You are not assigned to any department.');
        }

        $request->merge(['department_id' => $departmentId]);
        view()->share('userDepartmentId', $departmentId);

        return $next($request);
    }
}
