<?php

/**
 * DATABASE OPTIMIZATION CONFIGURATION
 * 
 * Improvements for handling bulk requests and improving query performance
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Database Connection Pooling
    |--------------------------------------------------------------------------
    */
    'pool' => [
        'min' => 5,
        'max' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | Query Optimization Settings
    |--------------------------------------------------------------------------
    */
    'query_cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Connection Timeout (seconds)
    |--------------------------------------------------------------------------
    */
    'timeout' => 60,

    /*
    |--------------------------------------------------------------------------
    | Enable Query Logging (disable in production)
    |--------------------------------------------------------------------------
    */
    'log_queries' => env('DB_LOG_QUERIES', false),

    /*
    |--------------------------------------------------------------------------
    | Eager Loading Configurations
    |--------------------------------------------------------------------------
    */
    'eager_load' => [
        'enabled' => true,
        'max_depth' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Batch Insert Optimization
    |--------------------------------------------------------------------------
    | Chunk size for batch operations
    */
    'batch_size' => 1000,

    /*
    |--------------------------------------------------------------------------
    | Index Optimization Tips
    |--------------------------------------------------------------------------
    | Add these indexes to your database for optimal performance:
    | 
    | Users table:
    |   - ALTER TABLE users ADD INDEX idx_email (email);
    |   - ALTER TABLE users ADD INDEX idx_username (username);
    |   - ALTER TABLE users ADD INDEX idx_office (office);
    |   - ALTER TABLE users ADD INDEX idx_role (role);
    |   - ALTER TABLE users ADD UNIQUE INDEX idx_unique_username (username);
    | 
    | Service Requests table:
    |   - ALTER TABLE service_requests ADD INDEX idx_user_id (user_id);
    |   - ALTER TABLE service_requests ADD INDEX idx_status (status);
    |   - ALTER TABLE service_requests ADD INDEX idx_created_at (created_at);
    |   - ALTER TABLE service_requests ADD INDEX idx_technician_id (technician_id);
    |   - ALTER TABLE service_requests ADD INDEX idx_priority_status (priority, status);
    | 
    | Messages table:
    |   - ALTER TABLE messages ADD INDEX idx_request_id (request_id);
    |   - ALTER TABLE messages ADD INDEX idx_sender_id (sender_id);
    |   - ALTER TABLE messages ADD INDEX idx_created_at (created_at);
    | 
    | Sessions table:
    |   - ALTER TABLE sessions ADD INDEX idx_user_id (user_id);
    |   - ALTER TABLE sessions ADD INDEX idx_last_activity (last_activity);
    */

];
