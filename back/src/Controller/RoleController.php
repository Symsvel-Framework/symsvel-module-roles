<?php

declare(strict_types=1);

namespace SymSvel\Module\Roles\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use SymSvel\Module\Roles\Repository\RoleRepository;

#[Route('/apibo/roles', name: 'apibo_roles_')]
#[IsGranted('ROLE_ADMIN')]
final class RoleController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(RoleRepository $roles): JsonResponse
    {
        return new JsonResponse([
            'data' => array_map(
                static fn ($role): array => [
                    'id' => $role->getId(),
                    'code' => $role->getCode(),
                    'label' => $role->getLabel(),
                    'native' => $role->isSystem(),
                ],
                $roles->findBy([], ['label' => 'ASC']),
            ),
        ]);
    }
}
