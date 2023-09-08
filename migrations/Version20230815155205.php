<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230815155205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clown_availability ADD scheduled_plays_month INT DEFAULT NULL, ADD scheduled_substitutions INT DEFAULT NULL');
        $this->addSql('UPDATE clown_availability SET scheduled_plays_month = calculated_plays_month, scheduled_substitutions = calculated_substitutions');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clown_availability DROP scheduled_plays_month, DROP scheduled_substitutions');
    }
}
