<?php

return [
    'directory' => env('BACKUP_DIRECTORY', storage_path('app/backups')),
    'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 30),
    'secondary_path' => env('BACKUP_SECONDARY_PATH'),
    'mysqldump_path' => env('MYSQLDUMP_PATH'),
    'mysql_path' => env('MYSQL_PATH'),
    'health_url' => env('BACKUP_HEALTH_URL', rtrim((string) env('APP_URL', 'http://localhost'), '/').'/health'),
];
