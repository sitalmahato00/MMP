<?php

/*
|--------------------------------------------------------------------------
| Accountant Routes
|--------------------------------------------------------------------------
|
| These routes will be activated when the Accounts/Finance module is
| implemented. Enable this file by registering it in bootstrap/app.php.
|
| Prerequisite:
|   - Role 'accountant' seeded via Spatie Permission
|   - config/roles.php:  'accountant' => [..., 'active' => true]
|   - app/Modules/Accounts/ controllers built out
|
*/

use Illuminate\Support\Facades\Route;

// TODO: Uncomment when the Accounts module is ready.

// Route::middleware(['auth', 'role:accountant'])->prefix('accounts')->name('accounts.')->group(function () {
//
//     Route::get('/dashboard', fn () => view('accounts.dashboard'))->name('dashboard');
//
//     // Fee collection
//     Route::resource('fees', \App\Modules\Accounts\Controllers\Admin\FeeController::class);
//
//     // Reports
//     Route::get('/reports/collection', \App\Modules\Accounts\Controllers\Admin\ReportController::class . '@collection')->name('reports.collection');
//
// });
