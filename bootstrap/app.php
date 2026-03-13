<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies (Docker / nginx SSL termination)
        $middleware->trustProxies(at: '*');

        // Language switching is a harmless locale preference — no CSRF needed
        $middleware->validateCsrfTokens(except: ['/language/switch']);

        // Block visitors from restricted countries (uses Cloudflare CF-IPCountry header)
        $middleware->prependToGroup('web', \App\Http\Middleware\BlockRestrictedCountries::class);

        // Prepend so it wraps all other middleware and sees the final response last
        $middleware->prependToGroup('web', \App\Http\Middleware\CachePublicPages::class);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role'             => \App\Http\Middleware\RoleMiddleware::class,
            'guest'            => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'client.onboarding' => \App\Http\Middleware\CheckClientOnboarding::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
