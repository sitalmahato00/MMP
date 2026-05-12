<?php

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Modules\User\Controllers\Auth\AuthController;
use App\Modules\Notification\Controllers\NotificationController;
use App\Modules\Public\Controllers\HomeController;
use App\Modules\Public\Controllers\MobilePreviewController;
use App\Modules\CMS\Controllers\PwaIconController;

// ─── PWA Icon Routes ────────────────────────
Route::get('/pwa-icon-{size}.png', [PwaIconController::class, 'icon'])
    ->where('size', '[0-9]+')
    ->name('pwa.icon');

// ─── Public Routes (SEO-optimized) ────────────────────────

Route::get('/brand-logo', function () {
    // PNG placeholder (1x1 red pixel, base64-encoded)
    $placeholder = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/w8AAn8B9nQn2wAAAABJRU5ErkJggg==');
    $fallbackPath = public_path('favicon.ico');

    if (! Schema::hasTable('site_settings')) {
        // If no database table exists, serve static favicon.ico if available
        if (file_exists($fallbackPath)) {
            return response()->file($fallbackPath, [
                'Content-Type' => 'image/x-icon',
                'Cache-Control' => 'public, max-age=3600',
            ]);
        }
        return response($placeholder, 200, ['Content-Type' => 'image/png']);
    }

    $siteLogoPath = Cache::remember('brand:site_logo', 600, function () {
        return SiteSetting::query()->where('key', 'site_logo')->value('value');
    });


    // Always use the latest logo uploaded from the admin branding page
    // If logo is missing, fallback to static favicon.ico

    if (! is_string($siteLogoPath) || trim($siteLogoPath) === '' || ! Storage::disk('public')->exists($siteLogoPath)) {
        // Fallback to static favicon.ico file if it exists
        if (file_exists($fallbackPath)) {
            return response()->file($fallbackPath, [
                'Content-Type' => 'image/x-icon',
                'Cache-Control' => 'public, max-age=3600',
            ]);
        }
        
        // If no favicon.ico exists either, return placeholder as last resort
        return response($placeholder, 200, ['Content-Type' => 'image/png']);
    }

    // Get file modification time for proper cache busting
    $lastModified = Storage::disk('public')->lastModified($siteLogoPath);
    return Storage::disk('public')
        ->response($siteLogoPath)
        ->setLastModified(new \DateTime('@' . $lastModified))
        ->setMaxAge(3600)
        ->setPublic();
})->name('public.brand-logo');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/notices', [HomeController::class, 'notices'])->name('public.notices');
Route::get('/notices/{slug}', [HomeController::class, 'noticeShow'])->name('public.notice.show');
Route::get('/news-events', [HomeController::class, 'newsEvents'])->name('public.news-events');
Route::get('/news-events/{slug}', [HomeController::class, 'newsEventShow'])->name('public.news-events.show');
Route::get('/departments', [HomeController::class, 'departments'])->name('public.departments');
Route::get('/departments/{slug}', [HomeController::class, 'departmentShow'])->name('public.department.show');
Route::get('/departments/{departmentSlug}/{programSlug}', [HomeController::class, 'programShow'])->name('public.program.show');
Route::get('/downloads', [HomeController::class, 'downloads'])->name('public.downloads');
Route::get('/downloads/{download}/file', [HomeController::class, 'downloadFile'])->name('public.downloads.file');
Route::get('/question-bank', [HomeController::class, 'questionBank'])->name('public.question-bank');
Route::get('/gallery', [HomeController::class, 'gallery'])->name('public.gallery');
Route::get('/result', [HomeController::class, 'result'])->middleware('throttle:result-check')->name('public.result');
Route::post('/result/submit', [HomeController::class, 'resultSubmit'])->middleware('throttle:result-check')->name('public.result.submit');
Route::get('/result/submit', function () {
    return redirect()->route('public.result');
})->name('public.result.submit.redirect');
Route::get('/people', [HomeController::class, 'people'])->name('public.people');
Route::get('/people/{type}/{id}', [HomeController::class, 'peopleProfile'])
    ->where('type', 'hod|teacher|staff')
    ->whereNumber('id')
    ->name('public.people.profile');
Route::get('/staff', [HomeController::class, 'staff'])->name('public.staff');
Route::get('/staff/{id}', [HomeController::class, 'staffProfile'])->whereNumber('id')->name('public.staff.profile');
Route::get('/leadership', [HomeController::class, 'leadership'])->name('public.leadership');
Route::get('/facilities', [HomeController::class, 'facilities'])->name('public.facilities');
Route::get('/contact', [HomeController::class, 'contact'])->name('public.contact');
Route::get('/alumni', [HomeController::class, 'alumniDirectory'])->name('public.alumni');
Route::get('/alumni/{id}', [HomeController::class, 'alumniProfile'])->name('public.alumni.profile')->whereNumber('id');
Route::get('/page/{slug}', [HomeController::class, 'page'])->name('public.page');
Route::get('/app-preview', MobilePreviewController::class)->name('public.app-preview');

// Application feature removed

// ─── Auth Routes ──────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/login/2fa', [AuthController::class, 'show2fa'])->name('login.2fa');
    Route::post('/login/2fa/verify', [AuthController::class, 'verify2fa'])->name('login.2fa.verify');
    Route::post('/login/2fa/resend', [AuthController::class, 'resend2fa'])->name('login.2fa.resend');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [AuthController::class, 'dashboardRedirect'])->name('dashboard');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});
