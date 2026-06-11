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
use Illuminate\Support\Facades\Cache;

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

        // API rate limiter (default for API routes)
        RateLimiter::for('api', function (Request $request): Limit {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

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

        // Share layout-level contact info + site identity to guest layout.
        // Uses cached SiteSettings when available, falls back to config/seo.php values
        // (which themselves read from APP_URL, SEO_SITE_NAME, CONTACT_EMAIL in .env).
        View::composer('layouts.guest', function ($view): void {
            $guestMeta = \Illuminate\Support\Facades\Cache::remember('guest_layout_meta', 600, function () {
                $settings = collect();

                if (Schema::hasTable('site_settings')) {
                    $settings = \App\Models\SiteSetting::all()->pluck('value', 'key');
                }

                $appUrl    = rtrim(config('app.url', ''), '/');
                $appDomain = parse_url($appUrl, PHP_URL_HOST) ?? $appUrl;

                return [
                    'site_name' => $settings->get('college_name')
                                ?? config('seo.site_name', 'Manmohan Memorial Polytechnic'),
                    'email'     => $settings->get('contact_email')
                                ?? config('seo.organization.email', ''),
                    'phone'     => $settings->get('contact_phone')
                                ?? implode(' / ', config('seo.organization.telephone', [])),
                    'address'   => $settings->get('contact_address')
                                ?? trim(implode(', ', array_filter([
                                    config('seo.organization.address.street',   ''),
                                    config('seo.organization.address.locality', ''),
                                    config('seo.organization.address.region',   ''),
                                    'Nepal',
                                ]))),
                    'app_url'    => $appUrl,
                    'app_domain' => $appDomain,
                ];
            });

            $view->with('guestMeta', $guestMeta);

            // SEO defaults — controllers override per-page via $seo variable
            if (! array_key_exists('seo', $view->getData())) {
                $view->with('seo', \App\Services\SeoService::build());
            }
        });
    }
}
