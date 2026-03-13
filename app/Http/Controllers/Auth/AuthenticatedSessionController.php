<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Role-based redirect after login
        if ($user->hasRole(['super-admin', 'administrator', 'admin', 'staff'])) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }

        return redirect()->intended($this->clientRedirectPath($user));
    }

    private function clientRedirectPath(User $user): string
    {
        $status = $user->registration_status ?: 'approved';

        return match ($status) {
            'incomplete', 'order_pending' => route('orders.create', absolute: false),
            'pending_approval', 'rejected' => route('onboarding.review', absolute: false),
            default => route('dashboard', absolute: false),
        };
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
