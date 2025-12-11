<?php
// Quick test runner for bookingByLetter API (invokes controller directly)
chdir(__DIR__ . '/../');
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

// Bootstrap the kernel to get container
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\MobileControllers\Accounts\MarketingPersonInfo;

$request = Request::create('/dummy', 'GET', [
    'search' => null,
    'month' => null,
    'year' => null,
    'perPage' => 25,
]);

$controller = new MarketingPersonInfo();
$response = $controller->bookingByLetter($request, 'MKT001');

// If it's a JsonResponse, output its data
if ($response instanceof Illuminate\Http\JsonResponse) {
    echo $response->getContent();
} else {
    // Try to json_encode whatever returned
    echo json_encode($response);
}
