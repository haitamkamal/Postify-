<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250421092452 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE discussion (id SERIAL NOT NULL, sender_id INT NOT NULL, receiver_id INT DEFAULT NULL, message TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C0B9F90FF624B39D ON discussion (sender_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C0B9F90FCD53EDB6 ON discussion (receiver_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN discussion.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE discussion ADD CONSTRAINT FK_C0B9F90FF624B39D FOREIGN KEY (sender_id) REFERENCES members (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE discussion ADD CONSTRAINT FK_C0B9F90FCD53EDB6 FOREIGN KEY (receiver_id) REFERENCES members (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE discussion DROP CONSTRAINT FK_C0B9F90FF624B39D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE discussion DROP CONSTRAINT FK_C0B9F90FCD53EDB6
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE discussion
        SQL);
    }
}
