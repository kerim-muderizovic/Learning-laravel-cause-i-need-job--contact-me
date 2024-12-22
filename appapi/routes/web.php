<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});
Route::post('/register', [AuthController::class, 'register']);
Route::post('/loginn', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return 'good job'; // or wherever you'd like to redirect after verification
})->middleware(['auth', 'signed'])->name('verification.verify');
Route::get('/auth/check', function () {
    if (Auth::check()) {
        $user = Auth::user();
        return response()->json([
            'isLoggedIn' => true,
            'user' => [
                'name' => $user->name,
                'profilePicture' => $user->url, // Adjust field name
                'role' => $user->role, // Adjust field name
            ],
        ]);
    }

    return response()->json(['isLoggedIn' => false]);
});
Route::get('/user/{userId}/tasks', [TaskController::class, 'getUserTasks']);
Route::get('/login', function () {
    return response()->json(['message' => 'Seems like you logged in']);
})->name('login');
Route::put('/user/{id}/update-image',[UserController::class , 'updatePhoto']);
Route::put('/user/{id}/update-name', [UserController::class, 'updateName']);