<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221120140521 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clown_availability_time ADD clown_availability_id INT NOT NULL');
        $this->addSql('ALTER TABLE clown_availability_time ADD CONSTRAINT FK_2E2787C26782F72E FOREIGN KEY (clown_availability_id) REFERENCES clown_availability (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_2E2787C26782F72E ON clown_availability_time (clown_availability_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE clown_availability_time DROP CONSTRAINT FK_2E2787C26782F72E');
        $this->addSql('DROP INDEX IDX_2E2787C26782F72E');
        $this->addSql('ALTER TABLE clown_availability_time DROP clown_availability_id');
    }
}
