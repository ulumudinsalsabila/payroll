<?php

declare(strict_types=1);

// Vercel functions can write temporary files only under /tmp.
$storagePath = '/tmp/easykan-storage';
foreach (['framework/cache/data', 'framework/sessions', 'framework/views', 'logs', 'app'] as $directory) {
    if (! is_dir($storagePath.'/'.$directory)) {
        mkdir($storagePath.'/'.$directory, 0775, true);
    }
}

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->useStoragePath($storagePath);

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());
$response->send();
$kernel->terminate($request, $response);
