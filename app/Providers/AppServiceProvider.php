<?php

namespace App\Providers;

use App\Models\SiteSetting;
use App\Services\PublicDataService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
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
        View::composer(['layouts.guest', 'components.sidebar', 'auth.login'], function ($view): void {
            $siteLogoPath = null;
            $publicCourses = collect();

            if (Schema::hasTable('site_settings')) {
                $siteLogoPath = Cache::remember('brand:site_logo', 600, function () {
                    return SiteSetting::query()->where('key', 'site_logo')->value('value');
                });
            }

            if (Schema::hasTable('departments')) {
                $publicCourses = app(PublicDataService::class)->getNavigationCourses();
            }

            $view->with('siteLogoPath', $siteLogoPath);
            $view->with('publicCourses', $publicCourses);
        });
    }
}
