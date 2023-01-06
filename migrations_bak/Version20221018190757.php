<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221018190757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE play_date DROP CONSTRAINT fk_a19bc9abd3129707');
        $this->addSql('ALTER TABLE play_date DROP CONSTRAINT fk_a19bc9aba7ff9380');
        $this->addSql('DROP INDEX idx_a19bc9aba7ff9380');
        $this->addSql('DROP INDEX idx_a19bc9abd3129707');
        $this->addSql('ALTER TABLE play_date RENAME COLUMN clown_id_id TO clown_id');
        $this->addSql('ALTER TABLE play_date RENAME COLUMN venue_id_id TO venue_id');
        $this->addSql('ALTER TABLE play_date ADD CONSTRAINT FK_A19BC9AB87F943E2 FOREIGN KEY (clown_id) REFERENCES clown (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE play_date ADD CONSTRAINT FK_A19BC9AB40A73EBA FOREIGN KEY (venue_id) REFERENCES venue (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A19BC9AB87F943E2 ON play_date (clown_id)');
        $this->addSql('CREATE INDEX IDX_A19BC9AB40A73EBA ON play_date (venue_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE play_date DROP CONSTRAINT FK_A19BC9AB87F943E2');
        $this->addSql('ALTER TABLE play_date DROP CONSTRAINT FK_A19BC9AB40A73EBA');
        $this->addSql('DROP INDEX IDX_A19BC9AB87F943E2');
        $this->addSql('DROP INDEX IDX_A19BC9AB40A73EBA');
        $this->addSql('ALTER TABLE play_date RENAME COLUMN clown_id TO clown_id_id');
        $this->addSql('ALTER TABLE play_date RENAME COLUMN venue_id TO venue_id_id');
        $this->addSql('ALTER TABLE play_date ADD CONSTRAINT fk_a19bc9abd3129707 FOREIGN KEY (clown_id_id) REFERENCES clown (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE play_date ADD CONSTRAINT fk_a19bc9aba7ff9380 FOREIGN KEY (venue_id_id) REFERENCES venue (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_a19bc9aba7ff9380 ON play_date (venue_id_id)');
        $this->addSql('CREATE INDEX idx_a19bc9abd3129707 ON play_date (clown_id_id)');
    }
}
