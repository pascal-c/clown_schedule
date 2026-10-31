<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261020164225 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_settings (id INT AUTO_INCREMENT NOT NULL, schedule_completed_email TINYINT DEFAULT 1 NOT NULL, open_invoice_on_dashboard TINYINT DEFAULT 1 NOT NULL, open_invoice_email TINYINT DEFAULT 0 NOT NULL, open_invoice_email_reminder TINYINT DEFAULT 1 NOT NULL, clown_id INT NOT NULL, UNIQUE INDEX UNIQ_5C844C587F943E2 (clown_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_settings ADD CONSTRAINT FK_5C844C587F943E2 FOREIGN KEY (clown_id) REFERENCES clown (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_settings DROP FOREIGN KEY FK_5C844C587F943E2');
        $this->addSql('DROP TABLE user_settings');
    }
}
