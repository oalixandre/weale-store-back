<?php

declare(strict_types=1);

namespace Weale\Infrastructure\Persistence\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240101000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create users table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE users (
                id            VARCHAR(36)  NOT NULL,
                name          VARCHAR(255) NOT NULL,
                email         VARCHAR(255) NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                role          VARCHAR(20)  NOT NULL DEFAULT \'customer\',
                active        BOOLEAN      NOT NULL DEFAULT TRUE,
                created_at    TIMESTAMP(0) NOT NULL,
                updated_at    TIMESTAMP(0) NOT NULL,
                PRIMARY KEY(id),
                CONSTRAINT uq_users_email UNIQUE (email)
            )
        ');
        $this->addSql('CREATE INDEX idx_users_email  ON users (email)');
        $this->addSql('CREATE INDEX idx_users_active ON users (active)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE users');
    }
}
