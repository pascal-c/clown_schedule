<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251110160106 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE clown_availability ADD availability_ratio DOUBLE PRECISION DEFAULT NULL');
        // Calculate availability_ratio for completed schedules with old availability algorithm
        $this->addSql("UPDATE clown_availability 
            INNER JOIN schedule ON schedule.month = clown_availability.month
            SET availability_ratio = (
                SELECT COUNT(*)
                FROM clown_availability_time TIME_SLOT 
                WHERE TIME_SLOT.clown_availability_id = clown_availability.id AND TIME_SLOT.availability != 'no'
                ) / (
                SELECT COUNT(*)
                FROM clown_availability_time TIME_SLOT 
                WHERE TIME_SLOT.clown_availability_id = clown_availability.id
                )
            WHERE schedule.status = 'completed'
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE clown_availability DROP availability_ratio');
    }
}
