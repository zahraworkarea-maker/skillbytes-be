<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
*/

// Public route for login
Route::post('/login', [UserController::class, 'login'])->name('users.login');

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // List users with pagination, search, and sort
    Route::get('/', [UserController::class, 'index'])->name('users.index');

    // Get all users (without pagination)
    Route::get('/all', [UserController::class, 'getAllUsers'])->name('users.all');

    // Get single user by ID
    Route::get('/{user}', [UserController::class, 'show'])
        ->whereNumber('user')
        ->name('users.show');

    // Create user (only admin)
    Route::post('/', [UserController::class, 'store'])->name('users.store');

    // Update user (admin or self)
    Route::put('/{user}', [UserController::class, 'update'])
        ->whereNumber('user')
        ->name('users.update');

    // Delete user (only admin)
    Route::delete('/{user}', [UserController::class, 'destroy'])
        ->whereNumber('user')
        ->name('users.destroy');
});
