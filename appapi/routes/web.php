<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
Route::get('/', function () {
    return ['Laravel' => app()->version()];
});
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/home'); // or wherever you'd like to redirect after verification
})->middleware(['auth', 'signed'])->name('verification.verify');
Route::get('/auth/check', function () {
    return response()->json(['isLoggedIn' => Auth::check()]);
});

