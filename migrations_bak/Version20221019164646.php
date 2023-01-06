<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221019164646 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE play_date DROP CONSTRAINT fk_a19bc9ab87f943e2');
        $this->addSql('DROP INDEX idx_a19bc9ab87f943e2');
        $this->addSql('ALTER TABLE play_date DROP clown_id');
        $this->addSql('ALTER TABLE venue ADD responsible_clown_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE venue ADD CONSTRAINT FK_91911B0DF952FC95 FOREIGN KEY (responsible_clown_id) REFERENCES clown (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_91911B0DF952FC95 ON venue (responsible_clown_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE venue DROP CONSTRAINT FK_91911B0DF952FC95');
        $this->addSql('DROP INDEX IDX_91911B0DF952FC95');
        $this->addSql('ALTER TABLE venue DROP responsible_clown_id');
        $this->addSql('ALTER TABLE play_date ADD clown_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE play_date ADD CONSTRAINT fk_a19bc9ab87f943e2 FOREIGN KEY (clown_id) REFERENCES clown (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_a19bc9ab87f943e2 ON play_date (clown_id)');
    }
}
