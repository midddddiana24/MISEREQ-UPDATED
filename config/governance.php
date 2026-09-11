<?php

return [
    'login_audit_retention_days' => (int) env('LOGIN_AUDIT_RETENTION_DAYS', 365),
    'revoked_session_retention_days' => (int) env('REVOKED_SESSION_RETENTION_DAYS', 90),
    'notification_retention_days' => (int) env('NOTIFICATION_RETENTION_DAYS', 730),
];
