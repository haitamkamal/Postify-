<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250422090416 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_banned and ban_until columns to members table';
    }

    public function up(Schema $schema): void
    {
        // Add the is_banned column with a default value of false
        $this->addSql(<<<'SQL'
            ALTER TABLE members ADD is_banned BOOLEAN NOT NULL DEFAULT false
        SQL);

        // Add the ban_until column with a default value of NULL
        $this->addSql(<<<'SQL'
            ALTER TABLE members ADD ban_until TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // Remove the is_banned and ban_until columns
        $this->addSql(<<<'SQL'
            ALTER TABLE members DROP is_banned
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE members DROP ban_until
        SQL);
    }
}
