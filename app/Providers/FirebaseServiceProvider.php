<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class FirebaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('firebase', function ($app) {
            $factory = (new Factory);

            if (config('firebase.credentials.file')) {
                $factory = $factory->withServiceAccount(config('firebase.credentials.file'));
            }

            if (config('firebase.project_id')) {
                $factory = $factory->withProjectId(config('firebase.project_id'));
            }

            if (config('firebase.database.url')) {
                $factory = $factory->withDatabaseUri(config('firebase.database.url'));
            }

            return $factory;
        });

        $this->app->singleton('firebase.auth', function ($app) {
            return $app['firebase']->createAuth();
        });

        $this->app->singleton('firebase.database', function ($app) {
            return $app['firebase']->createDatabase();
        });

        $this->app->singleton('firebase.firestore', function ($app) {
            return $app['firebase']->createFirestore();
        });

        $this->app->singleton('firebase.storage', function ($app) {
            return $app['firebase']->createStorage();
        });

        $this->app->singleton('firebase.messaging', function ($app) {
            return $app['firebase']->createMessaging();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register Firebase Storage Driver
        Storage::extend('firebase', function ($app, $config) {
            $firebase = $app['firebase'];
            $storage = $firebase->createStorage();
            
            return new \App\Services\FirebaseStorageDriver(
                $storage,
                $config['bucket']
            );
        });
    }
}