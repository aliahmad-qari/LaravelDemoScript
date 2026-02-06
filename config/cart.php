<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cart Configuration
    |--------------------------------------------------------------------------
    |
    | Configure cart and abandoned cart settings.
    |
    */

    // Time in minutes before a cart is considered abandoned
    'abandoned_threshold' => env('CART_ABANDONED_THRESHOLD', 60),

    // Time in minutes after abandonment before sending reminder
    'reminder_delay' => env('CART_REMINDER_DELAY', 120),

    // Enable abandoned cart reminders
    'enable_reminders' => env('CART_ENABLE_REMINDERS', true),
];
