<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class OtpController extends Controller
{
    /**
     * Send a 6-digit OTP to the supplied email address.
     * Stores the OTP in the cache keyed by email for 10 minutes.
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'email'      => 'required|email|max:255|unique:users,email',
            'first_name' => 'required|string|max:100',
        ]);

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in cache for 10 minutes
        Cache::put('otp:' . strtolower($request->email), $otp, now()->addMinutes(10));

        Mail::to($request->email)->send(new OtpMail($otp, $request->first_name));

        return response()->json(['message' => 'OTP sent successfully.']);
    }

    /**
     * Verify the OTP supplied by the user.
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string|size:6',
        ]);

        $key      = 'otp:' . strtolower($request->email);
        $stored   = Cache::get($key);

        if (! $stored || $stored !== $request->otp) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired verification code. Please try again.'],
            ]);
        }

        // Mark as verified in the cache (different key so it can't be re-used as an OTP)
        Cache::put('otp_verified:' . strtolower($request->email), true, now()->addMinutes(30));
        Cache::forget($key);

        return response()->json(['message' => 'Email verified successfully.']);
    }
}
