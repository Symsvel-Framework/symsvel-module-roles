<?php

declare(strict_types=1);

namespace SymSvel\Module\Roles\Tests\Integration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use SymSvel\Module\Acls\Access\BoAccessDecision;
use SymSvel\Module\Acls\Access\BoAccessGate;
use SymSvel\Module\Acls\AclAuthorizer;
use SymSvel\Module\Acls\Permission\NativeAclPermissionProvider;
use SymSvel\Module\Acls\Permission\PermissionCatalog;
use SymSvel\Module\Acls\Permission\RolePermissionManager;
use SymSvel\Module\Roles\Controller\RoleController;
use SymSvel\Module\Roles\Entity\Role;
use SymSvel\Module\Roles\Permission\RolePermissionProvider;
use SymSvel\Module\Roles\Repository\RoleRepository;
use SymSvel\Module\Roles\RoleManager;

final class RoleAdministrationHttpTest extends TestCase
{
    public function testBoCrudProtectsNativeRolesAndAssignedRoles(): void
    {
        [$controller, $db, $roles] = $this->fixture();
        $payload = ['code' => 'ROLE_EDITOR', 'label' => 'Éditeur', 'permissions' => ['roles.list']];
        self::assertSame(401, $controller->list(Request::create('/apibo/roles'))->getStatusCode());
        $created = $controller->create(self::request('POST', '/apibo/roles', $payload));
        self::assertSame(201, $created->getStatusCode());
        $body = json_decode((string) $created->getContent(), true, 8, JSON_THROW_ON_ERROR);
        $id = $body['data']['id'];
        self::assertSame('ROLE_EDITOR', $body['data']['code']);
        self::assertSame(['roles.list'], $body['data']['permissions']);
        self::assertTrue((new AclAuthorizer($db))->allows('ROLE_EDITOR', 'roles', 'list'));
        self::assertNotNull($roles->find($id));

        $modified = $controller->update(self::request('PATCH', '/apibo/roles/'.$id, [
            'label' => 'Édition', 'permissions' => ['roles.update'],
        ]), $id);
        self::assertSame(200, $modified->getStatusCode());
        self::assertFalse((new AclAuthorizer($db))->allows('ROLE_EDITOR', 'roles', 'list'));
        self::assertTrue((new AclAuthorizer($db))->allows('ROLE_EDITOR', 'roles', 'update'));

        $db->insert('symsvel_admins', ['role_code' => 'ROLE_EDITOR']);
        self::assertSame(409, $controller->delete(self::request('DELETE', '/apibo/roles/'.$id), $id)->getStatusCode());
        $db->delete('symsvel_admins', ['role_code' => 'ROLE_EDITOR']);
        self::assertSame(200, $controller->delete(self::request('DELETE', '/apibo/roles/'.$id), $id)->getStatusCode());
        self::assertFalse($db->fetchOne('SELECT code FROM symsvel_roles WHERE code = ?', ['ROLE_EDITOR']));
        self::assertSame([], $db->fetchAllAssociative('SELECT * FROM symsvel_acl_rules WHERE role_code = ?', ['ROLE_EDITOR']));

        $native = $roles->findOneBy(['code' => 'ROLE_ADMIN']);
        self::assertSame(409, $controller->update(self::request('PATCH', '/apibo/roles/'.$native->getId(), [
            'label' => 'Modified',
        ]), (int) $native->getId())->getStatusCode());
        self::assertSame(409, $controller->create(self::request('POST', '/apibo/roles', [
            'code' => 'ROLE_DEV', 'label' => 'Another dev',
        ]))->getStatusCode());
    }

    public function testInvalidPermissionDoesNotLeaveOrphanRole(): void
    {
        [$controller, $db] = $this->fixture();
        $response = $controller->create(self::request('POST', '/apibo/roles', [
            'code' => 'ROLE_ORPHAN', 'label' => 'Orphan', 'permissions' => ['does.not.exist'],
        ]));
        self::assertSame(400, $response->getStatusCode());
        self::assertFalse($db->fetchOne('SELECT code FROM symsvel_roles WHERE code = ?', ['ROLE_ORPHAN']));
    }

    /** @return array{RoleController, Connection, RoleRepository} */
    private function fixture(): array
    {
        if (!extension_loaded('pdo_sqlite')) self::markTestSkipped('pdo_sqlite required.');
        $db = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $config = ORMSetup::createAttributeMetadataConfiguration(
            [dirname((new \ReflectionClass(Role::class))->getFileName())], true,
        );
        $config->enableNativeLazyObjects(true);
        $em = new EntityManager($db, $config);
        (new SchemaTool($em))->createSchema([$em->getClassMetadata(Role::class)]);
        $db->executeStatement('CREATE TABLE symsvel_acl_rules (id INTEGER PRIMARY KEY AUTOINCREMENT, resource VARCHAR(150), action VARCHAR(100), role_code VARCHAR(100), allowed BOOLEAN, UNIQUE(resource, action, role_code))');
        $db->executeStatement('CREATE TABLE symsvel_admins (id INTEGER PRIMARY KEY AUTOINCREMENT, role_code VARCHAR(100))');
        $em->persist(new Role('ROLE_ADMIN', 'Administrateur', true));
        $em->flush();

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->with(Role::class)->willReturn($em);
        $roles = new RoleRepository($registry);
        $catalog = new PermissionCatalog([
            new NativeAclPermissionProvider(),
            new RolePermissionProvider($roles),
        ]);
        $gate = new class implements BoAccessGate {
            public function check(Request $request, string $resource, string $action): BoAccessDecision
            {
                return $request->headers->has('Authorization')
                    ? new BoAccessDecision(200, 1, 'ROLE_DEV')
                    : new BoAccessDecision(401);
            }
        };
        $controller = new RoleController(
            $gate, new AclAuthorizer($db), $roles, new RoleManager($em),
            new RolePermissionManager($db, $catalog), $db,
        );
        return [$controller, $db, $roles];
    }

    private static function request(string $method, string $url, array $payload = []): Request
    {
        return Request::create($url, $method, [], [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer test-token',
            'CONTENT_TYPE' => 'application/json',
        ], $method === 'DELETE' ? '' : json_encode($payload, JSON_THROW_ON_ERROR));
    }
}
