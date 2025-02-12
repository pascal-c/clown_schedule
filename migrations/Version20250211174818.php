<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250211174818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE config (id INT AUTO_INCREMENT NOT NULL, special_play_date_url VARCHAR(255) DEFAULT NULL, feature_max_per_week_active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = INNODB');
        $this->addSql('INSERT INTO config (id, special_play_date_url, feature_max_per_week_active) VALUES (1, NULL, 0)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE config');
    }
}
