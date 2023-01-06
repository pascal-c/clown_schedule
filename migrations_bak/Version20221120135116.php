<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221120135116 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE clown_availability2_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE clown_availability2 (id INT NOT NULL, clown_id INT NOT NULL, month VARCHAR(7) NOT NULL, max_plays_month INT NOT NULL, wished_plays_month INT NOT NULL, max_plays_day INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EE8A88DA87F943E2 ON clown_availability2 (clown_id)');
        $this->addSql('ALTER TABLE clown_availability2 ADD CONSTRAINT FK_EE8A88DA87F943E2 FOREIGN KEY (clown_id) REFERENCES clown (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE clown_availability2_id_seq CASCADE');
        $this->addSql('ALTER TABLE clown_availability2 DROP CONSTRAINT FK_EE8A88DA87F943E2');
        $this->addSql('DROP TABLE clown_availability2');
    }
}
