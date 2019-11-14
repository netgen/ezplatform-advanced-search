<?php

declare(strict_types=1);

namespace Netgen\Bundle\eZPlatformAdvancedSearchBundle;

use Netgen\Bundle\eZPlatformAdvancedSearchBundle\DependencyInjection\Compiler\ItemFilterMapperRegistryPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NetgeneZPlatformAdvancedSearchBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new ItemFilterMapperRegistryPass());
    }
}
