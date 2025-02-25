<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role, ...$guards): Response
    {
        $guard = $guards[0] ?? 'api';
        $user = Auth::guard($guard)->user();

        $allowedRoles = explode('|', $role);

        if (!$user) {
            return response()->json(['message' => 'User not found or deactivated'], 403);
        }

        if (!in_array($user->role, $allowedRoles)) {
            return response()->json(['message' => "Unauthorized access to Humanity detected"], 403);
        }

        return $next($request);
    }
}
