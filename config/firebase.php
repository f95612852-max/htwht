<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Firebase services integration
    |
    */

    'project_id' => env('FIREBASE_PROJECT_ID'),
    
    'credentials' => [
        'file' => env('FIREBASE_CREDENTIALS_PATH'),
        'auto_discovery' => env('FIREBASE_AUTO_DISCOVERY', true),
    ],

    'auth' => [
        'tenant_id' => env('FIREBASE_AUTH_TENANT_ID'),
    ],

    'database' => [
        'url' => env('FIREBASE_DATABASE_URL'),
    ],

    'storage' => [
        'default_bucket' => env('FIREBASE_STORAGE_DEFAULT_BUCKET'),
    ],

    'messaging' => [
        'http_timeout' => 30,
    ],

    'dynamic_links' => [
        'default_domain' => env('FIREBASE_DYNAMIC_LINKS_DEFAULT_DOMAIN'),
    ],
];