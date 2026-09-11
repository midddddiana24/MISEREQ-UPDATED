<?php

return [
    'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 30),
    'secondary_path' => env('BACKUP_SECONDARY_PATH'),
    'mysqldump_path' => env('MYSQLDUMP_PATH'),
    'mysql_path' => env('MYSQL_PATH'),
];
