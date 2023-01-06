<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221018151903 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE play_date_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE play_date (id INT NOT NULL, clown_id_id INT DEFAULT NULL, venue_id_id INT NOT NULL, date DATE NOT NULL, daytime VARCHAR(2) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A19BC9ABD3129707 ON play_date (clown_id_id)');
        $this->addSql('CREATE INDEX IDX_A19BC9ABA7FF9380 ON play_date (venue_id_id)');
        $this->addSql('ALTER TABLE play_date ADD CONSTRAINT FK_A19BC9ABD3129707 FOREIGN KEY (clown_id_id) REFERENCES clown (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE play_date ADD CONSTRAINT FK_A19BC9ABA7FF9380 FOREIGN KEY (venue_id_id) REFERENCES venue (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_91911B0D5E237E06 ON venue (name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE play_date_id_seq CASCADE');
        $this->addSql('ALTER TABLE play_date DROP CONSTRAINT FK_A19BC9ABD3129707');
        $this->addSql('ALTER TABLE play_date DROP CONSTRAINT FK_A19BC9ABA7FF9380');
        $this->addSql('DROP TABLE play_date');
        $this->addSql('DROP INDEX UNIQ_91911B0D5E237E06');
    }
}
