<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$request = Illuminate\Http\Request::create('/');
$request->setLaravelSession($app->make('session')->driver());
$app->instance('request', $request);
Illuminate\Support\Facades\View::share('errors', new Illuminate\Support\ViewErrorBag());

$page = $argv[1] ?? '';
if ($page === '2fa-verify') {
    echo view('auth.2fa-verify')->render();
} elseif ($page === '2fa-setup') {
    echo view('auth.2fa-setup', [
        'qrCodeInline' => 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200"><rect width="200" height="200" fill="#fff"/><text x="100" y="105" text-anchor="middle" font-size="16">QR preview</text></svg>'),
        'secret' => 'PREVIEWONLY',
    ])->render();
} else {
    fwrite(STDERR, "Unknown preview page\n");
    exit(1);
}
