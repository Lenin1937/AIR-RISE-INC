<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        
        // Force HTTPS in production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Log Viewer authorization
        Gate::define('viewLogViewer', function ($user = null) {
            // Allow access for local and production environments for now
            // TODO: Restrict to admin users only in production
            return true;
        });

        // Share translations with Inertia
        Inertia::share([
            'locale' => function () {
                return App::getLocale();
            },
            'translations' => function () {
                $locale = App::getLocale();
                $translations = [];
                
                // Load all translation files for current locale
                $langPath = lang_path($locale);
                if (File::exists($langPath)) {
                    $files = File::files($langPath);
                    foreach ($files as $file) {
                        $key = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                        $translations[$key] = include $file->getPathname();
                    }
                }
                
                return $translations;
            },
        ]);
    }
}
