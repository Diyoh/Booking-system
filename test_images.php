<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Halls Images:\n";
$halls = \App\Models\Hall::all();
foreach ($halls as $hall) {
    echo "ID: " . $hall->id . " URL: " . $hall->image_url . "\n";
}

echo "\nEvents Images:\n";
$events = \App\Models\Event::all();
foreach ($events as $event) {
    echo "ID: " . $event->id . " URL: " . $event->image_url . "\n";
}
