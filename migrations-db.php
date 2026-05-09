<?php

declare(strict_types=1);

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\DBAL\DriverManager;
use Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$isDevMode = ($_ENV['APP_ENV'] ?? 'production') !== 'production';

$config = ORMSetup::createAttributeMetadataConfiguration(
    paths: [__DIR__ . '/src/Infrastructure/Persistence/Entities'],
    isDevMode: $isDevMode,
);

$connection = DriverManager::getConnection([
    'driver'   => 'pdo_pgsql',
    'host'     => $_ENV['DB_HOST'] ?? 'db',
    'port'     => (int) ($_ENV['DB_PORT'] ?? 5432),
    'dbname'   => $_ENV['DB_DATABASE'] ?? 'weale_store',
    'user'     => $_ENV['DB_USERNAME'] ?? 'weale',
    'password' => $_ENV['DB_PASSWORD'] ?? 'secret',
]);

$entityManager = new EntityManager($connection, $config);

$migrationConfig = new PhpFile(__DIR__ . '/config/migrations.php');

return DependencyFactory::fromEntityManager($migrationConfig, new ExistingEntityManager($entityManager));
