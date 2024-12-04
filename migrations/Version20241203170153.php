<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241203170153 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE venue_fee RENAME fee');
        $this->addSql('ALTER TABLE fee ENGINE = InnoDB');


    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fee ENGINE = MyISAM');
        $this->addSql('ALTER TABLE fee RENAME venue_fee');
    }
}
