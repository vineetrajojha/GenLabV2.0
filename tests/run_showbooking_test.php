<?php
// Quick test runner for showBookingApi (invokes controller directly)
chdir(__DIR__ . '/../');
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

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
$response = $controller->showBookingApi($request, 'MKT001');

if ($response instanceof Illuminate\Http\JsonResponse) {
    echo $response->getContent();
} else {
    echo json_encode($response);
}
