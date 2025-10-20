<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251020154852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE clown_venue_preference (id INT AUTO_INCREMENT NOT NULL, preference VARCHAR(100) NOT NULL, clown_id INT NOT NULL, venue_id INT NOT NULL, INDEX IDX_6C3DA9F087F943E2 (clown_id), INDEX IDX_6C3DA9F040A73EBA (venue_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = INNODB');
        $this->addSql('ALTER TABLE clown_venue_preference ADD CONSTRAINT FK_6C3DA9F087F943E2 FOREIGN KEY (clown_id) REFERENCES clown (id)');
        $this->addSql('ALTER TABLE clown_venue_preference ADD CONSTRAINT FK_6C3DA9F040A73EBA FOREIGN KEY (venue_id) REFERENCES venue (id)');
        $this->addSql('ALTER TABLE config ADD points_per_preference_worst INT DEFAULT 10 NOT NULL, ADD points_per_preference_worse INT DEFAULT 4 NOT NULL, ADD points_per_preference_ok INT DEFAULT 2 NOT NULL, ADD points_per_preference_better INT DEFAULT 1 NOT NULL, ADD points_per_preference_best INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clown_venue_preference DROP FOREIGN KEY FK_6C3DA9F087F943E2');
        $this->addSql('ALTER TABLE clown_venue_preference DROP FOREIGN KEY FK_6C3DA9F040A73EBA');
        $this->addSql('DROP TABLE clown_venue_preference');
        $this->addSql('ALTER TABLE config DROP points_per_preference_worst, DROP points_per_preference_worse, DROP points_per_preference_ok, DROP points_per_preference_better, DROP points_per_preference_best');
    }
}
