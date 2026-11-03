<?php

declare(strict_types=1);

namespace SymSvel\Module\Roles\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SymSvel\Module\Roles\RoleCodes;

final class RoleCodesTest extends TestCase
{
    public function testXCoreNativeRoleCodesArePreserved(): void
    {
        self::assertSame(
            ['ROLE_DEV', 'ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_USER'],
            [RoleCodes::DEV, RoleCodes::SUPER_ADMIN, RoleCodes::ADMIN, RoleCodes::USER],
        );
    }
}
