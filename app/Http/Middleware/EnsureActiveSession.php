<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AcademicSession;

class EnsureActiveSession
{
    /**
     * Ensure there is an active academic session before accessing academic features.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $session = AcademicSession::current();

        if (!$session) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'No active academic session.'], 403);
            }
            return redirect()->back()->with('error', 'No active academic session. Please contact the administrator.');
        }

        // Share active session with all views
        view()->share('activeSession', $session);
        $request->merge(['active_session' => $session]);

        return $next($request);
    }
}
