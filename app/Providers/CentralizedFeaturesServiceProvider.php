<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Apple\Provider as AppleProvider;
use SocialiteProviders\Google\Provider as GoogleProvider;

class CentralizedFeaturesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register Socialite providers
        $this->bootSocialiteProviders();
        
        // Register Blade components
        $this->bootBladeComponents();
        
        // Register view composers
        $this->bootViewComposers();
    }

    private function bootSocialiteProviders(): void
    {
        // Configure Google provider
        Socialite::extend('google', function ($app) {
            $config = $app['config']['services.google'];
            return Socialite::buildProvider(GoogleProvider::class, $config);
        });

        // Configure Apple provider
        Socialite::extend('apple', function ($app) {
            $config = $app['config']['services.apple'];
            return Socialite::buildProvider(AppleProvider::class, $config);
        });
    }

    private function bootBladeComponents(): void
    {
        // Register verification badge component
        Blade::component('verification-badge', \App\View\Components\VerificationBadge::class);
        
        // Register custom Blade directives
        Blade::directive('verified', function ($expression) {
            return "<?php if({$expression}->is_verified): ?>";
        });
        
        Blade::directive('endverified', function () {
            return "<?php endif; ?>";
        });
    }

    private function bootViewComposers(): void
    {
        // Share verification status with all views
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $view->with('currentUserVerified', auth()->user()->is_verified);
            }
        });
    }
}