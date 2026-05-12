<?php

/*
|--------------------------------------------------------------------------
| Librarian Routes
|--------------------------------------------------------------------------
|
| These routes will be activated when the Library module is implemented.
| Enable this file by registering it in bootstrap/app.php.
|
| Prerequisite:
|   - Role 'librarian' seeded via Spatie Permission
|   - config/roles.php: 'librarian' => [..., 'active' => true]
|   - app/Modules/Library/ controllers built out
|
*/

use Illuminate\Support\Facades\Route;

// TODO: Uncomment when the Library module is ready.

// Route::middleware(['auth', 'role:librarian'])->prefix('library')->name('library.')->group(function () {
//
//     Route::get('/dashboard', fn () => view('library.dashboard'))->name('dashboard');
//
//     // Book management
//     Route::resource('books', \App\Modules\Library\Controllers\Admin\BookController::class);
//
//     // Issue / Return
//     Route::post('books/{book}/issue', \App\Modules\Library\Controllers\Admin\IssueController::class . '@issue')->name('books.issue');
//     Route::post('books/{book}/return', \App\Modules\Library\Controllers\Admin\IssueController::class . '@return')->name('books.return');
//
// });
