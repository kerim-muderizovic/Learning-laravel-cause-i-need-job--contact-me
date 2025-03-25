<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use Illuminate\Support\Facades\Log;

class IsAdmin
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
        
        if (!$user) {
            Log::warning('Unauthenticated user attempting to access admin route', [
                'ip' => $request->ip(),
                'path' => $request->path()
            ]);
            return response()->json(['message' => 'Unauthorized. Please log in first.'], 401);
        }
        
        // Check if user has admin role AND exists in admins table
        $isAdmin = strtolower($user->role) === 'admin';
        $hasAdminRecord = Admin::where('id', $user->id)->exists();
        
        // If not in sync, attempt to sync
        if ($isAdmin && !$hasAdminRecord) {
            try {
                Admin::create(['id' => $user->id]);
                Log::info("Auto-synced user {$user->id} to admins table via middleware");
                $hasAdminRecord = true;
            } catch (\Exception $e) {
                Log::error("Failed to auto-sync admin", [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        if (!$isAdmin || !$hasAdminRecord) {
            Log::warning('Non-admin user attempting to access admin route', [
                'user_id' => $user->id,
                'has_admin_role' => $isAdmin,
                'has_admin_record' => $hasAdminRecord,
                'path' => $request->path()
            ]);
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        return $next($request);
    }
} 