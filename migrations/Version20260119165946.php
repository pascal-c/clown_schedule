<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260119165946 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE venue_team (venue_id INT NOT NULL, clown_id INT NOT NULL, INDEX IDX_72C6ABB740A73EBA (venue_id), INDEX IDX_72C6ABB787F943E2 (clown_id), PRIMARY KEY (venue_id, clown_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE venue_team ADD CONSTRAINT FK_72C6ABB740A73EBA FOREIGN KEY (venue_id) REFERENCES venue (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE venue_team ADD CONSTRAINT FK_72C6ABB787F943E2 FOREIGN KEY (clown_id) REFERENCES clown (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE config ADD feature_teams TINYINT DEFAULT 0 NOT NULL, ADD points_per_person_not_in_team INT DEFAULT 30 NOT NULL');
        $this->addSql('ALTER TABLE venue ADD team_active TINYINT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE venue_team DROP FOREIGN KEY FK_72C6ABB740A73EBA');
        $this->addSql('ALTER TABLE venue_team DROP FOREIGN KEY FK_72C6ABB787F943E2');
        $this->addSql('DROP TABLE venue_team');
        $this->addSql('ALTER TABLE config DROP feature_teams, DROP points_per_person_not_in_team');
        $this->addSql('ALTER TABLE venue DROP team_active');
    }
}
