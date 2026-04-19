<?php

namespace App\Providers;

use App\Services\PublicDataService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.custom');

        RateLimiter::for('login', function (Request $request): Limit {
            $email = strtolower(trim((string) $request->input('email')));

            return Limit::perMinute(5)->by($email !== '' ? $email.'|'.$request->ip() : $request->ip());
        });

        RateLimiter::for('apply', function (Request $request): Limit {
            $email = strtolower(trim((string) $request->input('email')));

            return Limit::perHour(10)->by($email !== '' ? $email.'|'.$request->ip() : $request->ip());
        });

        RateLimiter::for('result-check', function (Request $request): Limit {
            return Limit::perMinute(30)->by($request->ip());
        });

        RateLimiter::for('public-api', function (Request $request): Limit {
            return Limit::perMinute(120)->by($request->ip());
        });

        View::composer(['layouts.guest', 'components.sidebar', 'auth.login'], function ($view): void {
            $publicCourses = collect();

            if (Schema::hasTable('departments')) {
                $publicCourses = app(PublicDataService::class)->getNavigationCourses();
            }

            $view->with('publicCourses', $publicCourses);
        });
    }
}
