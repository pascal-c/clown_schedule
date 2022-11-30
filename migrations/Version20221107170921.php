<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221107170921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE play_date_clown (play_date_id INT NOT NULL, clown_id INT NOT NULL, PRIMARY KEY(play_date_id, clown_id))');
        $this->addSql('CREATE INDEX IDX_4FA24BA1CE8951DB ON play_date_clown (play_date_id)');
        $this->addSql('CREATE INDEX IDX_4FA24BA187F943E2 ON play_date_clown (clown_id)');
        $this->addSql('ALTER TABLE play_date_clown ADD CONSTRAINT FK_4FA24BA1CE8951DB FOREIGN KEY (play_date_id) REFERENCES play_date (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE play_date_clown ADD CONSTRAINT FK_4FA24BA187F943E2 FOREIGN KEY (clown_id) REFERENCES clown (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE venue ADD meeting_time TIME(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE venue ADD play_time_from TIME(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE venue ADD play_time_to TIME(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE venue ADD emails TEXT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN venue.emails IS \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE play_date_clown DROP CONSTRAINT FK_4FA24BA1CE8951DB');
        $this->addSql('ALTER TABLE play_date_clown DROP CONSTRAINT FK_4FA24BA187F943E2');
        $this->addSql('DROP TABLE play_date_clown');
        $this->addSql('ALTER TABLE venue DROP meeting_time');
        $this->addSql('ALTER TABLE venue DROP play_time_from');
        $this->addSql('ALTER TABLE venue DROP play_time_to');
        $this->addSql('ALTER TABLE venue DROP emails');
    }
}
