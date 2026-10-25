<?php

declare(strict_types=1);

use Gaby\Shared\Config\Env;

require __DIR__ . '/vendor/autoload.php';

$dbPath = Env::get('DB_PATH', 'storage/database.sqlite');

if ($dbPath === null || $dbPath === '') {
    $dbPath = 'storage/database.sqlite';
}

if (!preg_match('#^(?:[A-Za-z]:[\\\\/]|[\\\\/])#', $dbPath)) {
    $dbPath = __DIR__ . DIRECTORY_SEPARATOR . ltrim($dbPath, '/\\');
}

return [
    'paths' => [
        'migrations' => __DIR__ . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'development' => [
            'adapter' => 'sqlite',
            'name' => $dbPath,
            'suffix' => '',
        ],
    ],
];
