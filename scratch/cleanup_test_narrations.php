<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
App\Models\Narration::whereIn('id', [438, 439])->delete();
echo "Cleaned.\n";
