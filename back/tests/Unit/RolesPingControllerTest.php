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
        $arguments = $routes[0]->getArguments();
        self::assertSame('/api/roles/ping', $arguments[0] ?? $arguments['path'] ?? null);
        self::assertSame(['GET'], $arguments['methods'] ?? null);
    }
}
