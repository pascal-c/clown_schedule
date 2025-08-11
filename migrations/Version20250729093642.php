<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250729093642 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE recurring_date (id INT AUTO_INCREMENT NOT NULL, daytime VARCHAR(3) NOT NULL, meeting_time TIME NOT NULL, play_time_from TIME NOT NULL, play_time_to TIME NOT NULL, is_super TINYINT(1) NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, rhythm VARCHAR(10) NOT NULL, day_of_week VARCHAR(10) NOT NULL, every SMALLINT NOT NULL, venue_id INT DEFAULT NULL, INDEX IDX_5739A6B40A73EBA (venue_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = INNODB');
        $this->addSql('ALTER TABLE recurring_date ADD CONSTRAINT FK_5739A6B40A73EBA FOREIGN KEY (venue_id) REFERENCES venue (id)');
        $this->addSql('ALTER TABLE play_date ADD recurring_date_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE play_date ADD CONSTRAINT FK_A19BC9AB6061A3E2 FOREIGN KEY (recurring_date_id) REFERENCES recurring_date (id)');
        $this->addSql('CREATE INDEX IDX_A19BC9AB6061A3E2 ON play_date (recurring_date_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recurring_date DROP FOREIGN KEY FK_5739A6B40A73EBA');
        $this->addSql('DROP TABLE recurring_date');
        $this->addSql('ALTER TABLE play_date DROP FOREIGN KEY FK_A19BC9AB6061A3E2');
        $this->addSql('DROP INDEX IDX_A19BC9AB6061A3E2 ON play_date');
        $this->addSql('ALTER TABLE play_date DROP recurring_date_id');
    }
}
