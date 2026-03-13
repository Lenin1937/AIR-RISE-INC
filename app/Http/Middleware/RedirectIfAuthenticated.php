<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                // Redirect admins to admin dashboard
                if ($user->hasRole(['super-admin', 'administrator', 'admin', 'staff'])) {
                    return redirect()->intended(route('admin.dashboard'));
                }

                return redirect()->intended($this->clientRedirectPath($user));
            }
        }

        return $next($request);
    }

    private function clientRedirectPath(User $user): string
    {
        $status = $user->registration_status ?: 'approved';

        return match ($status) {
            'incomplete', 'order_pending' => route('orders.create'),
            'pending_approval', 'rejected' => route('onboarding.review'),
            default => route('dashboard'),
        };
    }
}
