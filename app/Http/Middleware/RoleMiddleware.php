<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // If no roles specified, just check if user is authenticated
        if (empty($roles)) {
            return $next($request);
        }

        // Log detailed information for debugging
        \Log::info('RoleMiddleware Check', [
            'url' => $request->fullUrl(),
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_roles' => $user->roles->pluck('name')->toArray(),
            'required_roles' => $roles,
            'has_any_role' => $user->hasAnyRole($roles),
        ]);

        // Check if user has any of the required roles
        if (!$user->hasAnyRole($roles)) {
            \Log::warning('Access Denied', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_roles' => $user->roles->pluck('name')->toArray(),
                'required_roles' => $roles,
            ]);
            abort(403, 'Access denied. You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
