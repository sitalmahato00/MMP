<?php

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Public\HomeController;

// ─── Public Routes (SEO-optimized) ────────────────────────
Route::get('/brand-logo', function () {
    $fallbackPath = public_path('favicon.ico');

    if (! Schema::hasTable('site_settings')) {
        return response()->file($fallbackPath);
    }

    $siteLogoPath = Cache::remember('brand:site_logo', 600, function () {
        return SiteSetting::query()->where('key', 'site_logo')->value('value');
    });

    if (! is_string($siteLogoPath) || trim($siteLogoPath) === '' || ! Storage::disk('public')->exists($siteLogoPath)) {
        return response()->file($fallbackPath);
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
Route::get('/departments', [HomeController::class, 'departments'])->name('public.departments');
Route::get('/departments/{slug}', [HomeController::class, 'departmentShow'])->name('public.department.show');
Route::get('/departments/{departmentSlug}/{programSlug}', [HomeController::class, 'programShow'])->name('public.program.show');
Route::get('/downloads', [HomeController::class, 'downloads'])->name('public.downloads');
Route::get('/downloads/{download}/file', [HomeController::class, 'downloadFile'])->name('public.downloads.file');
Route::get('/question-bank', [HomeController::class, 'questionBank'])->name('public.question-bank');
Route::get('/gallery', [HomeController::class, 'gallery'])->name('public.gallery');
Route::get('/result', [HomeController::class, 'result'])->middleware('throttle:result-check')->name('public.result');
Route::post('/result/submit', [HomeController::class, 'resultSubmit'])->middleware('throttle:result-check')->name('public.result.submit');
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

// ─── Apply Now (Public Application Form) ───────────────────
Route::get('/apply', [HomeController::class, 'apply'])->name('public.apply');
Route::post('/apply', [HomeController::class, 'applyStore'])->middleware('throttle:apply')->name('public.apply.store');

// ─── Auth Routes ──────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [AuthController::class, 'dashboardRedirect'])->name('dashboard');
});
