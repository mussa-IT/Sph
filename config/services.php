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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
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

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'timeout' => (float) env('OPENAI_TIMEOUT', 20),
        'connect_timeout' => (float) env('OPENAI_CONNECT_TIMEOUT', 5),
        'retry_attempts' => (int) env('OPENAI_RETRY_ATTEMPTS', 2),
        'retry_sleep_ms' => (int) env('OPENAI_RETRY_SLEEP_MS', 250),
        'circuit_breaker' => [
            'threshold' => (int) env('OPENAI_CIRCUIT_THRESHOLD', 5),
            'cooldown_seconds' => (int) env('OPENAI_CIRCUIT_COOLDOWN_SECONDS', 60),
        ],
        'chat_async' => (bool) env('OPENAI_CHAT_ASYNC', false),
        'builder' => [
            'cache_ttl_seconds' => (int) env('AI_BUILDER_CACHE_TTL_SECONDS', 120),
        ],
        'rate_limits' => [
            'chat_per_minute' => (int) env('AI_CHAT_PER_MINUTE', 15),
            'chat_per_hour' => (int) env('AI_CHAT_PER_HOUR', 120),
            'chat_per_session_per_minute' => (int) env('AI_CHAT_PER_SESSION_PER_MINUTE', 10),
            'builder_per_minute' => (int) env('AI_BUILDER_PER_MINUTE', 12),
        ],
    ],

];
