<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
        ]);

        // Apply audit middleware to all web routes
        $middleware->appendToGroup('web', \App\Http\Middleware\AuditActivity::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
