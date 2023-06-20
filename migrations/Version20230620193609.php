<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230620193609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE venue CHANGE fee_by_car fee_by_car NUMERIC(8, 2) DEFAULT NULL, CHANGE fee_by_public_transport fee_by_public_transport NUMERIC(8, 2) DEFAULT NULL, CHANGE kilometers_fee_for_all_clowns kilometers_fee_for_all_clowns TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE venue CHANGE fee_by_car fee_by_car INT DEFAULT NULL, CHANGE fee_by_public_transport fee_by_public_transport INT DEFAULT NULL, CHANGE kilometers_fee_for_all_clowns kilometers_fee_for_all_clowns TINYINT(1) DEFAULT 1 NOT NULL');
    }
}
