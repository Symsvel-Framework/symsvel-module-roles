<?php

declare(strict_types=1);

namespace SymSvel\Module\Roles\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class RolesPingController
{
    #[Route('/api/roles/ping', name: 'symsvel_roles_ping', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse(['module' => 'roles', 'status' => 'ok']);
    }
}
