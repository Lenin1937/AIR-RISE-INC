<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],



    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
    ],

    'openai' => [
        'api_key'  => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'model'    => env('OPENAI_MODEL', 'gpt-4o-mini'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'crypto_enabled' => env('STRIPE_CRYPTO_ENABLED', false),
        'crypto_currencies' => explode(',', env('STRIPE_CRYPTO_CURRENCIES', 'usdc')),
        'usdc_wallet_address' => env('USDC_WALLET_ADDRESS', ''),
    ],

    'paypal' => [
        'mode' => env('PAYPAL_MODE', 'sandbox'),
        'sandbox' => [
            'client_id' => env('PAYPAL_SANDBOX_CLIENT_ID'),
            'secret' => env('PAYPAL_SANDBOX_SECRET'),
        ],
        'live' => [
            'client_id' => env('PAYPAL_LIVE_CLIENT_ID'),
            'secret' => env('PAYPAL_LIVE_SECRET'),
        ],
    ],

    'chatwoot' => [
        'url' => env('CHATWOOT_URL', 'http://chatwoot:3000'),
        'api_token' => env('CHATWOOT_API_TOKEN'),
        'account_id' => env('CHATWOOT_ACCOUNT_ID', 1),
        'inbox_id' => env('CHATWOOT_INBOX_ID', 1),
        'website_token' => env('CHATWOOT_WEBSITE_TOKEN'),
        'identity_secret' => env('CHATWOOT_IDENTITY_SECRET'),
    ],

    'google_analytics' => [
        'property_id'      => env('GA4_PROPERTY_ID'),
        'credentials_path' => env('GA4_CREDENTIALS_PATH', storage_path('app/google-credentials.json')),
    ],

    'google_search_console' => [
        'site_url'         => env('GSC_SITE_URL'),
        'credentials_path' => env('GSC_CREDENTIALS_PATH', storage_path('app/google-credentials.json')),
    ],

];
