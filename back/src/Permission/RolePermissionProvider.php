<?php

declare(strict_types=1);

namespace SymSvel\Module\Roles\Permission;

use SymSvel\Module\Acls\Permission\PermissionDefinition;
use SymSvel\Module\Acls\Permission\PermissionProviderInterface;
use SymSvel\Module\Roles\Repository\RoleRepository;

/** Declares Roles operations and assignment actions for actual persisted BO roles. */
final readonly class RolePermissionProvider implements PermissionProviderInterface
{
    public function __construct(private RoleRepository $roles) {}

    public function permissions(): iterable
    {
        foreach ([
            ['list', 'Consulter les rôles'],
            ['create', 'Créer un rôle'],
            ['update', 'Modifier un rôle'],
            ['delete', 'Supprimer un rôle'],
        ] as [$action, $label]) {
            yield new PermissionDefinition('roles', $action, $label);
        }

        foreach ($this->roles->findBy([], ['label' => 'ASC']) as $role) {
            if (in_array($role->getCode(), ['ROLE_DEV', 'ROLE_USER'], true)) continue;
            yield new PermissionDefinition(
                'roles', 'assign:'.$role->getCode(),
                'Attribuer le rôle '.$role->getLabel(),
                $role->getCode() === 'ROLE_SUPER_ADMIN',
            );
        }
    }
}
