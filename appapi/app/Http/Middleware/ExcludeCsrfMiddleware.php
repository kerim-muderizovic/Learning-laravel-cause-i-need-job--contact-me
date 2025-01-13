<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ExcludeCsrfMiddleware
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
        // If the request is to /broadcasting/auth, skip CSRF protection
        if ($request->is('broadcasting/auth')) {
            return $next($request);
        }

        // For all other routes, apply CSRF protection manually if needed
        return $next($request);
    }
}
