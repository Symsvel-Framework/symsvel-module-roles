<?php

declare(strict_types=1);

namespace SymSvel\Module\Roles\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use SymSvel\Module\Roles\Entity\Role;

/** @extends ServiceEntityRepository<Role> */
final class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }
}
