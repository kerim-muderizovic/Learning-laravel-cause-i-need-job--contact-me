<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BroadcastController;
use Illuminate\Support\Facades\Auth;
Broadcast::channel('chat.{receiverId}', function ($user, $receiverId) {
    return (int) $user->id === (int) $receiverId || $user->id;
});
Broadcast::channel('private-channel', function ($user) {
    return Auth::check() && Auth::user()->id === $user->id; // Ensure the authenticated user matches
});
Route::get('/broadcasting1/auth', [BroadcastController::class, 'authenticate']);