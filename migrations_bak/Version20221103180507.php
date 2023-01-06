<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221103180507 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE venue_clown (venue_id INT NOT NULL, clown_id INT NOT NULL, PRIMARY KEY(venue_id, clown_id))');
        $this->addSql('CREATE INDEX IDX_3804909F40A73EBA ON venue_clown (venue_id)');
        $this->addSql('CREATE INDEX IDX_3804909F87F943E2 ON venue_clown (clown_id)');
        $this->addSql('ALTER TABLE venue_clown ADD CONSTRAINT FK_3804909F40A73EBA FOREIGN KEY (venue_id) REFERENCES venue (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE venue_clown ADD CONSTRAINT FK_3804909F87F943E2 FOREIGN KEY (clown_id) REFERENCES clown (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE venue DROP CONSTRAINT fk_91911b0df952fc95');
        $this->addSql('DROP INDEX idx_91911b0df952fc95');
        $this->addSql('ALTER TABLE venue ADD daytime_default VARCHAR(2) DEFAULT NULL');
        $this->addSql('ALTER TABLE venue DROP responsible_clown_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE venue_clown DROP CONSTRAINT FK_3804909F40A73EBA');
        $this->addSql('ALTER TABLE venue_clown DROP CONSTRAINT FK_3804909F87F943E2');
        $this->addSql('DROP TABLE venue_clown');
        $this->addSql('ALTER TABLE venue ADD responsible_clown_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE venue DROP daytime_default');
        $this->addSql('ALTER TABLE venue ADD CONSTRAINT fk_91911b0df952fc95 FOREIGN KEY (responsible_clown_id) REFERENCES clown (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_91911b0df952fc95 ON venue (responsible_clown_id)');
    }
}
