<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [\App\Modules\Alumni\Controllers\Web\DashboardController::class, 'index'])->name('dashboard');

// Profile
Route::get('/profile', [\App\Modules\Alumni\Controllers\Web\ProfileController::class, 'index'])->name('profile.index');
Route::get('/profile/edit', [\App\Modules\Alumni\Controllers\Web\ProfileController::class, 'index'])->name('profile.edit'); // Alias for edit
Route::put('/profile', [\App\Modules\Alumni\Controllers\Web\ProfileController::class, 'update'])->name('profile.update');

// Career
Route::get('/career', [\App\Modules\Alumni\Controllers\Web\CareerController::class, 'index'])->name('career.index');
Route::post('/career/employment', [\App\Modules\Alumni\Controllers\Web\CareerController::class, 'storeEmployment'])->name('career.store-employment');
Route::delete('/career/employment/{employment}', [\App\Modules\Alumni\Controllers\Web\CareerController::class, 'destroyEmployment'])->name('career.destroy-employment');

// Projects
Route::get('/projects', [\App\Modules\Alumni\Controllers\Web\ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{type}/edit', [\App\Modules\Alumni\Controllers\Web\ProjectController::class, 'edit'])->name('projects.edit');
Route::put('/projects/{type}', [\App\Modules\Alumni\Controllers\Web\ProjectController::class, 'update'])->name('projects.update');

// Achievements
Route::get('/achievements', [\App\Modules\Alumni\Controllers\Web\AchievementController::class, 'index'])->name('achievements.index');
Route::post('/achievements', [\App\Modules\Alumni\Controllers\Web\AchievementController::class, 'store'])->name('achievements.store');
Route::delete('/achievements/{achievement}', [\App\Modules\Alumni\Controllers\Web\AchievementController::class, 'destroy'])->name('achievements.destroy');

// Notices
Route::get('/notices', [\App\Modules\Alumni\Controllers\Web\NoticeController::class, 'index'])->name('notices.index');

// Settings & Account Management
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [\App\Modules\Alumni\Controllers\Web\SettingsController::class, 'index'])->name('index');
    Route::patch('/profile', [\App\Modules\Alumni\Controllers\Web\SettingsController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/password', [\App\Modules\Alumni\Controllers\Web\SettingsController::class, 'updatePassword'])->name('password.update');
    Route::patch('/two-factor', [\App\Modules\Alumni\Controllers\Web\SettingsController::class, 'updateTwoFactor'])->name('two-factor.update');
    Route::patch('/preferences', [\App\Modules\Alumni\Controllers\Web\SettingsController::class, 'updatePreferences'])->name('preferences.update');
    Route::patch('/notifications', [\App\Modules\Alumni\Controllers\Web\SettingsController::class, 'updateNotifications'])->name('notifications.update');
    Route::post('/logout-all', [\App\Modules\Alumni\Controllers\Web\SettingsController::class, 'logoutAllDevices'])->name('logout-all');
    Route::post('/reset-dashboard', [\App\Modules\Alumni\Controllers\Web\SettingsController::class, 'resetDashboard'])->name('reset-dashboard');
    Route::post('/clear-preferences', [\App\Modules\Alumni\Controllers\Web\SettingsController::class, 'clearPreferences'])->name('clear-preferences');
});
