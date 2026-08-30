<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

config(['database.connections.mysql.database' => null]);

Illuminate\Support\Facades\DB::statement(
    'CREATE DATABASE IF NOT EXISTS reservasi_kampus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
);

echo "Database 'reservasi_kampus' siap.\n";
