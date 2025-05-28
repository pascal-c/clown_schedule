<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250528160508 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE fee RENAME COLUMN fee_by_public_transport TO fee_standard, RENAME COLUMN fee_by_car TO fee_alternative
        SQL);

    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE fee RENAME COLUMN fee_standard TO fee_by_public_transport, RENAME COLUMN fee_alternative TO fee_by_car
        SQL);
    }
}
