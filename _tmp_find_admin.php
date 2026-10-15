<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::whereHas('roles', function($q){
    $q->whereIn('name', ['Super Admin','Admin']);
})->first();

if ($user) {
    echo "Found: {$user->email}\n";
} else {
    echo "No admin user found\n";
    echo "Total users: " . App\Models\User::count() . "\n";
}
