<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $availableLocales = ['en', 'es', 'ru', 'ar'];

        // 1. Locale cookie (set by LanguageController — persisted across sessions)
        if ($request->hasCookie('locale') && in_array($request->cookie('locale'), $availableLocales)) {
            $locale = $request->cookie('locale');
        }
        // 2. Legacy: session-based locale (backward compat)
        elseif (Session::has('locale') && in_array(Session::get('locale'), $availableLocales)) {
            $locale = Session::get('locale');
        }
        // 3. Default to English
        else {
            $locale = 'en';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
