<?php

declare(strict_types=1);

namespace Weale\Infrastructure\Persistence\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240101000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create products table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE products (
                id                  VARCHAR(36)      NOT NULL,
                name                VARCHAR(255)     NOT NULL,
                description         TEXT             NOT NULL DEFAULT \'\',
                price_amount_cents  INTEGER          NOT NULL,
                price_currency      VARCHAR(3)       NOT NULL DEFAULT \'BRL\',
                stock               INTEGER          NOT NULL DEFAULT 0,
                category            VARCHAR(100)     NOT NULL,
                active              BOOLEAN          NOT NULL DEFAULT TRUE,
                created_at          TIMESTAMP(0)     NOT NULL,
                updated_at          TIMESTAMP(0)     NOT NULL,
                PRIMARY KEY(id)
            )
        ');

        $this->addSql('CREATE INDEX idx_products_category ON products (category)');
        $this->addSql('CREATE INDEX idx_products_active   ON products (active)');
        $this->addSql('CREATE INDEX idx_products_created  ON products (created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE products');
    }
}
