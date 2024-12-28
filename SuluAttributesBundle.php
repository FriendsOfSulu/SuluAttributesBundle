<?php declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle;

use FriendsOfSulu\Bundle\SuluAttributesBundle\DependencyInjection\AdminAttributeCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SuluAttributesBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AdminAttributeCompilerPass());
    }
}
