<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle;

use FriendsOfSulu\Bundle\SuluAttributesBundle\AttributeListProvider\AttributeFieldDescriptorFactory;
use FriendsOfSulu\Bundle\SuluAttributesBundle\DependencyInjection\AdminAttributeCompilerPass;
use FriendsOfSulu\Bundle\SuluAttributesBundle\SuluOverrides\NavigationAdmin;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SuluAttributesBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AdminAttributeCompilerPass());
    }

    /**
     * @param array<string, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $services = $container->services();

        $services->set(NavigationAdmin::class)
            ->args([
                new Reference('sulu_security.security_checker'),
            ])
            ->tag('sulu.admin')
            ->call('setAdminPool', [new Reference('sulu_admin.admin_pool')])
        ;

        $services->set(AttributeFieldDescriptorFactory::class)
            ->decorate('sulu_core.list_builder.field_descriptor_factory')
            ->args([
                new Reference(AttributeFieldDescriptorFactory::class.'.inner'),
            ])
        ;

//services:
  //friends_of_sulu.sulu_attributes.attribute_field_descriptor_factory:
      //class: FriendsOfSulu\Bundle\SuluAttributesBundle\AttributeListProvider\AttributeFieldDescriptorFactory
      //decorates: sulu_core.list_builder.field_descriptor_factory
      //arguments:
          //- '@friends_of_sulu.sulu_attributes.attribute_field_descriptor_factory.inner'

    }
}
