<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241126195058 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE venue_fee (id INT AUTO_INCREMENT NOT NULL, fee_by_car NUMERIC(8, 2) DEFAULT NULL, fee_by_public_transport NUMERIC(8, 2) DEFAULT NULL, kilometers INT DEFAULT NULL, fee_per_kilometer NUMERIC(3, 2) NOT NULL, kilometers_fee_for_all_clowns TINYINT(1) NOT NULL, valid_from DATE DEFAULT NULL, venue_id INT NOT NULL, INDEX IDX_D669F18A40A73EBA (venue_id), INDEX valid_from_idx (valid_from), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE venue_fee ADD CONSTRAINT FK_D669F18A40A73EBA FOREIGN KEY (venue_id) REFERENCES venue (id)');
        $this->addSql('INSERT INTO venue_fee (venue_id, fee_by_car, fee_by_public_transport, kilometers, fee_per_kilometer, kilometers_fee_for_all_clowns)  
                        SELECT id, fee_by_car, fee_by_public_transport, kilometers, fee_per_kilometer, kilometers_fee_for_all_clowns FROM `venue`');
        $this->addSql('ALTER TABLE venue DROP fee_by_car, DROP fee_by_public_transport, DROP kilometers, DROP kilometers_fee_for_all_clowns, DROP fee_per_kilometer');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE venue ADD fee_by_car NUMERIC(8, 2) DEFAULT NULL, ADD fee_by_public_transport NUMERIC(8, 2) DEFAULT NULL, ADD kilometers INT DEFAULT NULL, ADD kilometers_fee_for_all_clowns TINYINT(1) NOT NULL, ADD fee_per_kilometer NUMERIC(3, 2) NOT NULL');
        $this->addSql('ALTER TABLE venue_fee DROP FOREIGN KEY FK_D669F18A40A73EBA');
        $this->addSql('UPDATE venue 
                        INNER JOIN venue_fee ON venue.id = venue_fee.venue_id AND venue_fee.valid_from is NULL
                        SET venue.fee_by_car=venue_fee.fee_by_car, 
                            venue.fee_by_public_transport=venue_fee.fee_by_public_transport, 
                            venue.kilometers=venue_fee.kilometers, 
                            venue.fee_per_kilometer=venue_fee.fee_per_kilometer, 
                            venue.kilometers_fee_for_all_clowns=venue_fee.kilometers_fee_for_all_clowns
                        ');
        $this->addSql('DROP TABLE venue_fee');
    }
}
