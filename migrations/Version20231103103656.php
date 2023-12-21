<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231103103656 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create table play_date_change_request';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE play_date_change_request (id INT AUTO_INCREMENT NOT NULL, play_date_to_give_off_id INT NOT NULL, play_date_wanted_id INT DEFAULT NULL, requested_by_id INT NOT NULL, requested_to_id INT DEFAULT NULL, status VARCHAR(100) NOT NULL, type VARCHAR(100) NOT NULL, INDEX IDX_73343B28F1F33D1 (play_date_to_give_off_id), INDEX IDX_73343B28C25073E6 (play_date_wanted_id), INDEX IDX_73343B284DA1E751 (requested_by_id), INDEX IDX_73343B28D7738D30 (requested_to_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE play_date_change_request ADD CONSTRAINT FK_73343B28F1F33D1 FOREIGN KEY (play_date_to_give_off_id) REFERENCES play_date (id)');
        $this->addSql('ALTER TABLE play_date_change_request ADD CONSTRAINT FK_73343B28C25073E6 FOREIGN KEY (play_date_wanted_id) REFERENCES play_date (id)');
        $this->addSql('ALTER TABLE play_date_change_request ADD CONSTRAINT FK_73343B284DA1E751 FOREIGN KEY (requested_by_id) REFERENCES clown (id)');
        $this->addSql('ALTER TABLE play_date_change_request ADD CONSTRAINT FK_73343B28D7738D30 FOREIGN KEY (requested_to_id) REFERENCES clown (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE play_date_change_request');
    }
}
