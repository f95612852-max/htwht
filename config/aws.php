<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Firebase Configuration (Replaces AWS)
    |--------------------------------------------------------------------------
    |
    | This file has been updated to use Firebase instead of AWS services.
    | All AWS configurations have been replaced with Firebase equivalents.
    |
    */

    'disabled' => true, // AWS services are disabled in favor of Firebase
    
    'firebase' => [
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'credentials' => env('FIREBASE_CREDENTIALS_PATH'),
        'database_url' => env('FIREBASE_DATABASE_URL'),
        'storage_bucket' => env('FIREBASE_STORAGE_DEFAULT_BUCKET'),
    ],

];