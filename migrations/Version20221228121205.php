<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221228121205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clown_availability_time ALTER date TYPE DATE');
        $this->addSql('COMMENT ON COLUMN clown_availability_time.date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE play_date ALTER date TYPE DATE');
        $this->addSql('COMMENT ON COLUMN play_date.date IS \'(DC2Type:date_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE clown_availability_time ALTER date TYPE DATE');
        $this->addSql('COMMENT ON COLUMN clown_availability_time.date IS NULL');
        $this->addSql('ALTER TABLE play_date ALTER date TYPE DATE');
        $this->addSql('COMMENT ON COLUMN play_date.date IS NULL');
    }
}
