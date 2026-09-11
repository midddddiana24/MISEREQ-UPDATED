<?php

/**
 * Input Validation & Data Protection Configuration
 *
 * Configure validation, sanitization, and file upload security settings
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Input Validation Settings
    |--------------------------------------------------------------------------
    |
    | Configure global input validation rules and behaviors
    |
    */

    'validation' => [
        // Enable strict input validation
        'strict' => true,

        // Reject unknown fields in form requests
        'reject_unknown_fields' => false,

        // Max input size (in kilobytes)
        'max_input_size' => 10240,
    ],

    /*
    |--------------------------------------------------------------------------
    | Sanitization Settings
    |--------------------------------------------------------------------------
    |
    | Configure input sanitization behavior
    |
    */

    'sanitization' => [
        // Enable automatic input sanitization
        'enabled' => true,

        // Trim all string inputs
        'trim' => true,

        // Remove null bytes
        'remove_null_bytes' => true,

        // Remove control characters
        'remove_control_characters' => true,

        // Convert encoding to UTF-8
        'enforce_utf8' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | XSS Protection
    |--------------------------------------------------------------------------
    |
    | Configure Cross-Site Scripting (XSS) protection
    |
    */

    'xss_protection' => [
        // Enable XSS header
        'header_x_xss_protection' => '1; mode=block',

        // Enable X-Content-Type-Options
        'no_sniff' => true,

        // Frame options for clickjacking protection
        'frame_options' => 'DENY',

        // Content Security Policy enabled
        'csp_enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | CSRF Protection
    |--------------------------------------------------------------------------
    |
    | Configure CSRF token settings
    |
    */

    'csrf' => [
        // Enable CSRF protection
        'enabled' => true,

        // CSRF token field name
        'field_name' => '_token',

        // Token lifetime (in minutes)
        'lifetime' => 120,

        // Excluded paths from CSRF protection
        'except' => [
            // Add API routes that don't need CSRF
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    |
    | Configure secure file upload settings
    |
    */

    'file_upload' => [
        // Max file size (in megabytes)
        'max_size_mb' => 10,

        // Storage disk (private/secure)
        'storage_disk' => 'secure',

        // Allowed file extensions
        'allowed_extensions' => [
            'pdf',
            'doc',
            'docx',
            'xls',
            'xlsx',
            'jpg',
            'jpeg',
            'png',
            'gif',
        ],

        // Allowed MIME types
        'allowed_mimes' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/png',
            'image/gif',
        ],

        // Scan for malware
        'malware_scan_enabled' => true,

        // Quarantine suspicious files for analysis
        'quarantine_enabled' => true,

        // Store uploads outside web root
        'outside_web_root' => true,

        // Generate random filenames
        'random_filename' => true,

        // Log all file uploads
        'audit_enabled' => true,

        // Cleanup old files (days until deletion)
        'cleanup_after_days' => 90,
    ],

    /*
    |--------------------------------------------------------------------------
    | SQL Injection Prevention
    |--------------------------------------------------------------------------
    |
    | Laravel uses Eloquent ORM and prepared statements by default.
    | NEVER use raw queries with user input. Use:
    | - Eloquent ORM (all queries)
    | - Query Builder with bindings
    | - Prepared statements
    |
    */

    'sql_injection' => [
        // Disable raw queries in production
        'allow_raw_queries' => env('APP_DEBUG', false),

        // Log suspicious queries
        'log_suspicious_queries' => true,

        // Pattern to detect potentially dangerous queries
        'dangerous_patterns' => [
            'UNION',
            'SELECT.*FROM',
            'INSERT.*INTO',
            'UPDATE.*SET',
            'DELETE.*FROM',
            'DROP.*TABLE',
            'ALTER.*TABLE',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Blade Template Security
    |--------------------------------------------------------------------------
    |
    | Configure Blade templating security
    |
    | Usage:
    | - {{ $variable }} - AUTO-ESCAPED (safe from XSS)
    | - {!! $variable !!} - NOT ESCAPED (only for trusted HTML)
    | - @can/@cannot - Authorization checks
    | - @csrf - CSRF token field
    |
    */

    'blade' => [
        // Always escape by default in {{ }}
        'auto_escape' => true,

        // Warn about unescaped output
        'warn_unescaped' => true,

        // Safe HTML tags (only used with {!! !!})
        'safe_tags' => [
            'strong',
            'em',
            'b',
            'i',
            'u',
            'br',
            'p',
            'a',
            'img',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Input Validation Rules
    |--------------------------------------------------------------------------
    |
    | Common validation patterns for different input types
    |
    */

    'patterns' => [
        // Usernames: alphanumeric, hyphens, underscores
        'username' => '/^[a-zA-Z0-9_\-\.]+$/',

        // Full names: letters, spaces, hyphens, apostrophes
        'full_name' => '/^[a-zA-Z\s\-\']+$/',

        // Phone numbers: digits, plus, hyphen, parentheses, spaces
        'phone' => '/^[\d\+\-\(\)\s]+$/',

        // Email: standard email format
        'email' => '/^[^\s@]+@[^\s@]+\.[^\s@]+$/',

        // URL: standard URL format
        'url' => '/^https?:\/\/.+\..+/',

        // IP Address: IPv4 or IPv6
        'ip' => '/^(\d{1,3}\.){3}\d{1,3}$|^([0-9a-f]{1,4}:){7}[0-9a-f]{1,4}$/i',
    ],
];
