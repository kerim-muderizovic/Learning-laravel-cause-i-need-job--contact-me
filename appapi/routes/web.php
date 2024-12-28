<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;

// Public Routes
Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

// Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/loginn', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

// Email Verification
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/')->with('status', 'Email verified successfully!');
})->middleware(['auth', 'signed'])->name('verification.verify');

// Auth Check Route
Route::get('/auth/check', function () {
    if (Auth::check()) {
        $user = Auth::user();
        return response()->json([
            'isLoggedIn' => true,
            'user' => [
                'name' => $user->name,
                'profilePicture' => $user->url ?? null,
                'role' => $user->role ?? 'User',
            ],
        ]);
    }

    return response()->json(['isLoggedIn' => false]);
});

// Authenticated Routes

    // User Routes
    Route::prefix('user')->group(function () {
        Route::put('{id}/update-image', [UserController::class, 'updatePhoto']);
        Route::put('{id}/update-name', [UserController::class, 'updateName']);
        Route::get('{userId}/tasks', [TaskController::class, 'getUserTasks']);
    });

    // Task Routes
        Route::get('/getAllTasks', [TaskController::class, 'getAllTasks']); // Fetch all tasks
        Route::post('/postTask', [TaskController::class, 'store']); // Create a task
        Route::get('/assignable-users', [TaskController::class, 'getAssignableUsers']); // Fetch assignable users
        Route::delete('DeleteTask/{taskId}', [TaskController::class, 'destroy']); // Delete a task

// Fallback for Unauthenticated Access
Route::get('/login', function () {
    return response()->json(['message' => 'You must be logged in to access this route.'], 401);
})->name('login');
