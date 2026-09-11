<?php

return [
    'force_https' => (bool) env('FORCE_HTTPS', true),

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Configure security features for your application
    |
    */

    'password' => [
        // Minimum password length
        'min_length' => 12,

        // Password expiration (in days)
        'expiration_days' => 90,

        // Password history - prevent reuse of last N passwords
        'history_limit' => 5,

        // Require uppercase letters
        'require_uppercase' => true,

        // Require lowercase letters
        'require_lowercase' => true,

        // Require numbers
        'require_numbers' => true,

        // Require special characters
        'require_special_chars' => true,

        // Disallow consecutive/repeated characters
        'disallow_repeated_chars' => true,
    ],

    'authentication' => [
        // Maximum failed login attempts before lockout
        'max_failed_attempts' => 5,

        // Account lockout duration (in minutes)
        'lockout_duration' => 30,

        // Login rate limiting (per hour)
        'rate_limit_per_hour' => 10,
    ],

    'session' => [
        // Session lifetime (in minutes)
        'lifetime' => 30,

        // Expire session when browser closes
        'expire_on_close' => true,

        // Encrypt session data
        'encrypt' => true,

        // Validate session IP address
        'validate_ip' => true,

        // Validate session user agent
        'validate_user_agent' => true,
    ],

    '2fa' => [
        // Enable 2FA globally
        'enabled' => true,

        // Require 2FA for admin users
        'require_for_admin' => true,

        // Require 2FA for IT staff
        'require_for_it_staff' => true,

        // 2FA code validity (in minutes)
        'code_expiration' => 5,

        // Recovery codes to generate
        'recovery_codes_count' => 8,
    ],

    'security_headers' => [
        // Enable HSTS (HTTP Strict Transport Security)
        'hsts_enabled' => true,
        'hsts_max_age' => 31536000, // 1 year
        'hsts_include_subdomains' => true,

        // Enable X-Frame-Options
        'x_frame_options' => 'DENY',

        // Enable X-Content-Type-Options
        'x_content_type_options' => 'nosniff',

        // Enable X-XSS-Protection
        'x_xss_protection' => '1; mode=block',

        // Content Security Policy
        'csp_enabled' => true,
    ],

    'audit' => [
        // Log login attempts
        'log_login_attempts' => true,

        // Log password changes
        'log_password_changes' => true,

        // Log access to sensitive data
        'log_sensitive_access' => true,

        // Retention period for logs (in days)
        'log_retention_days' => 90,
    ],
];
