<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [\App\Http\Controllers\Alumni\DashboardController::class, 'index'])->name('dashboard');

// Profile
Route::get('/profile', [\App\Http\Controllers\Alumni\ProfileController::class, 'index'])->name('profile.index');
Route::get('/profile/edit', [\App\Http\Controllers\Alumni\ProfileController::class, 'index'])->name('profile.edit'); // Alias for edit
Route::put('/profile', [\App\Http\Controllers\Alumni\ProfileController::class, 'update'])->name('profile.update');

// Career
Route::get('/career', [\App\Http\Controllers\Alumni\CareerController::class, 'index'])->name('career.index');
Route::post('/career/employment', [\App\Http\Controllers\Alumni\CareerController::class, 'storeEmployment'])->name('career.store-employment');
Route::delete('/career/employment/{employment}', [\App\Http\Controllers\Alumni\CareerController::class, 'destroyEmployment'])->name('career.destroy-employment');

// Projects
Route::get('/projects', [\App\Http\Controllers\Alumni\ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{type}/edit', [\App\Http\Controllers\Alumni\ProjectController::class, 'edit'])->name('projects.edit');
Route::put('/projects/{type}', [\App\Http\Controllers\Alumni\ProjectController::class, 'update'])->name('projects.update');

// Achievements
Route::get('/achievements', [\App\Http\Controllers\Alumni\AchievementController::class, 'index'])->name('achievements.index');
Route::post('/achievements', [\App\Http\Controllers\Alumni\AchievementController::class, 'store'])->name('achievements.store');
Route::delete('/achievements/{achievement}', [\App\Http\Controllers\Alumni\AchievementController::class, 'destroy'])->name('achievements.destroy');

// Notices
Route::get('/notices', [\App\Http\Controllers\Alumni\NoticeController::class, 'index'])->name('notices.index');

// Settings & Account Management
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Alumni\SettingsController::class, 'index'])->name('index');
    Route::patch('/profile', [\App\Http\Controllers\Alumni\SettingsController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/password', [\App\Http\Controllers\Alumni\SettingsController::class, 'updatePassword'])->name('password.update');
    Route::patch('/preferences', [\App\Http\Controllers\Alumni\SettingsController::class, 'updatePreferences'])->name('preferences.update');
    Route::patch('/notifications', [\App\Http\Controllers\Alumni\SettingsController::class, 'updateNotifications'])->name('notifications.update');
    Route::post('/logout-all', [\App\Http\Controllers\Alumni\SettingsController::class, 'logoutAllDevices'])->name('logout-all');
    Route::post('/reset-dashboard', [\App\Http\Controllers\Alumni\SettingsController::class, 'resetDashboard'])->name('reset-dashboard');
    Route::post('/clear-preferences', [\App\Http\Controllers\Alumni\SettingsController::class, 'clearPreferences'])->name('clear-preferences');
});
