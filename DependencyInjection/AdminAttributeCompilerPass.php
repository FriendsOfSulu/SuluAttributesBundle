<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\DependencyInjection;

use FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\SuluResourceRoute;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\SuluResourceRoutes;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\Annotation\Route;

class AdminAttributeCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $this->registerControllerAttributes($container);
        $this->registerAdminClassAttributes($container);
    }

    private function registerControllerAttributes(ContainerBuilder $container): void
    {
        // TODO: Find a better way to check for methods that need to be processed
        foreach (array_keys($container->findTaggedServiceIds('controller.service_arguments')) as $id) {
            $classReflection = $this->getClassReflectionFromId($container, $id);
            $reflectionMethods = $classReflection?->getMethods() ?? [];
            foreach ($reflectionMethods as $method) {
                foreach ($method->getAttributes(SuluResourceRoute::class) as $attribute) {
                    $routeAttributes = $method->getAttributes(Route::class);
                    if ($routeAttributes === []){
                        throw new RuntimeException('Can not use '.SuluResourceRoute::class.' attribute without route annotation on class '.$classReflection->getName().'::'.$method->getName());
                    }
                    $route = $routeAttributes[0]->newInstance();
                    $configuration = $attribute->newInstance();

                    $container->prependExtensionConfig('sulu_admin', [
                        'resources' => [
                            $configuration->resourceKey => [
                                'routes' => [
                                    $configuration->type => $route->getName(),
                                ]
                            ]
                        ]
                    ]);
                }
            }
        }
    }

    private function registerAdminClassAttributes(ContainerBuilder $container): void
    {
        foreach (array_keys($container->findTaggedServiceIds('sulu.admin')) as $id) {
            $reflection = $this->getClassReflectionFromId($container, $id);

            foreach ($reflection?->getAttributes(SuluResourceRoutes::class) ?? [] as $attribute) {
                $configuration = $attribute->newInstance();
                $container->prependExtensionConfig('sulu_admin', [
                    'resources' => [
                        $configuration->resourceKey => ['routes' => $configuration->routes]
                    ]
                ]);
            }
        }
    }

    private function getClassReflectionFromId(ContainerBuilder $container, string $id): ?ReflectionClass
    {
        $className = $container->getDefinition($id)->getClass();
        if($className === null) {
            return null;
        }

        // Sulu does that, we should stop that and use proper decorators
        if(str_starts_with($className,'%')) {
            $className = $container->getParameter(trim($className, '%'));
        }
        return new ReflectionClass($className);
    }
}
