<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

return [
    'driver'   => 'pdo_pgsql',
    'host'     => $_ENV['DB_HOST'] ?? 'db',
    'port'     => (int) ($_ENV['DB_PORT'] ?? 5432),
    'dbname'   => $_ENV['DB_DATABASE'] ?? 'weale_store',
    'user'     => $_ENV['DB_USERNAME'] ?? 'weale',
    'password' => $_ENV['DB_PASSWORD'] ?? 'secret',
];
