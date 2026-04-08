<?php

return [

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    // Social Login
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI', '/auth/facebook/callback'),
    ],

    'ocr_space' => [
        'key' => env('OCR_SPACE_API_KEY'),
        'url' => env('OCR_SPACE_URL', 'https://api.ocr.space/parse/image'),
        'connect_timeout' => max(5, min(120, filter_var(env('OCR_SPACE_CONNECT_TIMEOUT', 30), FILTER_VALIDATE_INT) ?: 30)),
        // If engine 1 returns fewer than this many characters, retry once with engine 2 (0 = disable).
        'fallback_min_chars' => max(0, filter_var(env('OCR_SPACE_FALLBACK_MIN_CHARS', 50), FILTER_VALIDATE_INT) ?: 50),
    ],

];
