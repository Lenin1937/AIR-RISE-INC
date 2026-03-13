<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CachePublicPages
{
    /**
     * Routes that should never be cached (have forms, auth, or user-specific content).
     */
    protected array $noCacheRoutes = [
        'login', 'register', 'logout', 'password', 'email',
        'dashboard', 'contact', 'api', 'livewire', 'admin',
        'orders', 'profile', 'settings', 'documents', 'ai-chat',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // DEBUG: always add this header to confirm middleware runs
        $response->headers->set('X-Cache-Middleware', 'executed');

        // Only cache GET requests for anonymous users on public pages
        if (!$request->isMethod('GET') || $request->user()) {
            return $response;
        }

        // Skip if this is an auth/user-specific route
        $path = ltrim($request->path(), '/');
        foreach ($this->noCacheRoutes as $noCacheRoute) {
            if (str_starts_with($path, $noCacheRoute)) {
                return $response;
            }
        }

        // Only cache successful HTML responses
        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        $contentType = $response->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'text/html')) {
            return $response;
        }

        // Strip Set-Cookie headers so nginx/Cloudflare can cache this response.
        // Session will be created on the first non-cached request (register, contact, etc.)
        foreach ($response->headers->getCookies() as $cookie) {
            $response->headers->removeCookie(
                $cookie->getName(),
                $cookie->getPath(),
                $cookie->getDomain()
            );
        }
        // Remove any raw Set-Cookie headers as well
        $response->headers->remove('Set-Cookie');

        // If the user has switched to a non-default locale, serve them a private cached response.
        // We must NOT send this to CDN/nginx shared cache (it's language-specific).
        if ($request->hasCookie('locale') && $request->cookie('locale') !== 'en') {
            $response->headers->set('Cache-Control', 'private, max-age=300');
            return $response;
        }

        // Tell proxies (nginx FastCGI cache, Cloudflare) this response is publicly cacheable
        // max-age: browser cache 5 min | s-maxage: CDN/Cloudflare edge cache 1 hour
        $response->headers->set('Cache-Control', 'public, max-age=300, s-maxage=3600');

        return $response;
    }
}
