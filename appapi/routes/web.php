<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\IsAdmin;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Middleware\ExcludeCsrfMiddleware;
// Public Routes
use App\Http\Controllers\BroadcastController;
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
Route::post('/verify-2fa',[AuthController::class,'verify_twofactor']);
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
                'requires_2fa'=>$user->requires_2fa,
            ],
        ]);
    }

    // Log::info('Not Authenticated'); // Debugging
    return response()->json(['isLoggedIn' => false]);
});

// Authenticated Routes

    // User Routes
    Route::prefix('user')->group(function () {
        Route::put('{id}/update-image', [UserController::class, 'updatePhoto']);
        Route::put('{id}/update-name', [UserController::class, 'updateName']);
        Route::get('{userId}/tasks', [TaskController::class, 'getUserTasks']);
        Route::post('/update-password', [UserController::class, 'updatePassword']);
        Route::get('/getAll', [UserController::class, 'getAllUsers']);
    });

    // Task Routes
        Route::get('/getAllTasks', [TaskController::class, 'getAllTasks']); // Fetch all tasks
        Route::get('/getUserTasks', [TaskController::class, 'getUserTasks']); // Fetch all tasks
        Route::post('/postTask', [TaskController::class, 'store']); // Create a task
        Route::get('/assignable-users', [TaskController::class, 'getAssignableUsers']); // Fetch assignable users
        Route::delete('DeleteTask/{taskId}', [TaskController::class, 'destroy']); // Delete a task
// Fallback for Unauthenticated Access
Route::get('/login', function () {
    return response()->json(['message' => 'You must be logged in to access this route.'], 401);
})->name('login');
Route::put('/tasks/{taskId}', [TaskController::class, 'updateProgress']);

// Route::middleware([IsAdmin::class])->group(function () {
    Route::delete('Admin/users/{id}', [AdminController::class, 'deleteUser']);
    Route::delete('Admin/tasks/{id}', [AdminController::class, 'deleteTask']);
    Route::put('Admin/tasks/{id}', [AdminController::class, 'editTask']);
    Route::put('Admin/users/{id}', [AdminController::class, 'editUser']);
    Route::post('/Admin/AddTask', [AdminController::class, 'createTask']);
// });


Route::get('/messages', [MessageController::class, 'index']);
Route::post('/messages', [MessageController::class, 'store']);

// Route::post('/broadcasting/auth', function () {
//     // Custom logic (e.g., authentication of the user) can be added here
//     // Laravel will handle the broadcasting auth automatically, but you can add additional checks if needed.

//     return response()->json(['message' => 'Authenticated successfully']);
// });

Route::get('/api/messages', [MessageController::class, 'index']);
    Route::post('api/messages', [MessageController::class, 'store']);
    Route::post('/broadcasting1/auth', [BroadcastController::class, 'authenticate']);