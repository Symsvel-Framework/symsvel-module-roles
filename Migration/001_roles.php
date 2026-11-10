<?php

declare(strict_types=1);

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/** Immutable module migration compiled by SymSvel Tooling. */
final class RolesInitialMigration extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $table = $schema->createTable('symsvel_roles');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('code', 'string', ['length' => 100]);
        $table->addColumn('label', 'string', ['length' => 150]);
        $table->addColumn('system', 'boolean', ['default' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code'], 'uniq_symsvel_roles_code');

        // Bootstrap data is still SQL-dependent until the schema compiler has
        // an explicit portable data-migration contract.
        $this->addSql("INSERT INTO symsvel_roles (code, label, system) VALUES ('ROLE_DEV', 'Développeur', TRUE), ('ROLE_SUPER_ADMIN', 'Super administrateur', TRUE), ('ROLE_ADMIN', 'Administrateur', TRUE), ('ROLE_USER', 'Utilisateur', TRUE)");
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('symsvel_roles');
    }
}
