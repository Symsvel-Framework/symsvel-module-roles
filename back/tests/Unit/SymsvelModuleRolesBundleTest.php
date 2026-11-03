<?php

declare(strict_types=1);

namespace SymSvel\Module\SymsvelModuleRoles\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SymSvel\Module\SymsvelModuleRoles\SymsvelModuleRolesBundle;

final class SymsvelModuleRolesBundleTest extends TestCase
{
    public function testBundleCanBeInstantiated(): void
    {
        self::assertInstanceOf(SymsvelModuleRolesBundle::class, new SymsvelModuleRolesBundle());
    }
}
