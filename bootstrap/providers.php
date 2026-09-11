<?php

use App\Providers\AppServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\AuthServiceProvider;
use Illuminate\Auth\Passwords\PasswordResetServiceProvider;
use Illuminate\Broadcasting\BroadcastServiceProvider;
use Illuminate\Bus\BusServiceProvider;
use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Cookie\CookieServiceProvider;
use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Encryption\EncryptionServiceProvider;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Foundation\Providers\ConsoleSupportServiceProvider;
use Illuminate\Foundation\Providers\FoundationServiceProvider;
use Illuminate\Hashing\HashServiceProvider;
use Illuminate\Log\LogServiceProvider;
use Illuminate\Mail\MailServiceProvider;
use Illuminate\Notifications\NotificationServiceProvider;
use Illuminate\Pagination\PaginationServiceProvider;
use Illuminate\Pipeline\PipelineServiceProvider;
use Illuminate\Queue\QueueServiceProvider;
use Illuminate\Redis\RedisServiceProvider;
use Illuminate\Session\SessionServiceProvider;
use Illuminate\Translation\TranslationServiceProvider;
use Illuminate\Validation\ValidationServiceProvider;
use Illuminate\View\ViewServiceProvider;

return [
    // Laravel Framework Service Providers
    AuthServiceProvider::class,
    PasswordResetServiceProvider::class,
    BroadcastServiceProvider::class,
    BusServiceProvider::class,
    CacheServiceProvider::class,
    DatabaseServiceProvider::class,
    CookieServiceProvider::class,
    EncryptionServiceProvider::class,
    FilesystemServiceProvider::class,
    FoundationServiceProvider::class,
    ConsoleSupportServiceProvider::class,
    LogServiceProvider::class,
    HashServiceProvider::class,
    MailServiceProvider::class,
    NotificationServiceProvider::class,
    PaginationServiceProvider::class,
    PipelineServiceProvider::class,
    QueueServiceProvider::class,
    RedisServiceProvider::class,
    SessionServiceProvider::class,
    TranslationServiceProvider::class,
    ValidationServiceProvider::class,
    ViewServiceProvider::class,

    // Application Service Providers
    AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    EventServiceProvider::class,
    RouteServiceProvider::class,
    App\Providers\BroadcastServiceProvider::class,
];
