<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221226154341 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE time_slot_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE time_slot (id INT NOT NULL, substitution_clown_id INT DEFAULT NULL, month VARCHAR(7) NOT NULL, date DATE NOT NULL, daytime VARCHAR(2) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1B3294A57A76FF5 ON time_slot (substitution_clown_id)');
        $this->addSql('COMMENT ON COLUMN time_slot.date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE time_slot ADD CONSTRAINT FK_1B3294A57A76FF5 FOREIGN KEY (substitution_clown_id) REFERENCES clown (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE time_slot_id_seq CASCADE');
        $this->addSql('ALTER TABLE time_slot DROP CONSTRAINT FK_1B3294A57A76FF5');
        $this->addSql('DROP TABLE time_slot');
    }
}
