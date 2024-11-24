<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241123151734 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'migrate "isSpecial" to "type"';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE play_date ADD type VARCHAR(255) DEFAULT "regular"');
        $this->addSql('UPDATE play_date SET `type`="special" WHERE is_special=1');
        $this->addSql('UPDATE play_date SET type="training" WHERE title="Training"');
        $this->addSql('CREATE INDEX type_idx ON play_date (type)');

        $this->addSql('ALTER TABLE play_date DROP COLUMN `is_special`, CHANGE type type VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE play_date ADD is_special TINYINT(1) DEFAULT 1');
        $this->addSql('UPDATE play_date SET is_special=0 WHERE type="regular"');

        $this->addSql('ALTER TABLE play_date DROP COLUMN `type`, CHANGE is_special is_special TINYINT(1) NOT NULL');
    }
}
