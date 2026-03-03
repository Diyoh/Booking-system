<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/halls', 'GET')
);

echo "Halls Page Status Code: " . $response->getStatusCode() . "\n";

$response2 = $kernel->handle(
    $request2 = Illuminate\Http\Request::create('/events', 'GET')
);

echo "Events Page Status Code: " . $response2->getStatusCode() . "\n";
