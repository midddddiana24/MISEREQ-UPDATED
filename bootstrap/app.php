<?php

use App\Exceptions\Handler;
use App\Http\Kernel;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Application;

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| requests to your application, and console commands are used by your
| commands when running.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    ExceptionHandler::class,
    Handler::class
);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Register all service providers. We bootstrap config first
| by manually loading all config files.
|
*/

// Load and register configuration repository
$app->singleton('config', function ($app) {
    $config = new Repository;

    // Load all config files
    $configPath = __DIR__.'/../config';
    if (is_dir($configPath)) {
        foreach (glob($configPath.'/*.php') as $file) {
            $key = basename($file, '.php');
            $config->set($key, require $file);
        }
    }

    return $config;
});

$providers = require __DIR__.'/providers.php';

// Now register all providers
foreach ($providers as $provider) {
    $app->register($provider);
}

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
