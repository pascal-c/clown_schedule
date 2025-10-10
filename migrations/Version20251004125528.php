<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251004125528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE config ADD points_per_missing_person INT DEFAULT 100 NOT NULL, ADD points_per_maybe_person INT DEFAULT 1 NOT NULL, ADD points_per_target_shifts INT DEFAULT 2 NOT NULL, ADD points_per_max_per_week INT DEFAULT 10 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE config DROP points_per_missing_person, DROP points_per_maybe_person, DROP points_per_target_shifts, DROP points_per_max_per_week');
    }
}
