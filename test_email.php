<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Mail\AccountApprovedMail;
use App\Mail\AccountRejectedMail;
use App\Models\User;

$user = User::latest()->first();
echo "User: {$user->email}\n\n";

$mails = [
    'AccountApprovedMail'  => fn() => new AccountApprovedMail($user),
    'AccountRejectedMail'  => fn() => new AccountRejectedMail($user, 'Insufficient documentation provided.'),
];
foreach ($mails as $name => $factory) {
    try {
        $len = strlen($factory()->render());
        echo "  [OK] $name ({$len}b)\n";
    } catch (\Throwable $e) {
        echo "  [FAIL] $name: " . $e->getMessage() . " at " . basename($e->getFile()) . ":" . $e->getLine() . "\n";
    }
}
