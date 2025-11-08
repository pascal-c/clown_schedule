<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251103170458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE config CHANGE points_per_missing_person points_per_missing_person INT DEFAULT 1000 NOT NULL, CHANGE points_per_maybe_person points_per_maybe_person INT DEFAULT 10 NOT NULL, CHANGE points_per_target_shifts points_per_target_shifts INT DEFAULT 20 NOT NULL, CHANGE points_per_max_per_week points_per_max_per_week INT DEFAULT 100 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE config CHANGE points_per_missing_person points_per_missing_person INT DEFAULT 100 NOT NULL, CHANGE points_per_maybe_person points_per_maybe_person INT DEFAULT 1 NOT NULL, CHANGE points_per_target_shifts points_per_target_shifts INT DEFAULT 2 NOT NULL, CHANGE points_per_max_per_week points_per_max_per_week INT DEFAULT 10 NOT NULL');
    }
}
