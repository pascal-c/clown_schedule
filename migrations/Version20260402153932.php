<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260402153932 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE play_date DROP FOREIGN KEY `FK_A19BC9ABF1FAD9D3`');
        $this->addSql('ALTER TABLE play_date ADD CONSTRAINT FK_A19BC9ABF1FAD9D3 FOREIGN KEY (bundle_id) REFERENCES play_date_bundle (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE play_date DROP FOREIGN KEY FK_A19BC9ABF1FAD9D3');
        $this->addSql('ALTER TABLE play_date ADD CONSTRAINT `FK_A19BC9ABF1FAD9D3` FOREIGN KEY (bundle_id) REFERENCES play_date_bundle (id)');
    }
}
