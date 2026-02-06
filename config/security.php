<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Configure security settings for the application.
    |
    */

    // Maximum failed login attempts before IP block
    'max_failed_attempts' => env('SECURITY_MAX_FAILED_ATTEMPTS', 5),

    // IP block duration in minutes
    'ip_block_duration' => env('SECURITY_IP_BLOCK_DURATION', 30),

    // Maximum failed login attempts per user account
    'max_user_failed_attempts' => env('SECURITY_MAX_USER_FAILED_ATTEMPTS', 5),

    // Account lock duration in minutes
    'account_lock_duration' => env('SECURITY_ACCOUNT_LOCK_DURATION', 30),

    // Enable security alert emails
    'enable_security_alerts' => env('SECURITY_ENABLE_ALERTS', true),

    // Enable country restrictions
    'enable_country_restrictions' => env('SECURITY_ENABLE_COUNTRY_RESTRICTIONS', true),

    // Enable disposable email blocking
    'enable_disposable_email_blocking' => env('SECURITY_ENABLE_DISPOSABLE_EMAIL_BLOCKING', true),
];
