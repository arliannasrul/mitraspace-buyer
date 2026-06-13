<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "PAYMENT LOGS IN E-COMMERCE DATABASE:\n";
try {
    $logs = App\Models\PaymentLog::orderBy('created_at', 'desc')->take(10)->get();
    if ($logs->isEmpty()) {
        echo "No payment logs found.\n";
    }
    foreach ($logs as $log) {
        echo "ID: {$log->id} | Invoice: {$log->invoice_number} | Status: {$log->status} | Ref: {$log->doku_reference} | Seller Order: {$log->seller_order_number} | Created: {$log->created_at}\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
