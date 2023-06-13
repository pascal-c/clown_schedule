<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230613160220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE substitution DROP FOREIGN KEY FK_1B3294A57A76FF5');
        $this->addSql('DROP INDEX idx_1b3294a57a76ff5 ON substitution');
        $this->addSql('CREATE INDEX IDX_C7C90AE057A76FF5 ON substitution (substitution_clown_id)');
        $this->addSql('ALTER TABLE substitution ADD CONSTRAINT FK_1B3294A57A76FF5 FOREIGN KEY (substitution_clown_id) REFERENCES clown (id)');
        $this->addSql('ALTER TABLE venue ADD street_and_number VARCHAR(255) DEFAULT NULL, ADD postal_code VARCHAR(10) DEFAULT NULL, ADD city VARCHAR(255) DEFAULT NULL, ADD contact_person VARCHAR(255) DEFAULT NULL, ADD contact_phone VARCHAR(20) DEFAULT NULL, ADD contact_email VARCHAR(100) DEFAULT NULL, ADD fee_by_car INT DEFAULT NULL, ADD fee_by_public_transport INT DEFAULT NULL, ADD kilometers INT DEFAULT NULL, ADD comments LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE substitution DROP FOREIGN KEY FK_C7C90AE057A76FF5');
        $this->addSql('DROP INDEX idx_c7c90ae057a76ff5 ON substitution');
        $this->addSql('CREATE INDEX IDX_1B3294A57A76FF5 ON substitution (substitution_clown_id)');
        $this->addSql('ALTER TABLE substitution ADD CONSTRAINT FK_C7C90AE057A76FF5 FOREIGN KEY (substitution_clown_id) REFERENCES clown (id)');
        $this->addSql('ALTER TABLE venue DROP street_and_number, DROP postal_code, DROP city, DROP contact_person, DROP contact_phone, DROP contact_email, DROP fee_by_car, DROP fee_by_public_transport, DROP kilometers, DROP comments');
    }
}
