<?php

use App\Support\SentryBeforeSend;

$appEnvironment = env('APP_ENV', 'production');
$statusCheckEnabled = (bool) env('SENTRY_STATUS_CHECK_ENABLED', false);
$sentryEnabled = in_array($appEnvironment, ['production', 'staging'], true)
    ? ! env('APP_DEBUG', false)
    : $statusCheckEnabled && in_array($appEnvironment, ['local', 'testing'], true);

return [
    'dsn' => $sentryEnabled ? env('SENTRY_LARAVEL_DSN') : null,
    'release' => env('SENTRY_RELEASE'),
    'environment' => env('SENTRY_ENVIRONMENT', $appEnvironment),
    'sample_rate' => (float) env('SENTRY_SAMPLE_RATE', 1.0),
    'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE') === null
        ? null
        : (float) env('SENTRY_TRACES_SAMPLE_RATE'),
    'send_default_pii' => false,
    'before_send' => [SentryBeforeSend::class, 'handle'],
    'ignore_transactions' => [
        '/up',
    ],
];
