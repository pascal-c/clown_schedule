<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221108163457 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE clown_availability_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE clown_availability (id INT NOT NULL, clown_id INT NOT NULL, date DATE NOT NULL, daytime VARCHAR(2) NOT NULL, availability VARCHAR(20) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8B83861687F943E2 ON clown_availability (clown_id)');
        $this->addSql('CREATE UNIQUE INDEX clown_date_daytime_index ON clown_availability (clown_id, date, daytime)');
        $this->addSql('ALTER TABLE clown_availability ADD CONSTRAINT FK_8B83861687F943E2 FOREIGN KEY (clown_id) REFERENCES clown (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE clown_availability_id_seq CASCADE');
        $this->addSql('ALTER TABLE clown_availability DROP CONSTRAINT FK_8B83861687F943E2');
        $this->addSql('DROP TABLE clown_availability');
    }
}
