<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251124174357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE play_date DROP FOREIGN KEY `FK_A19BC9ABBFAF4E02`');
        $this->addSql('ALTER TABLE play_date ADD CONSTRAINT FK_A19BC9ABBFAF4E02 FOREIGN KEY (moved_to_id) REFERENCES play_date (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE play_date DROP FOREIGN KEY FK_A19BC9ABBFAF4E02');
        $this->addSql('ALTER TABLE play_date ADD CONSTRAINT `FK_A19BC9ABBFAF4E02` FOREIGN KEY (moved_to_id) REFERENCES play_date (id)');
    }
}
