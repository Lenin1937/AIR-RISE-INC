<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the multi-step registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle account creation (called on Step 3 – Access).
     * Requires that the email was previously OTP-verified in this session.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name'        => 'required|string|max:100',
            'last_name'         => 'required|string|max:100',
            'username'          => 'required|string|max:50|unique:users,username|alpha_dash',
            'email'             => 'required|string|lowercase|email|max:255|unique:users,email',
            'phone'             => 'required|string|max:30',
            'telegram_username' => 'nullable|string|max:100',
            'password'          => ['required', 'confirmed', Rules\Password::defaults()],
            'terms_accepted'    => 'accepted',
        ]);

        // Ensure email was OTP-verified in the last 30 minutes
        $verifiedKey = 'otp_verified:' . strtolower($request->email);
        if (! Cache::get($verifiedKey)) {
            throw ValidationException::withMessages([
                'email' => ['Your email has not been verified. Please complete the verification step.'],
            ]);
        }

        $user = User::create([
            'first_name'          => $request->first_name,
            'last_name'           => $request->last_name,
            'name'                => $request->first_name . ' ' . $request->last_name,
            'username'            => $request->username,
            'email'               => $request->email,
            'password'            => Hash::make($request->password),
            'phone'               => $request->phone,
            'telegram_username'   => $request->telegram_username,
            'email_verified_at'   => now(),
            'registration_status' => 'incomplete',
            'registration_source' => 'web',
            'terms_accepted'      => true,
            'terms_accepted_at'   => now(),
        ]);

        $user->assignRole('client');

        // Clear OTP verification flag
        Cache::forget($verifiedKey);

        Auth::login($user);

        // Send welcome email
        try {
            Mail::to($user->email)->queue(new WelcomeMail($user));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Welcome email failed', ['error' => $e->getMessage()]);
        }

        return redirect()->route('orders.create');
    }
}
