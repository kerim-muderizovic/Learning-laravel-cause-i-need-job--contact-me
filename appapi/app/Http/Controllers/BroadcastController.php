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
        // Log detailed information about the request
        Log::info('Broadcasting auth attempt', [
            'user' => Auth::check() ? Auth::id() : 'Not authenticated',
            'socketId' => $request->socket_id,
            'channelName' => $request->channel_name,
            'headers' => $request->headers->all(),
            'session' => $request->session()->all()
        ]);
        
        // If no authenticated user, try to find the user in session
        if (!Auth::check() && $request->session()->has('auth_user_id')) {
            $userId = $request->session()->get('auth_user_id');
            Log::info('Found user in session', ['userId' => $userId]);
            
            // You could attempt to authenticate the user here if needed
            // Auth::loginUsingId($userId);
        }
        
        // Return the authentication response
        return Broadcast::auth($request);
    }
}
