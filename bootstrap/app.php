<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Role-based route files
            Route::middleware(['web', 'auth', 'role:principal'])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));

            Route::middleware(['web', 'auth', 'role:hod', 'department.isolation'])
                ->prefix('hod')
                ->name('hod.')
                ->group(base_path('routes/hod.php'));

            Route::middleware(['web', 'auth', 'role:teacher', 'department.isolation'])
                ->prefix('teacher')
                ->name('teacher.')
                ->group(base_path('routes/teacher.php'));

            Route::middleware(['web', 'auth', 'role:student'])
                ->prefix('student')
                ->name('student.')
                ->group(base_path('routes/student.php'));

            Route::middleware(['web', 'auth', 'role:parent'])
                ->prefix('parent')
                ->name('parent.')
                ->group(base_path('routes/parent.php'));

            Route::middleware(['web', 'auth', 'role:alumni'])
                ->prefix('alumni')
                ->name('alumni.')
                ->group(base_path('routes/alumni.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'active.session' => \App\Http\Middleware\EnsureActiveSession::class,
            'department.isolation' => \App\Http\Middleware\DepartmentIsolation::class,
            'audit' => \App\Http\Middleware\AuditActivity::class,
            'prevent.back' => \App\Http\Middleware\PreventBackHistory::class,
            'force.json' => \App\Http\Middleware\ForceJsonResponse::class,
        ]);

        // Apply audit middleware to all web routes
        $middleware->appendToGroup('web', \App\Http\Middleware\AuditActivity::class);
        
        // Prevent browser back button cache for authenticated routes
        $middleware->appendToGroup('web', \App\Http\Middleware\PreventBackHistory::class);
        
        // Force JSON responses for all API routes
        $middleware->appendToGroup('api', \App\Http\Middleware\ForceJsonResponse::class);
        
        // Configure rate limiters
        $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle API exceptions with JSON responses
        $exceptions->render(function (\Throwable $e, Request $request) {
            // Only handle API routes
            if ($request->is('api/*')) {
                // Validation exceptions
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $e->errors(),
                    ], 422);
                }
                
                // Authentication exceptions
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthenticated',
                    ], 401);
                }
                
                // Authorization exceptions
                if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized',
                    ], 403);
                }
                
                // Throttle exceptions (rate limiting)
                if ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Too many attempts. Please try again later.',
                    ], 429);
                }
                
                // Model not found exceptions
                if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Resource not found',
                    ], 404);
                }
                
                // Generic server errors
                \Illuminate\Support\Facades\Log::error('API Error', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => app()->environment('production') 
                        ? 'An error occurred. Please try again later.' 
                        : $e->getMessage(),
                ], 500);
            }
            
            // Let Laravel handle non-API exceptions normally
            return null;
        });
    })->create();
