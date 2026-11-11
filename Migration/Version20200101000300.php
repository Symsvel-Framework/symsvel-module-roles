<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/** Initial native roles seed; kept separate from the table schema migration. */
final class Version20200101000300 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO symsvel_roles (code, label, is_system) VALUES ('ROLE_DEV', 'Développeur', TRUE), ('ROLE_SUPER_ADMIN', 'Super administrateur', TRUE), ('ROLE_ADMIN', 'Administrateur', TRUE), ('ROLE_USER', 'Utilisateur', TRUE)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM symsvel_roles WHERE code IN ('ROLE_DEV', 'ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_USER')");
    }
}
