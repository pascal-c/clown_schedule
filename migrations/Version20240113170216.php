<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240113170216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE play_date CHANGE is_super is_super TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE play_date_history RENAME INDEX idx_992eb92ace8951db TO IDX_C7B6E012CE8951DB');
        $this->addSql('ALTER TABLE play_date_history RENAME INDEX idx_992eb92a828ad0a0 TO IDX_C7B6E012828AD0A0');
        $this->addSql('ALTER TABLE play_date_history_clown RENAME INDEX idx_63486693ba4a1ea1 TO IDX_A0B4841859F57BC');
        $this->addSql('ALTER TABLE play_date_history_clown RENAME INDEX idx_6348669387f943e2 TO IDX_A0B4841887F943E2');
        $this->addSql('ALTER TABLE venue CHANGE fee_per_kilometer fee_per_kilometer NUMERIC(3, 2) NOT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE available_at available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL, CHANGE available_at available_at DATETIME NOT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE venue CHANGE fee_per_kilometer fee_per_kilometer NUMERIC(3, 2) DEFAULT \'0.35\' NOT NULL');
        $this->addSql('ALTER TABLE play_date_history RENAME INDEX idx_c7b6e012828ad0a0 TO IDX_992EB92A828AD0A0');
        $this->addSql('ALTER TABLE play_date_history RENAME INDEX idx_c7b6e012ce8951db TO IDX_992EB92ACE8951DB');
        $this->addSql('ALTER TABLE play_date_history_clown RENAME INDEX idx_a0b4841859f57bc TO IDX_63486693BA4A1EA1');
        $this->addSql('ALTER TABLE play_date_history_clown RENAME INDEX idx_a0b4841887f943e2 TO IDX_6348669387F943E2');
        $this->addSql('ALTER TABLE play_date CHANGE is_super is_super TINYINT(1) DEFAULT 0 NOT NULL');
    }
}
