<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

$user = User::first();
Auth::login($user);

$request = Request::create('/store-narration-ajax', 'POST', [
    'narration' => 'Test via HTTP Kernel',
    '_token' => csrf_token(),
]);
$request->headers->set('X-Requested-With', 'XMLHttpRequest');
$request->headers->set('Accept', 'application/json');

$response = $kernel->handle($request);
echo "Status Code: " . $response->getStatusCode() . "\n";
$content = json_decode($response->getContent(), true);
echo "Message: " . ($content['message'] ?? $response->getContent()) . "\n";
