<?php

/*
|--------------------------------------------------------------------------
| Inventory Routes
|--------------------------------------------------------------------------
|
| These routes will be activated when the Inventory module is implemented.
| Enable this file by registering it in bootstrap/app.php.
|
| Prerequisite:
|   - Role 'inventory_manager' seeded via Spatie Permission
|   - config/roles.php: 'inventory_manager' => [..., 'active' => true]
|   - app/Modules/Inventory/ controllers built out
|
*/

use Illuminate\Support\Facades\Route;

// TODO: Uncomment when the Inventory module is ready.

// Route::middleware(['auth', 'role:inventory_manager'])->prefix('inventory')->name('inventory.')->group(function () {
//
//     Route::get('/dashboard', fn () => view('inventory.dashboard'))->name('dashboard');
//
//     // Items
//     Route::resource('items', \App\Modules\Inventory\Controllers\Admin\ItemController::class);
//
//     // Categories
//     Route::resource('categories', \App\Modules\Inventory\Controllers\Admin\CategoryController::class);
//
//     // Stock adjustments
//     Route::post('items/{item}/adjust', \App\Modules\Inventory\Controllers\Admin\StockController::class . '@adjust')->name('items.adjust');
//
// });
