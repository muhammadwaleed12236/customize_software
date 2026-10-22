<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Narration;

echo "Columns in narrations table:\n";
print_r(Schema::getColumnListing('narrations'));

$details = DB::select("DESCRIBE narrations");
foreach ($details as $col) {
    echo "{$col->Field} | {$col->Type} | Null: {$col->Null} | Default: {$col->Default}\n";
}

echo "\nLatest narrations:\n";
$latest = Narration::latest()->take(5)->get();
print_r($latest->toArray());

echo "\nTesting Narration creation:\n";
try {
    $n = Narration::create([
        'expense_head' => 'Receipts Voucher',
        'narration'    => 'Test Narration ' . time(),
    ]);
    echo "Created narration ID: {$n->id}\n";
    $n->delete();
    echo "Deleted test narration successfully.\n";
} catch (\Throwable $e) {
    echo "Error creating narration: " . $e->getMessage() . "\n";
}
