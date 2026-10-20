<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261014093150 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE config ADD feature_invoice_management_clowns TINYINT DEFAULT 1 NOT NULL, ADD feature_invoice_management_venues TINYINT DEFAULT 0 NOT NULL, ADD feature_invoicing_clowns TINYINT DEFAULT 0 NOT NULL, ADD feature_invoice_payment_clowns TINYINT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE config DROP feature_invoice_management_clowns, DROP feature_invoice_management_venues, DROP feature_invoicing_clowns, DROP feature_invoice_payment_clowns');
    }
}
