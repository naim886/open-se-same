<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'naim886@gmail.com')->first();
echo "Current hash: {$user->password}\n";

if (($argv[1] ?? '') === 'set') {
    $user->password = Illuminate\Support\Facades\Hash::make('TempQA#2026!');
    $user->save();
    echo "Password set to temp value.\n";
} elseif (($argv[1] ?? '') === 'restore' && isset($argv[2])) {
    $user->password = $argv[2];
    $user->save();
    echo "Password restored.\n";
}
