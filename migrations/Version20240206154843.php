<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240206154843 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE venue_clown_blocked (venue_id INT NOT NULL, clown_id INT NOT NULL, INDEX IDX_BDB56BCD40A73EBA (venue_id), INDEX IDX_BDB56BCD87F943E2 (clown_id), PRIMARY KEY(venue_id, clown_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE venue_clown_blocked ADD CONSTRAINT FK_BDB56BCD40A73EBA FOREIGN KEY (venue_id) REFERENCES venue (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE venue_clown_blocked ADD CONSTRAINT FK_BDB56BCD87F943E2 FOREIGN KEY (clown_id) REFERENCES clown (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE venue_clown_blocked DROP FOREIGN KEY FK_BDB56BCD40A73EBA');
        $this->addSql('ALTER TABLE venue_clown_blocked DROP FOREIGN KEY FK_BDB56BCD87F943E2');
        $this->addSql('DROP TABLE venue_clown_blocked');
    }
}
