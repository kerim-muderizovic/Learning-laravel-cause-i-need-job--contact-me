<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Ensure2FACompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        // If user is not authenticated, redirect to login
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        
        // Check if 2FA is required but not completed
        if ($user->requires_2fa && $user->two_factor_key !== null) {
            Auth::logout();
            return response()->json([
                'message' => '2FA verification required',
                'requires_2fa' => true
            ], 403);
        }
        
        return $next($request);
    }
} 