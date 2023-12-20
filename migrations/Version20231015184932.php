<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231015184932 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE play_date_history (id INT AUTO_INCREMENT NOT NULL, play_date_id INT NOT NULL, changed_by_id INT DEFAULT NULL, changed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', reason VARCHAR(100) NOT NULL, INDEX IDX_992EB92ACE8951DB (play_date_id), INDEX IDX_992EB92A828AD0A0 (changed_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE play_date_history_clown (play_date_history_id INT NOT NULL, clown_id INT NOT NULL, INDEX IDX_63486693BA4A1EA1 (play_date_history_id), INDEX IDX_6348669387F943E2 (clown_id), PRIMARY KEY(play_date_history_id, clown_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE play_date_history ADD CONSTRAINT FK_992EB92ACE8951DB FOREIGN KEY (play_date_id) REFERENCES play_date (id)');
        $this->addSql('ALTER TABLE play_date_history ADD CONSTRAINT FK_992EB92A828AD0A0 FOREIGN KEY (changed_by_id) REFERENCES clown (id)');
        $this->addSql('ALTER TABLE play_date_history_clown ADD CONSTRAINT FK_63486693BA4A1EA1 FOREIGN KEY (play_date_history_id) REFERENCES play_date_history (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE play_date_history_clown ADD CONSTRAINT FK_6348669387F943E2 FOREIGN KEY (clown_id) REFERENCES clown (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE play_date_history DROP FOREIGN KEY FK_992EB92ACE8951DB');
        $this->addSql('ALTER TABLE play_date_history DROP FOREIGN KEY FK_992EB92A828AD0A0');
        $this->addSql('ALTER TABLE play_date_history_clown DROP FOREIGN KEY FK_63486693BA4A1EA1');
        $this->addSql('ALTER TABLE play_date_history_clown DROP FOREIGN KEY FK_6348669387F943E2');
        $this->addSql('DROP TABLE play_date_history');
        $this->addSql('DROP TABLE play_date_history_clown');
    }
}
