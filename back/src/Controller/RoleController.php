<?php

declare(strict_types=1);

namespace SymSvel\Module\Roles\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use SymSvel\Module\Acls\Access\BoAccessGate;
use SymSvel\Module\Acls\AclAuthorizer;
use SymSvel\Module\Acls\Permission\RolePermissionManager;
use SymSvel\Module\Roles\Entity\Role;
use SymSvel\Module\Roles\Repository\RoleRepository;
use SymSvel\Module\Roles\RoleManager;

/** BO role administration. Creation of native roles and deletion of assigned roles are forbidden. */
final readonly class RoleController
{
    public function __construct(
        private BoAccessGate $gate,
        private AclAuthorizer $acls,
        private RoleRepository $roles,
        private RoleManager $manager,
        private RolePermissionManager $grants,
        private Connection $db,
    ) {}

    #[Route('/apibo/roles', name: 'symsvel_roles_bo_list', methods: ['GET'], format: 'json')]
    public function list(Request $request): JsonResponse
    {
        if (($error = $this->check($request, 'list')) !== null) return $error;
        return self::reply(['data' => array_map(
            fn (Role $role): array => $this->summary($role),
            $this->roles->findBy([], ['label' => 'ASC']),
        )]);
    }

    #[Route('/apibo/roles', name: 'symsvel_roles_bo_create', methods: ['POST'], format: 'json')]
    public function create(Request $request): JsonResponse
    {
        if (($error = $this->check($request, 'create')) !== null) return $error;
        $data = self::payload($request);
        if ($data instanceof JsonResponse) return $data;
        if (array_diff(array_keys($data), ['code', 'label', 'permissions'])
            || !is_string($data['code'] ?? null)
            || !is_string($data['label'] ?? null)
            || (isset($data['permissions']) && (!is_array($data['permissions']) || !array_is_list($data['permissions'])))) {
            return self::failure(400);
        }
        if (array_key_exists('permissions', $data)
            && !$this->canGrant($request)) return self::failure(403);

        try {
            $role = $this->db->transactional(function () use ($data): Role {
                $role = $this->manager->create($data['code'], $data['label']);
                if (array_key_exists('permissions', $data)) {
                    $this->grants->replace($role->getCode(), $data['permissions']);
                }
                return $role;
            });
        } catch (\InvalidArgumentException) {
            return self::failure(400);
        } catch (UniqueConstraintViolationException|\DomainException) {
            return self::failure(409);
        }
        return self::reply(['data' => $this->summary($role)], 201);
    }

    #[Route('/apibo/roles/{id<\d+>}', name: 'symsvel_roles_bo_update', methods: ['PATCH'], format: 'json')]
    public function update(Request $request, int $id): JsonResponse
    {
        if (($error = $this->check($request, 'update')) !== null) return $error;
        $data = self::payload($request);
        if ($data instanceof JsonResponse) return $data;
        if ($data === [] || array_diff(array_keys($data), ['label', 'permissions'])
            || (isset($data['label']) && !is_string($data['label']))
            || (array_key_exists('permissions', $data) &&
                (!is_array($data['permissions']) || !array_is_list($data['permissions'])))) {
            return self::failure(400);
        }
        $role = $id > 0 ? $this->roles->find($id) : null;
        if (!$role instanceof Role) return self::failure(404);
        if (array_key_exists('permissions', $data) && !$this->canGrant($request, $role->getCode())) {
            return self::failure(403);
        }
        try {
            $this->db->transactional(function () use ($data, $role): void {
                if (array_key_exists('label', $data)) $this->manager->rename($role, $data['label']);
                if (array_key_exists('permissions', $data)) $this->grants->replace($role->getCode(), $data['permissions']);
            });
        } catch (\InvalidArgumentException) {
            return self::failure(400);
        } catch (\DomainException) {
            return self::failure(409);
        }
        return self::reply(['data' => $this->summary($role)]);
    }

    #[Route('/apibo/roles/{id<\d+>}', name: 'symsvel_roles_bo_delete', methods: ['DELETE'], format: 'json')]
    public function delete(Request $request, int $id): JsonResponse
    {
        if (($error = $this->check($request, 'delete')) !== null) return $error;
        $role = $id > 0 ? $this->roles->find($id) : null;
        if (!$role instanceof Role) return self::failure(404);
        if ($role->isSystem()) return self::failure(409);
        // Never break a persisted account's role or an invitation in progress.
        if ($this->db->fetchOne('SELECT id FROM symsvel_admins WHERE role_code = ? LIMIT 1', [$role->getCode()]) !== false) {
            return self::failure(409);
        }
        try {
            $this->db->transactional(function () use ($role): void {
                $this->db->delete('symsvel_acl_rules', ['role_code' => $role->getCode()]);
                // Remove dynamic grant permissions that target the deleted role.
                $this->db->delete('symsvel_acl_rules', ['resource' => 'roles', 'action' => 'assign:'.$role->getCode()]);
                $this->manager->remove($role);
            });
        } catch (\DomainException) {
            return self::failure(409);
        }
        return self::reply(['status' => 'deleted']);
    }

    private function check(Request $request, string $action): ?JsonResponse
    {
        $decision = $this->gate->check($request, 'roles', $action);
        return $decision->allowed() ? null : self::failure($decision->status);
    }

    private function canGrant(Request $request, ?string $targetCode = null): bool
    {
        $decision = $this->gate->check($request, 'acls', 'manage');
        return $decision->allowed()
            && ($targetCode === null || $targetCode !== $decision->roleCode || $targetCode === 'ROLE_DEV')
            && $this->acls->allows((string) $decision->roleCode, 'acls', 'manage', true);
    }

    /** @return array<string,mixed> */
    private function summary(Role $role): array
    {
        return [
            'id' => $role->getId(), 'code' => $role->getCode(),
            'label' => $role->getLabel(), 'native' => $role->isSystem(),
            'permissions' => $this->grants->granted($role->getCode()),
        ];
    }

    /** @return array<string,mixed>|JsonResponse */
    private static function payload(Request $request): array|JsonResponse
    {
        if (!str_starts_with(strtolower($request->headers->get('Content-Type', '')), 'application/json')) return self::failure(415);
        $raw = $request->getContent();
        if (strlen($raw) > 16384) return self::failure(400);
        try {
            $body = json_decode($raw, true, 8, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return self::failure(400);
        }
        return is_array($body) && !array_is_list($body) ? $body : self::failure(400);
    }

    private static function failure(int $status): JsonResponse
    {
        return self::reply(['error' => ['code' => match ($status) {
            400 => 'invalid_request', 401 => 'unauthorized', 403 => 'forbidden',
            404 => 'not_found', 409 => 'conflict', 415 => 'unsupported_media_type',
            default => 'unavailable',
        }]], $status);
    }

    private static function reply(array $data, int $status = 200): JsonResponse
    {
        $response = new JsonResponse($data, $status);
        $response->headers->set('Cache-Control', 'no-store');
        $response->headers->set('Pragma', 'no-cache');
        return $response;
    }
}
