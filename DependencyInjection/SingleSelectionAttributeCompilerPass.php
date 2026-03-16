<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\DependencyInjection;

use FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\Selection\SuluMultiSelection;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\Selection\SuluSingleSelection;
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
                $config['single_selection'] = $this->configureAttribute($attribute->newInstance());
            }

            foreach ((new \ReflectionClass($class))->getAttributes(SuluMultiSelection::class) as $attribute) {
                $config['selection'] = $this->configureAttribute($attribute->newInstance());
            }
        }

        $container->prependExtensionConfig('sulu_admin', [
            'field_type_options' => $config,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function configureAttribute(SuluSingleSelection|SuluMultiSelection $attribute): array
    {
        $config = [
            $attribute->name => [
                'default_type' => $attribute->defaultType,
                'resource_key' => $attribute->resourceKey,
                'types' => $attribute->getTypes(),
            ],
        ];
        if (($attribute->views ?? null) !== null) {
            $config[$attribute->name]['view'] = $attribute->views;
        }

        return $config;
    }
}
