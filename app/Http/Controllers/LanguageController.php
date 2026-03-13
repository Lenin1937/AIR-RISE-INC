<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class LanguageController extends Controller
{
    /**
     * Switch the application language
     */
    public function switch(Request $request)
    {
        $locale = $request->input('locale', 'en');
        $availableLocales = ['en', 'es', 'ru', 'ar'];

        if (!in_array($locale, $availableLocales)) {
            $locale = 'en';
        }

        // Store locale in a long-lived cookie (10 years) so it survives without a session.
        // No session needed — avoids conflict with the public page caching layer.
        $cookie = cookie('locale', $locale, 60 * 24 * 365 * 10, '/', null, true, false, false, 'Lax');

        return Redirect::back()->withCookie($cookie);
    }
}
