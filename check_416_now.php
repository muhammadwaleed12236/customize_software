<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$ws = DB::table('warehouse_stocks')->where('product_id', 416)->get();
echo "Current warehouse_stocks for 416:\n";
foreach($ws as $w) {
    echo "ID: {$w->id} | wh: " . ($w->warehouse_id ?: 'shop') . " | variant_id: " . ($w->variant_id ?: 'null') . " | qty: {$w->quantity}\n";
}

$st = DB::table('stocks')->where('product_id', 416)->get();
echo "\nCurrent stocks for 416:\n";
foreach($st as $s) {
    echo "ID: {$s->id} | variant_id: " . ($s->variant_id ?: 'null') . " | qty: {$s->qty}\n";
}
