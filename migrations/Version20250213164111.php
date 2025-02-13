<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250213164111 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add federal_state to config';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE config ADD federal_state VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE config DROP federal_state');
    }
}
