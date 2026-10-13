<?php
require __DIR__.'/vendor/autoload.php';
\ = require_once __DIR__.'/bootstrap/app.php';
\ = \->make(Illuminate\Contracts\Console\Kernel::class);
\->bootstrap();

\ = request();
\->merge(['key' => 'vV9svKJwTjChI8KoBP8ZFBkVCDBKI']);

try {
    \ = app(\App\Http\Controllers\Api\GoogleMerchantFeedController::class);
    \ = \->xml(\);
    echo "SUCCESS\n";
    echo substr(\->getContent(), 0, 500);
} catch (\Throwable \) {
    echo "ERROR: " . \->getMessage() . " on " . \->getFile() . ":" . \->getLine() . "\n";
}
