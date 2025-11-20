<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251119164226 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE clown_clown (clown_source INT NOT NULL, clown_target INT NOT NULL, INDEX IDX_4CE99418344D788E (clown_source), INDEX IDX_4CE994182DA82801 (clown_target), PRIMARY KEY (clown_source, clown_target)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE clown_clown ADD CONSTRAINT FK_4CE99418344D788E FOREIGN KEY (clown_source) REFERENCES clown (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE clown_clown ADD CONSTRAINT FK_4CE994182DA82801 FOREIGN KEY (clown_target) REFERENCES clown (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clown_clown DROP FOREIGN KEY FK_4CE99418344D788E');
        $this->addSql('ALTER TABLE clown_clown DROP FOREIGN KEY FK_4CE994182DA82801');
        $this->addSql('DROP TABLE clown_clown');
    }
}
