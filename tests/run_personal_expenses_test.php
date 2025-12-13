<?php
// Quick test runner for personal expenses APIs (invokes controller directly)
chdir(__DIR__ . '/../');
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\MobileControllers\Accounts\MarketingPersonInfo;

$request = Request::create('/dummy', 'GET', [
    'perPage' => 10,
]);

$controller = new MarketingPersonInfo();
$response = $controller->personalExpensesListApi($request, 'MKT001');

if ($response instanceof Illuminate\Http\JsonResponse) {
    echo $response->getContent();
} else {
    echo json_encode($response);
}
