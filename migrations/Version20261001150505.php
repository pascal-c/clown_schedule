<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261001150505 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE invoice (id INT AUTO_INCREMENT NOT NULL, month VARCHAR(7) NOT NULL, status VARCHAR(100) NOT NULL, clown_id INT NOT NULL, UNIQUE INDEX clown_month_index (clown_id, month), INDEX IDX_9065174487F943E2 (clown_id), PRIMARY KEY (id)) DEFAULT  CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_9065174487F943E2 FOREIGN KEY (clown_id) REFERENCES clown (id)');

        $this->addSql('ALTER TABLE venue_team ENGINE = InnoDB');
        $this->addSql('ALTER TABLE venue_team ADD CONSTRAINT FK_72C6ABB740A73EBA FOREIGN KEY (venue_id) REFERENCES venue (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE venue_team ADD CONSTRAINT FK_72C6ABB787F943E2 FOREIGN KEY (clown_id) REFERENCES clown (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_9065174487F943E2');
        $this->addSql('DROP TABLE invoice');

        $this->addSql('ALTER TABLE venue_team DROP FOREIGN KEY FK_72C6ABB740A73EBA');
        $this->addSql('ALTER TABLE venue_team DROP FOREIGN KEY FK_72C6ABB787F943E2');
        $this->addSql('ALTER TABLE venue_team ENGINE = MyISAM');
    }
}
