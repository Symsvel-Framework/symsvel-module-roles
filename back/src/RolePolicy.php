<?php

declare(strict_types=1);

namespace SymSvel\Module\Roles;

final class RolePolicy
{
    public function isDeveloper(array $roles): bool
    {
        return in_array(RoleCodes::DEV, $roles, true);
    }

    public function assertMutable(string $code): void
    {
        if (in_array($code, [RoleCodes::DEV, RoleCodes::SUPER_ADMIN, RoleCodes::ADMIN, RoleCodes::USER], true)) {
            throw new \DomainException('Native role codes are protected.');
        }
    }
}
