<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BroadcastController extends Controller
{
    public function authenticate(Request $request)
    {
        // Log the authentication attempt for debugging
        Log::info('Broadcasting auth attempt', [
            'user' => Auth::user(),
            'socketId' => $request->socket_id,
            'channelName' => $request->channel_name
        ]);
        
        // If no authenticated user, try to find the user in session
        if (!Auth::check() && $request->session()->has('auth_user_id')) {
            $userId = $request->session()->get('auth_user_id');
            Log::info('Found user in session', ['userId' => $userId]);
        }
        
        // Return the authentication response
        return Broadcast::auth($request);
    }
}
