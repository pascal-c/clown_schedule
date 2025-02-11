<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241211114715 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, venue_id INT DEFAULT NULL, `function` VARCHAR(255) DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, birth_date DATE DEFAULT NULL, street_and_number VARCHAR(255) DEFAULT NULL, zip_code VARCHAR(10) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('CREATE TABLE venue_contact (venue_id INT NOT NULL, contact_id INT NOT NULL, INDEX IDX_FCAA11E640A73EBA (venue_id), INDEX IDX_FCAA11E6E7A1254A (contact_id), PRIMARY KEY(venue_id, contact_id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('ALTER TABLE venue_contact ADD CONSTRAINT FK_FCAA11E640A73EBA FOREIGN KEY (venue_id) REFERENCES venue (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE venue_contact ADD CONSTRAINT FK_FCAA11E6E7A1254A FOREIGN KEY (contact_id) REFERENCES contact (id) ON DELETE CASCADE');

        $this->addSql('INSERT INTO contact (venue_id, last_name, email, phone) SELECT id, contact_person, contact_email, contact_phone FROM venue');
        $this->addSql('INSERT INTO venue_contact (venue_id, contact_id) SELECT venue_id, id FROM contact');

        $this->addSql('ALTER TABLE contact DROP venue_id');
        $this->addSql('ALTER TABLE venue DROP contact_person, DROP contact_phone, DROP contact_email');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE venue ADD contact_person VARCHAR(255) DEFAULT NULL, ADD contact_phone VARCHAR(20) DEFAULT NULL, ADD contact_email VARCHAR(100) DEFAULT NULL');

        $this->addSql('UPDATE venue 
            INNER JOIN venue_contact ON venue.id = venue_contact.venue_id 
            INNER JOIN contact ON contact.id = venue_contact.contact_id 
            SET contact_person=contact.last_name, contact_email=contact.email, contact_phone=contact.phone');

        $this->addSql('ALTER TABLE venue_contact DROP FOREIGN KEY FK_FCAA11E640A73EBA');
        $this->addSql('ALTER TABLE venue_contact DROP FOREIGN KEY FK_FCAA11E6E7A1254A');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE venue_contact');
    }
}
