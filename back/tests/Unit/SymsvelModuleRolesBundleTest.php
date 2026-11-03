<?php

declare(strict_types=1);

namespace SymSvel\Module\Roles\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SymSvel\Module\Roles\RolesBundle;

final class SymsvelModuleRolesBundleTest extends TestCase
{
    public function testBundleCanBeInstantiated(): void
    {
        self::assertInstanceOf(RolesBundle::class, new RolesBundle());
    }
}
