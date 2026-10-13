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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'facebook' => [
        'pixel_id'   => env('FB_PIXEL_ID'),
        'capi_token' => env('FB_CAPI_TOKEN'),
    ],

    'tinymce' => [
        'api_key' => env('TINYMCE_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Merchant Center Feed
    |--------------------------------------------------------------------------
    |
    | feed_key            — Secret token required as ?key= on all feed endpoints.
    |                       Set GOOGLE_MERCHANT_FEED_KEY in .env.
    | currency            — ISO 4217 currency code for prices (e.g. BDT, USD).
    | brand               — Brand name submitted to Google (defaults to APP_NAME).
    | condition           — Default product condition: new | used | refurbished.
    | google_product_category — Default Google product taxonomy category.
    |
    */
    'google_merchant' => [
        'feed_key'                => env('GOOGLE_MERCHANT_FEED_KEY'),
        'currency'                => env('GOOGLE_MERCHANT_CURRENCY', 'BDT'),
        'brand'                   => env('GOOGLE_MERCHANT_BRAND', env('APP_NAME', 'Open Se Same')),
        'condition'               => env('GOOGLE_MERCHANT_CONDITION', 'new'),
        'google_product_category' => env('GOOGLE_MERCHANT_CATEGORY', 'Apparel & Accessories'),
    ],

];
