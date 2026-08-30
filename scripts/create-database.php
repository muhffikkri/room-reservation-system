<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

// Nama database dapat diberikan sebagai argumen: php scripts/create-database.php <nama>
$database = $argv[1] ?? 'reservasi_kampus';

config(['database.connections.mysql.database' => null]);

DB::statement(
    "CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
);

echo "Database '{$database}' siap.\n";
