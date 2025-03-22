<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BroadcastController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

// Channel for private chat between users
Broadcast::channel('chat.{senderId}.{receiverId}', function ($user, $senderId, $receiverId) {
    // Log the authentication attempt for debugging
    Log::info('Channel auth attempt', [
        'user_id' => $user->id,
        'senderId' => $senderId,
        'receiverId' => $receiverId
    ]);
    
    // Allow if user is either the sender or receiver
    return (int) $user->id === (int) $senderId || (int) $user->id === (int) $receiverId;
});

// General private channel for authenticated users
Broadcast::channel('private-channel', function ($user) {
    return Auth::check();
});

// We're using a custom broadcasting auth route in web.php, so we don't need this
// Route::get('/broadcasting1/auth', [BroadcastController::class, 'authenticate']);