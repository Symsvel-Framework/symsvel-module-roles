<?php

declare(strict_types=1);

namespace SymSvel\Module\Roles\Tests\Unit;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use SymSvel\Module\Roles\Entity\Role;
use SymSvel\Module\Roles\RoleManager;

final class RoleManagerTest extends TestCase
{
    public function testCreatesOrdinaryRole(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist');
        $entityManager->expects(self::once())->method('flush');

        $role = (new RoleManager($entityManager))->create('ROLE_EDITOR', 'Editor');

        self::assertSame('ROLE_EDITOR', $role->getCode());
        self::assertFalse($role->isSystem());
    }

    public function testCannotCreateDeveloperRole(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('persist');
        $entityManager->expects(self::never())->method('flush');

        $this->expectException(\DomainException::class);
        (new RoleManager($entityManager))->create('ROLE_DEV', 'Developer');
    }

    public function testCannotRenameSystemRole(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('flush');

        $this->expectException(\DomainException::class);
        (new RoleManager($entityManager))->rename(new Role('ROLE_ADMIN', 'Admin', true), 'Other');
    }

    public function testCannotRemoveDeveloperRole(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('remove');

        $this->expectException(\DomainException::class);
        (new RoleManager($entityManager))->remove(new Role('ROLE_DEV', 'Developer', true));
    }

    public function testRenamesOrdinaryRole(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');
        $role = new Role('ROLE_EDITOR', 'Editor');

        (new RoleManager($entityManager))->rename($role, 'Content editor');

        self::assertSame('Content editor', $role->getLabel());
    }

    public function testRemovesOrdinaryRole(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('remove');
        $entityManager->expects(self::once())->method('flush');

        (new RoleManager($entityManager))->remove(new Role('ROLE_EDITOR', 'Editor'));
    }
}
