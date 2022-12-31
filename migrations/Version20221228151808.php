<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221228151808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clown ADD email VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE clown ADD password VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE clown ADD is_admin BOOLEAN NOT NULL DEFAULT FALSE');
        $this->addSql('ALTER TABLE clown ADD is_active BOOLEAN NOT NULL DEFAULT TRUE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE clown DROP email');
        $this->addSql('ALTER TABLE clown DROP password');
        $this->addSql('ALTER TABLE clown DROP is_admin');
        $this->addSql('ALTER TABLE clown DROP is_active');
    }
}
