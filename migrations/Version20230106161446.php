<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230106161446 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE clown (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, gender VARCHAR(7) NOT NULL, email VARCHAR(255) DEFAULT NULL, password VARCHAR(255) DEFAULT NULL, is_admin TINYINT(1) NOT NULL, is_active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_E0BF9D485E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE clown_availability (id INT AUTO_INCREMENT NOT NULL, clown_id INT NOT NULL, month VARCHAR(7) NOT NULL, max_plays_month INT NOT NULL, wished_plays_month INT NOT NULL, max_plays_day INT NOT NULL, entitled_plays_month DOUBLE PRECISION DEFAULT NULL, calculated_plays_month INT DEFAULT NULL, target_plays INT DEFAULT NULL, calculated_substitutions INT DEFAULT NULL, INDEX IDX_8B83861687F943E2 (clown_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE clown_availability_time (id INT AUTO_INCREMENT NOT NULL, clown_id INT NOT NULL, clown_availability_id INT NOT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', daytime VARCHAR(2) NOT NULL, availability VARCHAR(20) NOT NULL, INDEX IDX_2E2787C287F943E2 (clown_id), INDEX IDX_2E2787C26782F72E (clown_availability_id), UNIQUE INDEX clown_date_daytime_index (clown_id, date, daytime), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE play_date (id INT AUTO_INCREMENT NOT NULL, venue_id INT NOT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', daytime VARCHAR(2) NOT NULL, INDEX IDX_A19BC9AB40A73EBA (venue_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE play_date_clown (play_date_id INT NOT NULL, clown_id INT NOT NULL, INDEX IDX_4FA24BA1CE8951DB (play_date_id), INDEX IDX_4FA24BA187F943E2 (clown_id), PRIMARY KEY(play_date_id, clown_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE time_slot (id INT AUTO_INCREMENT NOT NULL, substitution_clown_id INT DEFAULT NULL, month VARCHAR(7) NOT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', daytime VARCHAR(2) NOT NULL, INDEX IDX_1B3294A57A76FF5 (substitution_clown_id), UNIQUE INDEX timeslot_date_daytime_index (date, daytime), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE venue (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, daytime_default VARCHAR(2) DEFAULT NULL, meeting_time TIME DEFAULT NULL, play_time_from TIME DEFAULT NULL, play_time_to TIME DEFAULT NULL, emails LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_91911B0D5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE venue_clown (venue_id INT NOT NULL, clown_id INT NOT NULL, INDEX IDX_3804909F40A73EBA (venue_id), INDEX IDX_3804909F87F943E2 (clown_id), PRIMARY KEY(venue_id, clown_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE clown_availability ADD CONSTRAINT FK_8B83861687F943E2 FOREIGN KEY (clown_id) REFERENCES clown (id)');
        $this->addSql('ALTER TABLE clown_availability_time ADD CONSTRAINT FK_2E2787C287F943E2 FOREIGN KEY (clown_id) REFERENCES clown (id)');
        $this->addSql('ALTER TABLE clown_availability_time ADD CONSTRAINT FK_2E2787C26782F72E FOREIGN KEY (clown_availability_id) REFERENCES clown_availability (id)');
        $this->addSql('ALTER TABLE play_date ADD CONSTRAINT FK_A19BC9AB40A73EBA FOREIGN KEY (venue_id) REFERENCES venue (id)');
        $this->addSql('ALTER TABLE play_date_clown ADD CONSTRAINT FK_4FA24BA1CE8951DB FOREIGN KEY (play_date_id) REFERENCES play_date (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE play_date_clown ADD CONSTRAINT FK_4FA24BA187F943E2 FOREIGN KEY (clown_id) REFERENCES clown (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE time_slot ADD CONSTRAINT FK_1B3294A57A76FF5 FOREIGN KEY (substitution_clown_id) REFERENCES clown (id)');
        $this->addSql('ALTER TABLE venue_clown ADD CONSTRAINT FK_3804909F40A73EBA FOREIGN KEY (venue_id) REFERENCES venue (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE venue_clown ADD CONSTRAINT FK_3804909F87F943E2 FOREIGN KEY (clown_id) REFERENCES clown (id) ON DELETE CASCADE');

        $this->addSql('INSERT INTO clown (gender, email, password, is_admin) VALUES ("male", "pascal.keimel@apwp.de", "'.password_hash('secret123', PASSWORD_DEFAULT).'", 1)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clown_availability DROP FOREIGN KEY FK_8B83861687F943E2');
        $this->addSql('ALTER TABLE clown_availability_time DROP FOREIGN KEY FK_2E2787C287F943E2');
        $this->addSql('ALTER TABLE clown_availability_time DROP FOREIGN KEY FK_2E2787C26782F72E');
        $this->addSql('ALTER TABLE play_date DROP FOREIGN KEY FK_A19BC9AB40A73EBA');
        $this->addSql('ALTER TABLE play_date_clown DROP FOREIGN KEY FK_4FA24BA1CE8951DB');
        $this->addSql('ALTER TABLE play_date_clown DROP FOREIGN KEY FK_4FA24BA187F943E2');
        $this->addSql('ALTER TABLE time_slot DROP FOREIGN KEY FK_1B3294A57A76FF5');
        $this->addSql('ALTER TABLE venue_clown DROP FOREIGN KEY FK_3804909F40A73EBA');
        $this->addSql('ALTER TABLE venue_clown DROP FOREIGN KEY FK_3804909F87F943E2');
        $this->addSql('DROP TABLE clown');
        $this->addSql('DROP TABLE clown_availability');
        $this->addSql('DROP TABLE clown_availability_time');
        $this->addSql('DROP TABLE play_date');
        $this->addSql('DROP TABLE play_date_clown');
        $this->addSql('DROP TABLE time_slot');
        $this->addSql('DROP TABLE venue');
        $this->addSql('DROP TABLE venue_clown');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
