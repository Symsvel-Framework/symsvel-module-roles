<?php

declare(strict_types=1);

namespace SymSvel\Module\Roles;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use SymSvel\Module\Roles\Controller\RolesPingController;
use SymSvel\Module\Roles\Controller\RoleController;
use SymSvel\Module\Roles\Repository\RoleRepository;

final class RolesBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->register(RolesPingController::class, RolesPingController::class)
            ->setAutowired(true)
            ->addTag('controller.service_arguments');
        $builder->register(RolePolicy::class, RolePolicy::class);
        $builder->register(RoleRepository::class, RoleRepository::class)
            ->setAutowired(true);
        $builder->register(RoleController::class, RoleController::class)
            ->setAutowired(true)
            ->addTag('controller.service_arguments');
        $builder->register(RoleManager::class, RoleManager::class)
            ->setAutowired(true)
            ->setArgument('$policy', new Reference(RolePolicy::class));
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->extension('doctrine', [
            'orm' => [
                'mappings' => [
                    'SymSvelRoles' => [
                        'type' => 'attribute',
                        'is_bundle' => false,
                        'dir' => __DIR__.'/Entity',
                        'prefix' => 'SymSvel\\Module\\Roles\\Entity',
                        'alias' => 'SymSvelRoles',
                    ],
                ],
            ],
        ]);
    }
}
