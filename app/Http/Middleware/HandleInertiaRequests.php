<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? $request->user()->load('roles')->append('profile_picture_url') : null,
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            // Only expose payment keys to authenticated users (prevent leaking to public pages)
            'stripe_key' => $request->user() ? config('services.stripe.key') : null,
            'stripe_crypto_enabled' => $request->user() ? config('services.stripe.crypto_enabled', false) : false,
            'stripe_crypto_currencies' => $request->user() ? config('services.stripe.crypto_currencies', ['bitcoin', 'ethereum', 'usdc']) : [],
            'usdc_wallet_address' => $request->user() ? config('services.stripe.usdc_wallet_address', '') : '',
            'paypal_client_id' => $request->user()
                ? (config('paypal.mode') === 'live'
                    ? config('paypal.live.client_id')
                    : config('paypal.sandbox.client_id'))
                : null,
            'locale' => fn () => \Illuminate\Support\Facades\App::getLocale(),
            'translations' => function () {
                $locale = \Illuminate\Support\Facades\App::getLocale();
                $cacheKey = "translations.{$locale}";
                $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);

                // Self-heal bad cache payloads (e.g. accidentally cached empty arrays)
                $hasMarketing = is_array($cached)
                    && isset($cached['marketing'])
                    && is_array($cached['marketing'])
                    && count($cached['marketing']) > 0;

                if (! $hasMarketing) {
                    $cached = $this->loadLocaleTranslations($locale);
                    \Illuminate\Support\Facades\Cache::put($cacheKey, $cached, 3600);
                }

                return $cached;
            },
        ];
    }

    /**
     * Load all translation files for a locale from disk.
     */
    protected function loadLocaleTranslations(string $locale): array
    {
        $translations = [];
        $langPath = lang_path($locale);

        if (\Illuminate\Support\Facades\File::exists($langPath)) {
            foreach (\Illuminate\Support\Facades\File::files($langPath) as $file) {
                $key = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                $translations[$key] = include $file->getPathname();
            }
        }

        return $translations;
    }
}
