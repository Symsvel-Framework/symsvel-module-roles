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
        if ($code === RoleCodes::DEV) {
            throw new \DomainException('ROLE_DEV is a protected system role.');
        }
    }
}
