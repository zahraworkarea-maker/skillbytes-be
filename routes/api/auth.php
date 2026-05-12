<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication & User Routes
|--------------------------------------------------------------------------
*/

Route::prefix('auth/user')->group(function () {
    // Public routes
    Route::post('/login', [UserController::class, 'login'])->name('auth.user.login');

    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        // List users with pagination, search, and sort
        Route::get('/', [UserController::class, 'index'])->name('auth.user.index');

        // Get all users (without pagination)
        Route::get('/all', [UserController::class, 'getAllUsers'])->name('auth.user.all');

        // Get single user by ID
        Route::get('/{user}', [UserController::class, 'show'])
            ->whereNumber('user')
            ->name('auth.user.show');

        Route::middleware('role:admin,guru')->group(function () {
            // Create user
            Route::post('/', [UserController::class, 'store'])->name('auth.user.store');

            // Delete user
            Route::delete('/{user}', [UserController::class, 'destroy'])
                ->whereNumber('user')
                ->name('auth.user.destroy');
        });

        // Update user - accessible by all authenticated users
        Route::match(['put', 'patch', 'post'], '/{user}', [UserController::class, 'update'])
            ->whereNumber('user')
            ->name('auth.user.update');

        // Update password
        Route::put('/update-password/{user}', [UserController::class, 'updatePassword'])
            ->whereNumber('user')
            ->name('auth.user.updatePassword');
    });
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    });
});
