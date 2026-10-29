<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261025163952 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, month VARCHAR(7) DEFAULT NULL, clown_id INT NOT NULL, UNIQUE INDEX notification_type_clown_month_index (type, clown_id, month), INDEX IDX_BF5476CA87F943E2 (clown_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA87F943E2 FOREIGN KEY (clown_id) REFERENCES clown (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA87F943E2');
        $this->addSql('DROP TABLE notification');
    }
}
