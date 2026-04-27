<?php

return [
    'hsts_max_age' => (int) env('SECURITY_HSTS_MAX_AGE', 31536000),
    'referrer_policy' => env('SECURITY_REFERRER_POLICY', 'strict-origin-when-cross-origin'),
    'permissions_policy' => env('SECURITY_PERMISSIONS_POLICY', 'camera=(), microphone=(), geolocation=()'),
    'rate_limits' => [
        'auth_per_minute' => (int) env('AUTH_RATE_LIMIT_PER_MINUTE', 10),
        'sensitive_per_minute' => (int) env('SENSITIVE_RATE_LIMIT_PER_MINUTE', 5),
        'session_creation_per_minute' => (int) env('SESSION_CREATION_RATE_LIMIT_PER_MINUTE', 8),
    ],
    'admin_emails' => array_values(array_filter(array_map(
        static fn (string $email): string => strtolower(trim($email)),
        explode(',', (string) env('ADMIN_EMAILS', ''))
    ))),
];
