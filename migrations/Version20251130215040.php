<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251130215040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE calendar (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) NOT NULL, type VARCHAR(100) NOT NULL, clown_id INT NOT NULL, INDEX IDX_6EA9A14687F943E2 (clown_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB ');
        $this->addSql('ALTER TABLE calendar ADD CONSTRAINT FK_6EA9A14687F943E2 FOREIGN KEY (clown_id) REFERENCES clown (id)');
        // TODO: das folgende muss wieder weg!
        /*$this->addSql('ALTER TABLE clown_clown ENGINE = InnoDB');
        $this->addSql('ALTER TABLE clown_clown ADD CONSTRAINT FK_4CE99418344D788E FOREIGN KEY (clown_source) REFERENCES clown (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE clown_clown ADD CONSTRAINT FK_4CE994182DA82801 FOREIGN KEY (clown_target) REFERENCES clown (id) ON DELETE CASCADE');*/
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE calendar DROP FOREIGN KEY FK_6EA9A14687F943E2');
        $this->addSql('DROP TABLE calendar');
    }
}
