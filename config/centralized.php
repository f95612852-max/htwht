<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Centralized Pixelfed Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for centralized Pixelfed instance
    |
    */

    'enabled' => env('CENTRALIZED_MODE', true),

    'single_instance' => [
        'name' => env('APP_NAME', 'Pixelfed'),
        'domain' => env('APP_DOMAIN', parse_url(env('APP_URL'), PHP_URL_HOST)),
        'description' => env('APP_DESCRIPTION', 'A centralized photo sharing platform'),
    ],

    'features' => [
        'federation' => false,
        'activitypub' => false,
        'webfinger' => false,
        'remote_follow' => false,
        'remote_media' => false,
    ],

    'aws' => [
        'enabled' => env('AWS_ENABLED', false),
        's3_storage' => env('AWS_S3_ENABLED', false),
        'ses_email' => env('AWS_SES_ENABLED', false),
        'cloudfront_cdn' => env('AWS_CLOUDFRONT_ENABLED', false),
    ],

    'limits' => [
        'max_photo_size' => env('MAX_PHOTO_SIZE', 15000), // KB
        'max_album_length' => env('MAX_ALBUM_LENGTH', 10),
        'max_caption_length' => env('MAX_CAPTION_LENGTH', 500),
        'max_bio_length' => env('MAX_BIO_LENGTH', 125),
        'max_name_length' => env('MAX_NAME_LENGTH', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Verification System
    |--------------------------------------------------------------------------
    */

    'verification' => [
        'enabled' => env('VERIFICATION_ENABLED', true),
        'admin_email' => env('VERIFICATION_ADMIN_EMAIL', env('MAIL_FROM_ADDRESS')),
        'max_file_size' => env('VERIFICATION_MAX_FILE_SIZE', 5120), // KB
        'allowed_file_types' => ['jpg', 'jpeg', 'png', 'pdf'],
        'review_time_hours' => env('VERIFICATION_REVIEW_TIME', 48),
    ],

    /*
    |--------------------------------------------------------------------------
    | Earnings System
    |--------------------------------------------------------------------------
    */

    'earnings' => [
        'enabled' => env('EARNINGS_ENABLED', true),
        'rate_per_thousand_views' => env('EARNINGS_RATE_PER_THOUSAND', 0.3),
        'minimum_payout' => env('EARNINGS_MINIMUM_PAYOUT', 10.0),
        'calculation_frequency' => env('EARNINGS_CALCULATION_FREQUENCY', 'hourly'),
        'view_deduplication_hours' => env('EARNINGS_VIEW_DEDUP_HOURS', 24),
    ],

    /*
    |--------------------------------------------------------------------------
    | Social Authentication
    |--------------------------------------------------------------------------
    */

    'social_auth' => [
        'google' => [
            'enabled' => env('GOOGLE_CLIENT_ID') && env('GOOGLE_CLIENT_SECRET'),
        ],
        'apple' => [
            'enabled' => env('APPLE_CLIENT_ID') && env('APPLE_CLIENT_SECRET'),
        ],
    ],

];