<?php
// Quick test runner for clientProfileApi (invokes controller directly)
chdir(__DIR__ . '/../');
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\MobileControllers\Accounts\MarketingPersonInfo;

$request = Request::create('/dummy', 'GET', []);

$controller = new MarketingPersonInfo();
try {
    // Adjust client id as appropriate for your DB
    $response = $controller->clientProfileApi($request, 'MKT001', 1);
    if ($response instanceof Illuminate\Http\JsonResponse) {
        echo $response->getContent();
    } else {
        echo json_encode($response);
    }
} catch (Exception $e) {
    echo json_encode(['status' => false, 'message' => $e->getMessage()]);
}
