<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use Pusher\Pusher;
class BroadcastController extends Controller
{
    public function authenticate(Request $request)
    {
        // Custom authentication logic for your users (e.g., check if the user is logged in)
        if (Auth::check()) {
            $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                [
                    'cluster' => env('PUSHER_APP_CLUSTER'),
                    'useTLS' => true
                ]
            );

            $channel = $request->channel_name;
            $socketId = $request->socket_id;

            // Build authentication data
            $authData = $pusher->socket_auth($channel, $socketId);

            return response()->json($authData);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }
}
