<?php

declare(strict_types=1);

namespace SymSvel\Module\Roles\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SymSvel\Module\Roles\RoleCodes;
use SymSvel\Module\Roles\RolePolicy;

final class NativeRoleProtectionTest extends TestCase
{
    public static function nativeCodes(): iterable
    {
        yield [RoleCodes::DEV];
        yield [RoleCodes::SUPER_ADMIN];
        yield [RoleCodes::ADMIN];
        yield [RoleCodes::USER];
    }

    #[DataProvider('nativeCodes')]
    public function testNativeRoleCodeIsProtected(string $code): void
    {
        $this->expectException(\DomainException::class);
        (new RolePolicy())->assertMutable($code);
    }
}
