<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241204143037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fee CHANGE venue_id venue_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fee ADD CONSTRAINT FK_964964B540A73EBA FOREIGN KEY (venue_id) REFERENCES venue (id)');
        $this->addSql('ALTER TABLE fee RENAME INDEX idx_d669f18a40a73eba TO IDX_964964B540A73EBA');
        $this->addSql('ALTER TABLE play_date ADD fee_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE play_date ADD CONSTRAINT FK_A19BC9ABAB45AECA FOREIGN KEY (fee_id) REFERENCES fee (id)');
        $this->addSql('CREATE INDEX IDX_A19BC9ABAB45AECA ON play_date (fee_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE play_date DROP FOREIGN KEY FK_A19BC9ABAB45AECA');
        $this->addSql('DROP INDEX IDX_A19BC9ABAB45AECA ON play_date');
        $this->addSql('ALTER TABLE play_date DROP fee_id');
        $this->addSql('ALTER TABLE fee DROP FOREIGN KEY FK_964964B540A73EBA');
        $this->addSql('ALTER TABLE fee CHANGE venue_id venue_id INT NOT NULL');
        $this->addSql('ALTER TABLE fee RENAME INDEX idx_964964b540a73eba TO IDX_D669F18A40A73EBA');
    }
}
