<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $controller = new App\Http\Controllers\Api\Admin\DashboardController();
    $response = $controller->index(request());
    echo "SUCCESS\n";
} catch (\Throwable $e) {
    echo "ERROR:\n";
    echo $e->getMessage() . "\n";
    echo $e->getFile() . " at line " . $e->getLine() . "\n";
}
