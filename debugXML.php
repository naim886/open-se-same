<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$req = request();
$req->merge(["key" => config("services.google_merchant.feed_key")]);
try {
    $c = app("App\Http\Controllers\Api\GoogleMerchantFeedController");
    $xml = $c->xml($req)->getContent();
    echo substr($xml, 0, 200);
} catch (\Throwable $e) {
    echo "THROWN: " . get_class($e) . "\n" . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine();
}

