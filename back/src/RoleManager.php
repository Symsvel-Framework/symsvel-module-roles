<?php

declare(strict_types=1);

namespace SymSvel\Module\Roles;

use Doctrine\ORM\EntityManagerInterface;
use SymSvel\Module\Roles\Entity\Role;

final class RoleManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RolePolicy $policy = new RolePolicy(),
    ) {
    }

    public function create(string $code, string $label): Role
    {
        $this->policy->assertMutable($code);
        $role = new Role($code, $label);
        $this->entityManager->persist($role);
        $this->entityManager->flush();

        return $role;
    }

    public function rename(Role $role, string $label): void
    {
        $this->policy->assertMutable($role->getCode());
        if ($role->isSystem()) {
            throw new \DomainException('System roles cannot be modified.');
        }
        $role->rename($label);
        $this->entityManager->flush();
    }

    public function remove(Role $role): void
    {
        $this->policy->assertMutable($role->getCode());
        if ($role->isSystem()) {
            throw new \DomainException('System roles cannot be deleted.');
        }
        $this->entityManager->remove($role);
        $this->entityManager->flush();
    }
}
