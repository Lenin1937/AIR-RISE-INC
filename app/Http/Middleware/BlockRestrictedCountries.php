<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Block access to the platform from countries where we do not offer services,
 * for legal / sanctions compliance and business policy reasons.
 *
 * Detection relies on the CF-IPCountry header injected by Cloudflare on every
 * proxied request, so no third-party GeoIP database or API is required.
 */
class BlockRestrictedCountries
{
    /**
     * ISO 3166-1 alpha-2 country codes that are not permitted to use this platform.
     * Visitors from these countries cannot register, log in, or place orders.
     */
    private const BLOCKED = [
        'KP', // North Korea
        'IR', // Iran
        'SY', // Syria
        'AF', // Afghanistan
        'ID', // Indonesia
        'VN', // Vietnam
        'PH', // Philippines
        'UA', // Ukraine
        'KZ', // Kazakhstan
        'KG', // Kyrgyzstan
        'MD', // Moldova
        'JP', // Japan
        'KR', // South Korea
        'SG', // Singapore
        'AE', // United Arab Emirates
        'AZ', // Azerbaijan
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Cloudflare sets CF-IPCountry on every request.
        // 'XX' means the country could not be determined (e.g. Tor, special IP).
        // If the header is absent (local dev / direct-to-origin), we allow through.
        $country = strtoupper((string) $request->header('CF-IPCountry', ''));

        if ($country !== '' && $country !== 'XX' && in_array($country, self::BLOCKED, true)) {
            return response()->view('errors.blocked_region', ['country' => $country], 451);
        }

        return $next($request);
    }
}
