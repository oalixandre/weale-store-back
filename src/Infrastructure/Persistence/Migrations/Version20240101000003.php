<?php

declare(strict_types=1);

namespace Weale\Infrastructure\Persistence\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240101000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create orders table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE orders (
                id                  VARCHAR(36)  NOT NULL,
                user_id             VARCHAR(36)  NOT NULL,
                items               JSONB        NOT NULL DEFAULT \'[]\',
                status              VARCHAR(20)  NOT NULL DEFAULT \'pending\',
                total_amount_cents  INTEGER      NOT NULL,
                total_currency      VARCHAR(3)   NOT NULL DEFAULT \'BRL\',
                created_at          TIMESTAMP(0) NOT NULL,
                updated_at          TIMESTAMP(0) NOT NULL,
                PRIMARY KEY(id),
                CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users (id)
            )
        ');
        $this->addSql('CREATE INDEX idx_orders_user_id  ON orders (user_id)');
        $this->addSql('CREATE INDEX idx_orders_status   ON orders (status)');
        $this->addSql('CREATE INDEX idx_orders_created  ON orders (created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE orders');
    }
}
