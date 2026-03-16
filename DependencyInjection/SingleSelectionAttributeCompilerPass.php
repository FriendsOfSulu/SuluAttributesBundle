<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\DependencyInjection;

use FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\SuluSingleSelection;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\SuluMultiSelection;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SingleSelectionAttributeCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $config = [];
        foreach (\array_keys($container->findTaggedServiceIds('sulu_content.property_resolver')) as $service) {
            /** @var class-string $class */
            $class = $container->getDefinition($service)->getClass();

            foreach ((new \ReflectionClass($class))->getAttributes(SuluSingleSelection::class) as $attribute) {
                $attributeInstance = $attribute->newInstance();
                $config['single_selection'][$attributeInstance->name] = $attributeInstance->config;
            }

            foreach ((new \ReflectionClass($class))->getAttributes(SuluMultiSelection::class) as $attribute) {
                $attributeInstance = $attribute->newInstance();
                $config['selection'][$attributeInstance->name] = $attributeInstance->config;
            }
        }

        $container->prependExtensionConfig('sulu_admin', [
            'field_type_options' => $config,
        ]);
    }
}
