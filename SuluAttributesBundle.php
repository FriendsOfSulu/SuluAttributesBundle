<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle;

use FriendsOfSulu\Bundle\SuluAttributesBundle\AttributeListProvider\AttributeFieldDescriptorFactory;
use FriendsOfSulu\Bundle\SuluAttributesBundle\DependencyInjection\AdminAttributeCompilerPass;
use FriendsOfSulu\Bundle\SuluAttributesBundle\DependencyInjection\SingleSelectionAttributeCompilerPass;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\AsForm;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\AttributeFormMetadataFactory;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\AttributeFormMetadataLoader;
use FriendsOfSulu\Bundle\SuluAttributesBundle\SuluOverrides\NavigationAdmin;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SuluAttributesBundle extends AbstractBundle
{
    /**
     * Service tag applied to every class marked with #[AsForm].
     *
     * @internal
     */
    public const TAG_FORM = 'sulu_attributes.form';

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AdminAttributeCompilerPass());
        $container->addCompilerPass(new SingleSelectionAttributeCompilerPass());

        // Any class carrying #[AsForm] is auto-tagged so the
        // AttributeFormMetadataLoader receives it via a tagged iterator.
        // Doing this in the bundle (instead of the app kernel) keeps the
        // feature self-contained — the consuming project needs no wiring.
        $container->registerAttributeForAutoconfiguration(
            AsForm::class,
            static function(ChildDefinition $definition): void {
                $definition->addTag(self::TAG_FORM);
            },
        );
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
                new TaggedIteratorArgument('sulu.admin', defaultPriorityMethod: 'getPriority', excludeSelf: true),
            ])
            ->tag('sulu.admin')
        ;

        // --- Attribute-based Sulu forms ----------------------------------------
        // Builds forms defined as PHP classes (#[AsForm]) and writes them into
        // the very same cache directory as the XML form loader, hooking into the
        // same `sulu_admin.form_metadata_loader` pipeline.
        $services->set(AttributeFormMetadataFactory::class)
            ->args([
                new Reference('sulu_admin.schema_metadata_provider'),
            ])
        ;

        $services->set(AttributeFormMetadataLoader::class)
            ->args([
                new TaggedIteratorArgument(self::TAG_FORM),
                new Reference(AttributeFormMetadataFactory::class),
                new Reference('sulu_admin.field_metadata_validator.chain'),
                '%sulu.cache_dir%/forms',
                '%kernel.debug%',
            ])
            ->tag('sulu_admin.form_metadata_loader', ['priority' => -128])
            ->tag('kernel.cache_warmer')
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
