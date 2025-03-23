<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class BroadcastController extends Controller
{
    public function authenticate(Request $request)
    {
        // Log the broadcasting authentication attempt
        Log::info('Broadcasting auth attempt', [
            'user' => Auth::check() ? Auth::id() : 'Not authenticated',
            'socketId' => $request->socket_id,
            'channelName' => $request->channel_name
        ]);
        
        // If no authenticated user, try to authenticate from session
        if (!Auth::check() && $request->session()->has('auth_user_id')) {
            $userId = $request->session()->get('auth_user_id');
            Log::info('Found user in session, logging in', ['userId' => $userId]);
            
            // Attempt to log in the user from session
            $user = User::find($userId);
            if ($user) {
                Auth::login($user);
                Log::info('User logged in for broadcast auth', ['userId' => $userId]);
            }
        }
        
        // Return the authentication response
        return Broadcast::auth($request);
    }
}
