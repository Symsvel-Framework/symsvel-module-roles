<?php

declare(strict_types=1);

namespace SymSvel\Module\Roles\Tests\Unit;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use SymSvel\Module\Roles\Entity\Role;
use SymSvel\Module\Roles\RoleManager;

final class RoleValidationTest extends TestCase
{
    public function testCreateNormalizesCodeAndLabel(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('persist');
        $em->expects(self::once())->method('flush');

        $role = (new RoleManager($em))->create(' role_editor ', ' Editor ');
        self::assertSame('ROLE_EDITOR', $role->getCode());
        self::assertSame('Editor', $role->getLabel());
    }

    public function testEmptyLabelIsRejected(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('persist');
        $this->expectException(\InvalidArgumentException::class);
        (new RoleManager($em))->create('ROLE_EDITOR', '   ');
    }

    public function testRenameRejectsEmptyLabel(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('flush');
        $this->expectException(\InvalidArgumentException::class);
        (new RoleManager($em))->rename(new Role('ROLE_EDITOR', 'Editor'), '  ');
    }
}
