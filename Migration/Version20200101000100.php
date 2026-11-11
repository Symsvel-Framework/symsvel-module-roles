<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/** Immutable module migration compiled by SymSvel Tooling. */
final class Version20200101000100 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $table = $schema->createTable('symsvel_roles');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('code', 'string', ['length' => 100]);
        $table->addColumn('label', 'string', ['length' => 150]);
        $table->addColumn('is_system', 'boolean', ['default' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code'], 'uniq_symsvel_roles_code');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('symsvel_roles');
    }
}
