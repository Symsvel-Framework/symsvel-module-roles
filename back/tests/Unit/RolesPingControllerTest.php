<?php

declare(strict_types=1);

namespace SymSvel\Module\Roles\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Attribute\Route;
use SymSvel\Module\Roles\Controller\RolesPingController;

final class RolesPingControllerTest extends TestCase
{
    public function testPingResponseAndRouteContract(): void
    {
        $controller = new RolesPingController();
        self::assertSame(['module' => 'roles', 'status' => 'ok'], json_decode($controller()->getContent(), true, 512, JSON_THROW_ON_ERROR));
        $routes = (new \ReflectionMethod(RolesPingController::class, '__invoke'))->getAttributes(Route::class);
        self::assertCount(1, $routes);
        $route = $routes[0]->newInstance();
        self::assertSame('/api/roles/ping', $route->getPath());
        self::assertSame(['GET'], $route->getMethods());
    }
}
