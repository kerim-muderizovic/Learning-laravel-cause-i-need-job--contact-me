<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BroadcastController;
use Illuminate\Support\Facades\Auth;
Broadcast::channel('chat.{receiverId}', function ($user, $receiverId) {
    return (int) $user->id === (int) $receiverId || $user->id;
});
Broadcast::channel('private-channel', function ($user) {
    // Manually authenticate user using your custom logic
    // Replace this with your custom authentication logic
    $authenticatedUser = auth::user(); // Your custom method to retrieve the authenticated user
    
    if ($authenticatedUser && $authenticatedUser->id === $user->id) {
        return true;
    }
    
    return false;
});
// Route::get('/broadcasting1/auth', [BroadcastController::class, 'authenticate']);
Route::group([
    'middleware' => [] // Remove all middleware here
], function () {
    Broadcast::routes(); // Define broadcast routes without middleware
});