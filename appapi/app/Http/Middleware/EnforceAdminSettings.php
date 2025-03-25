<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Admin;
use Illuminate\Support\Facades\Log;

class EnforceAdminSettings
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get global admin settings from database
        $adminSettings = Admin::first();
        
        // If no settings found, log warning but allow request
        if (!$adminSettings) {
            Log::warning('No admin settings found in database');
            return $next($request);
        }
        
        // Store settings in request for controllers to access
        $request->attributes->add(['admin_settings' => $adminSettings]);
        
        // Registration routes check
        if ($request->is('register') && !$adminSettings->allow_creating_accounts) {
            return response()->json([
                'message' => 'Registration is currently disabled by administrator'
            ], 403);
        }
        
        // Password reset routes check
        if (($request->is('forgot-password') || $request->is('reset-password*')) && 
            !$adminSettings->enable_reset_password) {
            return response()->json([
                'message' => 'Password reset functionality is currently disabled by administrator'
            ], 403);
        }
        
        return $next($request);
    }
}
