<?php

declare(strict_types=1);

namespace Weale\Tests\Helpers;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use PHPUnit\Framework\TestCase;

abstract class DatabaseTestCase extends TestCase
{
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = $this->buildEntityManager();
        $this->runMigrations();
        $this->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->rollbackTransaction();
        $this->em->close();
        parent::tearDown();
    }

    private function buildEntityManager(): EntityManagerInterface
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths:     [dirname(__DIR__, 2) . '/src/Infrastructure/Persistence/Entities'],
            isDevMode: true,
        );

        $connection = DriverManager::getConnection([
            'driver'   => 'pdo_pgsql',
            'host'     => $_ENV['DB_HOST']     ?? 'db_test',
            'port'     => (int) ($_ENV['DB_PORT'] ?? 5432),
            'dbname'   => $_ENV['DB_DATABASE']  ?? 'weale_store_test',
            'user'     => $_ENV['DB_USERNAME']  ?? 'weale',
            'password' => $_ENV['DB_PASSWORD']  ?? 'secret',
        ]);

        return new EntityManager($connection, $config);
    }

    private function runMigrations(): void
    {
        $migrationsConfig = new PhpFile(dirname(__DIR__, 2) . '/config/migrations.php');
        $factory          = DependencyFactory::fromEntityManager(
            $migrationsConfig,
            new ExistingEntityManager($this->em)
        );

        $factory->getMigrator()->migrate();
    }

    private function beginTransaction(): void
    {
        $this->em->getConnection()->beginTransaction();
    }

    private function rollbackTransaction(): void
    {
        if ($this->em->getConnection()->isTransactionActive()) {
            $this->em->getConnection()->rollBack();
        }
    }
}
