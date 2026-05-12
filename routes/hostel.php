<?php

/*
|--------------------------------------------------------------------------
| Hostel Routes
|--------------------------------------------------------------------------
|
| These routes will be activated when the Hostel module is implemented.
| Enable this file by registering it in bootstrap/app.php.
|
| Prerequisite:
|   - Role 'hostel_warden' seeded via Spatie Permission
|   - config/roles.php: 'hostel_warden' => [..., 'active' => true]
|   - app/Modules/Hostel/ controllers built out
|
*/

use Illuminate\Support\Facades\Route;

// TODO: Uncomment when the Hostel module is ready.

// Route::middleware(['auth', 'role:hostel_warden'])->prefix('hostel')->name('hostel.')->group(function () {
//
//     Route::get('/dashboard', fn () => view('hostel.dashboard'))->name('dashboard');
//
//     // Room management
//     Route::resource('rooms', \App\Modules\Hostel\Controllers\Admin\RoomController::class);
//
//     // Allocations
//     Route::resource('allocations', \App\Modules\Hostel\Controllers\Admin\AllocationController::class);
//
// });
