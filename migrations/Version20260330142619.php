<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260330142619 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE play_date_bundle (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('ALTER TABLE play_date ADD bundle_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE play_date ADD CONSTRAINT FK_A19BC9ABF1FAD9D3 FOREIGN KEY (bundle_id) REFERENCES play_date_bundle (id)');
        $this->addSql('CREATE INDEX IDX_A19BC9ABF1FAD9D3 ON play_date (bundle_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE play_date_bundle');
        $this->addSql('ALTER TABLE play_date DROP FOREIGN KEY FK_A19BC9ABF1FAD9D3');
        $this->addSql('DROP INDEX IDX_A19BC9ABF1FAD9D3 ON play_date');
        $this->addSql('ALTER TABLE play_date DROP bundle_id');
    }
}
