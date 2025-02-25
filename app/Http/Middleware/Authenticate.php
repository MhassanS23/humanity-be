<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        $guard = $guards[0] ?? 'api';
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Missing authentication token'], 401);
        }
        
        if (!Auth::guard($guard)->check()) {
            return response()->json(['message' => 'Invalid or expired token'], 401);
        }

        Auth::setUser(Auth::guard($guard)->user());
        return $next($request);
    }
}
