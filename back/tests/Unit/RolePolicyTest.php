<?php

declare(strict_types=1);

namespace SymSvel\Module\Roles\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SymSvel\Module\Roles\RoleCodes;
use SymSvel\Module\Roles\RolePolicy;

final class RolePolicyTest extends TestCase
{
    public function testDeveloperRoleIsRecognized(): void
    {
        $policy = new RolePolicy();
        self::assertTrue($policy->isDeveloper(['ROLE_USER', RoleCodes::DEV]));
        self::assertFalse($policy->isDeveloper(['ROLE_ADMIN']));
    }

    public function testDeveloperRoleCannotBeModified(): void
    {
        $this->expectException(\DomainException::class);
        (new RolePolicy())->assertMutable(RoleCodes::DEV);
    }

    public function testOrdinaryRoleCanBeModified(): void
    {
        (new RolePolicy())->assertMutable('ROLE_EDITOR');
        self::assertTrue(true);
    }
}
