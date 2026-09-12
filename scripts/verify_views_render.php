<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\ViewErrorBag;

/**
 * Render smoke test for modified view files (Item 10 verification).
 * Verifies each view compiles without errors after a11y edits.
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

// The $errors shared variable is registered by the ShareErrorsFromSession
// middleware during HTTP requests; provide an empty bag for CLI rendering.
view()->share('errors', new ViewErrorBag);

$views = [
    'layouts.app',
    'layouts.auth',
    'layouts.navbar',
    'partials.header',
    'partials.public-header',
    'user.partials.navigation',
    'user.partials.theme',
    'welcome',
    'admin.layout',
];

$failures = 0;
foreach ($views as $view) {
    try {
        $html = view($view, ['user' => null])->render();
        echo $view.': OK ('.strlen($html)." bytes)\n";
    } catch (Throwable $e) {
        echo $view.': FAIL - '.$e->getMessage()."\n";
        $failures++;
    }
}

exit($failures ? 1 : 0);
